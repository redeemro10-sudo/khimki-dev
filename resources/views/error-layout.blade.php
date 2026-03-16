@extends('layouts.app')

@push('head')
    <meta name="robots" content="noindex,follow">
@endpush

@section('content')
    @include('partials.error-page', ['statusCode' => $statusCode ?? 404])
@endsection
