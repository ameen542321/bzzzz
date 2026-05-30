@extends('dashboard.app')

@section('content')
    @include('user.stores.includes.store-form', ['store' => $store])
@endsection
