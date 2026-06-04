<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Services\LogService;
use App\Models\Store;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Accountant;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Support\ArabicPdf as PDF;

/**
 * ===================================================================
 * StoreController - إدارة المتاجر
 * ===================================================================
 *
 * هذا الكنترولر مسؤول عن جميع عمليات المتاجر:
 * - إنشاء، تعديل، عرض، حذف المتاجر
 * - إحصائيات المتاجر (المخزون، المبيعات، الموظفين)
 * - إدارة حالة المتاجر (نشط/معطل)
 * - سلة المهملات واستعادة المتاجر المحذوفة
 *
 * جميع الدوال تتحقق من ملكية المستخدم للمتجر قبل التنفيذ
 * -------------------------------------------------------------------
 */

class StoreController extends Controller
{
    /**
     * =================================================================
     * دوال التحقق من الصلاحية والخطة
     * =================================================================
     */

    /**
     * التحقق من صلاحية إنشاء متجر جديد حسب الخطة
     *
     * @return bool
     */
    protected function canUserAddStore()
    {
        $user = auth()->user();
        if (!$user->plan_id && !$user->allowed_stores) return false;

        $allowed = $user->plan_id ? $user->plan->allowed_stores : $user->allowed_stores;

        // استخدام withTrashed() لحساب المحذوف أيضاً
        return Store::withTrashed()->where('user_id', $user->id)->count() < $allowed;
    }

    /**
     * =================================================================
     * دوال CRUD الأساسية (إنشاء، عرض، تعديل، حذف)
     * =================================================================
     */

    /**
     * عرض قائمة المتاجر
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();

        // المتاجر الحالية (التي لم تحذف)
        $stores = $user->stores()->latest()->get();

        // إجمالي المتاجر (نشطة + في السلة) لغرض التحقق من الخطة
        $totalCountWithTrashed = Store::withTrashed()
            ->where('user_id', $user->id)
            ->count();

        // عدد المحذوفات فقط للعرض في الأيقونة
        $trashedCount = $user->stores()->onlyTrashed()->count();

        return view('user.stores.index', compact('stores', 'trashedCount', 'totalCountWithTrashed'));
    }

    /**
     * عرض صفحة إنشاء متجر جديد
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        $user = auth()->user();
        $totalUsed = $user->stores()->withTrashed()->count();
        $allowed = $user->plan->allowed_stores ?? $user->allowed_stores ?? 1;

        if ($totalUsed >= $allowed) {
            return redirect()->route('user.stores.index')
                ->with('error', 'لقد استنفدت الحد الأقصى للمتاجر المسموح بها في خطتك.');
        }

        return view('user.stores.create');
    }

    /**
     * حفظ متجر جديد في قاعدة البيانات
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // التحقق من البيانات
        $request->validate([
            'name'                => 'required|string|max:255',
            'phone'               => 'nullable|string|max:255',
            'address'             => 'nullable|string|max:255',
            'commercial_registration' => 'nullable|string|max:255',
            'tax_number'          => 'nullable|string|max:255',
            'description'         => 'nullable|string',
        ]);

        $user = auth()->user();

        // التحقق من الحصة (النشط + المحذوف)
        $totalUsed = $user->stores()->withTrashed()->count();
        $allowed   = $user->allowed_stores ?? ($user->plan->allowed_stores ?? 1);

        if ($totalUsed >= $allowed) {
            return redirect()->back()->with('error', 'لقد وصلت للحد الأقصى المسموح به في خطتك.');
        }

        // الحفظ الفعلي
        $user->stores()->create([
            'user_id'             => $user->id,
            'name'                => $request->name,
            'phone'               => $request->phone,
            'address'             => $request->address,
            // [تعديل آمن] توحيد اسم الحقل مع نماذج الإنشاء/التعديل والبطاقات.
            'commercial_registration' => $request->commercial_registration,
            'tax_number'          => $request->tax_number,
            'description'         => $request->description,
            'logo'                => null,
            'status'              => 'active',
            'slug'                => Str::slug($request->name) . '-' . uniqid(),
            'expires_at'          => null,
        ]);

        return redirect()->route('user.stores.index')->with('success', 'تم إنشاء المتجر بنجاح مع كافة البيانات الضريبية.');
    }

    /**
     * عرض صفحة تعديل المتجر
     *
     * @param Store $store
     * @return \Illuminate\View\View
     */
    public function edit(Store $store)
    {
        // التأكد أن المالك هو من يحاول التعديل
        if ($store->user_id !== auth()->id()) {
            abort(403);
        }

        return view('user.stores.edit', compact('store'));
    }

    /**
     * تحديث بيانات المتجر
     *
     * @param Request $request
     * @param Store $store
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Store $store)
    {
        // التحقق من الملكية
        if ($store->user_id !== auth()->id()) {
            abort(403);
        }

        // التحقق من البيانات
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'commercial_registration' => 'nullable|string',
            'bank_accounts' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // معالجة رفع الشعار
        if ($request->hasFile('logo')) {
            // حذف الشعار القديم
            if ($store->logo && file_exists(public_path('storage/' . $store->logo))) {
                @unlink(public_path('storage/' . $store->logo));
            }

            // تخزين الشعار الجديد
            $path = $request->file('logo')->store('stores/logos', 'public');
            $validated['logo'] = $path;
        }

        // تحديث البيانات
        $store->update($validated);

        return redirect()->route('user.stores.index')
            ->with('success', 'تم تحديث بيانات المتجر بنجاح');
    }

    /**
     * =================================================================
     * دوال العرض والصفحات (show, details, catalog)
     * =================================================================
     */

