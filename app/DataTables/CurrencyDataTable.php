<?php

namespace App\DataTables;

use App\Models\Currency;
use Yajra\DataTables\Services\DataTable;

class CurrencyDataTable extends DataTable {
	/**
	 * Build DataTable class.
	 *
	 * @param mixed $query Results from query() method.
	 * @return \Yajra\DataTables\DataTableAbstract
	 */
	public function dataTable($query) {

		return datatables($query)

			->addColumn('action', function ($query) {
				$edit = checkPermission('update-currency') ? '<a title="' . trans('admin_messages.edit') . '" href="' . route('admin.edit_currency', $query->id) . '" ><i class="material-icons">edit</i></a>' : '';
				$delete = checkPermission('delete-currency') ? '<a title="' . trans('admin_messages.delete') . '" href="javascript:void(0)" class="confirm-delete" data-href="' . route('admin.delete_currency', $query->id) . '"><i class="material-icons">close</i></a>' : '';
				return $edit." &nbsp; ".$delete;
			})
			->addColumn('rate', function ($query) {
				$rate_value=  number_format($query->rate, 2) ;
				 return str_replace( ',', '', $rate_value);
			})
			->addColumn('currency_status', function ($query) {
				return $query->currency_status;
			});
	}

	/**
	 * Get query source of dataTable.
	 *
	 * @param \App\User $model
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function query() {
		return Currency::get();
	}

	/**
	 * Optional method if you want to use html builder.
	 *
	 * @return \Yajra\DataTables\Html\Builder
	 */
	public function html() {
		return $this->builder()
			->columns(['id', 'name', 'code'])
			->addColumn(['data' => 'rate', 'name' => 'rate', 'title' => 'Rate'])
			->addColumn(['data' => 'currency_status', 'name' => 'currency_status', 'title' => 'Status'])
			->addAction(['width' => '80px', 'printable' => false])
			->parameters([
				'order' => [0, 'desc'],
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
		return 'Currency_' . date('YmdHis');
	}
}
