<?php

namespace App\Http\Controllers\Admin;

use App\Models\Support;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\SupportDataTable;
use Validator;
use App\Traits\FileProcessing;

class SupportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    use FileProcessing;

    public function __construct() {
        parent::__construct();
    }


    public function index(SupportDataTable $dataTable)
    {
        $this->view_data['form_name'] = trans('admin_messages.support'); 
        return $dataTable->render('admin/support/view',$this->view_data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        if($request->isMethod('GET')) {
            $this->view_data['form_name'] = trans('admin_messages.add_support'); 
            $this->view_data['form_action'] = route('admin.add_support');
            return view('admin/support/add',$this->view_data);
        }
        $rules = array(
            'name'          => 'required',
            'link'          => 'required',
            'status'        => 'required',
            'support_image' =>'required|mimes:jpg,png,jpeg,gif',
        );
        $attributes = array(
            'name'          => 'Name',                      
            'link'          => 'Link',
            'status'        => 'Status',
            'support_image' => 'Support Image',
        );

        $validator = Validator::make($request->all(), $rules, [], $attributes);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $support                = new Support;
        $support->name          = $request->name;
        $support->link          = $request->link;
        $support->status        = $request->status;
        $support->save();
        if ($request->file('support_image')) {
            $file = $request->file('support_image');
            $folder = 'support_image';
            $file_path = $this->fileUpload($file, 'public/images/'.$folder);
            $this->fileSave($folder, $support->id, $file_path['file_name'], '1');
        }
        $support->save();
        flash_message('success', 'Added Successfully');
        return redirect('admin/support');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if($request->isMethod('GET')) {
            $this->view_data['form_name'] = trans('admin_messages.edit_support');
            $this->view_data['form_action'] = route('admin.edit_support', $request->id);
            $this->view_data['support'] = Support::find($request->id);
            return view('admin/support/edit', $this->view_data);
        }
        // dd($request->all());
        $rules = array(
            'name'          => 'required',
            'link'          => 'required',
            'status'        => 'required',
            'support_image' => 'mimes:jpg,png,jpeg,gif',
        );
        $attributes = array(
            'name'          => 'Name',                      
            'link'          => 'Link',
            'status'        => 'Status',
            'support_image' => 'Support Image',
        );
        $validator = Validator::make($request->all(), $rules, [], $attributes);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $support                = Support::find($request->id);
        $support->name          = $request->name;
        $support->link          = $request->link;
        $support->status        = $request->status;
        if ($request->file('support_image')) {
            $file = $request->file('support_image');
            $folder = 'support_image';
            $file_path = $this->fileUpload($file, 'public/images/'.$folder);
            $this->fileSave($folder, $support->id, $file_path['file_name'], '1');
        }
        $support->save();
        flash_message('success', 'Updated Successfully');
        return redirect('admin/support');
    }

    public function delete(Request $request)
    {
        if($request->id =='1' || $request->id =='2'){
            flash_message('danger', "This is required one. So can't delete this"); 
            return redirect('admin/support');
        }
        Support::find($request->id)->delete();
        flash_message('success', 'Deleted Successfully'); 
        return redirect('admin/support');
    }
}
