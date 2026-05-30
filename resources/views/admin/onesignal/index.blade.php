@extends('dashboard.app')

@section('content')
<div class="max-w-3xl mx-auto py-8">

    <h2 class="text-2xl font-bold text-white mb-6">إعدادات OneSignal</h2>

    @if(session('success'))
        <div class="bg-green-600 text-white p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-600 text-white p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.onesignal.update') }}"
          class="bg-gray-800 p-6 rounded-lg border border-gray-700 mb-6">
        @csrf

        <div class="mb-4">
            <label class="text-gray-300 font-semibold">App ID</label>
            <input type="text" name="app_id"
                   value="{{ $settings->app_id ?? '' }}"
                   class="w-full mt-2 p-2 bg-gray-900 text-gray-200 rounded border border-gray-700">
        </div>

        <div class="mb-4">
            <label class="text-gray-300 font-semibold">API Key</label>
            <input type="text" name="api_key"
                   value="{{ $settings->api_key ?? '' }}"
                   class="w-full mt-2 p-2 bg-gray-900 text-gray-200 rounded border border-gray-700">
        </div>

        <button class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded">
            حفظ الإعدادات
        </button>
    </form>

    <form method="POST" action="{{ route('admin.onesignal.test') }}"
          class="bg-gray-800 p-6 rounded-lg border border-gray-700">
        @csrf
        <button class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded">
            إرسال إشعار تجريبي
        </button>
    </form>

</div>
@endsection
