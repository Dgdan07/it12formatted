@extends('layouts.app')
@section('title', 'Suppliers - ATIN Admin')
@push('styles')
<link href="{{ asset('css/page-style.css') }}" rel="stylesheet">
@endpush
@section('content')
    @include('components.alerts')
    
    <div class="page-header">
        <h2 class="mb-0">
            <b>Supplier Management</b>
        </h2>
    </div>

    <!-- Table -->
    <div class="table-container">
        
    </div>

@endsection