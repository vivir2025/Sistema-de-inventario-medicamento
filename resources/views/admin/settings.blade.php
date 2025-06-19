
@extends('admin.layouts.app')
@php
    $title ='settings';
@endphp

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Configuración General</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="javascript:(0)">Configuración</a></li>
        <li class="breadcrumb-item active">Configuración General</li>
    </ul>
</div>
@endpush

@section('content')

<div class="row">				
    <div class="col-12">
        @include('app_settings::_settings')	
    </div>
</div>
@endsection