@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">إدارة اشتراكات المستخدمين</h1>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 overflow-x-auto">

    <table class="w-full text-right min-w-[900px]">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">المستخدم</th>
                <th class="py-3">الخطة الحالية</th>
                <th class="py-3">تاريخ البداية</th>
                <th class="py-3">تاريخ الانتهاء</th>
                <th class="py-3">المدة المتبقية</th>
                <th class="py-3">العمليات</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            {{-- مثال (لاحقًا foreach) --}}
            <tr class="border-b border-gray-700">
                <td class="py-3">سعود – محاسب</td>
                <td class="py-3">الخطة المتقدمة</td>
                <td class="py-3">2025-01-01</td>
                <td class="py-3 text-blue-400">2025-04-01</td>
                <td class="py-3 text-green-400">70 يوم</td>
                <td class="py-3">
                    <a href="{{ route('admin.subscriptions.assign', 2) }}"
                       class="text-blue-400 hover:underline">
                        تعديل الاشتراك
                    </a>
                </td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">محمد – كاشير</td>
                <td class="py-3">الخطة الأساسية</td>
                <td class="py-3">2025-02-10</td>
                <td class="py-3 text-blue-400">2025-03-10</td>
                <td class="py-3 text-green-400">20 يوم</td>
                <td class="py-3">
                    <a href="{{ route('admin.subscriptions.assign', 3) }}"
                       class="text-blue-400 hover:underline">
                        تعديل الاشتراك
                    </a>
                </td>
            </tr>

        </tbody>
    </table>

</div>

@endsection
