<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Accountant;
use App\Models\Employee;
use Illuminate\Http\Request;

class StoreMembersController extends Controller
{
    // عرض المحاسبين داخل المتجر
    public function accountants(Store $store)
    {
        $accountants = Accountant::with('employee')
            ->where('store_id', $store->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('user.stores.members.accountants', compact('store', 'accountants'));
    }

    // عرض الموظفين داخل المتجر
public function employees(Store $store)
{
    // 1) جلب IDs الموظفين المرتبطين بمحاسبين فعالين (للاستبعاد لاحقاً)
    $activeAccountantEmployeeIds = Accountant::where('store_id', $store->id)
        ->where('status', 'active')
        ->pluck('employee_id')
        ->filter()
        ->toArray();

    // 2) جلب موظفي المتجر (الفعالين فقط) مع استبعاد المحاسبين الفعالين
    $employees = Employee::where('store_id', $store->id)
        ->where('status', 'active') // التعديل: عرض الحالات الفعالة فقط
        ->whereNotIn('id', $activeAccountantEmployeeIds)
        ->orderBy('id', 'desc')
        ->get();

    // 3) جلب المحاسبين غير الفعالين (موقوف + محذوف) - يبقى كما هو بناءً على طلبك
    $accountants = Accountant::withTrashed()
        ->where('store_id', $store->id)
        ->where(function ($q) {
            $q->where('status', 'suspended')     // موقوف
              ->orWhereNotNull('deleted_at');    // محذوف
        })
        ->orderBy('id', 'desc')
        ->get();

    return view('user.stores.members.employees', compact('store', 'employees', 'accountants'));
}


}
