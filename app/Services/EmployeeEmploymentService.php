<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeEmploymentService
{
    public const SUSPENSION_ABSENCE_DESCRIPTION = 'غياب تلقائي بسبب إيقاف الموظف';

    public function suspend(Employee $employee, ?Carbon $at = null): void
    {
        $at ??= now();

        DB::transaction(function () use ($employee, $at) {
            $employee->update([
                'status' => 'suspended',
                'suspended_at' => $at,
            ]);

            if ($employee->accountant && $employee->accountant->status === 'active') {
                $employee->accountant->update(['status' => 'suspended']);
            }
        });
    }

    public function activate(Employee $employee, ?Carbon $at = null, ?int $addedBy = null): int
    {
        $at ??= now();
        $absenceCount = 0;

        DB::transaction(function () use ($employee, $at, $addedBy, &$absenceCount) {
            $suspendedAt = $employee->suspended_at ? Carbon::parse($employee->suspended_at) : null;

            if ($suspendedAt) {
                $date = $suspendedAt->copy()->startOfDay()->addDay();
                $lastAbsentDay = $at->copy()->startOfDay()->subDay();

                while ($date->lte($lastAbsentDay)) {
                    $daysInMonth = $date->daysInMonth;
                    $dailyWage = $daysInMonth > 0 ? round((float) $employee->salary / $daysInMonth, 2) : 0;

                    $absence = $employee->absences()->firstOrNew(['date' => $date->toDateString()]);
                    $wasNew = !$absence->exists;
                    $absence->fill([
                        'store_id' => $employee->store_id,
                        'person_id' => $employee->id,
                        'person_type' => Employee::class,
                        'penalty_amount' => max((float) ($absence->penalty_amount ?? 0), $dailyWage),
                        'status' => $absence->status ?: 'pending',
                        'month' => $date->format('Y-m'),
                        'added_by' => $absence->added_by ?: ($addedBy ?? $employee->user_id ?? $employee->store?->user_id),
                        'description' => $absence->description ?: self::SUSPENSION_ABSENCE_DESCRIPTION,
                        'created_at' => $absence->created_at ?: $date->copy()->setTimeFrom($at),
                    ]);
                    $absence->save();

                    if ($wasNew) {
                        $absenceCount++;
                    }

                    $date->addDay();
                }
            }

            $employee->update([
                'status' => 'active',
                'suspended_at' => null,
            ]);

            if ($employee->accountant && $employee->accountant->status !== 'active') {
                $employee->accountant->update(['status' => 'active']);
            }
        });

        return $absenceCount;
    }

    public function earnedSalaryForMonth(Employee $employee, Carbon $month): float
    {
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        $salary = (float) $employee->salary;

        $entitledSalary = $salary;
        $deductionEnd = $monthEnd;

        if ($employee->status === 'suspended' && $employee->suspended_at) {
            $suspendedAt = Carbon::parse($employee->suspended_at);

            if ($suspendedAt->lt($monthStart)) {
                return 0.0;
            }

            if ($suspendedAt->betweenIncluded($monthStart, $monthEnd)) {
                // يوم الإيقاف نفسه يوم مستحق، ويبدأ توقف الراتب من اليوم التالي.
                $paidDays = $monthStart->diffInDays($suspendedAt->copy()->startOfDay()) + 1;
                $entitledSalary = ($salary / $monthStart->daysInMonth) * $paidDays;
                $deductionEnd = $suspendedAt;
            }
        }

        $absenceDeductions = (float) $employee->absences()
            ->whereBetween('date', [$monthStart->toDateString(), $deductionEnd->toDateString()])
            ->sum('penalty_amount');

        return max(0, round($entitledSalary - $absenceDeductions, 2));
    }
}
