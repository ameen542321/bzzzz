<?php

namespace App\Http\Controllers\Employees;

use App\Support\ArabicPdf as PDF;
use App\Models\Debt;
use App\Traits\FindPersonTrait;
use App\Services\EmployeeLogService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Employee;

/**
 * --------------------------------------------------------------------------
 * EmployeeReports
 * --------------------------------------------------------------------------
 * هذا الملف مسؤول عن:
 * - تصدير تقارير PDF الخاصة بالموظف أو المحاسب
 * - يعتمد على FindPersonTrait لتحديد نوع الشخص (موظف / محاسب)
 * --------------------------------------------------------------------------
 */
class EmployeeReports
{
    use FindPersonTrait;

    /**
     * ----------------------------------------------------------------------
     * تصدير تقرير PDF باستخدام mPDF الداعم للعربية
     * ----------------------------------------------------------------------
     * - يقوم بجلب بيانات الشخص (موظف أو محاسب)
     * - يجمع جميع العمليات المرتبطة به
     * - ينشئ ملف PDF جاهز للتحميل
     * ----------------------------------------------------------------------
     */
    public static function exportPdf($id)
    {
        // إنشاء نسخة من الكلاس لاستخدام الـ trait
        $self = new self();

        // جلب الموظف أو المحاسب
        $person = $self->findPerson($id);

        // الشهر المستهدف:
        // - إذا كان التصدير يوم 1 أو 2 أو 3: نعتمد الشهر السابق.
        // - فيما عدا ذلك: نعتمد الشهر الحالي.
        $reportMonth = Carbon::now()->day <= 3
            ? Carbon::now()->subMonthNoOverflow()
            : Carbon::now();
        $reportMonthKey = $reportMonth->format('Y-m');
        $monthStart = $reportMonth->copy()->startOfMonth()->toDateString();
        $monthEnd = $reportMonth->copy()->endOfMonth()->toDateString();

        // حسابات المديونية
        $remainingDebt = $person->debts()->sum('amount');

        // نعتمد على التاريخ الفعلي للعملية (date) مع fallback على month لضمان عدم ضياع السجلات القديمة
        $debtOperations = $person->debts()
            ->where(function ($query) use ($monthStart, $monthEnd, $reportMonthKey) {
                $query->whereBetween('date', [$monthStart, $monthEnd])
                    ->orWhere('month', $reportMonthKey);
            })
            ->orderBy('date')
            ->get();

        $collectedThisMonth = $debtOperations->where('amount', '<', 0)->sum('amount');
        $addedThisMonth = $debtOperations->where('amount', '>', 0)->sum('amount');

        $withdrawals = $person->withdrawals()
            ->where(function ($query) use ($monthStart, $monthEnd, $reportMonthKey) {
                $query->whereBetween('date', [$monthStart, $monthEnd])
                    ->orWhere('month', $reportMonthKey);
            })
            ->orderBy('date')
            ->get();

        $absences = $person->absences()
            ->where(function ($query) use ($monthStart, $monthEnd, $reportMonthKey) {
                $query->whereBetween('date', [$monthStart, $monthEnd])
                    ->orWhere('month', $reportMonthKey);
            })
            ->with('addedBy')
            ->orderBy('date')
            ->get();

        $creditSalesPending = $person->creditSales()
            ->where(function ($query) use ($monthStart, $monthEnd, $reportMonthKey) {
                $query->whereBetween('date', [$monthStart, $monthEnd])
                    ->orWhere('month', $reportMonthKey);
            })
            ->where('status', 'pending')
            ->get();

        $creditSalesCollected = $person->creditSales()
            ->where('status', 'deducted')
            ->where(function ($query) use ($monthStart, $monthEnd, $reportMonthKey) {
                $query->where('deducted_month', $reportMonthKey)
                    ->orWhereBetween('date', [$monthStart, $monthEnd]);
            })
            ->with('addedBy')
            ->get();

        // تجهيز البيانات للعرض داخل الـ PDF
        $data = [
            'person'               => $person,
            'report_month'         => $reportMonthKey,
            'withdrawals'          => $withdrawals,
            'absences'             => $absences,
            'absences_count'       => $absences->count(),
            'debts'                => $debtOperations, // العمليات كاملة
            'remainingDebt'        => $remainingDebt,  // الرصيد النهائي
            'addedThisMonth'       => $addedThisMonth, // مجموع الإضافات
            'collectedThisMonth'   => abs($collectedThisMonth), // التحصيل الشهري (موجب)
            'creditSalesPending'   => $creditSalesPending,
            'creditSalesCollected' => $creditSalesCollected,
            'created_by'           => auth()->user(),
        ];

        // تسجيل عملية التصدير
        EmployeeLogService::add(
            $person,
            'report_exported',
            "تم تصدير تقرير PDF للموظف/المحاسب {$person->name} لشهر {$reportMonthKey}"
        );

        // إنشاء ملف PDF
        $pdf = PDF::loadView('pdf.employee-pdf', $data)
            ->setPaper('a4')
            ->setOption('encoding', 'UTF-8');

        // بعد التصدير:
        // - تصفير السحب والغياب
        // - حذف العمليات المحصّلة فقط (المديونيات والآجل المحصّل)
        // - الإبقاء على غير المحصّل كما هو
        $resetCounts = [
            'withdrawals' => 0,
            'absences' => 0,
            'debts' => 0,
            'credit_collections' => 0,
        ];

        DB::transaction(function () use ($person, $reportMonthKey, $monthStart, $monthEnd, &$resetCounts) {
            $resetCounts['withdrawals'] = $person->withdrawals()
                ->where(function ($query) use ($monthStart, $monthEnd, $reportMonthKey) {
                    // حذف سحوبات الشهر المستهدف + أي سحوبات قديمة ما زالت pending من أشهر سابقة
                    $query->where(function ($targetMonth) use ($monthStart, $monthEnd, $reportMonthKey) {
                        $targetMonth->whereBetween('date', [$monthStart, $monthEnd])
                            ->orWhere('month', $reportMonthKey);
                    })->orWhere(function ($oldPending) use ($monthStart, $reportMonthKey) {
                        $oldPending->where('status', 'pending')
                            ->where(function ($oldScope) use ($monthStart, $reportMonthKey) {
                                $oldScope->whereDate('date', '<', $monthStart)
                                    ->orWhere('month', '<', $reportMonthKey);
                            });
                    });
                })
                ->delete();

            $resetCounts['absences'] = $person->absences()
                ->where(function ($query) use ($monthStart, $monthEnd, $reportMonthKey) {
                    $query->whereBetween('date', [$monthStart, $monthEnd])
                        ->orWhere('month', $reportMonthKey);
                })
                ->delete();

            $resetCounts['debts'] = $person->debts()
                ->where(function ($query) use ($monthStart, $monthEnd, $reportMonthKey) {
                    $query->whereBetween('date', [$monthStart, $monthEnd])
                        ->orWhere('month', $reportMonthKey);
                })
                // لا نحذف إلا المديونية المحصّلة/المسددة
                // ونُبقي المديونية غير المحصّلة كما هي.
                ->where(function ($query) {
                    $query->where('status', 'deducted')
                        ->orWhere('amount', '<', 0);
                })
                ->delete();

            // الآجل: تصفير المحصّل فقط
            $resetCounts['credit_collections'] = $person->creditSales()
                ->where(function ($query) use ($monthStart, $monthEnd, $reportMonthKey) {
                    $query->where('deducted_month', $reportMonthKey)
                        ->orWhereBetween('date', [$monthStart, $monthEnd]);
                })
                ->where('status', 'deducted')
                ->delete();

            $logsQuery = $person->logs();

            if (Schema::hasColumn('employee_logs', 'logged_at')) {
                $logsQuery->where(function ($query) use ($monthStart, $monthEnd) {
                    $query->whereBetween('logged_at', [$monthStart, $monthEnd])
                        ->orWhereBetween('created_at', [$monthStart, $monthEnd]);
                });
            } else {
                $logsQuery->whereBetween('created_at', [$monthStart, $monthEnd]);
            }

            $logsQuery->delete();
        });

        session()->flash(
            'success',
            "تم تصدير التقرير وتصفير البيانات بنجاح (السحوبات: {$resetCounts['withdrawals']}، الغياب: {$resetCounts['absences']}، المديونيات: {$resetCounts['debts']}، الآجل المحصّل: {$resetCounts['credit_collections']})."
        );

        return $pdf->download("تقرير {$reportMonthKey} - {$person->name}.pdf");
    }

    /**
     * 2026-05-16: دالة توافق مؤقتة لأي استدعاء قديم ما زال يستخدم اسم exportSnappy.
     */
    public static function exportSnappy($id)
    {
        return self::exportPdf($id);
    }
}
