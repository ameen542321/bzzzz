# خارطة طريق توحيد الحسابات المالية وإعادة هيكلة الكنترولرات

> **حالة الوثيقة:** مرجع تنفيذي مستقبلي  
> **نطاقها:** المبيعات، الشفتات، تكلفة المنتجات، الأرباح، لوحة المالك، التقارير، وكنترولرات المتجر والمبيعات والمحاسب.  
> **الهدف:** توحيد النتائج المالية بين جميع أجزاء النظام، وتقليل تكرار المعادلات، وتفكيك الكنترولرات الكبيرة تدريجيًا دون كسر الوظائف الحالية.

---

## 1. النتيجة المستهدفة

يجب أن تستخدم جميع الصفحات مصدرًا حسابيًا واحدًا:

```text
صفحة المبيعات اليومية ───────┐
إغلاق الشفت ─────────────────┤
لوحة المالك ─────────────────┼──> FinancialSummaryService
التقرير الشهري ──────────────┤
PDF وWhatsApp ────────────────┘
```

ولنفس المتجر والفترة يجب أن يتحقق:

```text
المبيعات المستلمة في صفحة المبيعات
= المبيعات المستلمة في إغلاق الشفت
= المبيعات المستلمة في لوحة المالك
= المبيعات المستلمة في التقرير الشهري
```

وينطبق المبدأ نفسه على تكلفة المنتجات والربح والمصروفات.

---

## 2. المبادئ الملزمة أثناء التنفيذ

1. **لا يُعاد بناء النظام دفعة واحدة.**
2. **تُكتب اختبارات تثبيت قبل نقل أي معادلة مالية.**
3. **استخراج المنطق لا يغيّر النتيجة في الخطوة نفسها.**
4. **أي تغيير محاسبي يكون في Pull Request مستقل وموثق.**
5. **لا يُحذف المنطق القديم قبل مقارنة نتائجه مع الخدمة الجديدة.**
6. **كل مرحلة قابلة للنشر والتراجع بصورة مستقلة.**
7. **Blade يعرض النتائج فقط ولا ينفذ معادلات مالية.**
8. **الكنترولر يستقبل الطلب ويستدعي الخدمة ويعيد الاستجابة فقط.**
9. **الخدمات المالية لا تعتمد على واجهة بعينها.**
10. **كل القيم المالية تُقارن بدقة 0.01 ريال.**

---

## 3. البنية المعمارية المستهدفة

```text
HTTP Request
    │
    ▼
Controller
    │  التحقق من الطلب + استدعاء حالة الاستخدام + الاستجابة
    ▼
Application Service
    │  تنفيذ العملية: تعديل بيع، إغلاق شفت، نقل تاريخ...
    ▼
Finance / Domain Services
    │  المبالغ والتكلفة والربح والفترات والملخصات
    ▼
Eloquent / Models / Database
```

### الهيكل المقترح

```text
app/
├── Data/
│   ├── Finance/
│   │   ├── SaleAmounts.php
│   │   ├── SaleItemCost.php
│   │   ├── SaleProfit.php
│   │   ├── ExpenseSummary.php
│   │   └── FinancialSummary.php
│   └── Shifts/
│       ├── ShiftWindow.php
│       └── ShiftClosingResult.php
│
├── Services/
│   ├── Finance/
│   │   ├── SaleAmountCalculator.php
│   │   ├── SaleItemCostCalculator.php
│   │   ├── SaleProfitCalculator.php
│   │   ├── ExpenseSummaryService.php
│   │   └── FinancialSummaryService.php
│   ├── Sales/
│   │   ├── SaleEditService.php
│   │   ├── SaleItemEditService.php
│   │   └── CreditCollectionService.php
│   ├── Shifts/
│   │   ├── ShiftWindowService.php
│   │   ├── ShiftClosingService.php
│   │   └── ShiftDateMoveService.php
│   └── Reports/
│       ├── StoreReportService.php
│       ├── ShiftReportService.php
│       ├── WhatsAppReportService.php
│       └── ReportFileService.php
│
└── Http/
    ├── Controllers/
    │   ├── Sales/
    │   │   ├── DailySalesController.php
    │   │   ├── SaleUpdateController.php
    │   │   └── ShiftDateController.php
    │   ├── Shifts/
    │   │   ├── ShiftClosingController.php
    │   │   └── ShiftReportController.php
    │   └── Stores/
    │       ├── StoreController.php
    │       ├── StoreTrashController.php
    │       ├── StoreReportController.php
    │       └── StoreStatisticsController.php
    └── Requests/
        ├── UpdateSaleRequest.php
        ├── MoveShiftDateRequest.php
        └── CloseShiftRequest.php
```

