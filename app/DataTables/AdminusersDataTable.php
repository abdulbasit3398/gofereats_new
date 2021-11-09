<?php

/**
 * Admin Users DataTable
 *
 * @package     GoferEats
 * @subpackage  DataTable
 * @category    Admin Users
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;
use App\Models\Admin;
use DB;

class AdminusersDataTable extends DataTable
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
            ->addColumn('status', function ($query) {
                return $query->status_text;
            })
            ->addColumn('role_name', function ($query) {
                return $query->role_name;
            })
            ->addColumn('action', function ($query) {
                $edit = checkPermission('update-admin') ? '<a title="' . trans('admin_messages.edit') . '" href="' . route('admin.update_admin', $query->id) . '" ><i class="material-icons">edit</i></a>' : '';
                $delete = checkPermission('delete-admin') ? '<a title="' . trans('admin_messages.delete') . '" href="javascript:void(0)" class="confirm-delete" data-href="' . route('admin.delete_admin', $query->id) . '"><i class="material-icons">close</i></a>' : '';
                return $edit." &nbsp; ".$delete;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Admin $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Admin $model)
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
        
                    ->addColumn(['data' => 'id', 'name' => 'id', 'title' => 'ID'])
                    ->addColumn(['data' => 'username', 'name' => 'username', 'title' => 'Username'])
                    ->addColumn(['data' => 'email', 'name' => 'email', 'title' => 'Email'])
                    ->addColumn(['data' => 'role_name', 'name' => 'role_name', 'title' => 'Role Name'])
                     ->addColumn(['data' => 'status', 'name' => 'status', 'title' => 'Status'])
                    ->addAction(['width' => '80px', 'printable' => false])
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
    // protected function getColumns()
    // {
    //     return [
    //         ['data' => 'id', 'name' => 'id', 'title' => 'Id'],
    //         ['data' => 'username', 'name' => 'username', 'title' => 'Username'],
    //         ['data' => 'email', 'name' => 'email', 'title' => 'Email'],
    //         ['data' => 'role_name', 'name' => 'role_name', 'title' => 'Role'],
    //         ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
    //         ['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false ,'printable' => false],
    //     ];
    // }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'admin_users_' . date('YmdHis');
    }
}