@extends('layouts.app')

@push('head')
    <meta name="robots" content="noindex,follow">
@endpush

@section('content')
    @php(status_header(410))

    @include('partials.error-page', ['statusCode' => 410])
@endsection
