<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Debt;
use App\Models\Store;
use App\Models\Absence;
use App\Models\Employee;
use App\Models\Accountant;
use App\Models\CreditSale;
use App\Models\Withdrawal;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Services\EmployeeLogService;
use Illuminate\Support\Facades\Auth;

class EmployeeActionsController extends Controller
{
    /**
     * عرض صفحة العمليات
     */
    public function index($id)
    {
        $person = $this->findPerson($id);
        $this->authorizePerson($person);

        $returnTo = $this->safeReturnTo(request()->query('return_to')) ?? route('user.employees.index');

        return view('employees.actions', [
            'employee' => $person,
            'returnTo' => $returnTo,
        ]);
    }

    /**
     * حفظ عملية السحب
     */
    public function storeWithdrawal(Request $request, $id)
{
    $person = $this->findPerson($id);
    $this->authorizePerson($person);
    $this->ensureFinancialOperationAllowed($person, $request->input('date'));

    $actorName = auth('accountant')->user()?->name ?? auth()->user()?->name ?? 'النظام';

    $request->validate([
        'amount' => 'required|numeric|min:0.01',
        'date' => 'required|date',
        'description' => 'nullable|string|max:255',
    ]);

    // ================================
    // 🔥 منع التكرار خلال يوم كامل
    // ================================
    $exists = $person->withdrawals()
        ->whereDate('date', $request->date)
        ->where('amount', $request->amount)
        ->where('description', $request->description)
        ->exists();

    if ($exists) {
        return back()->withErrors([
            'duplicate' => 'لا يمكن تكرار نفس عملية السحب بنفس الوصف والقيمة في نفس اليوم.'
        ]);
    }

    // ================================
    // إنشاء عملية السحب
    // ================================
    $operationDate = \Carbon\Carbon::parse($request->date);

    $person->withdrawals()->create([
        'store_id'     => $person->store_id,
        'person_id'    => $person->id,
        'person_type'  => get_class($person),
        'amount'       => $request->amount,
        'description'  => $request->description,
        'date'         => $request->date,
        'status'       => 'pending',
        'month'        => $operationDate->format('Y-m'),
        'created_at'   => $operationDate->copy()->setTimeFrom(now()),
        'added_by'     => Auth::id(),
    ]);

    EmployeeLogService::add(
        $person,
        'withdrawal',
        "سحب مبلغ {$request->amount} ريال",
        $request->amount,
        'operation'
    );

    LogHelper::add(
        'withdrawal',
        "قام {$actorName} بتسجيل سحب بقيمة {$request->amount} ريال للموظف {$person->name}",
        $person->store_id
    );

    return back()->with('success', 'تم إضافة السحب بنجاح');
}


