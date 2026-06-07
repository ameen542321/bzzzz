<?php

namespace App\Http\Controllers\Store;
use App\Helpers\LogHelper;
use App\Models\Debt;
use App\Models\Absence;
use App\Models\Expense;
use App\Models\Employee;
use App\Models\CreditSale;
use App\Models\Withdrawal;
use App\Models\EmployeeLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Services\EmployeeLogService;

class EmployeeFinanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Helpers: findPerson + authorizePerson
    |--------------------------------------------------------------------------
    */
    private function findPerson($id)
    {
        return Employee::findOrFail($id);
    }

    private function authorizePerson($person)
    {
        $accountant = auth('accountant')->user();

        if ($person->store_id !== $accountant->store_id) {
            abort(403, 'غير مسموح');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 2) تنفيذ عملية السحب (مع منع التكرار)
    |--------------------------------------------------------------------------
    */
    public function storeWithdrawal(Request $request, $id)
{
    $person = $this->findPerson($id);
    $this->authorizePerson($person);

    $request->validate([
        'amount'      => 'required|numeric|min:0.01',
        'date'        => 'required|date',
        'description' => 'nullable|string|max:255',
    ]);

    $description = trim($request->description) ?: null;
    $operationDate = Carbon::parse($request->date);

    // منع التكرار لنفس تاريخ العملية المدخل
    $exists = Withdrawal::where('store_id', $person->store_id)
        ->where('person_id', $person->id)
        ->where('amount', $request->amount)
        ->where('description', $description)
        ->whereDate('date', $operationDate->toDateString())
        ->exists();

    if ($exists) {
        return back()->with('error', 'تم تسجيل عملية السحب مسبقًا بنفس البيانات اليوم');
    }

    $accountant = auth('accountant')->user();

    // إنشاء السحب
    // مهم: يتم احتساب السحب ضمن شهر التاريخ المدخل، وليس شهر يوم الإدخال الحالي.
    $person->withdrawals()->create([
        'store_id'    => $person->store_id,
        'person_id'   => $person->id,
        'person_type' => Employee::class,
        'amount'      => $request->amount,
        'description' => $description,
        'date'        => $operationDate->toDateString(),
        'status'      => 'pending',
        'month'       => $operationDate->format('Y-m'),
        'created_at'  => $operationDate->copy()->setTimeFrom(now()),
        'added_by'    => $accountant->id,
    ]);

    // لوق الموظف
    EmployeeLogService::add(
        $person,
        'withdrawal',
        "سحب مبلغ {$request->amount} ريال",
        $request->amount,
        'operation'
    );

    // 🔥 لوق صاحب المتجر (يظهر في الداشبورد)
    LogHelper::add(
        'withdrawal',
        "قام المحاسب {$accountant->name} بتسجيل سحب بقيمة {$request->amount} ريال للموظف {$person->name}",
        $person->store_id
    );

    return back()->with('success', 'تم إضافة السحب بنجاح');
}



    /*
    |--------------------------------------------------------------------------
    | 3) تنفيذ عملية الغياب (جاهزة مسبقًا)
    |--------------------------------------------------------------------------
    */
    public function storeAbsence(Request $request, $id)
{
    $person = $this->findPerson($id);
    $this->authorizePerson($person);

    $request->validate([
        'date'        => 'required|date',
        'description' => 'nullable|string|max:255',
    ]);

    $description = trim($request->description) ?: null;

    // منع التكرار
    $exists = Absence::where('store_id', $person->store_id)
        ->where('person_id', $person->id)
        ->whereDate('date', $request->date)
        ->exists();

    if ($exists) {
        return back()->with('error', 'تم تسجيل غياب لهذا الموظف مسبقًا في هذا التاريخ');
    }

    $accountant = auth('accountant')->user();

    // إنشاء الغياب
    $person->absences()->create([
        'store_id'    => $person->store_id,
        'person_id'   => $person->id,
        'person_type' => Employee::class,
        'date'        => $request->date,
        'description' => $description,
        'status'      => 'pending',
        'month'       => now()->format('Y-m'),
        'added_by'    => $accountant->id,
    ]);

    // لوق الموظف
    EmployeeLogService::add(
        $person,
        'absence',
        "تسجيل غياب بتاريخ {$request->date}",
        null,
        'operation'
    );

    // 🔥 لوق صاحب المتجر (يظهر في الداشبورد)
    LogHelper::add(
        'employee_absence',
        "قام المحاسب {$accountant->name} بتسجيل غياب للموظف {$person->name} بتاريخ {$request->date}",
        $person->store_id
    );

    return back()->with('success', 'تم تسجيل الغياب بنجاح');
}



    // /*
    // |--------------------------------------------------------------------------
    // | 4) تنفيذ عملية المصروف (مع منع التكرار)
    // |--------------------------------------------------------------------------
    // */
    // public function storeExpense(Request $request, $id)
    // {
    //     $person = $this->findPerson($id);
    //     $this->authorizePerson($person);

    //     $request->validate([
    //         'amount'      => 'required|numeric|min:0.01',
    //         'description' => 'required|string|max:255',
    //         'date'        => 'required|date',
    //     ]);

    //     // 🔥 منع التكرار
    //     $exists = Expense::where('store_id', $person->store_id)
    //         ->where('person_id', $person->id)
    //         ->where('amount', $request->amount)
    //         ->where('description', $request->description)
    //         ->whereDate('date', $request->date)
    //         ->exists();

    //     if ($exists) {
    //         return back()->with('error', 'تم تسجيل المصروف مسبقًا بنفس البيانات');
    //     }

    //     $accountant = auth('accountant')->user();

    //     $person->expenses()->create([
    //         'store_id'    => $person->store_id,
    //         'person_id'   => $person->id,
    //         'person_type' => Employee::class,
    //         'amount'      => $request->amount,
    //         'description' => $request->description,
    //         'date'        => $request->date,
    //         'month'       => date('Y-m'),
    //         'added_by'    => $accountant->id,
    //         'type'        => 'employee_expense',
    //     ]);

    //     EmployeeLogService::add(
    //         $person,
    //         'expense',
    //         "مصروف بقيمة {$request->amount} ريال",
    //         $request->amount,
    //         'operation'
    //     );

    //     return back()->with('success', 'تم إضافة المصروف بنجاح');
    // }

    /*
    |--------------------------------------------------------------------------
    | 5) تنفيذ عملية البيع الآجل (مع منع التكرار)
    |--------------------------------------------------------------------------
    */
 public function storeCreditSale(Request $request, $id)
{
    $person = $this->findPerson($id);
    $this->authorizePerson($person);

    $request->validate([
        'amount'      => 'required|numeric|min:0.01',
        'description' => 'nullable|string|max:255',
        'date'        => 'required|date',
    ]);

    $description = trim($request->description) ?: null;

    // منع التكرار
    $exists = CreditSale::where('store_id', $person->store_id)
        ->where('person_id', $person->id)
        ->where('amount', $request->amount)
        ->where('description', $description)
        ->whereDate('created_at', today())
        ->exists();

    if ($exists) {
        return back()->with('error', 'تم تسجيل البيع الآجل مسبقًا بنفس البيانات اليوم');
    }

    $accountant = auth('accountant')->user();

    // إنشاء البيع الآجل
    $person->creditSales()->create([
        'store_id'         => $person->store_id,
        'person_id'        => $person->id,
        'person_type'      => Employee::class,
        'amount'           => $request->amount,
        'remaining_amount' => $request->amount,
        'partial_payments' => [],
        'description'      => $description,
        'date'             => $request->date,
        'status'           => 'pending',
        'month'            => now()->format('Y-m'),
        'added_by'         => $accountant->id,
    ]);

    // لوق الموظف
    EmployeeLogService::add(
        $person,
        'credit_sale',
        "تسجيل بيع آجل بقيمة {$request->amount} ريال",
        $request->amount,
        'operation'
    );

    // 🔥 لوق صاحب المتجر (يظهر في الداشبورد)
    LogHelper::add(
        'credit_sale',
        "قام المحاسب {$accountant->name} بتسجيل بيع آجل بقيمة {$request->amount} ريال على الموظف {$person->name}",
        $person->store_id
    );

    return back()->with('success', 'تم تسجيل البيع الآجل بنجاح');
}


    /*
    |--------------------------------------------------------------------------
    | 6) تنفيذ عملية التحصيل (مع منع التكرار)
    |--------------------------------------------------------------------------
    */
public function storeCollection(Request $request, $saleId)
{
    $accountant = auth('accountant')->user();

    $sale = CreditSale::where('store_id', $accountant->store_id)
        ->where('id', $saleId)
        ->firstOrFail();

    $person = $sale->person;

    // منع المحاسب من تحصيل مديونيته الشخصية
    if ($person->id == $accountant->employee_id) {
        return back()->with('error', 'غير مصرح لك بتحصيل البيع الآجل الخاص بك.');
    }

    // منع التحصيل إذا كان السداد مكتمل مسبقًا
    if ($sale->status === 'deducted') {
        return response('', 403);
    }

    /*
    |--------------------------------------------------------------------------
    | تحصيل كامل
    |--------------------------------------------------------------------------
    */
    if (!$request->has('amount')) {

        $sale->update([
            'remaining_amount' => 0,
            'partial_payments' => [],
            'status'           => 'deducted',
            'deducted_month'   => now()->format('Y-m'),
        ]);

        $sale->syncLinkedSaleCollectionState();

        EmployeeLog::create([
            'person_id'   => $sale->person_id,
            'person_type' => $sale->person_type,
            'store_id'    => $sale->store_id,
            'action_name' => 'credit_sale_deducted',
            'amount'      => $sale->amount,
            'description' => 'تحصيل بيع آجل بقيمة كاملة ' . number_format($sale->amount, 2) . ' ريال',
        ]);

        // 🔥 تسجيل لوق لصاحب المتجر
        LogHelper::add(
            'credit_sale_deducted',
            "قام المحاسب {$accountant->name} بتحصيل بيع آجل بقيمة كاملة {$sale->amount} ريال من الموظف {$person->name}",
            $sale->store_id
        );

        $sale->delete();

        return back()->with('success', 'تم تحصيل البيع الآجل بنجاح');
    }

    /*
    |--------------------------------------------------------------------------
    | تحصيل جزئي
    |--------------------------------------------------------------------------
    */
    if (!is_numeric($request->amount)) {
        return response('', 422);
    }

    $amount = floatval($request->amount);

    if ($amount < 1 || $amount > $sale->remaining_amount) {
        return response('', 422);
    }

    $sale->remaining_amount -= $amount;

    $payments = $sale->partial_payments ?? [];
    $payments[] = [
        'amount' => $amount,
        'date'   => now()->toDateTimeString(),
    ];

    $sale->partial_payments = $payments;

    if ($sale->remaining_amount == 0) {
        $sale->status = 'deducted';
        $sale->deducted_month = now()->format('Y-m');
    } else {
        $sale->status = 'pending';
    }

    $sale->save();
    $sale->syncLinkedSaleCollectionState();

    EmployeeLog::create([
        'person_id'   => $sale->person_id,
        'person_type' => $sale->person_type,
        'store_id'    => $sale->store_id,
        'action_name' => 'credit_sale_partial',
        'amount'      => $amount,
        'description' => 'تحصيل بيع آجل بقيمة جزئية ' . number_format($amount, 2) . ' ريال',
    ]);

    // 🔥 تسجيل لوق لصاحب المتجر
    LogHelper::add(
        'credit_sale_partial',
        "قام المحاسب {$accountant->name} بتحصيل مبلغ {$amount} ريال من بيع آجل للموظف {$person->name}",
        $sale->store_id
    );

    if ($sale->remaining_amount == 0) {
        $sale->delete();
    }

    return response()->noContent();
}





    /*
    |--------------------------------------------------------------------------
    | 7) تنفيذ عملية المديونية (مع منع التكرار)
    |--------------------------------------------------------------------------
    */
  public function storeDebt(Request $request, $id)
{
    $person = $this->findPerson($id);
    $this->authorizePerson($person);

    $request->validate([
        'amount'      => 'required|numeric|min:0.01',
        'description' => 'nullable|string|max:255',
        'date'        => 'required|date',
    ]);

    $description = trim($request->description) ?: null;

    // منع التكرار خلال نفس اليوم
    $exists = Debt::where('store_id', $person->store_id)
        ->where('person_id', $person->id)
        ->where('amount', $request->amount)
        ->where('description', $description)
        ->whereDate('created_at', today())
        ->exists();

    if ($exists) {
        return back()->with('error', 'تم تسجيل المديونية مسبقًا بنفس البيانات اليوم');
    }

    $accountant = auth('accountant')->user();

    // إنشاء المديونية
    $debt = $person->debts()->create([
        'store_id'    => $person->store_id,
        'person_id'   => $person->id,
        'person_type' => Employee::class,
        'amount'      => $request->amount,
        'description' => $description,
        'date'        => $request->date,
        'status'      => 'pending',
        'month'       => now()->format('Y-m'),
        'added_by'    => $accountant->id,
    ]);

    // تسجيل لوق للموظف
    EmployeeLogService::add(
        $person,
        'debt',
        "تسجيل مديونية بقيمة {$request->amount} ريال",
        $request->amount,
        'operation'
    );

    // تسجيل لوق لصاحب المتجر
LogHelper::add(
    'employee_debt',
    "قام المحاسب {$accountant->name} بتسجيل مديونية بقيمة {$request->amount} ريال على الموظف {$person->name}",
    $person->store_id
);




    // إشعار لصاحب المتجر
    Notification::create([
        'sender_id'    => $accountant->id,
        'sender_type'  => 'accountant',

        'target_type'  => 'user',
        'target_ids'   => [$person->store->user->id],

        'title'        => 'تسجيل مديونية',
        'message'      => "قام المحاسب {$accountant->name} بتسجيل مديونية بقيمة {$request->amount} ريال على الموظف {$person->name}",
        'template_key' => 'debt_add',
        'channel'      => 'CARLED',
    ]);

    return back()->with('success', 'تم تسجيل المديونية بنجاح');
}



public function collectPartial($debtId, $amount)
{
    $debt = Debt::findOrFail($debtId);
    $person = $debt->person;
    $accountant = auth('accountant')->user();

    // 🔥 منع المحاسب من تحصيل مديونيته الشخصية
    if ($person->id == $accountant->employee_id) {
        return back()->with('error', 'غير مصرح لك بتحصيل مديونيتك الشخصية.');
    }

    // التحقق من صحة المبلغ
    if (!is_numeric($amount) || $amount <= 0 || $amount > $debt->amount) {
        return back()->with('error', 'مبلغ التحصيل غير صالح.');
    }

    // 🔥 منع التكرار (عمليات اليوم فقط)
    $exists = Debt::where('store_id', $person->store_id)
        ->where('person_id', $person->id)
        ->where('amount', -$amount)
        ->whereDate('created_at', today())
        ->exists();

    if ($exists) {
        return back()->with('error', 'تم تسجيل هذا التحصيل مسبقًا اليوم.');
    }

    // 1) إنشاء عملية التحصيل
    $person->debts()->create([
        'store_id'    => $person->store_id,
        'person_id'   => $person->id,
        'person_type' => Employee::class,
        'amount'      => -$amount,
        'description' => 'تحصيل جزئي',
        'date'        => now()->toDateString(),
        'status'      => 'pending',
        'month'       => now()->format('Y-m'),
        'added_by'    => $accountant->id,
    ]);

    // 2) تعديل المديونية الأصلية
    $debt->update([
        'amount' => $debt->amount - $amount
    ]);

    // 3) تسجيل لوق
    EmployeeLogService::add(
        $person,
        'debt_collect_partial',
        "تحصيل جزئي بقيمة {$amount} ريال",
        $amount,
        'operation'
    );

    LogHelper::add(
        'employee_debt_collect_partial',
        "قام المحاسب {$accountant->name} بتحصيل جزئي بقيمة {$amount} ريال من مديونية الموظف {$person->name}",
        $person->store_id
    );

    // 4) إرسال إشعار لصاحب المتجر
    Notification::create([
        'sender_id'    => $accountant->id,
        'sender_type'  => 'accountant',
        'target_type'  => 'user',
        'target_ids'   => [$person->store->user->id],
        'title'        => 'تحصيل جزئي للمديونية',
        'message'      => "قام المحاسب {$accountant->name} بتحصيل مبلغ {$amount} ريال من مديونية الموظف {$person->name}",
        'template_key' => 'debt_collect_partial',
        'channel'      => 'CARLED',
    ]);

    // 🔥 5) حذف المديونية إذا أصبح المتبقي = صفر
    $remaining = $person->debts()->sum('amount');
    if ($remaining == 0) {
        Debt::where('person_id', $person->id)->delete();
    }

    return back()->with('success', 'تم التحصيل الجزئي بنجاح');
}


public function collectFull($debtId)
{
    $debt = Debt::findOrFail($debtId);
    $person = $debt->person;
    $accountant = auth('accountant')->user();

    // 🔥 منع المحاسب من تحصيل مديونيته الشخصية
    if ($person->id == $accountant->employee_id) {
        return back()->with('error', 'غير مصرح لك بتحصيل مديونيتك الشخصية.');
    }

    // 🔥 منع التكرار (عمليات اليوم فقط)
    $exists = Debt::where('store_id', $person->store_id)
        ->where('person_id', $person->id)
        ->where('amount', -$debt->amount)
        ->whereDate('created_at', today())
        ->exists();

    if ($exists) {
        return back()->with('error', 'تم تحصيل هذه العملية مسبقًا اليوم.');
    }

    $collectedAmount = $debt->amount;

    // 1) إنشاء عملية التحصيل
    $person->debts()->create([
        'store_id'    => $person->store_id,
        'person_id'   => $person->id,
        'person_type' => Employee::class,
        'amount'      => -$collectedAmount,
        'description' => 'تحصيل كامل',
        'date'        => now()->toDateString(),
        'status'      => 'pending',
        'month'       => now()->format('Y-m'),
        'added_by'    => $accountant->id,
    ]);

    // 2) تصفير المديونية الأصلية
    $debt->update([
        'amount' => 0
    ]);

    // 3) تسجيل لوق
    EmployeeLogService::add(
        $person,
        'debt_collect_full',
        "تحصيل كامل بقيمة {$collectedAmount} ريال",
        $collectedAmount,
        'operation'
    );

    LogHelper::add(
        'employee_debt_collect_full',
        "قام المحاسب {$accountant->name} بتحصيل كامل مديونية الموظف {$person->name} بمبلغ {$collectedAmount} ريال",
        $person->store_id
    );

    // 4) إرسال إشعار لصاحب المتجر
    Notification::create([
        'sender_id'    => $accountant->id,
        'sender_type'  => 'accountant',
        'target_type'  => 'user',
        'target_ids'   => [$person->store->user->id],
        'title'        => 'تحصيل كامل للمديونية',
        'message'      => "قام المحاسب {$accountant->name} بتحصيل كامل مديونية الموظف {$person->name} بمبلغ {$collectedAmount} ريال",
        'template_key' => 'debt_collect_full',
        'channel'      => 'CARLED',
    ]);

    // 🔥 5) حذف جميع سجلات المديونية إذا أصبح الرصيد = صفر
    $remaining = $person->debts()->sum('amount');
    if ($remaining == 0) {
        Debt::where('person_id', $person->id)->delete();
    }

    return back()->with('success', 'تم التحصيل الكامل بنجاح');
}



public function getDebts($id)
{
    $person = Employee::findOrFail($id);

    // جلب المديونيات النشطة فقط (amount > 0)
    $debts = $person->debts()
        ->where('amount', '>', 0)
        ->orderBy('created_at', 'desc')
        ->get(['id', 'amount', 'description', 'date']);

    // تنسيق البيانات قبل الإرجاع
    $debts->transform(function ($d) {
        return [
            'id'          => $d->id,
            'amount'      => (float) $d->amount,
            'description' => $d->description ?: null,
            'date'        => $d->date,
        ];
    });

    return response()->json($debts);
}
    /*
    |--------------------------------------------------------------------------
    | صفحات العرض (بدون تعديل)
    |--------------------------------------------------------------------------
    */
public function withdrawalPage()
{
    $storeId = auth('accountant')->user()->store_id;

    $people = Employee::where('store_id', $storeId)->get();

    $lastWithdrawals = Withdrawal::where('store_id', $storeId)
        ->whereDate('created_at', today())   // 👈 عمليات اليوم فقط
        ->latest()
        ->get();

    return view('accountants.pos.withdrawals', compact('people', 'lastWithdrawals'));
}
    public function absencePage()
{
    $storeId = auth('accountant')->user()->store_id;

    $people = Employee::where('store_id', $storeId)->get();

    $lastAbsences = Absence::where('store_id', $storeId)
        ->whereDate('created_at', today())   // 👈 عمليات اليوم فقط
        ->orderBy('created_at', 'desc')
        ->get();

    return view('accountants.pos.absence', compact('people', 'lastAbsences'));
}


    public function debtPage()
{
    $storeId = auth('accountant')->user()->store_id;

    $people = Employee::where('store_id', $storeId)->get();

    $lastDebts = Debt::where('store_id', $storeId)
        ->whereDate('created_at', today())   // 👈 عمليات اليوم فقط
        ->with(['person', 'addedBy'])
        ->orderBy('created_at', 'desc')
        ->get();

    return view('accountants.pos.debt', compact('people', 'lastDebts'));
}

   public function creditSalePage()
{
    $storeId = auth('accountant')->user()->store_id;

    $people = Employee::where('store_id', $storeId)->get();

    $lastCreditSales = CreditSale::where('store_id', $storeId)
        ->whereDate('created_at', today())   // 👈 عمليات اليوم فقط
        ->orderBy('created_at', 'desc')
        ->get();

    return view('accountants.pos.credit-sale', compact('people', 'lastCreditSales'));
}

public function collectionPage()
{
    $storeId = auth('accountant')->user()->store_id;

    // جلب الموظفين الذين لديهم عمليات بيع آجل معلّقة
    $people = Employee::where('store_id', $storeId)
        ->whereHas('creditSales', function ($q) {
            $q->where('status', 'pending');
        })
        ->get();

    // تجهيز بيانات البيع الآجل لكل موظف
    foreach ($people as $emp) {
        $emp->pending_credit_sales = $emp->creditSales()
            ->where('status', 'pending')
            ->get()
            ->map(function ($sale) {
                return [
                    'id'               => $sale->id,
                    'amount'           => $sale->amount,
                    'remaining_amount' => $sale->remaining_amount ?? $sale->amount,
                    'date'             => $sale->date,
                    'description'      => $sale->description,
                    'partial_payments' => $sale->partial_payments ?? [],
                ];
            });
    }

    // آخر 5 عمليات تحصيل من اللوج (بعد تطوير اللوج)
    $lastCollections = EmployeeLog::where('store_id', $storeId)
        ->whereIn('action_name', ['credit_sale_deducted', 'credit_sale_partial'])
        ->latest()
        ->take(5)
        ->get();

    return view('accountants.pos.collection', compact('people', 'lastCollections'));
}




}