---

# مراحل التنفيذ

## المرحلة 0: تجهيز البيئة وتنظيف المستودع

### الهدف

إنشاء نقطة بداية آمنة قبل تعديل الحسابات الحساسة.

### الأعمال

- [ ] تثبيت تبعيات Composer.
- [ ] تثبيت تبعيات Node.
- [ ] تشغيل بناء Vite.
- [ ] إنشاء قاعدة بيانات اختبار مستقلة.
- [ ] التأكد من عمل Queue وScheduler في بيئة التطوير.
- [ ] إخراج تقارير PDF المولدة من Git.
- [ ] إزالة ملفات Windows cache من `public/%SystemDrive%`.
- [ ] إزالة نواتج البناء الموجودة في مكان غير قياسي بعد التحقق من الاستضافة.
- [ ] إزالة نسخة قاعدة البيانات الحساسة `database/reference/carled.sql` من Git.
- [ ] تغيير أي بيانات اعتماد مكشوفة في نسخة قاعدة البيانات القديمة.

### أوامر التحقق

```bash
composer install
npm install
npm run build
php artisan migrate --env=testing
php artisan test
git status --short
```

### شرط الإكمال

- التطبيق يعمل محليًا.
- الاختبارات قابلة للتشغيل.
- شجرة Git نظيفة.
- لا توجد بيانات إنتاج في بيئة الاختبار.

---

## المرحلة 1: توثيق القواعد المالية

### الهدف

الاتفاق على معنى كل رقم قبل برمجة الخدمات المركزية.

### ملف إضافي مقترح

```text
docs/FINANCIAL_RULES_AR.md
```

### القواعد الواجب حسمها

#### قيمة العملية

يجب تحديد المصدر الرسمي:

```text
final_total
أو products_total + labor_total
أو total
```

#### المبلغ المستلم

يجب تحديد قاعدة واحدة للتعامل مع:

```text
paid_amount
cash_amount
card_amount
remaining_amount
```

#### أنواع الدفع

- [ ] كاش.
- [ ] شبكة.
- [ ] مختلط.
- [ ] آجل كامل.
- [ ] آجل جزئي.
- [ ] تحصيل دين قديم.

#### تكلفة المنتجات

- [ ] منتج عادي.
- [ ] رول كامل.
- [ ] بيع بالمتر.
- [ ] قص مخصص.
- [ ] طقم كامل.
- [ ] حبة من طقم.
- [ ] منتج بلا سعر تكلفة.
- [ ] منتج حُذف بعد عملية البيع.

#### الربح

```text
ربح المنتجات = قيمة بيع المنتجات - تكلفة المنتجات
```

ويجب توثيق:

- [ ] الربح المحقق.
- [ ] الربح المؤجل.
- [ ] ربح التحصيل الجزئي.
- [ ] شغل اليد.
- [ ] الضرائب إن وجدت.

#### المصروفات والتكاليف

