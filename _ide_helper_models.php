<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * Class Absence
 * 
 * يمثل غياب موظف داخل متجر معيّن.
 * يحتوي على:
 * - تاريخ الغياب
 * - قيمة الخصم
 * - حالة الغياب
 * - الشهر المحسوب عليه
 *
 * @property int $id
 * @property int $store_id
 * @property int $person_id
 * @property string|null $person_type
 * @property string $date
 * @property string|null $description
 * @property numeric $penalty_amount
 * @property string $status
 * @property string $month
 * @property string|null $deducted_month
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $addedBy
 * @property-read \App\Models\Employee|null $employee
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $person
 * @property-read \App\Models\Store|null $store
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereDeductedMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence wherePenaltyAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence wherePersonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence wherePersonType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absence withoutTrashed()
 */
	class Absence extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $employee_id
 * @property int $user_id
 * @property int|null $store_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $password
 * @property string $role
 * @property string $status
 * @property string|null $suspension_reason
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\Store|null $store
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant forUserStores()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereSuspensionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accountant withoutTrashed()
 */
	class Accountant extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Category
 * 
 * يمثل قسم المنتجات داخل متجر معيّن.
 * كل قسم ينتمي لمتجر واحد، ويمكن أن يحتوي على عدة منتجات.
 *
 * @property int $id
 * @property int $store_id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_main_category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \App\Models\Store $store
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsMainCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withoutTrashed()
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * ClassCreditSale
 * 
 * يمثل عملية بيع آجل قام بها موظف داخل متجر معيّن.
 * تحتوي على:
 * - قيمة البيع
 * - الشهر المحسوب عليه
 * - الشهر الذي سيتم الخصم فيه
 * - حالة العملية
 *
 * @property int $id
 * @property int $person_id
 * @property string|null $person_type
 * @property int $store_id
 * @property numeric $amount
 * @property numeric $remaining_amount
 * @property array<array-key, mixed>|null $partial_payments
 * @property string|null $description
 * @property string $date
 * @property string $status
 * @property string $month
 * @property string|null $deducted_month
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $addedBy
 * @property-read \App\Models\Employee|null $employee
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $person
 * @property-read \App\Models\Store $store
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereDeductedMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale wherePartialPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale wherePersonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale wherePersonType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereRemainingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditSale withoutTrashed()
 */
	class CreditSale extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $store_id
 * @property int $accountant_id
 * @property numeric $system_sales_total
 * @property numeric $system_cash_expected
 * @property numeric $actual_cash_submitted
 * @property numeric $difference
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Accountant $accountant
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance whereAccountantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance whereActualCashSubmitted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance whereDifference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance whereSystemCashExpected($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance whereSystemSalesTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyBalance whereUpdatedAt($value)
 */
	class DailyBalance extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Debt
 * 
 * يمثل مديونية على موظف داخل متجر معيّن.
 * تحتوي على:
 * - قيمة المديونية
 * - نوعها (خصم، سلفة، إلخ)
 * - الشهر المحسوب عليه
 * - الشهر الذي سيتم الخصم فيه
 *
 * @property int $id
 * @property int $store_id
 * @property int $person_id
 * @property string|null $person_type
 * @property numeric $amount
 * @property string|null $description
 * @property string|null $date
 * @property string $type
 * @property string $status
 * @property string $month
 * @property string|null $deducted_month
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $addedBy
 * @property-read \App\Models\Employee|null $employee
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $person
 * @property-read \App\Models\Store|null $store
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereDeductedMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt wherePersonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt wherePersonType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Debt withoutTrashed()
 */
	class Debt extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $accountant_id
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken whereAccountantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken whereUserId($value)
 */
	class DeviceToken extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Employee
 * 
 * يمثل موظفًا داخل متجر معيّن.
 * الموظف يمكن أن يكون له:
 * - سحبيات
 * - مديونيات
 * - غيابات
 * - تقارير رواتب
 * - عمليات بيع آجل
 *
 * @property int $id
 * @property int|null $user_id
 * @property int $store_id
 * @property string $role
 * @property string $name
 * @property string|null $phone
 * @property numeric $salary
 * @property string $status
 * @property int|null $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Absence> $absences
 * @property-read int|null $absences_count
 * @property-read \App\Models\Accountant|null $accountant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditSale> $creditSales
 * @property-read int|null $credit_sales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Debt> $debts
 * @property-read int|null $debts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeLog> $logs
 * @property-read int|null $logs_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $person
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalaryReport> $salaryReports
 * @property-read int|null $salary_reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sale> $sales
 * @property-read int|null $sales_count
 * @property-read \App\Models\Store $store
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Withdrawal> $withdrawals
 * @property-read int|null $withdrawals_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee withoutTrashed()
 */
	class Employee extends \Eloquent {}
}

