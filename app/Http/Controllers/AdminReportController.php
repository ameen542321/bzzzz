<?php

namespace App\Http\Controllers;

use App\Support\ArabicPdf as PDF;
use App\Models\Debt;
use App\Models\User;
use App\Models\Absence;
use App\Models\Employee;
use App\Models\CreditSale;
use App\Models\Withdrawal;
use App\Models\EmployeeLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminReportController extends Controller
{
   public function sendAllReports()
{
    $users = User::withoutTrashed()->get();

    foreach ($users as $user) {

        // حماية: المستخدم بدون متاجر
        if (!$user->stores()->exists()) {
            continue;
        }

        // متاجر المستخدم
        $storeIds = $user->stores()->pluck('id');

        // موظفي متاجر المستخدم فقط
        $employees = Employee::whereIn('store_id', $storeIds)->get();

        foreach ($employees as $employee) {

            try {

                $data = [
                    'employee' => $employee,
                    'withdrawals' => $employee->withdrawals,
                    'absences' => $employee->absences,
                    'debts' => $employee->debts,
                    'creditSalesPending' => $employee->creditSales()->where('status', 'pending')->get(),
                    'creditSalesCollected' => $employee->creditSales()->where('status', 'deducted')->get(),
                    'created_by' => $user,
                ];

                // توليد PDF
                $pdf = PDF::loadView('pdf.employee-pdf', $data)
                    ->setPaper('a4')
                    ->setOption('encoding', 'UTF-8');

                // إرسال الإيميل
                Mail::send([], [], function ($message) use ($user, $pdf, $employee) {
                    $message->to($user->email)
                        ->subject("التقرير الشهري للموظف : {$employee->name}")
                        ->attachData($pdf->output(), "report-{$employee->id}.pdf");
                });

                // حذف السجلات
                Withdrawal::where('employee_id', $employee->id)->delete();
                Absence::where('employee_id', $employee->id)->delete();
                Debt::where('employee_id', $employee->id)->delete();
                CreditSale::where('employee_id', $employee->id)->delete();
                EmployeeLog::where('employee_id', $employee->id)->delete();

            } catch (\Exception $e) {
                Log::error("فشل إرسال تقرير الموظف {$employee->id}: " . $e->getMessage());
            }
        }
    }

    return back()->with('success', 'تم إرسال جميع التقارير لكل المستخدمين بنجاح');
}

}