| البند | تصنيفه المقترح | يحتاج قرارًا نهائيًا |
|---|---|---:|
| مصروف تشغيلي | مصروف | نعم |
| شراء مالك | مشتريات/تمويل مخزون | نعم |
| سحب موظف | تسوية راتب | نعم |
| راتب | تكلفة تشغيلية | نعم |
| استخدام داخلي | تكلفة تشغيل | نعم |
| شراء مخزون | أصل مخزني حتى البيع | نعم |

### شرط الإكمال

يجب اعتماد وثيقة القواعد قبل كتابة `FinancialSummaryService`.

---

## المرحلة 2: اختبارات تثبيت السلوك

### الهدف

حماية النظام من تغير النتائج بصورة غير مقصودة أثناء إعادة الهيكلة.

### ملفات الاختبار المقترحة

```text
tests/
├── Unit/
│   └── Finance/
│       ├── SaleAmountCalculatorTest.php
│       ├── SaleItemCostCalculatorTest.php
│       ├── SaleProfitCalculatorTest.php
│       └── FinancialSummaryServiceTest.php
└── Feature/
    ├── DailySalesSummaryTest.php
    ├── ShiftClosingTest.php
    ├── OwnerDashboardSummaryTest.php
    ├── MonthlyStoreReportTest.php
    ├── ShiftDateMoveTest.php
    └── SaleItemEditingTest.php
```

### سيناريوهات المبالغ

- [ ] بيع كاش كامل.
- [ ] بيع شبكة كامل.
- [ ] بيع مختلط.
- [ ] آجل كامل.
- [ ] آجل جزئي.
- [ ] تحصيل جزئي لاحق.
- [ ] بيانات تاريخية يكون فيها `paid_amount` مختلفًا عن `cash + card`.

### سيناريوهات تكلفة المنتجات

- [ ] منتج عادي: `quantity × cost_price`.
- [ ] رول كامل.
- [ ] استهلاك عدد أمتار من رول.
- [ ] قص مخصص مع `custom_consumption`.
- [ ] طقم كامل.
- [ ] حبة من طقم.
- [ ] كمية عشرية.
- [ ] سعر تكلفة صفر أو مفقود.

### سيناريوهات الشفت

- [ ] شفت في اليوم نفسه.
- [ ] شفت يعبر منتصف الليل.
- [ ] شفت مفتوح.
- [ ] أكثر من شفت مغلق في اليوم.
- [ ] يوم بلا شفت.
- [ ] تعديل تاريخ الشفت.
- [ ] رفض نقل شفت إلى فترة متداخلة.

### شرط الإكمال

```bash
php artisan test
```

يجب أن ينجح قبل استخراج أول خدمة مالية.

---

## المرحلة 3: إنشاء DTOs للنتائج

### الهدف

منع اختلاف أسماء المفاتيح بين الصفحات مثل:

```text
total_sales / paid_total / collected_total / received_total
```

### الكائنات المقترحة

#### `SaleAmounts`

```php
final readonly class SaleAmounts
{
    public function __construct(
        public float $operationTotal,
        public float $received,
        public float $cash,
        public float $card,
        public float $remaining,
    ) {}
}
```

#### `FinancialSummary`

```php
final readonly class FinancialSummary
{
    public function __construct(
        public float $salesReceived,
        public float $cashSales,
        public float $cardSales,
        public float $creditCollections,
        public float $productsSales,
        public float $productsCost,
        public float $productsProfit,
        public float $recognizedProfit,
        public float $deferredProfit,
        public float $expenses,
        public float $withdrawals,
        public float $internalUse,
        public float $ownerPurchases,
        public float $netResult,
        public int $operationsCount,
    ) {}
}
```

### شرط الإكمال

الخدمات الجديدة تعيد DTOs بدل مصفوفات غير موحدة.

---

## المرحلة 4: استخراج `SaleAmountCalculator`

### الهدف

توحيد:

- قيمة العملية.
- المبلغ المستلم.
- الكاش.
- الشبكة.
- المتبقي.

### الملف

