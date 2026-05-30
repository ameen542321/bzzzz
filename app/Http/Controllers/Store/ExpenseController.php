<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Notification;
use App\Models\Store;
use App\Services\EmployeeLogService;
use App\Support\ArabicPdf;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request, ?int $store = null)
    {
        [$storeModel, $storeId, $accountant, $user] = $this->resolveStoreContext($store);

        $month = max(1, min(12, (int) ($request->month ?? now()->month)));
        $year = (int) ($request->year ?? now()->year);

        $expenses = Expense::where('store_id', $storeId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->latest()
            ->get();

        $total = (float) $expenses->sum('amount');
        $ownerPurchasesTotal = (float) $expenses->where('actor_type', 'owner_purchase')->sum('amount');
        $operationalTotal = (float) ($total - $ownerPurchasesTotal);
        $ownerPurchaseGroups = $this->buildOwnerPurchaseGroups($expenses);

        return view('accountants.pos.expense', [
            'expenses' => $expenses,
            'total' => $total,
            'month' => $month,
            'year' => $year,
            'ownerPurchasesTotal' => $ownerPurchasesTotal,
            'operationalTotal' => $operationalTotal,
            'storeModel' => $storeModel,
            'isAccountant' => (bool) $accountant,
            'isOwnerInternalUseRoute' => request()->routeIs('user.stores.internal-use.add-consumption'),
            'ownerPurchaseGroups' => $ownerPurchaseGroups,
            'ownerPurchaseTypeOptions' => array_values($this->ownerPurchaseGroupingMap()),
        ]);
    }

    public function store(Request $request, ?int $store = null)
    {
        [$storeModel, $storeId, $accountant, $user, $actor] = $this->resolveStoreContext($store, true);

        $validated = $request->validate([
            'type' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
            'consumption_source' => 'nullable|in:operational,direct_purchase',
        ]);

        $source = $validated['consumption_source'] ?? 'operational';
        $isAccountant = (bool) $accountant;

        if ($isAccountant && $source === 'direct_purchase') {
            return back()->with('error', 'لا يمكن للمحاسب تسجيل مشتريات المالك المباشرة.');
        }

        $actorType = $source === 'direct_purchase' ? 'owner_purchase' : 'operational_expense';
        $type = trim((string) ($validated['type'] ?? ''));
        if ($type === '') {
            $type = 'عام';
        }

        if ($source === 'direct_purchase') {
            $type = $this->normalizeOwnerPurchaseType($type);
        }

        $description = trim((string) ($validated['description'] ?? ''));
        if ($description === '') {
            $description = $type;
        }

        Expense::create([
            'store_id' => $storeId,
            'user_id' => $actor->id,
            'type' => $type,
            'description' => $description,
            'amount' => $validated['amount'],
            'actor_type' => $actorType,
        ]);

        $amount = number_format((float) $validated['amount'], 2);
        $sourceText = 'عملية عامة';

        if ($isAccountant) {
            EmployeeLogService::add(
                $actor,
                'expense_added',
                "قام {$actor->name} بإضافة {$sourceText} بقيمة {$amount} ريال",
                $validated['amount'],
                'operation'
            );
        } else {
            \App\Helpers\LogHelper::add(
                'expense_added',
                "قام {$actor->name} بإضافة {$sourceText} بقيمة {$amount} ريال",
                $storeId
            );
        }

        Notification::create([
            'sender_id' => $actor->id,
            'sender_type' => $isAccountant ? 'accountant' : 'user',
            'target_type' => 'user',
            'target_ids' => [$storeModel->user_id],
            'title' => 'استهلاك جديد',
            'message' => "قام {$actor->name} بإضافة {$sourceText} بقيمة {$amount} ريال",
            'template_key' => 'expense_added',
            'channel' => 'CARLED',
        ]);

        return back()->with('success', 'تم تسجيل العملية بنجاح');
    }

    public function exportPdf(Request $request, ?int $store = null)
    {
        [$storeModel, $storeId] = $this->resolveStoreContext($store);

        $month = max(1, min(12, (int) ($request->month ?? now()->month)));
        $year = (int) ($request->year ?? now()->year);

        $expenses = Expense::where('store_id', $storeId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->latest()
            ->get();

        $data = [
            'store' => $storeModel,
            'expenses' => $expenses,
            'month' => $month,
            'year' => $year,
            'total' => (float) $expenses->sum('amount'),
            'ownerPurchasesTotal' => (float) $expenses->where('actor_type', 'owner_purchase')->sum('amount'),
            'operationalTotal' => (float) $expenses->where('actor_type', '!=', 'owner_purchase')->sum('amount'),
            'ownerPurchaseGroups' => $this->buildOwnerPurchaseGroups($expenses),
            'generatedAt' => now()->format('Y-m-d H:i'),
        ];

        $pdf = ArabicPdf::loadView('pdf.expense-consumption', $data)
            ->setOption('encoding', 'utf-8')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10);

        return $pdf->download("تقرير-الاستهلاك-{$year}-" . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . ".pdf");
    }

    public function destroy($id)
    {
        $accountant = auth('accountant')->user();
        $owner = auth()->user();

        if (!$accountant && !$owner) {
            abort(403, 'غير مصرح بالدخول');
        }

        $expense = Expense::findOrFail($id);

        if ($accountant) {
            if ((int) $expense->store_id !== (int) $accountant->store_id) {
                abort(403, 'لا تملك صلاحية حذف هذه العملية');
            }
        } else {
            $store = Store::find($expense->store_id);
            if (!$store || (int) $store->user_id !== (int) $owner->id) {
                abort(403, 'لا تملك صلاحية حذف هذه العملية');
            }
        }

        $expense->delete();

        return back()->with('success', 'تم حذف المصروف بنجاح');
    }

    public function update(Request $request, $id)
    {
        $owner = auth()->user();

        if (!$owner) {
            abort(403, 'غير مصرح بالدخول');
        }

        $expense = Expense::findOrFail($id);
        $store = Store::find($expense->store_id);

        if (!$store || (int) $store->user_id !== (int) $owner->id) {
            abort(403, 'لا تملك صلاحية تعديل هذه العملية');
        }

        $request->validate([
            'type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $expense->update([
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => trim((string) $request->description) !== '' ? $request->description : $request->type,
        ]);

        return back()->with('success', 'تم تعديل المصروف بنجاح');
    }


    private function buildOwnerPurchaseGroups($expenses)
    {
        return $expenses
            ->where('actor_type', 'owner_purchase')
            ->groupBy(function ($expense) {
                return $this->normalizeOwnerPurchaseType((string) $expense->type);
            })
            ->map(function ($group, $name) {
                return [
                    'name' => $name,
                    'total' => (float) $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    private function normalizeOwnerPurchaseType(string $type): string
    {
        $value = trim(mb_strtolower($type));
        if ($value === '') {
            return 'مشتريات أخرى';
        }

        foreach ($this->ownerPurchaseGroupingMap() as $keywords => $label) {
            foreach (explode('|', $keywords) as $keyword) {
                if ($keyword !== '' && mb_strpos($value, $keyword) !== false) {
                    return $label;
                }
            }
        }

        return $type;
    }

    private function ownerPurchaseGroupingMap(): array
    {
        return [
            'فطور|افطار|إفطار' => 'فطور',
            'غداء' => 'غداء',
            'عشاء|عشار' => 'عشاء',
            'خبز' => 'خبز',
        ];
    }

    private function resolveStoreContext(?int $store = null, bool $withActor = false): array
    {
        $accountant = auth('accountant')->user();
        $user = auth()->user();
        $actor = $accountant ?: $user;

        if (!$actor) {
            abort(403, 'غير مصرح بالدخول');
        }

        $storeModel = $store
            ? Store::findOrFail($store)
            : Store::findOrFail((int) $actor->store_id);

        if ($accountant && (int) $storeModel->id !== (int) $accountant->store_id) {
            abort(403, 'لا تملك صلاحية الوصول لهذا المتجر');
        }

        if (!$accountant && (int) $storeModel->user_id !== (int) $user->id) {
            abort(403, 'لا تملك صلاحية الوصول لهذا المتجر');
        }

        $payload = [$storeModel, (int) $storeModel->id, $accountant, $user];
        if ($withActor) {
            $payload[] = $actor;
        }

        return $payload;
    }
}
