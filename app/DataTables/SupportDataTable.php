<?php

/**
 * Support DataTable
 *
 * @package     Gofer
 * @subpackage  DataTable
 * @category    Support
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\Support;
use Yajra\DataTables\Services\DataTable;
use DB;

class SupportDataTable extends DataTable
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
            ->addColumn('action', function ($query) {
                $edit = checkPermission('update-support') ? '<a title="' . trans('admin_messages.edit_support') . '" href="' . route('admin.edit_support', $query->id) . '" ><i class="material-icons">edit</i></a>' : '';
                if($query->id !=1 &&  $query->id != 2)
                {
                    $delete = checkPermission('delete-support') ? '<a title="' . trans('admin_messages.delete_support') . '" href="javascript:void(0)" class="confirm-delete" data-href="' . route('admin.delete_support', $query->id) . '"><i class="material-icons">close</i></a>' : '';
                }
                else
                {
                    $delete = '';
                }
                return $edit." &nbsp; ".$delete;
            });
    }   

    /**
     * Get query source of dataTable.
     *
     * @param Support $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Support $model)
    {
        return $model->all();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->columns($this->getColumns())
                    ->addColumn(['data' => 'name', 'name' => 'name', 'title' => 'Name','orderable' => false])  
                    ->addColumn(['data' => 'link', 'name' => 'link', 'title' => 'Link','orderable' => false])   
                    ->addAction()
                    ->minifiedAjax()
                    ->dom('lBfr<"table-responsive"t>ip')
                    ->orderBy(0,'DESC')
                    ->buttons(
                        ['csv', 'excel', 'print']
                    );
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
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'support_' . date('YmdHis');
    }
}