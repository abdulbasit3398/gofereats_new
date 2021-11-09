<?php
/**
 * UserController
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
use App\Http\Controllers\EloquentDataTableBase;
use App\DataTables\PenalityDataTable;
use App\Models\Order;
use App\Models\User;
use DataTables;
use Hash;
use Illuminate\Http\Request;
use Validator;
use Session;
use DB;

class UserController extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add_user(Request $request) {
		if ($request->getMethod() == 'GET') {
			$this->view_data['form_action'] = route('admin.add_user');
			$this->view_data['form_name'] = trans('admin_messages.add_user');
			return view('admin/user/add_user', $this->view_data);
		} else {
			Session::put('user_ph_code', $request->get('country_code'));
			$rules = array(
				'first_name' => 'required',
				'last_name' => 'required',
				'email' => ['required', 'max:255', 'email', 'regex:/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/', 'unique:user,email,NULL,id,type,0'],
				// 'email' => 'required|email|unique:user,email,NULL,user,type,0',
				'password' => 'required|min:6',
				'country_code' => 'required',
				'status' => 'required',
				'country_code' => 'required',
				'mobile_number' => 'required|regex:/^[0-9]+$/|min:6|unique:user,mobile_number,NULL,user,type,0,country_id,'.$request->country_code,
			);
			
			// Add Admin User Validation Custom Names
			$niceNames = array(
				'first_name' => trans('admin_messages.first_name'),
				'last_name' => trans('admin_messages.last_name'),
				'email' => trans('admin_messages.email'),
				'password' => trans('admin_messages.password'),
				'country_code' => trans('admin_messages.country_code'),
				'mobile_number' => trans('admin_messages.mobile_number'),
				'status' => trans('admin_messages.status'),
				'country_code' => trans('admin_messages.country_code'),
			);

			$validator = Validator::make(request()->all(), $rules);
			$validator->setAttributeNames($niceNames);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
				 // Form calling with Errors and Input values
			} else {
				$country_code = str_replace('+', '', $request->text);	
				$user = new User;
				$user->name = $request->first_name.'~'.$request->last_name;
				$user->user_first_name = $request->first_name;
				$user->user_last_name = $request->last_name;
				$user->email = $request->email;
				$user->password = Hash::make($request->password);
				$user->country_id = $request->country_code;
				$user->currency_code = session('currency');
				$user->mobile_number = $request->mobile_number;
				$user->type = 0;
				$user->status = $request->status;
				$user->country_code = $country_code ;
				$user->save();
				
				flash_message('success', trans('admin_messages.updated_successfully'));
				return redirect()->route('admin.view_user');
			}

		}
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function view() {
		$this->view_data['form_name'] = trans('admin_messages.user_management');
		return view('admin.user.view', $this->view_data);
		return $dataTable->render('admin.user.view', $this->view_data);
	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */

	public function all_users(Request $request) 
	{

		$users 	= User::where('type', 0);
		$filter_type = request()->filter_type;
		$from = date('Y-m-d' . ' 00:00:00', strtotime(change_date_format(request()->from_dates)));
		if (request()->to_dates != '') {
			$to = date('Y-m-d' . ' 23:59:59', strtotime(change_date_format(request()->to_dates)));
			$users = $users->where('created_at', '>=', $from)->where('created_at', '<=', $to);
		}

		$users->select('id','name',DB::raw('SUBSTRING_INDEX(name, "~",1) as first_name'),DB::raw('SUBSTRING_INDEX(name, "~",-1) as last_name'),'email','status','created_at',DB::raw('CASE WHEN status = 0  THEN  "Inactive" WHEN status = 1  THEN  "Active" WHEN status = 2  THEN  "Vehicle Details" WHEN status = 3  THEN  "Document Upload" WHEN status = 4  THEN  "Pending" WHEN status = 5  THEN  "Waiting for Approval"  ELSE "Pending" END as status'));
		

		$datatable = DataTables::of($users)
			->addColumn('action', function ($users) {
				return '<a title="' . trans('admin_messages.edit') . '" href="' . route('admin.edit_user', $users->id) . '" ><i class="material-icons">edit</i></a>&nbsp;<a title="' . trans('admin_messages.delete') . '" href="javascript:void(0)" class="confirm-delete" data-href="' . route('admin.delete_user', $users->id) . '"><i class="material-icons">close</i></a>';
			});
			$datatable->filterColumn('first_name', function($query, $keyword) {
				$query->whereRaw('SUBSTRING_INDEX(name, "~",1)  LIKE "%'.$keyword.'%"');
            })->filterColumn('last_name', function($query, $keyword) {
				$query->whereRaw('SUBSTRING_INDEX(name, "~",-1)  LIKE "%'.$keyword.'%"');
            })->filterColumn('status', function($query, $keyword) {
				$query->whereRaw('CASE WHEN status = 0  THEN  "Inactive" WHEN status = 1  THEN  "Active" WHEN status = 2  THEN  "Vehicle Details" WHEN status = 3  THEN  "Document Upload" WHEN status = 4  THEN  "Pending" WHEN status = 5  THEN  "Waiting for Approval"  ELSE "Pending" END  LIKE "%'.$keyword.'%"');
            });

		$columns = ['id', 'first_name','last_name', 'email', 'status', 'created_at'];
		$base = new EloquentDataTableBase($users, $datatable, $columns,'Users');
		return $base->render(null);

	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function delete(Request $request)
	{
		$user = User::whereId($request->id)->first();
		if($user->wallet_amount>0) {
			flash_message('danger', 'This User have some amount an our wallet So can\'t delete this user.' );
			return redirect()->route('admin.view_user');
		}

		$is_order = Order::where('user_id', $user->id)->notstatus()->first();
		if ($is_order) {
			flash_message('danger', 'Sorry,This user booked some orders. So, Can\'t delete this user.');
			return redirect()->route('admin.view_user');
		}

		$delete_data = $user->delete_data();
		if($delete_data) {
			flash_message('success', trans('admin_messages.deleted_successfully'));
		}
		else {
			flash_message('success', 'Sorry,This user booked some orders. So, Can\'t delete this user');
		}
		return redirect()->route('admin.view_user');
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit_user(Request $request) {
		if ($request->getMethod() == 'GET') {
			$this->view_data['form_name'] = trans('admin_messages.edit_user');
			$this->view_data['form_action'] = route('admin.edit_user', $request->id);
			$this->view_data['user'] = User::findOrFail($request->id);
			// dd($this->view_data['user']);
			return view('admin/user/add_user', $this->view_data);
		} else {
		

			$rules = array(
				'first_name' => 'required',
				'last_name' => 'required',
				'email' => 'required|email|unique:user,email,' . $request->id . ',id,type,0',
				'status' => 'required',
				'country_code' => 'required',
				'mobile_number' => 'required|regex:/^[0-9]+$/|min:6|unique:user,mobile_number,'. $request->id .',id,type,0,country_id,'.$request->country_code,
			);
			if ($request->password) {
				$rules['password'] = 'min:6';
			}

			// Add Admin User Validation Custom Names
			$niceNames = array(
				'first_name' => trans('admin_messages.first_name'),
				'last_name' => trans('admin_messages.last_name'),
				'email' => trans('admin_messages.email'),
				'password' => trans('admin_messages.password'),
				'mobile_number' => trans('admin_messages.mobile_number'),
				'status' => trans('admin_messages.status'),
				'country_code' => trans('admin_messages.country_code'),
			);

			$validator = Validator::make(request()->all(), $rules);
			$validator->setAttributeNames($niceNames);

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
			} else {
				$country_code = str_replace('+', '', $request->text);	
				$user = User::find($request->id);
				$user->name = $request->first_name.'~'.$request->last_name;
				$user->user_first_name = $request->first_name;
				$user->user_last_name = $request->last_name;
				$user->email = $request->email;
				if ($request->password) {
					$user->password = Hash::make($request->password);
				}
				$user->country_id = $request->country_code;
				$user->country_code = $country_code;
				$user->mobile_number = $request->mobile_number;
				$user->type = 0;
				$user->status = $request->status;
				$user->save();

				flash_message('success', trans('admin_messages.updated_successfully'));
				return redirect()->route('admin.view_user');
			}

		}
	}


	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function penality(PenalityDataTable $dataTable) {
		$this->view_data['form_name'] = trans('admin_messages.penalty');
		return $dataTable->render('admin.user.penality', $this->view_data);
	}

}