    /**
     * حفظ عملية الغياب
     */
    public function storeAbsence(Request $request, $id)
    {
        $person = $this->findPerson($id);
        $this->authorizePerson($person);

        $actorName = auth('accountant')->user()?->name ?? auth()->user()?->name ?? 'النظام';

        $request->validate([
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        if ($person->absences()->where('date', $request->date)->exists()) {
            return back()->with('error', 'تم تسجيل غياب لهذا المستخدم في هذا التاريخ مسبقًا');
        }

        $operationDate = \Carbon\Carbon::parse($request->date);

        $person->absences()->create([
            'store_id'     => $person->store_id,
            'person_id'    => $person->id,
            'person_type'  => get_class($person),
            'date'         => $request->date,
            'description'  => $request->description,
            'status'       => 'pending',
            'month'        => $operationDate->format('Y-m'),
            'created_at'   => $operationDate->copy()->setTimeFrom(now()),
            'added_by'     => Auth::id(),
        ]);

        EmployeeLogService::add(
            $person,
            'absence',
            "تسجيل غياب بتاريخ {$request->date}",
            null,
            'operation'
        );

        LogHelper::add(
            'employee_absence',
            "قام {$actorName} بتسجيل غياب للموظف {$person->name} بتاريخ {$request->date}",
            $person->store_id
        );

        // 🔥 إشعار تلقائي لصاحب المتجر عند تسجيل غياب
        \App\Services\NotificationService::sendTemplate('absence_recorded', [
            'sender_type' => 'CARLED',
            'target_type' => 'store',
            'target_ids'  => [$person->store_id],
        ]);

        return back()->with('success', 'تم تسجيل الغياب بنجاح');
    }

    /**
     * حفظ المديونية
     */
public function storeDebt(Request $request, $id)
{
    $person = $this->findPerson($id);
    $this->authorizePerson($person);
    $this->ensureFinancialOperationAllowed($person, $request->input('date'));

    $actorName = auth('accountant')->user()?->name ?? auth()->user()?->name ?? 'النظام';

    $request->validate([
        'amount'      => 'required|numeric|min:0.01',
        'description' => 'nullable|string|max:255',
        'date'        => 'required|date',
    ]);

    // منع تكرار نفس المديونية في نفس اليوم
    $exists = $person->debts()
        ->whereDate('date', $request->date)
        ->where('amount', $request->amount)
        ->where('description', $request->description)
        ->exists();

    if ($exists) {
        return back()->withErrors([
            'duplicate' => 'لا يمكن تكرار نفس المديونية بنفس الوصف والقيمة في نفس اليوم.'
        ]);
    }

    $operationDate = \Carbon\Carbon::parse($request->date);

    $debt = $person->debts()->create([
        'store_id'     => $person->store_id,
        'amount'       => $request->amount,
        'description'  => $request->description,
        'date'         => $request->date,
        'type'         => 'normal',
        'status'       => 'pending',
        'month'        => $operationDate->format('Y-m'),
        'created_at'   => $operationDate->copy()->setTimeFrom(now()),
        'added_by'     => auth()->id(),
    ]);

    EmployeeLogService::add(
        $person,
        'debt_add',
        "إضافة مديونية بقيمة {$request->amount} ريال",
        $debt->id,
        'operation'
    );

    LogHelper::add(
        'employee_debt',
        "قام {$actorName} بتسجيل مديونية بقيمة {$request->amount} ريال على الموظف {$person->name}",
        $person->store_id
    );

    $this->notifyStoreOwnerForInternalOps(
        $person,
        'إضافة مديونية',
        "قام {$actorName} بإضافة مديونية بقيمة {$request->amount} ريال على {$person->name}.",
        'debt_add'
    );

    return back()->with('success', 'تم إضافة المديونية بنجاح');
}


public function collectPartial($debtId, $amount)
{
    $debt = Debt::findOrFail($debtId);
    $person = $this->authorizeDebtAccess($debt);
    $actorName = auth('accountant')->user()?->name ?? auth()->user()?->name ?? 'النظام';


    if ($amount <= 0 || $amount > $debt->amount) {
        return back()->with('error', 'مبلغ التحصيل غير صالح.');
    }

    $newAmount = $debt->amount - $amount;

    $debt->update([
        'amount' => $newAmount,
        'status' => $newAmount == 0 ? 'cleared' : 'pending'
    ]);

    EmployeeLogService::add(
        $person,
        'debt_collect_partial',
        "تحصيل جزئي بقيمة {$amount} ريال",
        $debt->id,
        'operation'
    );

    LogHelper::add(
        'employee_debt_collect_partial',
        "قام {$actorName} بتحصيل جزئي بقيمة {$amount} ريال من مديونية الموظف {$person->name}",
        $person->store_id
    );

    $this->notifyStoreOwnerForInternalOps(
        $person,
        'تحصيل جزئي',
        "قام {$actorName} بتحصيل جزئي بقيمة {$amount} ريال من مديونية {$person->name}. المتبقي {$newAmount} ريال.",
        'debt_collect_partial'
    );

    return back()->with('success', 'تم التحصيل الجزئي بنجاح');
}

// دالة إنشاء بيع آجل جديد
public function storeCreditSale(Request $request, $employeeId)
{
    $person = $this->findPerson($employeeId);
    $this->authorizePerson($person);
    $this->ensureFinancialOperationAllowed($person, $request->input('date'));

    $validated = $request->validate([
        'amount'      => 'required|numeric|min:1',
        'description' => 'nullable|string|max:255',
        'date'        => 'required|date',
    ]);

    $sale = CreditSale::create([
        'person_id'        => $person->id,
        'person_type'      => get_class($person),
        'store_id'         => $person->store_id,
        'amount'           => $validated['amount'],
        'remaining_amount' => $validated['amount'],
        'description'      => $validated['description'] ?? null,
        'date'             => $validated['date'],
        'status'           => 'pending',
        'month'            => date('Y-m'),
        'added_by'         => auth()->id(),
        'partial_payments' => [],
    ]);

    EmployeeLogService::add(
        $person,
        'credit_sale_created',
        "إضافة بيع آجل بقيمة {$sale->amount} ريال",
        $sale->amount,
        'operation'
    );

    $actorName = auth('accountant')->user()?->name ?? auth()->user()?->name ?? 'النظام';
    LogHelper::add(
        'credit_sale',
        "قام {$actorName} بتسجيل بيع آجل بقيمة {$sale->amount} ريال على الموظف {$person->name}",
        $person->store_id
    );

    return back()->with('success', 'تم إنشاء عملية بيع آجل بنجاح');
}
// دالة التحصيل الكامل
public function collectCreditSale($employeeId, CreditSale $sale)
{
    $person = $this->findPerson($employeeId);
    $this->authorizePerson($person);

    $actorName = auth('accountant')->user()?->name ?? auth()->user()?->name ?? 'النظام';

    if ($sale->person_id !== $person->id || $sale->person_type !== get_class($person)) {
        abort(403, 'غير مسموح');
    }

    $sale->remaining_amount = 0;
    $sale->status = 'deducted';
    $sale->deducted_month = date('Y-m');
    $sale->partial_payments = [];

    $sale->save();
    $sale->syncLinkedSaleCollectionState();

    EmployeeLogService::add(
        $person,
        'credit_sale_deducted',
        "تحصيل بيع آجل بقيمة {$sale->amount} ريال",
        $sale->amount,
        'operation'
    );

    LogHelper::add(
        'credit_sale_deducted',
        "قام {$actorName} بتحصيل كامل بيع آجل بقيمة {$sale->amount} ريال من الموظف {$person->name}",
        $person->store_id
    );

   Notification::create([
    'sender_id'    => auth()->id(),
    'sender_type'  => 'system',
    'target_type'  => 'store',
    'target_ids'   => [$person->store_id],
    'title'        => 'تحصيل كامل',
    'message'      => "تم تحصيل مبلغ {$sale->amount} ريال بالكامل.",
    'template_key' => 'due_collected',
    'channel'      => 'CARLED',
]);


    return back()->with('success', 'تم التحصيل الكامل بنجاح');
}


public function collectFull($debtId)
{
    $debt = Debt::findOrFail($debtId);
    $person = $this->authorizeDebtAccess($debt);
    $actorName = auth('accountant')->user()?->name ?? auth()->user()?->name ?? 'النظام';

    if ($debt->amount <= 0) {
        return back()->with('error', 'لا توجد مديونية لتسديدها.');
    }

    $amount = $debt->amount;

    $debt->update([
        'amount' => 0,

    ]);

    EmployeeLogService::add(
        $person,
        'debt_collect_full',
        "تحصيل كامل بقيمة {$amount} ريال",
        $debt->id,
        'operation'
    );

    LogHelper::add(
        'employee_debt_collect_full',
        "قام {$actorName} بتحصيل كامل مديونية الموظف {$person->name} بقيمة {$amount} ريال",
        $person->store_id
    );

    $this->notifyStoreOwnerForInternalOps(
        $person,
        'تحصيل كامل',
        "قام {$actorName} بتحصيل كامل مديونية {$person->name} بقيمة {$amount} ريال.",
        'debt_collect_full'
    );

    return back()->with('success', 'تم التحصيل الكامل بنجاح');
}

public function collectDebt(Request $request, $id)
{
    $person = $this->findPerson($id);
    $this->authorizePerson($person);

    $actorName = auth('accountant')->user()?->name ?? auth()->user()?->name ?? 'النظام';

    $request->validate([
        'amount'      => 'required|numeric|min:0.01',
        'description' => 'nullable|string|max:255',
        'date'        => 'required|date',
        'mode'        => 'required|in:partial,full',
    ]);

    $month = date('Y-m', strtotime($request->date));
    $currentBalance = $person->debts()->sum('amount');

    if ($currentBalance <= 0) {
        return back()->withErrors(['amount' => 'لا توجد مديونية حالية على هذا الموظف.']);
    }

    // ================================
    // 🔥 منع التكرار خلال يوم كامل
    // ================================
    $exists = $person->debts()
        ->whereDate('date', $request->date)
        ->where('amount', -($request->mode === 'full' ? $currentBalance : min($request->amount, $currentBalance)))
        ->where('description', $request->description ?: 'تحصيل مديونية')
        ->exists();

    if ($exists) {
        return back()->withErrors([
            'duplicate' => 'لا يمكن تكرار نفس عملية التحصيل بنفس الوصف والقيمة في نفس اليوم.'
        ]);
    }

    // ================================
    // حساب مبلغ التحصيل
    // ================================
    $collectAmount = $request->mode === 'full'
        ? $currentBalance
        : min($request->amount, $currentBalance);

    $person->debts()->create([
        'store_id'     => $person->store_id,
        'amount'       => -$collectAmount,
        'description'  => $request->description ?: 'تحصيل مديونية',
        'date'         => $request->date,
        'type'         => 'normal',
        'status'       => 'pending',
        'month'        => $month,
        'added_by'     => auth()->id(),
    ]);

    EmployeeLogService::add(
        $person,
        'debt_collect',
        "تحصيل مديونية بقيمة {$collectAmount} ريال"
    );

    LogHelper::add(
        'debt_collect',
        "قام {$actorName} بتحصيل مديونية بقيمة {$collectAmount} ريال للموظف {$person->name}",
        $person->store_id
    );

    // 🔥 إشعار داخلي
    $message = $request->mode === 'full'
        ? "قام المحاسب بتحصيل كامل مديونية الموظف {$person->name} بقيمة {$collectAmount} ريال"
        : "قام المحاسب بتحصيل مبلغ جزئي بقيمة {$collectAmount} ريال من مديونية الموظف {$person->name}";

    $this->notifyStoreOwnerForInternalOps(
        $person,
        'تحصيل مديونية',
        $message,
        'debt_collect'
    );

    return back()->with('success', 'تم تحصيل المديونية بنجاح');
}

public function collectPartialCreditSale($employeeId, CreditSale $sale, $amount)
{
    $person = $this->findPerson($employeeId);
    $this->authorizePerson($person);

    $actorName = auth('accountant')->user()?->name ?? auth()->user()?->name ?? 'النظام';

    if ($sale->person_id !== $person->id || $sale->person_type !== get_class($person)) {
        abort(403, 'غير مسموح');
    }

    if ($amount <= 0 || $amount > $sale->remaining_amount) {
        return back()->with('error', 'مبلغ التحصيل غير صالح.');
    }

    // خصم من المتبقي
    $sale->remaining_amount -= $amount;

    // إضافة سجل JSON
    $payments = $sale->partial_payments ?? [];

    $payments[] = [
        'amount' => $amount,
        'date'   => now()->toDateString(),
    ];

    $sale->partial_payments = $payments;

    // إذا انتهى السداد → إغلاق العملية
    if ($sale->remaining_amount == 0) {
        $sale->status = 'deducted';
        $sale->deducted_month = date('Y-m');
    }

    $sale->save();
    $sale->syncLinkedSaleCollectionState();

    // لوج
    EmployeeLogService::add(
        $person,
        'credit_sale_partial',
        "تحصيل جزئي من بيع آجل بقيمة {$amount} ريال",
        $amount,
        'operation'
    );

    LogHelper::add(
        'credit_sale_partial',
        "قام {$actorName} بتحصيل جزئي بقيمة {$amount} ريال من بيع آجل للموظف {$person->name}",
        $person->store_id
    );

    // إشعار
    Notification::create([
        'sender_id'    => auth()->id(),
        'sender_type'  => 'system',
        'target_type'  => 'store',
        'target_ids'   => [$person->store_id],
        'title'        => 'تحصيل جزئي',
        'message'      => "تم تحصيل مبلغ {$amount} ريال. المتبقي الآن {$sale->remaining_amount} ريال.",
        'template_key' => 'due_collected_partial',
        'channel'      => 'CARLED',
    ]);

    return back()->with('success', 'تم التحصيل الجزئي بنجاح');
}


    private function authorizeDebtAccess(Debt $debt)
    {
        $person = $debt->person;

        if (!$person) {
            abort(404, 'لم يتم العثور على صاحب المديونية');
        }

        $this->authorizePerson($person);

        return $person;
    }

    /**
     * صفحة السجل
     */
    public function logs($id)
    {
        $person = $this->findPerson($id);
        $this->authorizePerson($person);

        $logs = $person->logs()
            ->orderBy('created_at', 'desc')
            ->get();

        $returnTo = $this->safeReturnTo(request()->query('return_to'))
            ?? route('user.employees.show', ['employee' => $person->id]);

        return view('employees.logs', [
            'employee' => $person,
            'logs'     => $logs,
            'returnTo' => $returnTo,
        ]);
    }


    private function notifyStoreOwnerForInternalOps($person, string $title, string $message, ?string $templateKey = null): void
    {
        if (!auth('accountant')->check()) {
            return;
        }

        $accountant = auth('accountant')->user();

        Notification::create([
            'sender_id'    => $accountant->id,
            'sender_type'  => 'accountant',
            'target_type'  => 'store',
            'target_ids'   => [$person->store_id],
            'title'        => $title,
            'message'      => $message,
            'template_key' => $templateKey,
            'channel'      => 'CARLED',
        ]);
    }

    private function safeReturnTo(?string $returnTo): ?string
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

    /**
     * إيجاد موظف أو محاسب
     */
    private function findPerson($id)
    {
        return Employee::find($id) ?? Accountant::findOrFail($id);
    }

    /**
     * حماية المستخدم حسب المتجر
     */
   private function authorizePerson($person)
{
    $user = auth()->user();
/** @var \App\Models\User $user */
    // المالك: يجب أن يكون المتجر تابعاً له حصراً
    if (auth('web')->check() && $user->role === 'user') {
        if (!$user->stores()->where('id', $person->store_id)->exists()) {
            abort(403, 'هذا الموظف لا ينتمي لمتاجرك');
        }
        return;
    }

    // المحاسب: يجب أن يكون في نفس المتجر
    if (auth('accountant')->check()) {
        if ($person->store_id !== auth('accountant')->user()->store_id) {
            abort(403, 'لا يمكنك إدارة موظفين خارج متجرك');
        }
        return;
    }

    // الأدمن له صلاحية كاملة تلقائياً
    if ($user && $user->role === 'admin') return;

    abort(403, 'غير مسموح');
}

    private function ensureFinancialOperationAllowed(Employee $employee, ?string $date): void
    {
        if (!$date) {
            return;
        }

        try {
            $operationDate = \Illuminate\Support\Carbon::parse($date);
        } catch (\Throwable) {
            return;
        }

        if (!$employee->canReceiveFinancialOperationOn($operationDate)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'employee' => 'لا يمكن إضافة سحب أو مديونية أو آجل للموظف خلال فترة إيقافه.',
            ]);
        }
    }
}
