@extends('layouts.app')

@section('title', 'Produse – VOLTA')

@section('content')
<div style="padding: 20px;">
  <h1 style="color: #fff; margin-bottom: 20px;">Produse</h1>
  <p style="color: #999;">Lista produselor va fi afișată aici.</p>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('css/produse.css') }}">
@endpush

