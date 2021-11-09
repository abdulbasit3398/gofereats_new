<?php

/**
 * Help Category DataTable
 *
 * @package     GoferEats
 * @subpackage  DataTable
 * @category    Help Category
 * @author      Trioangle Product Team
 * @version     1.5.8.2
 * @link        http://trioangle.com
 */


namespace App\DataTables;

use App\Models\HelpCategory;
use Yajra\DataTables\Services\DataTable;

class HelpCategoryDataTable extends DataTable
{
   /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query) {

        return datatables($query)
            ->addColumn('status', function ($query) {
                return $query->status_text;
            })
            ->addColumn('type', function ($query) {
                return $query->type_text;
            })
            ->addColumn('action', function ($query) {
                $edit = checkPermission('update-help_category') ? '<a title="' . trans('admin_messages.edit') . '" href="' . route('admin.edit_help_category', $query->id) . '" ><i class="material-icons">edit</i></a>' : '';
                $delete = checkPermission('delete-help_category') ? '<a title="' . trans('admin_messages.delete') . '" href="javascript:void(0)" class="confirm-delete" data-href="' . route('admin.delete_help_category', $query->id) . '"><i class="material-icons">close</i></a>' : '';
                return $edit." &nbsp; ".$delete;
            });
           
    }

    /**
     * Get the query object to be processed by datatables.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return HelpCategory::get();

    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->columns(['id','name','description','type','status'])
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
    protected function filename()
    {
        return 'help_category_' . date('YmdHis');
    }
}