```text
app/Services/Finance/SaleAmountCalculator.php
```

### الواجهة

```php
public function calculate(Sale $sale): SaleAmounts;
```

### ترتيب الربط

1. [ ] صفحة المبيعات اليومية.
2. [ ] إغلاق الشفت.
3. [ ] لوحة المالك.
4. [ ] التقرير الشهري.

### آلية مقارنة مؤقتة

```php
$oldValue = /* الحساب الحالي */;
$newValue = $calculator->calculate($sale)->received;

if (abs($oldValue - $newValue) > 0.01) {
    Log::warning('sale_received_mismatch', [
        'sale_id' => $sale->id,
        'old' => $oldValue,
        'new' => $newValue,
    ]);
}
```

### شرط الإكمال

نفس العملية تعرض المبلغ المستلم نفسه في كل الصفحات.

---

## المرحلة 5: توحيد تكلفة عناصر البيع

### الهدف

جعل حاسب واحد مسؤولًا عن تكلفة كل أنواع المنتجات.

### نقطة البداية

```text
app/Support/ProductProfitCostCalculator.php
```

### الملف المستهدف

```text
app/Services/Finance/SaleItemCostCalculator.php
```

### الواجهة

```php
public function calculate(SaleItem $item): SaleItemCost;
```

### النتيجة

```php
final readonly class SaleItemCost
{
    public function __construct(
        public float $soldQuantity,
        public float $stockQuantity,
        public float $unitCost,
        public float $totalCost,
    ) {}
}
```

### ترتيب مصدر سعر التكلفة

1. سعر التكلفة المحفوظ في عنصر البيع وقت العملية.
2. سعر المنتج الحالي عند غياب السعر التاريخي.
3. صفر مع تسجيل تحذير إذا لم يوجد أي سعر.

### شرط الإكمال

لا يبقى حساب مباشر لتكلفة الرول أو الحبة داخل الكنترولرات.

---

## المرحلة 6: استخراج `SaleProfitCalculator`

### الهدف

توحيد:

- قيمة بيع المنتجات.
- تكلفة المنتجات.
- ربح المنتجات.
- الربح المحقق.
- الربح المؤجل.

### الملف

```text
app/Services/Finance/SaleProfitCalculator.php
```

### النتيجة

```php
final readonly class SaleProfit
{
    public function __construct(
        public float $productsSales,
        public float $productsCost,
        public float $productsProfit,
        public float $laborValue,
        public float $recognizedProfit,
        public float $deferredProfit,
    ) {}
}
```

### شرط الإكمال

- لا توجد معادلة ربح مستقلة في لوحة المالك.
- لا توجد معادلة ربح مستقلة في تقرير الشفت.
- لا توجد معادلة ربح مستقلة في التقرير الشهري.

---

## المرحلة 7: استخراج `ShiftWindowService`

### الهدف

توحيد تعريف اليوم والشفت في جميع الصفحات.

### الملف

```text
app/Services/Shifts/ShiftWindowService.php
```

### الواجهة

```php
public function forDate(int $storeId, Carbon $date): Collection;
public function currentOpenShift(int $storeId): ?ShiftWindow;
public function resolveWindow(Carbon $operationDate, Collection $windows): ?ShiftWindow;
public function overlaps(int $storeId, Carbon $start, Carbon $end, ?int $exceptBalanceId = null): bool;
```

### مصادر المنطق الحالية التي ستُوحّد

- `DailySalesController::buildShiftWindows()`.
- `DailySalesController::resolveShiftKey()`.
- `UserDashboardController::dailySalesWindows()`.
- فترات إغلاق الشفت في `Accountant/DashboardController`.

### شرط الإكمال

صفحة المبيعات ولوحة المالك وإغلاق الشفت تستخدم الفترة نفسها حرفيًا.

---

## المرحلة 8: استخراج `ExpenseSummaryService`

### الهدف

منع احتساب أي بند مرتين أو بتصنيف مختلف بين التقارير.