namespace App\Models{
/**
 * Class EmployeeLog
 * 
 * يمثل سجلًا لأي عملية تمت على الموظف:
 * - سحب
 * - غياب
 * - خصم
 * - إضافة
 * - تعديل راتب
 *
 * @property int $id
 * @property int $person_id
 * @property string $person_type
 * @property int $store_id
 * @property string|null $action_name
 * @property numeric|null $amount
 * @property array<array-key, mixed>|null $meta
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $addedBy
 * @property-read \App\Models\Employee|null $employee
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $loggable
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $person
 * @property-read \App\Models\Store|null $store
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog whereActionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog wherePersonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog wherePersonType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeLog withoutTrashed()
 */
	class EmployeeLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $store_id
 * @property int $user_id
 * @property string|null $type
 * @property int|null $employee_id
 * @property string|null $actor_type
 * @property string $description
 * @property numeric $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\Store|null $store
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereActorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense withoutTrashed()
 */
	class Expense extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $store_id
 * @property int $user_id
 * @property int $product_id
 * @property int $quantity_change
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Store|null $store
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog whereQuantityChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryLog withoutTrashed()
 */
	class InventoryLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $sale_id
 * @property string $invoice_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $customer_name
 * @property string|null $customer_phone
 * @property string|null $vehicle_type
 * @property string|null $plate_number
 * @property string|null $notes
 * @property string|null $description
 * @property string|null $tax_number
 * @property numeric $subtotal
 * @property numeric $tax_amount
 * @property numeric $total_amount
 * @property string $status
 * @property-read mixed $zatca_qr_code
 * @property-read \App\Models\Sale $sale
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCustomerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice wherePlateNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereVehicleType($value)
 */
	class Invoice extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $store_id
 * @property int|null $user_id
 * @property string|null $actor_type
 * @property int|null $actor_id
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string $action
 * @property string|null $description
 * @property array<array-key, mixed>|null $details
 * @property string|null $ip
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $actor
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $model
 * @property-read \App\Models\Store|null $store
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereActorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereActorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log withoutTrashed()
 */
	class Log extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $sender_id
 * @property string $sender_type
 * @property string $target_type
 * @property array<array-key, mixed>|null $target_ids
 * @property string $title
 * @property string $message
 * @property string|null $template_key
 * @property string $channel
 * @property array<array-key, mixed>|null $read_by
 * @property array<array-key, mixed>|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification forUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification unreadCountFor($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification unreadFor($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereReadBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereSenderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTargetIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTemplateKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $app_id
 * @property string|null $api_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OneSignalSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OneSignalSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OneSignalSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OneSignalSetting whereApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OneSignalSetting whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OneSignalSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OneSignalSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OneSignalSetting whereUpdatedAt($value)
 */
	class OneSignalSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Plan
 * 
 * يمثل خطة اشتراك في نظام CARLED.
 *
 * @property int $id
 * @property string $name
 * @property int $allowed_stores
 * @property int $allowed_accountants
 * @property numeric $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereAllowedAccountants($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereAllowedStores($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan withoutTrashed()
 */
	class Plan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $store_id
 * @property int $user_id
 * @property int|null $category_id
 * @property string $product_type
 * @property numeric $roll_length طول الرول الكامل بالأمتار (للمنتجات من نوع fractional)
 * @property numeric $waste_percentage
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $barcode
 * @property numeric $price
 * @property numeric|null $cost_price
 * @property numeric|null $quantity
 * @property int $min_stock
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductFraction> $fractions
 * @property-read int|null $fractions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SaleItem> $saleItems
 * @property-read int|null $sale_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockMovement> $stockMovements
 * @property-read int|null $stock_movements_count
 * @property-read \App\Models\Store $store
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product lowStock()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCostPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMinStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereRollLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWastePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withoutTrashed()
 */
	class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $product_id
 * @property string $option_label
 * @property numeric $deduction_value
 * @property numeric $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFraction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFraction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFraction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFraction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFraction whereDeductionValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFraction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFraction whereOptionLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFraction wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFraction whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductFraction whereUpdatedAt($value)
 */
	class ProductFraction extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Purchase
 * 
 * يمثل عملية شراء (إضافة مخزون) داخل متجر معيّن.
 *
 * @property int $id
 * @property int $store_id
 * @property int $user_id
 * @property int $product_id
 * @property int $quantity
 * @property numeric $cost
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Store|null $store
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase withoutTrashed()
 */
	class Purchase extends \Eloquent {}
}

namespace App\Models{
/**
 * Class SalaryReport
 * 
 * يمثل تقرير الراتب النهائي للموظف لشهر معيّن.
 * يحتوي على:
 * - الراتب الأساسي
 * - إجمالي السحبيات
 * - إجمالي الغيابات
 * - إجمالي المديونيات العادية
 * - إجمالي البيع الآجل
 * - الديون السابقة
 * - المكافآت
 * - الخصومات الإضافية
 * - الراتب النهائي
 *
 * @property int $id
 * @property int $person_id
 * @property string|null $person_type
 * @property int $store_id
 * @property int $user_id
 * @property string $month
 * @property string $year
 * @property numeric $base_salary
 * @property numeric $total_withdrawals
 * @property numeric $total_absences
 * @property numeric $total_normal_debts
 * @property numeric $total_credit_sales
 * @property numeric $previous_debts
 * @property numeric $bonus
 * @property numeric $extra_deduction
 * @property numeric $final_salary
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $person
 * @property-read \App\Models\Store $store
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereBaseSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereBonus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereExtraDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereFinalSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport wherePersonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport wherePersonType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport wherePreviousDebts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereTotalAbsences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereTotalCreditSales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereTotalNormalDebts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereTotalWithdrawals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryReport withoutTrashed()
 */
	class SalaryReport extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $store_id
 * @property int $accountant_id
 * @property int|null $employee_id
 * @property numeric $total
 * @property float $paid_amount
 * @property float $remaining_amount
 * @property string $sale_type
 * @property bool $has_invoice
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $products_total
 * @property int $tax_rate
 * @property float $labor_total
 * @property float $final_total
 * @property float $profit
 * @property-read \App\Models\Accountant|null $accountant
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\Invoice|null $invoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SaleItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Store $store
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereAccountantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereFinalTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereHasInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereLaborTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereProductsTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereProfit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereRemainingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereSaleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereUpdatedAt($value)
 */
	class Sale extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $sale_id
 * @property int $product_id
 * @property int $quantity
 * @property numeric $price
 * @property numeric $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $fraction_id
 * @property int $is_custom
 * @property string|null $custom_name
 * @property numeric|null $custom_consumption
 * @property numeric|null $custom_meters
 * @property numeric|null $roll_length_at_sale
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Sale $sale
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereCustomConsumption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereCustomMeters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereCustomName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereFractionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereIsCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereRollLengthAtSale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SaleItem whereUpdatedAt($value)
 */
	class SaleItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $store_id
 * @property int $product_id
 * @property int|null $user_id
 * @property string $type
 * @property numeric $quantity
 * @property numeric|null $meters
 * @property numeric|null $roll_length_at_movement
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Store $store
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereMeters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereRollLengthAtMovement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereUserId($value)
 */
	class StockMovement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $tax_number
 * @property string|null $commercial_registration
 * @property array<array-key, mixed>|null $bank_accounts
 * @property string|null $logo
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string $status
 * @property string|null $suspension_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $number_of_shifts
 * @property string|null $shift_1_start
 * @property string|null $shift_2_start
 * @property string|null $shift_3_start
 * @property int $force_shift_closure
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accountant> $accountants
 * @property-read int|null $accountants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees
 * @property-read int|null $employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Expense> $expenses
 * @property-read int|null $expenses_count
 * @property-read mixed $logo_url
 * @property-read mixed $owner_email
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SaleItem> $saleItems
 * @property-read int|null $sale_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sale> $sales
 * @property-read int|null $sales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockMovement> $stockMovements
 * @property-read int|null $stock_movements_count
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Withdrawal> $withdrawals
 * @property-read int|null $withdrawals_count
 * @method static \Database\Factories\StoreFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereBankAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereCommercialRegistration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereForceShiftClosure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereNumberOfShifts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereShift1Start($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereShift2Start($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereShift3Start($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereSuspensionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store withoutTrashed()
 */
	class Store extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Subscription
 * 
 * يمثل اشتراك مستخدم في نظام CARLED.
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property numeric $price
 * @property \Illuminate\Support\Carbon $start_at
 * @property \Illuminate\Support\Carbon $end_at
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscription withoutTrashed()
 */
	class Subscription extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $current_store_id
 * @property string $name
 * @property string $email
 * @property string|null $email_verified_at
 * @property string|null $phone
 * @property string $password
 * @property string $role
 * @property string $status
 * @property string|null $suspension_reason
 * @property \Illuminate\Support\Carbon|null $subscription_end_at
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $slug
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $plan_id
 * @property int $allowed_stores
 * @property int $allowed_accountants
 * @property bool $welcome_shown
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Accountant> $accountants
 * @property-read int|null $accountants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees
 * @property-read int|null $employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Log> $logs
 * @property-read int|null $logs_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Plan|null $plan
 * @property-read \App\Models\UserSetting|null $settings
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Store> $stores
 * @property-read int|null $stores_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User active()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User suspended()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User users()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAllowedAccountants($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAllowedStores($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCurrentStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSubscriptionEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSuspensionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWelcomeShown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $notifications_expiry
 * @property int $invoices_expiry
 * @property bool $email_notifications
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereEmailNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereInvoicesExpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereNotificationsExpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereUserId($value)
 */
	class UserSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Withdrawal
 * 
 * يمثل سحبًا ماليًا قام به موظف داخل متجر معيّن.
 * يحتوي على:
 * - قيمة السحب
 * - الشهر المحسوب عليه
 * - الشهر الذي سيتم الخصم فيه
 * - حالة العملية
 *
 * @property int $id
 * @property int $store_id
 * @property int $person_id
 * @property string|null $person_type
 * @property numeric $amount
 * @property string|null $description
 * @property string $date
 * @property string $status
 * @property string $month
 * @property string|null $deducted_month
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $addedBy
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $person
 * @property-read \App\Models\Store|null $store
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal byMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal byYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal forCurrentStore()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal forStore($storeId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereDeductedMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal wherePersonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal wherePersonType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Withdrawal withoutTrashed()
 */
	class Withdrawal extends \Eloquent {}
}