    /**
     * عرض الصفحة الرئيسية للمتجر (إحصائيات سريعة)
     *
     * @param Store $store
     * @return \Illuminate\View\View
     */
    public function show(Store $store)
    {
        // التحقق من ملكية المتجر
        if ($store->user_id !== auth()->id()) {
            abort(403);
        }

        $now = now();
        $user = auth()->user();

        // إحصائيات المبيعات (المحصل الفعلي) مع توحيد أنواع البيع داخل صفحة المتجر
        $includedSaleTypes = ['cash', 'card', 'credit', 'mixed'];

        $todaySales = Sale::where('store_id', $store->id)
            ->whereDate('created_at', today())
            ->whereIn('sale_type', $includedSaleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('paid_amount');

        $monthSales = Sale::where('store_id', $store->id)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->whereIn('sale_type', $includedSaleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('paid_amount');

        // عدد الفواتير/العمليات الشهرية لنفس أنواع البيع المعتمدة
        $invoicesCount = Sale::where('store_id', $store->id)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->whereIn('sale_type', $includedSaleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->count();

        $totalProfit = Sale::where('store_id', $store->id)
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->selectRaw('COALESCE(SUM(paid_amount - ((products_total + labor_total) - profit)), 0) as total_profit')
            ->value('total_profit');

        // إحصائيات الموارد البشرية والأقسام
        $accountantsCount = Accountant::where('store_id', $store->id)->count();
        $employeesCount = Employee::where('store_id', $store->id)->count();
        $categoriesCount = Category::where('store_id', $store->id)->count();
        $productsCount = Product::where('store_id', $store->id)->count();

        // عدد عمليات الاستهلاك الداخلي
        $consumptionCount = Sale::where('store_id', $store->id)
            ->where('sale_type', 'internal_use')
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->count();

        // أفضل المنتجات مبيعاً لهذا الشهر
        $topProducts = Product::where('store_id', $store->id)
            ->withCount(['saleItems as total_sold' => function($query) use ($now) {
                $query->select(DB::raw('SUM(quantity)'))
                    ->whereHas('sale', function($q) use ($now) {
                        $q->whereMonth('created_at', $now->month)
                            ->whereYear('created_at', $now->year);
                    });
            }])
            ->orderBy('total_sold', 'desc')
            ->take(10)
            ->get();

        // بيانات الرسم البياني (آخر 7 أيام) - تعتمد المحصل الفعلي لنفس أنواع البيع
        $sevenDaysStats = Sale::where('store_id', $store->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->whereIn('sale_type', $includedSaleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(paid_amount) as total_sales'),
                DB::raw('COALESCE(SUM(paid_amount - ((products_total + labor_total) - profit)), 0) as total_profit')
            )
            ->groupBy('date')
            ->get();

        $chartLabels = [];
        $chartData = [];
        $profitData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayName = now()->subDays($i)->translatedFormat('l');

            $stats = $sevenDaysStats->firstWhere('date', $date);

            $chartLabels[] = $dayName;
            $chartData[] = $stats ? (float) $stats->total_sales : 0;
            $profitData[] = $stats ? (float) $stats->total_profit : 0;
        }

        // سجل العمليات
        $operations = Log::where('store_id', $store->id)
            ->with('user')
            ->latest()
            ->limit(30)
            ->get();

        return view('user.stores.show', [
            'store'            => $store,
            'accountantsCount' => $accountantsCount,
            'employeesCount'   => $employeesCount,
            'categoriesCount'  => $categoriesCount,
            'productsCount'    => $productsCount,
            'consumptionCount' => $consumptionCount,
            'todaySales'       => $todaySales,
            'monthSales'       => $monthSales,
            'invoicesCount'    => $invoicesCount,
            'totalProfit'      => $totalProfit,
            'topProducts'      => $topProducts,
            'chartLabels'      => $chartLabels,
            'chartData'        => $chartData,
            'profitData'       => $profitData,
            'operations'       => $operations,
            'user'             => $user
        ]);
    }

    /**
     * عرض صفحة التفاصيل المتقدمة للمتجر
     * (إحصائيات شاملة: مخزون، موظفين، مبيعات، أرباح)
     *
     * @param int $storeId
     * @return \Illuminate\View\View
     */
    public function details($storeId)
    {
        $store = auth()->user()->stores()->findOrFail($storeId);
        $now = now();
        $currentMonthText = $now->format('Y-m');

        // ===== 1. إحصائيات المخزون =====
        $categoriesCount = $store->categories()->count();
        $productsCount = $store->products()->count();

        $lowStockProducts = $store->products()->lowStock()->get();
        $lowStockCount = $lowStockProducts->count();
        $trashedCount = $store->products()->onlyTrashed()->count();
        $latestProducts = $store->products()->latest()->take(5)->get();

        $latestMovements = \App\Models\StockMovement::where('store_id', $store->id)->latest()->take(5)->get();

        // قيمة المخزون: (الكمية / الطول) * السعر للمتري، أو (الكمية * السعر) للعادي
        $totalInventoryValue = $store->products()->selectRaw('SUM(
            CASE
                WHEN product_type = "fractional" AND roll_length > 0 THEN (quantity / roll_length) * price
                ELSE (quantity * price)
            END
        ) as total_value')->value('total_value') ?? 0;

        $metersAvailable = $store->products()->sum('quantity');

        // ===== 2. إحصائيات الموظفين والديون =====
        $totalEmployees = $store->employees()->count();
        $totalAccountants = $store->accountants()->count();
        $totalMonthlySalaries = $store->employees()->sum('salary') ?? 0;

        $monthlyWithdrawals = \App\Models\Withdrawal::where('store_id', $store->id)->where('month', $currentMonthText)->where('status', 'pending')->sum('amount') ?? 0;
        $monthlyDebts = \App\Models\Debt::where('store_id', $store->id)->where('month', $currentMonthText)->where('status', 'pending')->sum('amount') ?? 0;
        $monthlyAbsences = \App\Models\Absence::where('store_id', $store->id)->where('month', $currentMonthText)->where('status', 'pending')->count();

        $creditSales = \App\Models\CreditSale::where('store_id', $store->id)->where('status', 'pending')->sum('remaining_amount') ?? 0;
        $monthlyCollections = \App\Models\CreditSale::where('store_id', $store->id)->where('status', 'deducted')->where('deducted_month', $currentMonthText)->sum('amount') ?? 0;

        $activeEmployees = $store->employees()->where('status', 'active')->count();
        $suspendedEmployees = $store->employees()->where('status', 'suspended')->count();
        $topSalaries = $store->employees()->orderBy('salary', 'desc')->take(5)->get(['name', 'salary', 'status']);

        // الموظفون الأكثر مديونية وغياباً
        $mostDebtEmployees = \App\Models\Debt::where('store_id', $store->id)->where('status', 'pending')->where('person_type', 'App\\Models\\Employee')->selectRaw('person_id, SUM(amount) as total_debt')->groupBy('person_id')->orderBy('total_debt', 'desc')->take(5)->get()->map(fn($item) => ['name' => \App\Models\Employee::find($item->person_id)->name ?? 'غير معروف', 'total_debt' => $item->total_debt]);
        $mostAbsentEmployees = \App\Models\Absence::where('store_id', $store->id)->where('status', 'pending')->where('person_type', 'App\\Models\\Employee')->selectRaw('person_id, COUNT(*) as absence_count')->groupBy('person_id')->orderBy('absence_count', 'desc')->take(5)->get()->map(fn($item) => ['name' => \App\Models\Employee::find($item->person_id)->name ?? 'غير معروف', 'absence_count' => $item->absence_count]);

        // ===== 3. إحصائيات المبيعات والمصروفات (باستخدام paid_amount كأساس البيع) =====
        $monthlySales = Sale::where('store_id', $store->id)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('paid_amount');
        $todaySales = Sale::where('store_id', $store->id)
            ->whereDate('created_at', today())
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('paid_amount');

        $cashSales = Sale::where('store_id', $store->id)->where('sale_type', 'cash')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('paid_amount');
        $cardSales = Sale::where('store_id', $store->id)->where('sale_type', 'card')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('paid_amount');
        $creditSalesToday = Sale::where('store_id', $store->id)->where('sale_type', 'credit')->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('paid_amount');

        $monthlyExpenses = \App\Models\Expense::where('store_id', $store->id)->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('amount');
        $todayExpenses = \App\Models\Expense::where('store_id', $store->id)->whereDate('created_at', today())->sum('amount');

        // ===== 4. إحصائيات الربحية =====
        $monthlyProfit = Sale::where('store_id', $store->id)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->selectRaw('COALESCE(SUM(paid_amount - ((products_total + labor_total) - profit)), 0) as monthly_profit')
            ->value('monthly_profit');
        $monthlyOperatingExpenses = $monthlyExpenses;
        $totalMonthlyCosts = $totalMonthlySalaries + $monthlyOperatingExpenses;
        $monthlyNetProfit = $monthlyProfit - $totalMonthlyCosts;

        $profitMargin = ($monthlySales > 0) ? ($monthlyNetProfit / $monthlySales) * 100 : 0;
        $dailyAverageProfit = ($now->day > 0) ? ($monthlyNetProfit / $now->day) : $monthlyNetProfit;
        $costToRevenueRatio = ($monthlySales > 0) ? ($totalMonthlyCosts / $monthlySales) * 100 : 0;

        // ===== 5. الموازنات والبيانات الأخرى =====
        $lastBalance = \App\Models\DailyBalance::where('store_id', $store->id)->latest()->first();
        $monthlyDifferences = \App\Models\DailyBalance::where('store_id', $store->id)->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('difference');
        $monthlyShifts = \App\Models\DailyBalance::where('store_id', $store->id)->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();

        $averageProductPrice = $store->products()->avg('price') ?? 0;
        $productsWithoutImages = $store->products()->where(fn($q) => $q->whereNull('image')->orWhere('image', ''))->count();
        $lowStockPercentage = $productsCount > 0 ? round(($lowStockCount / $productsCount) * 100, 2) : 0;
        $monthlyMovements = \App\Models\StockMovement::where('store_id', $store->id)->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $mostActiveProducts = $store->products()->withCount('stockMovements')->orderBy('stock_movements_count', 'desc')->take(5)->get();
        $categoryStats = $store->categories()->withCount('products')->orderBy('products_count', 'desc')->take(5)->get();
        $todayInvoices = \App\Models\Invoice::whereHas('sale', function ($q) use ($store) {
            $q->where('store_id', $store->id)
                ->where(function ($subQuery) {
                    $subQuery->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                });
        })->whereDate('created_at', today())->count();
        $averageInvoiceValue = $todayInvoices > 0 ? $todaySales / $todayInvoices : 0;

        return view('user.stores.details', compact(
            'store', 'categoriesCount', 'productsCount', 'lowStockProducts', 'lowStockCount', 'trashedCount', 'latestProducts', 'latestMovements',
            'totalInventoryValue', 'averageProductPrice', 'productsWithoutImages', 'lowStockPercentage', 'monthlyMovements', 'mostActiveProducts', 'categoryStats',
            'totalEmployees', 'totalAccountants', 'totalMonthlySalaries', 'monthlyWithdrawals', 'monthlyDebts', 'monthlyAbsences', 'creditSales', 'monthlyCollections',
            'activeEmployees', 'suspendedEmployees', 'topSalaries', 'mostDebtEmployees', 'mostAbsentEmployees',
            'todaySales', 'monthlySales', 'todayInvoices', 'averageInvoiceValue', 'cashSales', 'cardSales', 'creditSalesToday',
            'monthlyProfit', 'monthlyOperatingExpenses', 'monthlyNetProfit', 'profitMargin', 'dailyAverageProfit', 'totalMonthlyCosts', 'costToRevenueRatio',
            'monthlyExpenses', 'todayExpenses', 'lastBalance', 'monthlyDifferences', 'monthlyShifts', 'metersAvailable'
        ));
    }

    /**
     * عرض صفحة كتالوج المنتجات للمتجر
     *
     * @param int $storeId
     * @return \Illuminate\View\View
     */
    public function catalog($storeId)
    {
        $store = auth()->user()->stores()->findOrFail($storeId);

        $categoriesCount = $store->categories()->count();
        $productsCount = $store->products()->count();

        $lowStockProducts = $store->products()
            ->lowStock()
            ->get();

        $lowStockCount = $lowStockProducts->count();
        $trashedCount = $store->products()->onlyTrashed()->count();

        $latestProducts = $store->products()
            ->latest()
            ->take(5)
            ->get();

        $latestMovements = \App\Models\StockMovement::where('store_id', $store->id)
            ->latest()
            ->take(5)
            ->get();

        return view('user.stores.store-catalog', compact(
            'store',
            'categoriesCount',
            'productsCount',
            'lowStockProducts',
            'lowStockCount',
            'trashedCount',
            'latestProducts',
            'latestMovements'
        ));
    }

    /**
     * =================================================================
     * دوال إدارة الحالة والإعدادات
     * =================================================================
     */

    /**
     * تعيين متجر كمتجر حالي للمستخدم
     *
     * @param Store $store
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setCurrentStore(Store $store)
    {
        $this->authorizeStoreAccess($store);

        if ($store->status == 'suspended') {
            return back()->with('error', 'لا يمكن تعيين متجر معطل كمتجر حالي');
        }

        auth()->user()->update(['current_store_id' => $store->id]);

        // تسجيل العملية
        app(LogService::class)->add(
            action: 'set_current',
            description: 'تم تعيين المتجر كمتجر حالي',
            model: $store,
            details: ['name' => $store->name],
            storeId: $store->id,
        );

        return back()->with('success', 'تم تعيين ' . $store->name . ' كمتجر حالي');
    }

    /**
     * تغيير حالة المتجر (تفعيل/تعطيل)
     *
     * @param Store $store
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus(Store $store)
    {
        $this->authorizeStoreAccess($store);

        // تبديل الحالة تلقائياً
        $oldStatus = $store->status;
        $newStatus = ($oldStatus === 'active') ? 'suspended' : 'active';

        $store->update([
            'status' => $newStatus,
            'suspension_reason' => $newStatus == 'suspended' ? 'تم الإيقاف بواسطة المالك' : null
        ]);

        // تسجيل العملية
        app(LogService::class)->add(
            action: 'status_change',
            description: 'تم تغيير حالة المتجر إلى ' . ($newStatus == 'active' ? 'نشط' : 'معطل'),
            model: $store,
            details: ['old_status' => $oldStatus, 'new_status' => $newStatus],
            storeId: $store->id,
        );

        $message = $newStatus == 'active' ? 'تم تفعيل المتجر بنجاح' : 'تم إيقاف المتجر بنجاح';
        return back()->with('success', $message);
    }

    /**
     * =================================================================
     * دوال سلة المهملات والحذف
     * =================================================================
     */

    /**
     * حذف المتجر (نقل للسلة)
     *
     * @param Store $store
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Store $store)
    {
        $this->authorizeStoreAccess($store);

        DB::beginTransaction();
        try {
            // تسجيل العملية قبل الحذف
            app(LogService::class)->add(
                action: 'delete',
                description: 'تم نقل المتجر إلى سلة المهملات',
                model: $store,
                details: ['name' => $store->name],
                storeId: $store->id,
            );

            // حذف المتجر (Soft Delete)
            $store->delete();

            DB::commit();

            return redirect()->route('user.stores.index')
                ->with('success', 'تم نقل المتجر إلى سلة المهملات بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('user.stores.show', $store)
                ->with('error', 'حدث خطأ أثناء حذف المتجر: ' . $e->getMessage());
        }
    }

    /**
     * عرض سلة المهملات (المتاجر المحذوفة)
     *
     * @return \Illuminate\View\View
     */
    public function trash()
    {
        $stores = Store::onlyTrashed()
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('user.stores.trash', compact('stores'));
    }

    /**
     * استعادة متجر محذوف
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        $user = auth()->user();
        $store = Store::onlyTrashed()
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // التحقق من الخطة
        $allowed = $user->plan_id ? $user->plan->allowed_stores : ($user->allowed_stores ?? 1);
        $activeCount = $user->stores()->count();

        if ($activeCount >= $allowed) {
            return redirect()->route('user.stores.trash')
                ->with('error', 'لا يمكنك استعادة المتجر لأنك وصلت للحد الأقصى المسموح به في خطتك (' . $allowed . ') متجر.');
        }

        $store->restore();

        return redirect()->route('user.stores.trash')
            ->with('success', 'تم استعادة المتجر بنجاح');
    }

    /**
     * حذف المتجر نهائياً من قاعدة البيانات
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        $store = Store::onlyTrashed()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $store->forceDelete();

        return redirect()->route('user.stores.trash')
            ->with('success', 'تم حذف المتجر نهائياً');
    }

    /**
     * صفحة مركز التقارير للمتجر
     */
    public function reportsIndex(Store $store)
    {
        $this->authorizeStoreAccess($store);

        return view('user.stores.reports.index', compact('store'));
    }


    /**
     * تقرير بحث شامل للمتجر يجمع المبيعات والاستهلاك الداخلي ومشتريات المالك.
     */
    public function reportsComprehensiveSearch(Store $store, Request $request)
    {
        $this->authorizeStoreAccess($store);

        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'scope' => 'nullable|in:all,sales,internal,purchases',
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $from = $validated['from'] ?? now()->startOfMonth()->format('Y-m-d');
        $to = $validated['to'] ?? now()->format('Y-m-d');
        $scope = $validated['scope'] ?? 'all';
        $startDate = $from . ' 00:00:00';
        $endDate = $to . ' 23:59:59';

        $saleTypes = ['cash', 'card', 'credit', 'mixed'];

        $salesQuery = Sale::query()
            ->where('store_id', $store->id)
            ->whereIn('sale_type', $saleTypes)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->with([
                'accountant:id,name',
                'employee:id,name',
                'items.product:id,name,description,barcode',
            ]);

        if ($search !== '') {
            $salesQuery->where(function ($query) use ($search) {
                $query->where('description', 'like', "%{$search}%")
                    ->orWhere('internal_notes', 'like', "%{$search}%")
                    ->orWhereHas('items', function ($itemsQuery) use ($search) {
                        $itemsQuery->where('custom_name', 'like', "%{$search}%")
                            ->orWhereHas('product', function ($productQuery) use ($search) {
                                $productQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('description', 'like', "%{$search}%")
                                    ->orWhere('barcode', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $salesSummary = (clone $salesQuery)
            ->selectRaw('COUNT(*) as operations_count')
            ->selectRaw('COALESCE(SUM(products_total), 0) as products_total')
            ->selectRaw('COALESCE(SUM(labor_total), 0) as labor_total')
            ->selectRaw('COALESCE(SUM(COALESCE(final_total, total, 0)), 0) as final_total')
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as paid_amount')
            ->selectRaw('COALESCE(SUM(cash_amount), 0) as cash_amount')
            ->selectRaw('COALESCE(SUM(card_amount), 0) as card_amount')
            ->selectRaw('COALESCE(SUM(remaining_amount), 0) as remaining_amount')
            ->selectRaw('COALESCE(SUM(profit), 0) as profit')
            ->first();

        $matchingItemsQuery = \App\Models\SaleItem::query()
            ->whereHas('sale', function ($saleQuery) use ($store, $saleTypes, $startDate, $endDate) {
                $saleQuery->where('store_id', $store->id)
                    ->whereIn('sale_type', $saleTypes)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->where(function ($query) {
                        $query->whereNull('description')
                            ->orWhere('description', '!=', 'manual_invoice_entry');
                    });
            });

        if ($search !== '') {
            $matchingItemsQuery->where(function ($itemsQuery) use ($search) {
                $itemsQuery->where('custom_name', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
            });
        }

        $matchingItemsSummary = $matchingItemsQuery
            ->selectRaw('COUNT(*) as rows_count')
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity')
            ->selectRaw('COALESCE(SUM(total), 0) as total')
            ->first();

        $internalUseQuery = Sale::query()
            ->where('store_id', $store->id)
            ->where('sale_type', 'internal_use')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->with([
                'accountant:id,name',
                'items.product:id,name,description,barcode',
            ]);

        if ($search !== '') {
            $internalUseQuery->where(function ($query) use ($search) {
                $query->where('description', 'like', "%{$search}%")
                    ->orWhere('internal_notes', 'like', "%{$search}%")
                    ->orWhereHas('items', function ($itemsQuery) use ($search) {
                        $itemsQuery->where('custom_name', 'like', "%{$search}%")
                            ->orWhereHas('product', function ($productQuery) use ($search) {
                                $productQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('description', 'like', "%{$search}%")
                                    ->orWhere('barcode', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $internalSummary = (clone $internalUseQuery)
            ->selectRaw('COUNT(*) as operations_count')
            ->selectRaw('COALESCE(SUM(COALESCE(total, final_total, 0)), 0) as total_cost')
            ->first();

        $ownerPurchasesQuery = Purchase::query()
            ->where('store_id', $store->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('product:id,name,description,barcode');

        if ($search !== '') {
            $ownerPurchasesQuery->where(function ($query) use ($search) {
                $query->where('purchase_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
            });
        }

        $ownerPurchasesSummary = (clone $ownerPurchasesQuery)
            ->selectRaw('COUNT(*) as purchases_count')
            ->selectRaw('COALESCE(SUM(cost), 0) as total_cost')
            ->first();

        $summary = [
            'sales_total' => (float) ($salesSummary->final_total ?? 0),
            'sales_count' => (int) ($salesSummary->operations_count ?? 0),
            'products_total' => (float) ($salesSummary->products_total ?? 0),
            'labor_total' => (float) ($salesSummary->labor_total ?? 0),
            'paid_total' => (float) ($salesSummary->paid_amount ?? 0),
            'cash_total' => (float) ($salesSummary->cash_amount ?? 0),
            'card_total' => (float) ($salesSummary->card_amount ?? 0),
            'remaining_total' => (float) ($salesSummary->remaining_amount ?? 0),
            'profit_total' => (float) ($salesSummary->profit ?? 0),
            'matching_items_total' => (float) ($matchingItemsSummary->total ?? 0),
            'matching_items_quantity' => (float) ($matchingItemsSummary->quantity ?? 0),
            'internal_total' => (float) ($internalSummary->total_cost ?? 0),
            'internal_count' => (int) ($internalSummary->operations_count ?? 0),
            'owner_purchases_total' => (float) ($ownerPurchasesSummary->total_cost ?? 0),
            'owner_purchases_count' => (int) ($ownerPurchasesSummary->purchases_count ?? 0),
        ];

        $summary['net_after_internal'] = $summary['sales_total'] - $summary['internal_total'];
        $summary['net_after_internal_and_purchases'] = $summary['sales_total'] - $summary['internal_total'] - $summary['owner_purchases_total'];
        $summary['all_operations_count'] = $summary['sales_count'] + $summary['internal_count'] + $summary['owner_purchases_count'];
        $summary['all_operations_total'] = $summary['sales_total'] + $summary['internal_total'] + $summary['owner_purchases_total'];
        $summary['selected_operations_count'] = match ($scope) {
            'sales' => $summary['sales_count'],
            'internal' => $summary['internal_count'],
            'purchases' => $summary['owner_purchases_count'],
            default => $summary['all_operations_count'],
        };
        $summary['selected_operations_total'] = match ($scope) {
            'sales' => $summary['sales_total'],
            'internal' => $summary['internal_total'],
            'purchases' => $summary['owner_purchases_total'],
            default => $summary['all_operations_total'],
        };

        $unifiedOperations = collect();

        if (in_array($scope, ['all', 'sales'], true)) {
            $unifiedOperations = $unifiedOperations->merge(
                (clone $salesQuery)->latest()->limit(100)->get()->map(function ($sale) {
                    $itemsTitle = $sale->items
                        ->map(fn ($item) => $item->product->name ?? $item->custom_name ?? null)
                        ->filter()
                        ->implode('، ');
                    $itemsDetails = $sale->items
                        ->map(function ($item) {
                            $product = $item->product;
                            $parts = [
                                $product->name ?? $item->custom_name ?? 'منتج محذوف',
                                'الكمية: ' . number_format((float) $item->quantity, 2),
                                'السعر: ' . number_format((float) $item->price, 2),
                                'الإجمالي: ' . number_format((float) $item->total, 2),
                            ];

                            if (!empty($product?->barcode)) {
                                $parts[] = 'باركود: ' . $product->barcode;
                            }

                            if (!empty($product?->description)) {
                                $parts[] = 'وصف المنتج: ' . $product->description;
                            }

                            return implode(' | ', $parts);
                        })
                        ->implode(PHP_EOL);

                    return [
                        'type' => 'sale',
                        'type_label' => 'بيع',
                        'badge_class' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
                        'id' => $sale->id,
                        'date' => $sale->created_at,
                        'title' => $itemsTitle ?: ($sale->description ?: 'بيع'),
                        'details' => $itemsDetails ?: ($sale->description ?: 'لا يوجد تفاصيل'),
                        'amount' => (float) ($sale->final_total ?? $sale->total ?? 0),
                        'meta' => 'إجمالي الفاتورة كاملة | شغل يد: ' . number_format((float) $sale->labor_total, 2) . ' ر.س',
                    ];
                })
            );
        }

        if (in_array($scope, ['all', 'internal'], true)) {
            $unifiedOperations = $unifiedOperations->merge(
                (clone $internalUseQuery)->latest()->limit(100)->get()->map(function ($sale) {
                    return [
                        'type' => 'internal',
                        'type_label' => 'استهلاك',
                        'badge_class' => 'bg-yellow-500/10 text-yellow-300 border-yellow-500/30',
                        'id' => $sale->id,
                        'date' => $sale->created_at,
                        'title' => $sale->internal_notes ?: ($sale->description ?: 'استهلاك داخلي'),
                        'details' => $sale->items->map(fn ($item) => $item->product->name ?? $item->custom_name ?? 'منتج محذوف')->filter()->implode('، ') ?: 'لا يوجد تفاصيل',
                        'amount' => (float) ($sale->total ?? $sale->final_total ?? 0),
                        'meta' => 'لا يوجد تفاصيل',
                    ];
                })
            );
        }

        if (in_array($scope, ['all', 'purchases'], true)) {
            $unifiedOperations = $unifiedOperations->merge(
                (clone $ownerPurchasesQuery)->latest()->limit(100)->get()->map(function ($purchase) {
                    return [
                        'type' => 'purchase',
                        'type_label' => 'مشتريات مالك',
                        'badge_class' => 'bg-orange-500/10 text-orange-300 border-orange-500/30',
                        'id' => $purchase->id,
                        'date' => $purchase->created_at,
                        'title' => $purchase->purchase_name ?: ($purchase->product->name ?? 'مشتريات مالك'),
                        'details' => $purchase->description ?: ($purchase->product->name ?? 'لا يوجد تفاصيل'),
                        'amount' => (float) ($purchase->cost ?? 0),
                        'meta' => 'الكمية: ' . number_format((float) $purchase->quantity, 2),
                    ];
                })
            );
        }

        $unifiedOperations = $unifiedOperations
            ->sortByDesc(fn ($operation) => optional($operation['date'])->timestamp ?? 0)
            ->take(150)
            ->values();

        $summary['all_operations_count'] = $unifiedOperations->count();
        $summary['all_operations_total'] = (float) $unifiedOperations->sum('amount');
        $summary['selected_operations_count'] = $summary['all_operations_count'];
        $summary['selected_operations_total'] = $summary['all_operations_total'];

        return view('user.stores.reports.comprehensive-search', compact(
            'store',
            'search',
            'from',
            'to',
            'scope',
            'summary',
            'unifiedOperations'
        ));
    }

    /**
     * تقارير مبيعات آخر 10 أيام (ملفات PDF المولدة للإقفال)
     */
    public function reportsLastTenDays(Store $store)
    {
        $this->authorizeStoreAccess($store);

        $folder = public_path('reports/');
        $cutoff = now()->subDays(10)->getTimestamp();
        $files = [];

        if (is_dir($folder)) {
            $pattern = $folder . 'Report_*_' . $store->id . '.pdf';
            $all = glob($pattern) ?: [];

            $files = collect($all)
                ->map(function ($path) {
                    return [
                        'name' => basename($path),
                        'url' => url('reports/' . basename($path)),
                        'created_at' => \Carbon\Carbon::createFromTimestamp(filemtime($path)),
                        'size_kb' => round(filesize($path) / 1024, 2),
                    ];
                })
                ->filter(fn ($file) => $file['created_at']->timestamp >= now()->subDays(10)->timestamp)
                ->sortByDesc('created_at')
                ->values();
        }

        return view('user.stores.reports.last-ten-days', [
            'store' => $store,
            'reports' => $files,
            'cutoffDate' => \Carbon\Carbon::createFromTimestamp($cutoff),
        ]);
    }

    /**
     * التقرير الشهري للمتجر (واجهة)
     */
    public function reportsMonthly(Store $store, Request $request)
    {
        $this->authorizeStoreAccess($store);

        $month = $request->get('month', now()->format('Y-m'));
        $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $salesQuery = Sale::where('store_id', $store->id)
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('sale_type', ['cash', 'card', 'credit', 'mixed']);

        $totalSales = (float) (clone $salesQuery)->sum('paid_amount');
        $operationsCount = (int) (clone $salesQuery)->count();
        $cashSales = (float) (clone $salesQuery)->sum('cash_amount');
        $cardSales = (float) (clone $salesQuery)->sum('card_amount');

        $internalUseSales = (float) Sale::where('store_id', $store->id)
            ->where('sale_type', 'internal_use')
            ->whereBetween('created_at', [$start, $end])
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('total');

        $ownerPurchases = (float) \App\Models\Purchase::where('store_id', $store->id)
            ->whereBetween('created_at', [$start, $end])
            ->sum('cost');

        $monthlySoldProductsCost = $this->calculateSoldProductsCostForPeriod(
            $store->id,
            $start,
            $end,
            ['cash', 'card', 'credit', 'mixed']
        );

        $profitDeductionTotal = $monthlySoldProductsCost;
        $totalConsumption = $internalUseSales + $ownerPurchases;

        $expensesTotal = (float) \App\Models\Expense::where('store_id', $store->id)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $withdrawalsTotal = (float) \App\Models\Withdrawal::where('store_id', $store->id)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $monthlySalaries = (float) $store->employees()->sum('salary');

        $netAfterCosts = $totalSales - ($profitDeductionTotal + $totalConsumption + $expensesTotal);

        $dailyRows = Sale::where('store_id', $store->id)
            ->whereBetween('created_at', [$start, $end])
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->selectRaw('DATE(created_at) as day, SUM(paid_amount) as sales_total, COUNT(*) as ops_count')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return view('user.stores.reports.monthly', compact(
            'store',
            'month',
            'start',
            'end',
            'totalSales',
            'operationsCount',
            'cashSales',
            'cardSales',
            'internalUseSales',
            'ownerPurchases',
            'monthlySoldProductsCost',
            'profitDeductionTotal',
            'totalConsumption',
            'expensesTotal',
            'withdrawalsTotal',
            'monthlySalaries',
            'netAfterCosts',
            'dailyRows'
        ));
    }

    /**
     * تصدير PDF للتقرير الشهري
     */
    public function reportsMonthlyPdf(Store $store, Request $request)
    {
        $this->authorizeStoreAccess($store);

        $month = $request->get('month', now()->format('Y-m'));
        $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $salesQuery = Sale::where('store_id', $store->id)
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('sale_type', ['cash', 'card', 'credit', 'mixed']);

        $data = [
            'store' => $store,
            'month' => $month,
            'totalSales' => (float) (clone $salesQuery)->sum('paid_amount'),
            'operationsCount' => (int) (clone $salesQuery)->count(),
            'cashSales' => (float) (clone $salesQuery)->sum('cash_amount'),
            'cardSales' => (float) (clone $salesQuery)->sum('card_amount'),
            'internalUseSales' => (float) Sale::where('store_id', $store->id)
                ->where('sale_type', 'internal_use')
                ->whereBetween('created_at', [$start, $end])
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->sum('total'),
            'ownerPurchases' => (float) \App\Models\Purchase::where('store_id', $store->id)->whereBetween('created_at', [$start, $end])->sum('cost'),
            'expensesTotal' => (float) \App\Models\Expense::where('store_id', $store->id)->whereBetween('created_at', [$start, $end])->sum('amount'),
            'withdrawalsTotal' => (float) \App\Models\Withdrawal::where('store_id', $store->id)->whereBetween('created_at', [$start, $end])->sum('amount'),
            'monthlySalaries' => (float) $store->employees()->sum('salary'),
        ];
        $data['monthlySoldProductsCost'] = $this->calculateSoldProductsCostForPeriod(
            $store->id,
            $start,
            $end,
            ['cash', 'card', 'credit', 'mixed']
        );
        $data['profitDeductionTotal'] = $data['monthlySoldProductsCost'];
        $data['totalConsumption'] = $data['internalUseSales'] + $data['ownerPurchases'];
        $data['netAfterCosts'] = $data['totalSales'] - ($data['profitDeductionTotal'] + $data['totalConsumption'] + $data['expensesTotal']);

        $pdf = PDF::loadView('pdf.store-monthly-report', $data)
            ->setOption('encoding', 'utf-8');

        return $pdf->download("monthly-report-{$store->id}-{$month}.pdf");
    }

    /**
     * يحسب تكلفة المنتجات المباعة مع دعم العمليات القديمة قبل إضافة sale_items.total_cost.
     */
    private function calculateSoldProductsCostForPeriod(int $storeId, $start, $end, array $saleTypes): float
    {
        $salesFallbackQuery = Sale::where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('sale_type', $saleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            });

        if (! \Illuminate\Support\Facades\Schema::hasColumn('sale_items', 'total_cost')) {
            return (float) $salesFallbackQuery
                ->selectRaw('COALESCE(SUM(products_total), 0) as products_cost')
                ->value('products_cost');
        }

        $salesCosts = DB::table('sales')
            ->leftJoin('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.store_id', $storeId)
            ->whereBetween('sales.created_at', [$start, $end])
            ->whereIn('sales.sale_type', $saleTypes)
            ->where(function ($query) {
                $query->whereNull('sales.description')
                    ->orWhere('sales.description', '!=', 'manual_invoice_entry');
            })
            ->groupBy('sales.id', 'sales.products_total')
            ->selectRaw('sales.id')
            ->selectRaw('COALESCE(SUM(sale_items.total_cost), 0) as item_total_cost')
            ->selectRaw('SUM(CASE WHEN sale_items.total_cost IS NULL THEN 0 ELSE 1 END) as costed_items_count')
            ->selectRaw('COALESCE(sales.products_total, 0) as legacy_products_cost');

        return (float) DB::query()
            ->fromSub($salesCosts, 'sales_costs')
            ->selectRaw('COALESCE(SUM(CASE WHEN costed_items_count > 0 THEN item_total_cost ELSE legacy_products_cost END), 0) as total_cost')
            ->value('total_cost');
    }

    /**
     * =================================================================
     * دوال مساعدة و API
     * =================================================================
     */

    /**
     * دالة مساعدة للتحقق من صلاحية الوصول للمتجر
     *
     * @param Store $store
     * @return void
     */
    private function authorizeStoreAccess(Store $store)
    {
        if ($store->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بالوصول لهذا المتجر.');
        }
    }

    /**
     * الحصول على إحصائيات متقدمة للمتجر (API)
     *
     * @param Store $store
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdvancedStats(Store $store)
    {
        $this->authorizeStoreAccess($store);

        // إحصائيات المبيعات الشهرية
        $monthlySales = Sale::where('store_id', $store->id)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(paid_amount) as total_sales'),
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('COALESCE(SUM(paid_amount - ((products_total + labor_total) - profit)), 0) as total_profit')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // إحصائيات المنتجات
        $productStats = Product::where('store_id', $store->id)
            ->selectRaw("
                COUNT(*) as total_products,
                SUM(quantity) as total_quantity,
                SUM(CASE
                    WHEN product_type = 'fractional' AND roll_length > 0 THEN (quantity / roll_length) * price
                    ELSE (quantity * price)
                END) as total_value,
                AVG(price) as average_price
            ")
            ->first();

        // المنتجات قليلة المخزون
        $lowStockProducts = Product::where('store_id', $store->id)
            ->lowStock()
            ->orderBy('quantity')
            ->limit(10)
            ->get(['id', 'name', 'quantity', 'min_stock', 'price', 'roll_length']);

        // إحصائيات الموظفين
        $employeeStats = Employee::where('store_id', $store->id)
            ->selectRaw("
                COUNT(*) as total_employees,
                SUM(salary) as total_salary,
                AVG(salary) as average_salary
            ")
            ->first();

        return response()->json([
            'monthly_sales'      => $monthlySales,
            'product_stats'      => $productStats,
            'employee_stats'     => $employeeStats,
            'low_stock_products' => $lowStockProducts,
            'low_stock_count'    => Product::where('store_id', $store->id)
                ->lowStock()->count()
        ]);
    }
}