### الملف

```text
app/Services/Finance/ExpenseSummaryService.php
```

### النتيجة

```php
final readonly class ExpenseSummary
{
    public function __construct(
        public float $operatingExpenses,
        public float $ownerPurchases,
        public float $withdrawals,
        public float $salaries,
        public float $internalUse,
    ) {}
}
```

### قواعد إلزامية

- [ ] `owner_purchase` لا يحتسب مرتين.
- [ ] السحب لا يحتسب راتبًا ومصروفًا معًا.
- [ ] الاستخدام الداخلي لا يحتسب مبيعات.
- [ ] شراء المخزون لا يخصم كاملًا من ربح المبيعات فورًا.

---

## المرحلة 9: إنشاء `FinancialSummaryService`

### الهدف

مصدر واحد للملخص المالي لأي متجر وفترة.

### الملف

```text
app/Services/Finance/FinancialSummaryService.php
```

### الواجهة

```php
public function forStorePeriod(
    int $storeId,
    Carbon $start,
    Carbon $end
): FinancialSummary;

public function forWindows(
    int $storeId,
    Collection $windows
): FinancialSummary;
```

### يعتمد على

```text
SaleAmountCalculator
SaleItemCostCalculator
SaleProfitCalculator
ExpenseSummaryService
ShiftWindowService
```

### ترتيب دمج الصفحات

1. [ ] صفحة المبيعات اليومية.
2. [ ] إغلاق الشفت.
3. [ ] لوحة المالك اليومية.
4. [ ] التقرير الشهري.
5. [ ] مخطط لوحة المالك.
6. [ ] PDF.
7. [ ] WhatsApp.

### شرط الإكمال

تطابق جميع الملخصات عند استخدام المتجر والفترة نفسيهما.

---

## المرحلة 10: توحيد التقارير والملفات

### الخدمات المقترحة

```text
app/Services/Reports/StoreReportService.php
app/Services/Reports/ShiftReportService.php
app/Services/Reports/WhatsAppReportService.php
app/Services/Reports/ReportFileService.php
```

### المسؤوليات

#### `StoreReportService`

- التقرير اليومي.
- التقرير الشهري.
- تفاصيل المبيعات.
- الاعتماد على `FinancialSummaryService` دون إعادة الحساب.

#### `ShiftReportService`

- تحويل نتيجة إغلاق الشفت إلى بيانات عرض.
- تجهيز بيانات PDF.

#### `WhatsAppReportService`

```php
public function buildMessage(...): string;
public function buildUrl(string $phone, string $message): string;
```

#### `ReportFileService`

- حفظ PDF.
- إنشاء اسم آمن.
- إرجاع الرابط العام.
- تنظيف الملفات القديمة.

### شرط الإكمال

لا يحتوي `Accountant/DashboardController` على بناء HTML أو رسالة WhatsApp أو إدارة ملفات PDF.

---

## المرحلة 11: تفكيك `DailySalesController`

### التقسيم المستهدف

```text
DailySalesController
├── index
└── destroy

SaleUpdateController
└── update

ShiftDateController
└── update

SaleItemEditService
├── buildPlan
└── applyPlan

CreditCollectionService
├── operationsForPeriod
├── extractPayments
└── calculateProfitBreakdown
```

### مبدأ ترحيل المسارات

تبقى أسماء المسارات الحالية كما هي، ويتغير الكنترولر الهدف فقط؛ حتى لا تتأثر الواجهات.

### شرط الإكمال

- الكنترولر لا يحسب تكلفة أو ربحًا بنفسه.
- منطق تعديل المخزون داخل Service ذرّية تستخدم Transaction.
- اختبارات تعديل البيع وحذف العملية ناجحة.

---

## المرحلة 12: تفكيك `Accountant/DashboardController`

### التقسيم المستهدف

