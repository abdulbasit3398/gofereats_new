@extends('admin/template')
@section('main')
<div class="content">
	<div class="container-fluid">
		<div class="card">
		<div class="d-md-flex my-4 justify-content-between">
			<div class="col-md-6 text-center text-md-left">
				<a class="btn btn-normal" href="{{$link}}" >{{$name}} </a>
			</div>
			@if($week_payment > 0)
			<div class="col-md-6 text-center text-md-right">
				@if($payout_account_id)
				<form method="post" action="{{route('admin.week_amount_payout')}}" class="text-right">
					@csrf
					<input type="hidden" name="payout_id" value="{{$payout_id}}">
					<input type="hidden" name="amount" value="{{$week_payment}}">
					<input type="hidden" name="payout_account_id" value="{{$payout_account_id}}">
					<input type="hidden" name="start_date" value="{{$start_date}}">
					<input type="hidden" name="end_date" value="{{$end_date}}">
					<input type="hidden" name="user_id" value="{{$user_id}}">
					<button type="submit" class="btn btn-success">Payout to {{$name}} {{currency_symbol()}}  {{$week_payment}} </button>
					@if($payout_method == 'Manual')
						<a href="#" data-toggle="modal" data-target="#payout_preference" data-payout-details="{{ $payout_details }}">payout Info</a>
					@endif
				@else
				<form method="post" action="{{route('admin.need_payout_info')}}" class="text-right">
				@csrf
					<input type="hidden" name="user_id" value="{{$user_id}}">
					<input type="hidden" name="type" value="driver">	
					<button type="submit" class="btn btn-danger">Add payout to complete this payment </button>
				@endif
				</form>
			</div>
			@endif
			</div>
			<div class="table-responsive">
				{!! $dataTable->table() !!}
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="{{asset('admin_assets/css/buttons.dataTables.css')}}">
<script src="{{asset('admin_assets/js/dataTables.buttons.js')}}">
</script>
<script src={{url('vendor/datatables/buttons.server-side.js')}}></script>
{!! $dataTable->scripts() !!}

@endpush