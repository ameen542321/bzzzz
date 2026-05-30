@extends('dashboard.app')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold">سجل الاشتراكات</h1>
            <p class="text-gray-400 text-sm mt-1">عرض جميع الاشتراكات السابقة والحالية</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('subscription.renew') }}" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white transition flex items-center">
                <i class="fas fa-plus ml-2"></i>
                تجديد اشتراك
            </a>
            <a href="{{ route('dashboard') }}" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white transition">
                <i class="fas fa-arrow-right ml-2"></i>
                العودة
            </a>
        </div>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        @if($subscriptions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-right py-3 px-4 text-gray-400">#</th>
                            <th class="text-right py-3 px-4 text-gray-400">نوع الاشتراك</th>
                            <th class="text-right py-3 px-4 text-gray-400">المبلغ</th>
                            <th class="text-right py-3 px-4 text-gray-400">تاريخ البداية</th>
                            <th class="text-right py-3 px-4 text-gray-400">تاريخ النهاية</th>
                            <th class="text-right py-3 px-4 text-gray-400">تاريخ الإنشاء</th>
                            <th class="text-right py-3 px-4 text-gray-400">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $index => $sub)
                        <tr class="border-b border-gray-700 hover:bg-gray-750 transition">
                            <td class="py-3 px-4 text-gray-300">{{ $index + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="flex items-center">
                                    @if($sub->type === 'trial')
                                        <i class="fas fa-gift text-yellow-500 ml-2"></i>
                                    @elseif($sub->type === 'yearly')
                                        <i class="fas fa-calendar-alt text-green-500 ml-2"></i>
                                    @else
                                        <i class="fas fa-calendar text-blue-500 ml-2"></i>
                                    @endif
                                    @switch($sub->type)
                                        @case('monthly') اشتراك شهري @break
                                        @case('yearly') اشتراك سنوي @break
                                        @case('trial') اشتراك تجريبي @break
                                        @default {{ $sub->type }}
                                    @endswitch
                                </span>
                            </td>
                            <td class="py-3 px-4 text-gray-300 font-semibold">{{ number_format($sub->price) }} ريال</td>
                            <td class="py-3 px-4 text-gray-300">{{ \Carbon\Carbon::parse($sub->start_at)->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 text-gray-300">{{ \Carbon\Carbon::parse($sub->end_at)->format('Y-m-d') }}</td>
                            <td class="py-3 px-4 text-gray-300">{{ \Carbon\Carbon::parse($sub->created_at)->format('Y-m-d') }}</td>
                            <td class="py-3 px-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold flex items-center w-fit
                                    @if($sub->status === 'نشط') bg-green-500 bg-opacity-20 text-green-400 border border-green-500
                                    @elseif($sub->status === 'ملغي') bg-red-500 bg-opacity-20 text-red-400 border border-red-500
                                    @else bg-gray-500 bg-opacity-20 text-gray-400 border border-gray-500
                                    @endif">
                                    <i class="fas fa-circle text-xs ml-2"></i>
                                    {{ $sub->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- إحصائيات سريعة --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-700">
                <div class="bg-gray-900 rounded-lg p-4 text-center">
                    <p class="text-gray-400 text-sm mb-1">إجمالي الاشتراكات</p>
                    <p class="text-2xl font-bold text-white">{{ $subscriptions->count() }}</p>
                </div>
                <div class="bg-gray-900 rounded-lg p-4 text-center">
                    <p class="text-gray-400 text-sm mb-1">إجمالي المبلغ المدفوع</p>
                    <p class="text-2xl font-bold text-green-400">{{ number_format($subscriptions->sum('price')) }} ريال</p>
                </div>
                <div class="bg-gray-900 rounded-lg p-4 text-center">
                    <p class="text-gray-400 text-sm mb-1">الاشتراكات النشطة</p>
                    <p class="text-2xl font-bold text-blue-400">{{ $subscriptions->where('status', 'نشط')->count() }}</p>
                </div>
                <div class="bg-gray-900 rounded-lg p-4 text-center">
                    <p class="text-gray-400 text-sm mb-1">آخر اشتراك</p>
                    <p class="text-sm font-bold text-white">{{ $subscriptions->first() ? \Carbon\Carbon::parse($subscriptions->first()->created_at)->format('Y-m-d') : '-' }}</p>
                </div>
            </div>
        @else
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-history text-4xl text-gray-500"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">لا يوجد سجل اشتراكات</h3>
                <p class="text-gray-400 mb-6">لم تقم بأي اشتراك سابق. ابدأ رحلتك معنا الآن!</p>
                <a href="{{ route('subscription.renew') }}" class="inline-block bg-blue-600 hover:bg-blue-700 px-8 py-3 rounded text-white font-semibold transition transform hover:scale-105">
                    <i class="fas fa-rocket ml-2"></i>
                    ابدأ اشتراكك الآن
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
