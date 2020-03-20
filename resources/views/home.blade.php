@extends('layouts.app')

@section('content')
<div class="flex-center position-ref full-height">
	<div class="content">
	    <div class="title">
	        {{ config('app.name', 'Laravel') }}
	    </div>
	</div>
</div>
@endsection

@section('styles')
<style type="text/css">
.full-height {
    height: 93vh;
}
.flex-center {
    align-items: center;
    display: flex;
    justify-content: center;
}
.position-ref {
    position: relative;
}
.content {
    text-align: center;
}
.title {
    font-size: 84px;
}
</style>
@endsection
