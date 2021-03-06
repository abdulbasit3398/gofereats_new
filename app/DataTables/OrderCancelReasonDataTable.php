<?php

namespace App\DataTables;

use App\Models\OrderCancelReason;
use Yajra\DataTables\Services\DataTable;

class OrderCancelReasonDataTable extends DataTable {
	/**
	 * Build DataTable class.
	 *
	 * @param mixed $query Results from query() method.
	 * @return \Yajra\DataTables\DataTableAbstract
	 */
	public function dataTable($query) {

		return datatables($query)
			->addColumn('action', function ($query) {
				$edit = checkPermission('update-cancel_reason') ? '<a title="' . trans('admin_messages.edit') . '" href="' . route('admin.edit_cancel_reason', $query->id) . '" ><i class="material-icons">edit</i></a>' : '';
				$delete = checkPermission('delete-cancel_reason') ? '<a title="' . trans('admin_messages.delete') . '" href="javascript:void(0)" class="confirm-delete" data-href="' . route('admin.delete_cancel_reason', $query->id) . '"><i class="material-icons">close</i></a>' : '';
				return $edit." &nbsp; ".$delete;
			})
			->addColumn('status', function ($query) {
				return $query->status_text;
			})
			->addColumn('user_type', function ($query) {
				return $query->user_type;
			});
	}

	/**
	 * Get query source of dataTable.
	 *
	 * @param \App\User $model
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function query() {
		return OrderCancelReason::get();
	}

	/**
	 * Optional method if you want to use html builder.
	 *
	 * @return \Yajra\DataTables\Html\Builder
	 */
	public function html() {
		return $this->builder()
			->columns(['id','name','user_type','status'])
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
		return 'OrderCancelReason_' . date('YmdHis');
	}
}
