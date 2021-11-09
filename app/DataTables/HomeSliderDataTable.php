<?php

namespace App\DataTables;

use App\Models\HomeSlider;
use Yajra\DataTables\Services\DataTable;

class HomeSliderDataTable extends DataTable {
	/**
	 * Build DataTable class.
	 *
	 * @param mixed $query Results from query() method.
	 * @return \Yajra\DataTables\DataTableAbstract
	 */
	public function dataTable($query) {

		return datatables($query)
			->addColumn('action', function ($query) {
				$edit = checkPermission('update-restaurant_slider') ? '<a title="' . trans('admin_messages.edit') . '" href="' . route('admin.edit_home_slider', $query->id) . '" ><i class="material-icons">edit</i></a>' : '';
				$delete = checkPermission('delete-restaurant_slider') ? '<a title="' . trans('admin_messages.delete') . '" href="javascript:void(0)" class="confirm-delete" data-href="' . route('admin.delete_home_slider', $query->id) . '"><i class="material-icons">close</i></a>' : '';
				return $edit." &nbsp; ".$delete;
			})
			->addColumn('status', function ($query) {
				return $query->status_text;
			});
	}

	/**
	 * Get query source of dataTable.
	 *
	 * @param \App\User $model
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function query() {
		return HomeSlider::get();
	}

	/**
	 * Optional method if you want to use html builder.
	 *
	 * @return \Yajra\DataTables\Html\Builder
	 */
	public function html() {
		return $this->builder()
			->columns(['id', 'title','description','status'])
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
		return 'HomeSlider_' . date('YmdHis');
	}
}
