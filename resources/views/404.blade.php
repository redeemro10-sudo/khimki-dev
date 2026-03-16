@extends('layouts.app')

@push('head')
    <meta name="robots" content="noindex,follow">
@endpush

@section('content')
    @php($statusCode = (int) http_response_code() === 410 ? 410 : 404)

    @include('partials.error-page', ['statusCode' => $statusCode])
@endsection
