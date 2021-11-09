@extends('template2')
@section('main')
<main id="site-content" role="main" class="log-user" ng-controller="signup_page">
	<div class="container">
		<div class="static-content py-4 py-md-5">
			{!! $page->content !!}
		</div>
	</div>
</main>
@stop