```text
AccountantDashboardController
└── index

ShiftClosingController
├── store
└── show

ShiftReportController
├── show
└── viewFile
```

### الخدمات

```text
ShiftClosingService
FinancialSummaryService
ShiftReportService
WhatsAppReportService
ReportFileService
```

### الشكل المتوقع لإغلاق الشفت

```php
public function store(
    CloseShiftRequest $request,
    ShiftClosingService $service
) {
    $result = $service->close(
        accountant: auth('accountant')->user(),
        actualCash: $request->validated('actual_cash'),
        notes: $request->validated('notes'),
    );

    return redirect()
        ->route('accountant.balance.show', $result->balance->id)
        ->with('success', 'تم إغلاق الشفت بنجاح.');
}
```

### شرط الإكمال

`storeBalance()` القديم لا يبقى مسؤولًا عن الحساب والحفظ وPDF وWhatsApp معًا.

---

## المرحلة 13: تفكيك `StoreController`

### التقسيم المستهدف

```text
StoreController
StoreTrashController
StoreCatalogController
StoreReportController
StoreMonthlyReportController
StoreStatisticsController
```

### قاعدة إلزامية

لا تحتوي هذه الكنترولرات على معادلات مثل:

```text
SUM(paid_amount)
SUM(products_total)
products_total - cost
profit - expenses
```

بل تستخدم:

```php
$summary = $financialSummaryService->forStorePeriod(...);
```

### شرط الإكمال

- إدارة المتجر منفصلة عن التقارير.
- التقارير الشهرية تستخدم الملخص المركزي.
- PDF يستخدم بيانات التقرير نفسها دون إعادة الحساب.

---

## المرحلة 14: تبسيط `UserDashboardController`

### الهدف

تحويله إلى منسق بيانات بدل حاسب مالي مستقل.

### يبقى مسؤولًا عن

- اختيار المتاجر.
- اختيار التاريخ والشهر.
- استدعاء الخدمات.
- ترتيب أفضل وأضعف متجر.
- تمرير النتائج للواجهة.

### لا يبقى مسؤولًا عن

- معادلة المستلم.
- حساب تكلفة المنتجات.
- حساب الربح.
- تعريف نوافذ الشفت.
- تصنيف المصروفات.

### شرط الإكمال

أرقام لوحة المالك تأتي من `FinancialSummaryService` و`ShiftWindowService` فقط.

---

## المرحلة 15: توحيد الواجهات

### الهدف

منع الحسابات داخل Blade.

### ممنوع

```php
@php
    $profit = $sales - $cost - $expenses;
@endphp
```

### مطلوب

```php
{{ $summary->salesReceived }}
{{ $summary->productsCost }}
{{ $summary->recognizedProfit }}
{{ $summary->netResult }}
```

### الواجهات المستهدفة

- [ ] `resources/views/user/stores/daily.blade.php`
- [ ] `resources/views/dashboard/user/index.blade.php`
- [ ] `resources/views/dashboard/accountant/index.blade.php`
- [ ] `resources/views/user/stores/reports/monthly.blade.php`
- [ ] `resources/views/pdf/pdf_report.blade.php`

---

## المرحلة 16: شاشة التدقيق المالي

### الهدف

كشف البيانات التاريخية غير المتناسقة والفروقات المستقبلية.

### المسار المقترح

```text
/user/stores/{store}/financial-audit
```

### تعرض

- الفترة.
- عدد العمليات.
- مجموع `paid_amount`.
- مجموع `cash_amount + card_amount`.
- المبلغ المستلم المعتمد.
- تكلفة المنتجات.
- الربح.
- المصروفات.
- السحوبات.
- الفروقات.

### التنبيهات

- [ ] `cash + card` لا يساوي `paid_amount`.
- [ ] `remaining_amount` لا يساوي قيمة العملية ناقص المستلم.
- [ ] عنصر بيع بلا منتج.
- [ ] عنصر بيع بلا سعر تكلفة.
- [ ] كمية سالبة.
- [ ] شفتان متداخلان.
- [ ] عملية خارج جميع نوافذ الشفت.

