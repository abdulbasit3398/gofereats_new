<?php

/**
 * Help Subcategory DataTable
 *
 * @package     GoferEats
 * @subpackage  DataTable
 * @category    Help Subcategory
 * @author      Trioangle Product Team
 * @version     1.5.8.2
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use App\Models\HelpSubCategory;
use Yajra\DataTables\Services\DataTable;

class HelpSubCategoryDataTable extends DataTable
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
            ->addColumn('category_name', function ($query) {
                return $query->category_name;
            })
            ->addColumn('action', function ($query) {
                $edit = checkPermission('update-help_subcategory') ? '<a title="' . trans('admin_messages.edit') . '" href="' . route('admin.edit_help_subcategory', $query->id) . '" ><i class="material-icons">edit</i></a>' : '';
                $delete = checkPermission('delete-help_subcategory') ? '<a title="' . trans('admin_messages.delete') . '" href="javascript:void(0)" class="confirm-delete" data-href="' . route('admin.delete_help_subcategory', $query->id) . '"><i class="material-icons">close</i></a>' : '';
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
       return HelpSubCategory::get();

    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\Datatables\Html\Builder
     */
    public function html()
    {

        return $this->builder()
                    ->columns(['id','category_name','name','description','status'])
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
        return 'help_subcategory' . date('YmdHis');
    }
}
