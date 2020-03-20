@extends('layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">    

    <!-- Content Row -->
    <div class="row">
    	<div class="col-md-12 text-center">
        	<img src="{{ asset('images/accessdenied.png') }}">
        	<br/>
        	<a href="{{ route('home') }}">&larr; Back to Previous</a>
        </div>
    </div>

</div>
<!-- /.container-fluid -->
@endsection

@section('scripts')
@endsection