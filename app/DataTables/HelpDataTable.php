<?php

/**
 * Help DataTable
 *
 * @package     GoferEats
 * @subpackage  DataTable
 * @category    Help
 * @author      Trioangle Product Team
 * @version     1.5.8.2
 * @link        http://trioangle.com
 */


namespace App\DataTables;

use App\Models\Help;
use Yajra\DataTables\Services\DataTable;

class HelpDataTable extends DataTable
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
            ->addColumn('category', function ($query) {
                return $query->category_name;
            })
            ->addColumn('sub_category', function ($query) {
                return $query->subcategory_name;
            })
            ->addColumn('type', function ($query) {
                return $query->category->type_text;
            })
            ->addColumn('action', function ($query) {
                $edit = checkPermission('update-help') ? '<a title="' . trans('admin_messages.edit') . '" href="' . route('admin.edit_help', $query->id) . '" ><i class="material-icons">edit</i></a>' : '';
                $delete = checkPermission('delete-help') ? '<a title="' . trans('admin_messages.delete') . '" href="javascript:void(0)" class="confirm-delete" data-href="' . route('admin.delete_help', $query->id) . '"><i class="material-icons">close</i></a>' : '';
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
        return Help::get();
    }

        /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->columns(['id','category','sub_category','type','question','status'])
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
        return 'help' . date('YmdHis');
    }
}
