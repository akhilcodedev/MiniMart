@extends('api::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('api.name') !!}</p>
@endsection
