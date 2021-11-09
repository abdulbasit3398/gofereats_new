<?php

namespace App\DataTables;

use App\Models\ServiceType;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ServiceTypeDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        
        return datatables()
            ->of($query)
            ->addColumn('name', function ($query) {
                return $query->service_name;
            })
            ->addColumn('status', function ($query) {
                return $query->service_type_status;
            })
            ->addColumn('action', function ($query) {
                $edit = checkPermission('update-home_banner') ? '<a title="' . trans('admin_messages.edit') . '" href="' . route('admin.edit_home_banner', $query->id) . '" ><i class="material-icons">edit</i></a>' : '';
                return $edit;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\ServiceType $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ServiceType $model)
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('servicetype-table')
                    ->columns($this->getColumns())
                    ->dom('Bfrtip')
                    ->addAction()
                    ->parameters([
                        'order' => [0, 'desc'],
                        'dom' => 'Bfrtip',
                        'buttons' => ['csv','excel', 'print'],
                    ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'id',
            'name',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'home_banner_' . date('YmdHis');
    }
}