---

## المرحلة 17: المقارنة والمراقبة

### سجلات مؤقتة مقترحة

```text
financial_summary_mismatch
sale_received_mismatch
sale_cost_mismatch
shift_window_mismatch
```

### البيانات

```php
[
    'store_id' => $storeId,
    'sale_id' => $saleId,
    'old_value' => $oldValue,
    'new_value' => $newValue,
    'difference' => $newValue - $oldValue,
]
```

### حد الفرق

```text
0.01 ريال
```

أي فرق أكبر يسجل للتحقيق ولا يُخفى تلقائيًا.

---

## المرحلة 18: إزالة الكود القديم

لا يبدأ الحذف إلا بعد:

- [ ] نجاح جميع الاختبارات.
- [ ] انتهاء فترة المقارنة.
- [ ] معالجة الفروقات المسجلة.
- [ ] اعتماد نتائج اليوم والشهر والشفت.
- [ ] توثيق المعادلات النهائية.

### ما يُحذف

- الحسابات المالية المكررة داخل الكنترولرات.
- Helpers المكررة لتكلفة الرول والطقم.
- نسخ منطق نوافذ الشفت.
- فروع fallback التاريخية غير المستخدمة.
- بناء بيانات PDF وWhatsApp من الكنترولرات.

---

# ترتيب الإصدارات المقترح

## الإصدار 1: الأساس والاختبارات

```text
1. توثيق القواعد المالية
2. إنشاء Fixtures
3. اختبارات المبلغ المستلم
4. اختبارات تكلفة المنتج
5. اختبارات الربح
6. اختبارات الشفت
```

## الإصدار 2: الحسابات المركزية

```text
7. SaleAmountCalculator
8. SaleItemCostCalculator
9. SaleProfitCalculator
10. ExpenseSummaryService
```

## الإصدار 3: الفترات والملخصات

```text
11. ShiftWindowService
12. FinancialSummaryService
13. شاشة التدقيق المالي
```

## الإصدار 4: توحيد الصفحات

```text
14. صفحة المبيعات اليومية
15. إغلاق الشفت
16. لوحة المالك اليومية
17. التقرير الشهري
18. المخطط
19. PDF
20. WhatsApp
```

## الإصدار 5: تفكيك الكنترولرات

```text
21. DailySalesController
22. Accountant/DashboardController
23. StoreController
24. UserDashboardController
```

## الإصدار 6: التنظيف النهائي

```text
25. حذف الحسابات القديمة
26. حذف الدوال المكررة
27. تحسين أسماء الحقول
28. تحديث وثائق المشروع
29. اختبارات End-to-End
30. إصدار مستقر
```

---

# الأولويات

## حرجة

1. اختبارات الحسابات.
2. `SaleAmountCalculator`.
3. `SaleItemCostCalculator`.
4. `ShiftWindowService`.
5. `FinancialSummaryService`.

## متوسطة

6. توحيد التقرير الشهري.
7. توحيد PDF وWhatsApp.
8. `ExpenseSummaryService`.
9. شاشة التدقيق المالي.

## تنظيمية

10. تفكيك الكنترولرات.
11. نقل Validation إلى Form Requests.
12. اعتماد DTOs.
13. تنظيف أسماء الدوال والحقول.

---

# معايير القبول النهائية

## تطابق المبيعات

لنفس المتجر والفترة:

```text
صفحة المبيعات اليومية
= لوحة المالك
= إغلاق الشفت
= PDF
= WhatsApp
```

## تطابق التكلفة

```text
تكلفة عناصر العمليات
= تكلفة ملخص اليوم
= تكلفة الشفت
= تكلفة الشهر
```

## تطابق الربح

```text
قيمة بيع المنتجات - تكلفة المنتجات = ربح المنتجات
```

