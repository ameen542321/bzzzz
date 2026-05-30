<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Traits\HasLogs;
use App\Models\Employee;
use App\Models\Accountant;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\PlanLimitService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class AccountantController extends Controller
{
    use HasLogs;

    /*
    |--------------------------------------------------------------------------
    | عرض قائمة المحاسبين
    |--------------------------------------------------------------------------
    */
  public function index()
{
    /** @var \App\Models\User $user */
    $user = auth('web')->user();

    // جميع المتاجر التابعة للمستخدم
    $storeIds = $user->stores()->pluck('id');

    // جلب المحاسبين المرتبطين بمتاجر المستخدم وبشرط الحالة "فعالة" فقط
    $accountants = Accountant::with(['employee.store'])
        ->whereIn('store_id', $storeIds)
        ->where('status', 'active') // إضافة شرط الحالة الفعالة
        ->paginate(20);

    // جلب عدد المحاسبين المحذوفين لنفس المتاجر (يبقى كما هو بناءً على طلبك عدم الحذف)
    $trashedCount = Accountant::onlyTrashed()
        ->whereIn('store_id', $storeIds)
        ->count();

    return view('user.accountants.index', compact('accountants', 'trashedCount'));
}


    /*
    |--------------------------------------------------------------------------
    | صفحة إنشاء محاسب
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
{
    $user = auth()->user();

    // إذا جئت من صفحة المتجر
    if ($request->from === 'store' && $request->store) {

        $store = Store::where('id', $request->store)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->firstOrFail();

        // لا نحتاج قائمة المتاجر هنا
        $stores = collect();

        return view('user.accountants.create', [
            'store'  => $store,
            'stores' => $stores
        ]);
    }

    // إذا جئت من صفحة كل المحاسبين
    $stores = Store::where('user_id', $user->id)
        ->where('status', 'active')
        ->get();

    if ($stores->isEmpty()) {
        return back()->with('error', 'لا يمكنك إضافة محاسب لأنه لا يوجد لديك أي متجر نشط.');
    }

    return view('user.accountants.create', [
        'store'  => null,
        'stores' => $stores
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | حفظ محاسب جديد
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:accountants,email',
            'password' => 'required|min:6',
            'phone'    => 'required|string',
            'store_id' => [
                'required',
                Rule::exists('stores', 'id')->where(fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->where('status', 'active')),
            ],
        ]);

        // التحقق من ملكية المتجر
        $store = Store::where('id', $request->store_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->firstOrFail();

        // فحص حدود الخطة
        PlanLimitService::assertCanAddAccountant($store);

        // إنشاء أو جلب الموظف
        $employee = Employee::firstOrCreate(
    [
        'store_id' => $store->id,
        'phone'    => $request->phone,
    ],
    [
        'user_id'   => $user->id,
        'name'      => $request->name,
        'role'      => 'accountant',
        'salary'    => $request->salary ?? 0, // وضع الراتب هنا
        'status'    => 'active',
        'added_by'  => $user->id,
        'email'     => $request->email,
    ]
);

// في حال كان الموظف موجود مسبقًا بدون user_id
if (!$employee->user_id) {
    $employee->update(['user_id' => $user->id]);
}
// في حال كان الموظف موجود مسبقًا، نقوم بتحديث راتبه والـ user_id
    $employee->update([
        'user_id' => $user->id,
        'salary'  => $request->salary ?? $employee->salary // تحديث الراتب إذا أُرسل
    ]);
        // إنشاء المحاسب
        $accountant = Accountant::create([
            'user_id'     => $user->id,
            'store_id'    => $store->id,
            'employee_id' => $employee->id,
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'password'    => bcrypt($request->password),
            'role'        => 'accountant',
            'status'      => 'active',
        ]);

        // تسجيل اللوق
        $this->addLog(
            'accountant_created',
            "تم إضافة المحاسب: {$accountant->name} براتب: {$employee->salary}",
            $accountant,
            [
                'store_id'    => $store->id,
                'employee_id' => $employee->id,
                'new_values'  => $accountant->only(['name', 'email', 'phone', 'store_id', 'employee_id']),
            ]
        );
        $returnTo = $this->safeReturnTo($request->input('return_to'));
        if ($returnTo) {
            return redirect()->to($returnTo)
                ->with('success', 'تم إضافة المحاسب بنجاح');
        }
       // fallback: ارجع لصفحة محاسبي المتجر
       return redirect()
       ->route('user.stores.accountants.index', $request->store_id)
 ->with('success', 'تم إضافة المحاسب بنجاح');
 }

    /*
    |--------------------------------------------------------------------------
    | صفحة تعديل محاسب
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $accountant = Accountant::with('employee.store')
            ->forUserStores()
            ->findOrFail($id);

        $user = auth()->user();

        $stores = Store::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        return view('user.accountants.edit', compact('accountant', 'stores'));
    }

    /*
    |--------------------------------------------------------------------------
    | تحديث بيانات محاسب
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
{
    $user = auth()->user();

    $accountant = Accountant::with('store')->forUserStores()->findOrFail($id);

    $request->validate([
        'email'    => 'required|email|unique:accountants,email,' . $accountant->id,
        'password' => 'nullable|min:6',
        'status'   => 'required|in:active,suspended',
        'store_id' => [
            'required',
            Rule::exists('stores', 'id')->where(fn ($query) => $query
                ->where('user_id', $user->id)
                ->where('status', 'active')),
        ],
        'name'     => 'required|string|max:255',
        'phone'    => 'required|string|max:20',

    ]);

    $store = Store::where('id', $request->store_id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    DB::transaction(function () use ($request, $accountant, $store) {

        // 🔵 1) تحديث بيانات المحاسب
        $data = [
            'email'    => $request->email,
            'status'   => $request->status,
            'store_id' => $store->id,
            'name'     => $request->name,
            'phone'    => $request->phone,

        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $accountant->update($data);

        // 🔵 2) تحديث بيانات الموظف المرتبط
        if ($accountant->employee) {
            $accountant->employee->update([
                'name'     => $request->name,
                'phone'    => $request->phone,
                'store_id' => $store->id,
                'status'   => $request->status,

            ]);
        }
    });

    // 🔵 3) تسجيل السجل
    $this->addLog(
        'accountant_updated',
        "تم تعديل بيانات المحاسب: {$accountant->email}",
        $accountant,
        [
            'store_id'    => $store->id,
            'employee_id' => $accountant->employee_id,
        ]
    );

    $returnTo = $this->safeReturnTo($request->input('return_to'));
    if ($returnTo) {
        return redirect()->to($returnTo)
            ->with('success', 'تم تحديث بيانات المحاسب');
    }

    return redirect()
        ->route('user.accountants.index')
        ->with('success', 'تم تحديث بيانات المحاسب');
}


    /*
    |--------------------------------------------------------------------------
    | إيقاف محاسب
    |--------------------------------------------------------------------------
    */
    public function suspend($id)
    {
        $accountant = Accountant::forUserStores()->findOrFail($id);

        $oldStatus = $accountant->status;

        $accountant->update(['status' => 'suspended']);

        if ($accountant->employee) {
            $accountant->employee->update(['status' => 'suspended']);
        }

        $this->addLog(
            'accountant_suspended',
            "تم إيقاف المحاسب: {$accountant->name}",
            $accountant,
            [
                'store_id'    => $accountant->store_id,
                'employee_id' => $accountant->employee_id,
                'old_values'  => ['status' => $oldStatus],
                'new_values'  => ['status' => 'suspended'],
            ]
        );

        return back()->with('success', 'تم إيقاف المحاسب');
    }

    /*
    |--------------------------------------------------------------------------
    | تفعيل محاسب
    |--------------------------------------------------------------------------
    */
   public function activate($id)
    {
        $accountant = Accountant::forUserStores()->findOrFail($id);

        $accountant->update(['status' => 'active']);

        // ⭐ مسح القيود باستخدام الإيميل فقط
        $throttleKey = Str::lower($accountant->email);
        RateLimiter::clear($throttleKey);

        if ($accountant->employee) {
            $accountant->employee->update(['status' => 'active']);
        }

        return back()->with('success', 'تم تفعيل الحساب ومسح قيود الدخول بنجاح.');
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

        return $targetHost && $appHost === $targetHost ? $returnTo : null;
    }

    /*
    |--------------------------------------------------------------------------
    | حذف محاسب (Soft Delete)
    |--------------------------------------------------------------------------
    */
    public function delete($id)
    {
        $accountant = Accountant::forUserStores()->findOrFail($id);

        $accountant->delete();

        $this->addLog(
            'accountant_deleted',
            "تم حذف المحاسب (Soft Delete): {$accountant->name}",
            $accountant,
            [
                'store_id'    => $accountant->store_id,
                'employee_id' => $accountant->employee_id,
                'old_values'  => ['status' => $accountant->status],
                'new_values'  => ['status' => 'deleted'],
            ]
        );

        return back()->with('success', 'تم حذف المحاسب (سلة المحذوفات).');
    }

    /*
    |--------------------------------------------------------------------------
    | عرض سلة المحذوفات
    |--------------------------------------------------------------------------
    */
    public function trash()
    {
        $accountants = Accountant::onlyTrashed()
            ->forUserStores()
            ->with('employee.store')
            ->get();

        return view('user.accountants.trash', compact('accountants'));
    }

    /*
    |--------------------------------------------------------------------------
    | استعادة محاسب
    |--------------------------------------------------------------------------
    */
    public function restore($id)
    {
        $accountant = Accountant::onlyTrashed()
            ->forUserStores()
            ->findOrFail($id);

        $accountant->restore();

        $this->addLog(
            'accountant_restored',
            "تم استعادة المحاسب: {$accountant->name}",
            $accountant,
            [
                'store_id'    => $accountant->store_id,
                'employee_id' => $accountant->employee_id,
                'new_values'  => ['status' => 'restored'],
            ]
        );

        return back()->with('success', 'تم استعادة المحاسب بنجاح.');
    }

    /*
    |--------------------------------------------------------------------------
    | حذف نهائي
    |--------------------------------------------------------------------------
    */
   public function forceDelete($id)
{
    $user = auth()->user();

    $accountant = Accountant::onlyTrashed()
        ->whereIn('store_id', $user->stores->pluck('id'))
        ->where('id', $id)
        ->firstOrFail();

    // تأكد أن العلاقات ليست null قبل isNotEmpty()
    if ($accountant->employee && $accountant->employee->debts && $accountant->employee->debts->isNotEmpty()) {
        return back()->with('error', 'لا يمكن حذف المحاسب لوجود مديونيات مرتبطة به.');
    }

    if ($accountant->employee && $accountant->employee->creditSales && $accountant->employee->creditSales->isNotEmpty()) {
        return back()->with('error', 'لا يمكن حذف المحاسب لوجود عمليات بيع آجل مرتبطة به.');
    }

    if ($accountant->employee && $accountant->employee->withdrawals && $accountant->employee->withdrawals->isNotEmpty()) {
        return back()->with('error', 'لا يمكن حذف المحاسب لوجود عمليات سحب مرتبطة به.');
    }

    // حذف الموظف إذا لم يكن مرتبطًا بأي شيء
    if ($accountant->employee) {
        $accountant->employee->forceDelete();
    }

    // حذف المحاسب نهائيًا
    $accountant->forceDelete();

    return redirect()
        ->route('user.accountants.trash')
        ->with('success', 'تم حذف المحاسب نهائيًا.');
}



}
