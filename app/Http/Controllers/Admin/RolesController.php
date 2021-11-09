<?php


/**
 * PromoCodeController
 *
 * @package     Gofer Delivery All
 * @subpackage  Controller
 * @category    Admin
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */


namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\DataTables\RolesDataTable;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(RolesDataTable $dataTable)
    {
        $this->view_data['form_name'] = "Manage Roles";
        return $dataTable->render('admin.roles.view', $this->view_data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        if ($request->getMethod() == 'GET') {
            $this->view_data['form_action'] = route('admin.create_role');
            $this->view_data['form_name'] = "Add Role";
            $this->view_data['result'] = new Role;
            $this->view_data['permissions'] = Permission::get();
            $this->view_data['old_permissions'] = array();

            return view('admin.roles.roles_form', $this->view_data);
        }

        $rules = array(
            'name' => 'required',
            'display_name' => 'required',
            'permission' =>'required',
        );
        $attributes = array(
            'name' => 'Name',
            'display_name' => 'Display Name',
            'permission' =>'Permission',
        );

        $this->validate($request,$rules,[],$attributes);

        $role = new Role;
        $role->name = $request->name;
        $role->display_name = $request->display_name;
        $role->description = $request->description ?? '';
        $role->save();

        $permission = $request->permission;
        $permissions = Permission::whereIn('id',$permission)->get();

        $role->permissions()->sync($permissions);

        flash_message('success', trans('admin_messages.added_successfully'));
        return redirect()->route('admin.view_role');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($request->getMethod() == 'GET') {
            $this->view_data['form_name'] = "Edit Role";
            $this->view_data['result'] = Role::findOrFail($id);
            $this->view_data['form_action'] = route('admin.update_role',['id' => $id]);
            $this->view_data['permissions'] = Permission::get();
            $this->view_data['old_permissions'] = \DB::table('permission_role')->where('role_id',$id)->pluck('permission_id')->toArray();

            return view('admin.roles.roles_form', $this->view_data);
        }
        // dd($request->all());
        $rules = array(
            'name' => 'required',
            'display_name' => 'required',
            'permission'  =>'required',

        );
        $attributes = array(
            'name' => 'Name',
            'display_name' => 'Display name',
            'permission'  =>'Permission',
        );

        $this->validate($request,$rules,[],$attributes);

        $role = Role::find($id);
        $role->name = $request->name;
        $role->display_name = $request->display_name;
        $role->description = $request->description ?? '';
        $role->save();

        $permission = $request->permission;
        $permissions = Permission::whereIn('id',$permission)->get();

        $role->permissions()->sync($permissions);

        flash_message('success', trans('admin_messages.updated_successfully'));
        return redirect()->route('admin.view_role');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role_count = Role::where('id','!=',$id)->count();

        if($role_count == 0) {
            flash_message('danger',"You cannot delete last role");
            return redirect()->route('admin.view_role');
        }

        try {
            $role = Role::find($id);
            $role->users()->sync([]);
            $role->permissions()->sync([]);
            $role->forceDelete();
            flash_message('success',trans('admin_messages.deleted_successfully'));
        }
        catch (Exception $e) {
            flash_message('danger',$e->getMessage());
        }

        return redirect()->route('admin.view_role');
    }
}
