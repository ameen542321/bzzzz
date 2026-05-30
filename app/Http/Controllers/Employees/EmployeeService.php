<?php

namespace App\Http\Controllers\Employees;

use App\Models\Store;
use App\Models\Employee;
use App\Models\Accountant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\EmployeeLogService;

class EmployeeService
{
    public static function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $storeIds = Store::pluck('id');
        } elseif ($user->role === 'user') {
            $storeIds = $user->stores->pluck('id');
        } elseif (auth('accountant')->check()) {
            $storeIds = [auth('accountant')->user()->store_id];
        } else {
            abort(403);
        }

        // إعادة منطقك الأصلي: جلب IDs الموظفين المرتبطين بمحاسبين فعالين لاستبعادهم
        $activeAccountantEmployeeIds = Accountant::whereIn('store_id', $storeIds)
            ->where('status', 'active')
            ->pluck('employee_id')
            ->filter()
            ->toArray();

        // إعادة منطقك الأصلي: استبعاد موظفي المحاسبة من قائمة الموظفين
        $employees = Employee::whereIn('store_id', $storeIds)
            ->whereNotIn('id', $activeAccountantEmployeeIds)
            ->get();

        $accountants = Accountant::whereIn('store_id', $storeIds)
            ->where('status', '!=', 'active')
            ->get();

        return view('employees.index', compact('employees', 'accountants'));
    }

    public function create(array $data)
    {
        // الحفظ المباشر للبيانات المجهزة من الكنترولر
        return Employee::create($data);
    }

    // بقية الدوال (edit, update, store القديمة) تبقى كما هي دون تغيير في منطقها الأصلي
    public static function edit(Employee $employee)
    {
        if (auth('accountant')->check()) { abort(403); }

        $user = auth('admin')->user() ?: auth('web')->user();

        if (!$user) {
            abort(403);
        }

        if ($user->role !== 'admin' && !$user->stores()->where('id', $employee->store_id)->exists()) {
            abort(403);
        }

        $stores = ($user->role === 'admin') ? Store::all() : $user->stores;

        return view('employees.edit', compact('employee', 'stores'));
    }

    public static function update(Request $request, Employee $employee)
    {
        if (auth('accountant')->check()) { abort(403); }

        $user = auth('admin')->user() ?: auth('web')->user();

        if (!$user) {
            abort(403);
        }

        if ($user->role !== 'admin' && !$user->stores()->where('id', $employee->store_id)->exists()) {
            abort(403);
        }

        $storeRule = Rule::exists('stores', 'id');

        if ($user->role !== 'admin') {
            $storeRule = $storeRule->where(fn ($query) => $query->where('user_id', $user->id));
        }

        $request->validate([
            'store_id' => ['required', $storeRule],
            'name'     => 'required|string|max:255',
            'salary'   => 'required|numeric|min:0',
        ]);

        $oldStoreId = $employee->store_id;
        $oldSalary  = $employee->salary;
        $employee->update($request->only('store_id', 'name', 'phone', 'salary'));

        EmployeeLogService::add($employee, 'employee_updated', "تم تعديل بيانات الموظف {$employee->name}");

        if ($oldStoreId != $employee->store_id) {
            $oldStore = Store::find($oldStoreId);
            $newStore = $employee->store;
            if ($oldStore && $newStore) {
                EmployeeLogService::add($employee, 'store_transfer', "نقل الموظف من متجر {$oldStore->name} إلى متجر {$newStore->name}");
            }
        }

        if ($oldSalary != $employee->salary) {
            EmployeeLogService::add($employee, 'salary_update', "تعديل الراتب من {$oldSalary} إلى {$employee->salary} ريال");
        }

        $returnTo = self::safeReturnTo($request->input('return_to'));

        return redirect($returnTo ?? route('user.employees.index'))
            ->with('success', 'تم تحديث بيانات العامل بنجاح');
    }

    public static function safeReturnTo(?string $returnTo): ?string
    {
        if (!$returnTo) {
            return null;
        }

        if (str_starts_with($returnTo, '/')) {
            return $returnTo;
        }

        $appHost = parse_url(url('/'), PHP_URL_HOST);
        $targetHost = parse_url($returnTo, PHP_URL_HOST);

        return $targetHost && $targetHost === $appHost ? $returnTo : null;
    }
}
