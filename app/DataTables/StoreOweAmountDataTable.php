<?php

namespace App\DataTables;

use App\Models\StoreOweAmount;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class StoreOweAmountDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables($query)
            ->addColumn('paid_amount', function ($query) {
                return currency_symbol().$query->paid_amount;
            })
            ->addColumn('name', function ($query) {
                return $query->name;
            })
            ->addColumn('remaining_amount', function ($query) {
                return currency_symbol().$query->amount;
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\StoreOweAmount $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(StoreOweAmount $model)
    {
        return StoreOweAmount::where('user_id','!=','')->get();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns(['id'])
             ->addColumn(['data' => 'name', 'name' => 'name', 'title' => 'Store Name'])
            ->addColumn(['data' => 'paid_amount', 'name' => 'paid_amount', 'title' => 'Owe Amount'])
            ->addColumn(['data' => 'remaining_amount', 'name' => 'remaining_amount', 'title' => 'Remaining Amount'])
            ->parameters([
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
    //         Column::computed('action')
    //               ->exportable(false)
    //               ->printable(false)
    //               ->width(60)
    //               ->addClass('text-center'),
    //         Column::make('id'),
    //         Column::make('add your columns'),
    //         Column::make('created_at'),
    //         Column::make('updated_at'),
    //     ];
    // }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'StoreOweAmount_' . date('YmdHis');
    }
}
