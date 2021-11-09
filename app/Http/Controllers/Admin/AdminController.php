<?php

/**
 * AdminController
 *
 * @package     Gofer Delivery All
 * @subpackage  Controller
 * @category    Admin
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataTables\AdminusersDataTable;
use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use App\Models\Role;
use Auth;
use App\Charts\BasicChart;
use DB;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
	/**
	 * Show the admin login page
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function login()
	{
		$data['email'] = (canDisplayCredentials()) ? 'admin' : '';
		$data['password'] = (canDisplayCredentials()) ? 'gofereats' : '';
		return view('admin/login',$data);
	}

	/**
	 * Show the admin logout page
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function logout()
	{
		Auth::guard('admin')->logout();
		return redirect()->route('admin.login');
	}

	/**
	 * admin authenticate
	 *
	 * @return
	 */
	public function authenticate()
	{
		$request = request();
		$admin = Admin::where('username', $request->user_name)->first();

		if (@$admin->status != '0') {
			if (Auth::guard('admin')->attempt(['username' => $request->user_name, 'password' => $request->password])) {
				return redirect()->route('admin.dashboard');
			}
			return back()->withErrors(['user_name' => trans('admin_messages.invalid_user_name_or_password')])->withInput();
		}
		return back()->withErrors(['user_name' => trans('admin_messages.invalid_user_name_or_password')])->withInput();
	}

	/**
	 * Show the admin dashboard page
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function dashboard()
	{
		$admin_earnings = Order::selectRaw('store_id,user_id,id,sum(booking_fee+store_commision_fee+driver_commision_fee) as amount,Month(created_at) as month')->where('status', 6)->where(DB::raw("(DATE_FORMAT(created_at,'%Y'))"), date('Y'))
			->get()->groupBy(function ($date) {
			return Carbon::parse($date->created_at)->format('m');
		});

		$admin_earnings = $admin_earnings->toArray();

		foreach ($admin_earnings as $key => $value) {
			foreach ($value as $val) {
				$month[] = array('amount' => $val['amount'], 'month' => $val['month']);
			}
		}

		$months = array_column($month, 'month');
		$amount = array_column($month, 'amount');

		$data = [];
		for ($i = 1; $i <= 12; $i++) {
			$monthName = date('F', mktime(0, 0, 0, $i, 10));
			if (false !== $key = array_search($i, $months)) {
				$data[] = array('month' => $monthName, 'amount' => $amount[$key]);
			}
			else {
				$data[] = array('month' => $monthName, 'amount' => 0);
			}
		}

		$months = array_column($data, 'month');
		$amount = array_column($data, 'amount');

		$this->view_data['total_drivers'] = User::where('type', 2)->get()->count();
		$this->view_data['total_stores'] = User::where('type', 1)->get()->count();
		$this->view_data['total_users'] = User::where('type', 0)->get()->count();
		$this->view_data['total_booking'] = Order::get()->count();
		$this->view_data['form_name'] = 'Dashboard';

		$month = array_column($data, 'month');
		$amount = array_column($data, 'amount');
		
		$chart = new BasicChart;
		$chart->title("Earnings for ".date('Y'));
		$chart->labels($month);
		$chart->dataset('Earnings', 'line' ,$amount)->color("#43A422");

		$this->view_data['earning_chart'] = $chart;

		return view('admin/dashboard', $this->view_data);
	}

	/**
	 * Manage Admin Users
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(AdminusersDataTable $dataTable)
	{
		$this->view_data['form_name'] = "Admin Users";
		return $dataTable->render('admin.admin_users.view', $this->view_data);
	}

	/**
	 * Update Admin Details
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add(Request $request)
	{
		if($request->isMethod("GET")) {
			$this->view_data['roles'] = Role::all()->pluck('name','id');
			return view('admin.admin_users.add', $this->view_data);
		}

		$rules = array(
            'username'   => 'required|unique:admin,username',
            'email'      => 'required|email|unique:admin,email',
            'password'   => 'required',
            'role'       => 'required',
            'status'     => 'required',
        );

        $messages = array();

        $attributes = array(
            'username'   => 'Username',
            'email'      => 'Email',
            'role'       => 'Role',
            'status'     => 'Status',
        );

        $request->validate($rules,$messages,$attributes);

        $admin = new Admin;

        $admin->username = $request->username;
        $admin->email    = $request->email;
        $admin->password = $request->password;
        $admin->status   = $request->status;

        $admin->save();

        $admin->attachRole($request->role);

        flash_message('success', 'Admin User Created Successfully.');

		return redirect()->route('admin.view_admin');
	}

	/**
	 * Update Admin Details
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request,$id)
	{
		if($request->isMethod("GET")) {
			$this->view_data['roles'] = Role::all()->pluck('name','id');
			$this->view_data['result'] = Admin::find($id);
			return view('admin.admin_users.edit', $this->view_data);
		}

		$rules = array(
            'username'   => 'required|unique:admin,username,'.$id,
            'email'      => 'required|email|unique:admin,email,'.$id,
            'role'          => 'required',
            'status'        => 'required',
        );

        if(!$this->checkOtherActiveUsers($id) && $request->status == "0") {
        	flash_message('danger', 'Unable to update the status, You are the only active admin');
        	return back();
        }

        $messages = array();

        $attributes = array(
            'username'   => 'Username',
            'email'      => 'Email',
            'role'       => 'Role',
            'status'     => 'Status',
        );

        $request->validate($rules,$messages,$attributes);

        $admin = Admin::find($id);

        $admin->username = $request->username;
        $admin->email    = $request->email;
        $admin->status   = $request->status;

        if($request->password != '') {
            $admin->password = $request->password;
        }

        $admin->save();
        if(!is_null($admin->roles()->first()))
        {
        	if($request->role != $admin->roles()->first()->id) {
	        $admin->detachRoles();
	        $admin->attachRole($request->role); 
    		}
        }
	    else{
	    	 $admin->attachRole($request->role); 
	    } 
	    flash_message('success', 'Updated Successfully.');   
		return redirect()->route('admin.view_admin');
	}

	public function delete($id)
	{
		if(!$this->checkOtherActiveUsers($id)) {
        	flash_message('danger', 'Unable to update the status, You are the only active admin');
        	return redirect()->route('admin.view_admin');
        }
		try {
            Admin::where('id',$id)->delete();
            flash_message('success',"Deleted Successfully");
        }
        catch (\Exception $e) {
            flash_message('danger',$e->getMessage());            
        }
        return redirect()->route('admin.view_admin');
	}

	protected function checkOtherActiveUsers($id)
	{
		$admin_count = Admin::where('id','!=',$id)->where('status','1')->count();
		return $admin_count > 0;
	}
}