مع عرض مستقل للربح المؤجل.

## تطابق الفترات

جميع الصفحات تستخدم نافذة الشفت نفسها، بما فيها الشفتات التي تعبر منتصف الليل.

## الكنترولرات

- يفضّل ألا يتجاوز الكنترولر 300–400 سطر.
- لا يحتوي الكنترولر على معادلات مالية معقدة.
- لا يجمع الكنترولر بين إغلاق الشفت وPDF وWhatsApp.
- لكل كنترولر مسؤولية رئيسية واحدة.

## الاختبارات

- [ ] جميع أنواع الدفع.
- [ ] جميع أنواع المنتجات.
- [ ] الآجل والتحصيلات.
- [ ] الشفت العادي والعابر لمنتصف الليل.
- [ ] تعديل منتجات العملية.
- [ ] تسوية المخزون.
- [ ] إغلاق الشفت.
- [ ] الملخص اليومي والشهري.

---

# قائمة تحقق سريعة

```text
[ ] توثيق القواعد المالية
[ ] إنشاء قاعدة اختبار مستقلة
[ ] اختبارات المبلغ المستلم
[ ] اختبارات تكلفة المنتج
[ ] اختبارات الربح
[ ] اختبارات الشفت
[ ] إنشاء DTOs
[ ] إنشاء SaleAmountCalculator
[ ] إنشاء SaleItemCostCalculator
[ ] إنشاء SaleProfitCalculator
[ ] إنشاء ShiftWindowService
[ ] إنشاء ExpenseSummaryService
[ ] إنشاء FinancialSummaryService
[ ] ربط صفحة المبيعات
[ ] ربط إغلاق الشفت
[ ] ربط لوحة المالك
[ ] ربط التقرير الشهري
[ ] ربط المخطط
[ ] ربط PDF
[ ] ربط WhatsApp
[ ] إضافة شاشة التدقيق
[ ] مقارنة الحساب القديم والجديد
[ ] تفكيك DailySalesController
[ ] تفكيك Accountant/DashboardController
[ ] تفكيك StoreController
[ ] تبسيط UserDashboardController
[ ] حذف الكود القديم
[ ] تشغيل الاختبارات الكاملة
[ ] اعتماد الإصدار النهائي
```

---

# سياسة تنفيذ Pull Requests

كل Pull Request يجب أن:

1. يعالج خطوة واحدة محددة.
2. يوضح هل هو **استخراج دون تغيير سلوك** أو **تغيير قاعدة مالية**.
3. يحتوي على اختبارات للحالات المتأثرة.
4. يعرض مقارنة قبل/بعد عند تعديل معادلة.
5. لا يجمع إعادة هيكلة واسعة مع تعديل واجهة غير مرتبط.
6. يذكر خطة التراجع عند المساس بإغلاق الشفت أو المخزون.

### أمثلة عناوين

```text
test: cover received sale amount scenarios
refactor: extract sale amount calculator
refactor: centralize sale item cost calculation
refactor: centralize shift window resolution
refactor: use financial summary in daily sales
refactor: use financial summary in owner dashboard
refactor: split shift closing from accountant dashboard
```

---

# ملاحظات مهمة

- هذه الخارطة لا تعني أن المشروع غير قابل للاستخدام حاليًا؛ إنها خطة لتقليل مخاطر التطوير المستقبلي.
- الأولوية لتوحيد الحسابات قبل تفكيك الملفات؛ لأن نقل المعادلات المكررة كما هي إلى كنترولرات أصغر لا يحل مشكلة اختلاف النتائج.
- يجب عدم تنفيذ إعادة الهيكلة على بيانات الإنتاج دون نسخة احتياطية وتجربة مسبقة على نسخة مماثلة.
- أي اختلاف تاريخي في البيانات يجب أن يظهر في شاشة التدقيق، لا أن يُصحح بصمت.
