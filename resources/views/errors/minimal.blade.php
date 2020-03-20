@extends('layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="flex-center position-ref full-height">
    <div class="text-center page-error">
        <h1 class="code">404</h1>
        <h4 class="message">Page Not Found</h4>
        <a href="{{ URL::previous() }}">&larr; Back to Previous</a>
    </div>
</div>
<!-- /.container-fluid -->
@endsection

@section('styles')
<style type="text/css">
.full-height {
    height: 100vh;
}

.flex-center {
    align-items: center;
    display: flex;
    justify-content: center;
}

.position-ref {
    position: relative;
}
</style>
@endsection

@section('scripts')
@endsection