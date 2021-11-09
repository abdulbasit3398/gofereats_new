<?php

namespace App\DataTables;

use App\Models\DriverOweAmount;
use Yajra\DataTables\Services\DataTable;

class DriverOweAmountDataTable extends DataTable {
	/**
	 * Build DataTable class.
	 *
	 * @param mixed $query Results from query() method.
	 * @return \Yajra\DataTables\DataTableAbstract
	 */
	public function dataTable($query) {

		return datatables($query)
			->addColumn('user_name', function ($query) {
				return $query->user->name;
			})
			->addColumn('paid_amount', function ($query) {
				return currency_symbol().$query->paid_amount;
			})
			->addColumn('remaining_amount', function ($query) {
				return currency_symbol().$query->amount;
			});
	}

	/**
	 * Get query source of dataTable.
	 *
	 * @param \App\User $model
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function query() {

		return DriverOweAmount::where('user_id','!=','')->get();
	}

	/**
	 * Optional method if you want to use html builder.
	 *
	 * @return \Yajra\DataTables\Html\Builder
	 */
	public function html() {
		return $this->builder()
			->columns(['id','user_name'])
			->addColumn(['data' => 'paid_amount', 'name' => 'paid_amount', 'title' => 'Owe Amount'])
			->addColumn(['data' => 'remaining_amount', 'name' => 'remaining_amount', 'title' => 'Remaining Amount'])
			->parameters([
				'dom' => 'Bfrtip',
				'buttons' => ['csv','excel', 'print'],
			]);
	}

	/**
	 * Get filename for export.
	 *
	 * @return string
	 */
	protected function filename() {
		return 'DriverOweAmount_' . date('YmdHis');
	}
}
