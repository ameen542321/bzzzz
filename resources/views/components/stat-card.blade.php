@props([
    'title' => '',
    'value' => '',
    'color' => 'emerald'
])

@php
    $colors = [
        'emerald' => 'text-emerald-400',
        'red'     => 'text-red-400',
        'yellow'  => 'text-yellow-300',
        'indigo'  => 'text-indigo-300',
        'blue'    => 'text-blue-300',
        'gray'    => 'text-gray-300',
    ];

    $colorClass = $colors[$color] ?? 'text-white';
@endphp

<div class="bg-gray-900/70 border border-gray-800 rounded-2xl p-5">
    <p class="text-xs text-gray-400">{{ $title }}</p>
    <p class="text-2xl font-bold {{ $colorClass }} mt-2">
        {{ $value }}
    </p>
</div>
