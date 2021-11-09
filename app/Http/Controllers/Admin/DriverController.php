<?php

/**
* DriverController
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
use App\Http\Controllers\EloquentDataTableBase;
use App\DataTables\DriverOweAmountDataTable;
use App\DataTables\DriverRequestDataTable;
use App\DataTables\DriverHomeSliderDataTable;

use App\Models\Driver;
use App\Models\User;
use App\Models\File;
use App\Models\VehicleType;
use App\Models\OrderDelivery;
use App\Models\DriverHomeSlider;
use App\Traits\FileProcessing;

use DataTables;
use Hash;
use Validator;
use Storage;
use Session;
use DB;

class DriverController extends Controller
{
	use FileProcessing;

	/**
	 * Driver Request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function driver_request(DriverRequestDataTable $dataTable)
	{
		$this->view_data['form_name'] = trans('admin_messages.driver_request');
		return $dataTable->render('admin.driver.driver_request', $this->view_data);
	}

	/**
	 * Manage Owe Amount
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function oweAmount(DriverOweAmountDataTable $dataTable)
	{
		$this->view_data['form_name'] = trans('admin_messages.owe_amount_management');
		return $dataTable->render('admin.driver.owe_amount', $this->view_data);
	}

	/**
	 * All Drivers
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function all_drivers(Request $request)
	{
		$driver = User::where('type', 2)
		 			->join('driver', function($join) {
                            $join->on('user.id', '=', 'driver.user_id');
                    });
		$filter_type = $request->filter_type;

		$from = date('Y-m-d' . ' 00:00:00', strtotime(change_date_format($request->from_dates)));
		if ($request->to_dates != '') {
			$to = date('Y-m-d' . ' 23:59:59', strtotime(change_date_format($request->to_dates)));
			$driver = $driver->where('created_at', '>=', $from)->where('created_at', '<=', $to);
		}
		
		
		$driver->select('driver.id as id','user.id as user_id','user.name',DB::raw('SUBSTRING_INDEX(user.name, "~",1) as first_name'),DB::raw('SUBSTRING_INDEX(user.name, "~",-1) as last_name'),'user.email as email','user.mobile_number as mobile_number','user.status','user.created_at as created_at',DB::raw('CASE WHEN user.status = 0  THEN  "Inactive" WHEN user.status = 1  THEN  "Active" WHEN user.status = 2  THEN  "Vehicle Details" WHEN user.status = 3  THEN  "Document Upload" WHEN user.status = 4  THEN  "Pending" WHEN user.status = 5  THEN  "Waiting for Approval"  ELSE "Pending" END as status'));

		$datatable = DataTables::of($driver)
			->addColumn('action', function ($driver) {
				return '<a title="' . trans('admin_messages.driver_request') . '" href="' . route('admin.driver_request', $driver->user_id) . '" ><i class="material-icons"><i className="material-icons">phonelink_ring</i></i></a>&nbsp;<a title="' . trans('admin_messages.edit') . '" href="' . route('admin.edit_driver', $driver->user_id) . '" ><i class="material-icons">edit</i></a>&nbsp;<a title="' . trans('admin_messages.delete') . '" href="javascript:void(0)" class="confirm-delete" data-href="' . route('admin.delete_driver', $driver->user_id) . '"><i class="material-icons">close</i></a>';
			});

			$datatable->filterColumn('first_name', function($query, $keyword) {
				$query->whereRaw('SUBSTRING_INDEX(user.name, "~",1)  LIKE "%'.$keyword.'%"');
            })->filterColumn('last_name', function($query, $keyword) {
				$query->whereRaw('SUBSTRING_INDEX(user.name, "~",-1)  LIKE "%'.$keyword.'%"');
            })->filterColumn('status', function($query, $keyword) {
				$query->whereRaw('CASE WHEN user.status = 0  THEN  "Inactive" WHEN user.status = 1  THEN  "Active" WHEN user.status = 2  THEN  "Vehicle Details" WHEN user.status = 3  THEN  "Document Upload" WHEN user.status = 4  THEN  "Pending" WHEN user.status = 5  THEN  "Waiting for Approval"  ELSE "Pending" END  LIKE "%'.$keyword.'%"');
            });

		$columns = ['id', 'first_name','last_name', 'email','mobile_number', 'status', 'created at'];
		$base = new EloquentDataTableBase($driver, $datatable, $columns,'Drivers','sdfsdfsdf');
		return $base->render(null);
	}

	/**
	 * View Driver
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function view()
	{
		$this->view_data['form_name'] = trans('admin_messages.driver_management');
		return view('admin/driver/view', $this->view_data);
	}

	/**
	 * Add Driver
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add_driver(Request $request)
	{
		if ($request->getMethod() == 'GET') {
			$this->view_data['form_action'] = route('admin.add_driver');
			$this->view_data['form_name'] = trans('admin_messages.add_driver');
			$this->view_data['vehicle_type'] = VehicleType::status()->get()->pluck('name', 'id');
			$this->view_data['driver_document'] = driver_default_documents();
			$user = new User;
			$this->view_data['driver_status'] = $user->statusTextArray;

			return view('admin/driver/add_driver', $this->view_data);
		}
		// dd("hiell000");
		
		$all_variables = $request->all();
		$all_variables['convert_dob'] = $all_variables['date_of_birth'];
		Session::put('driver_pho_code', $request->get('country_code'));
		$country_code = str_replace('+', '', $request->text);	
		
		$rules = array(
			'first_name' => 'required',
			'last_name' => 'required',
			'password' => 'required|min:6',
			'convert_dob' => 'required|before:18 years ago|date_format:d-m-Y',
			'country_code' => 'required',
			'status' => 'required',
			'country_code' => 'required',
			'vehicle_type' => 'required',
			'vehicle_name' => 'required',
			'vehicle_number' => 'required',
			'email' => ['required', 'max:255', 'email', 'regex:/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/', 'unique:user,email,NULL,id,type,2'],
			'mobile_number' => 'required|regex:/^[0-9]+$/|min:6|unique:user,mobile_number,NULL,id,type,2,country_id,'.$request->get('country_code'),
			// 'mobile_number' => 'required|regex:/^[0-9]+$/|min:6|unique:user,mobile_number,NULL,id,type,2',
		);

		if ($request->document) {
			foreach ($request->document as $key => $value) {
				$all_variables[$key] = $value;
			}
		}

		// Add Admin User Validation Custom Names
		$attributes = array(
			'first_name' => trans('admin_messages.first_name'),
			'last_name' => trans('admin_messages.last_name'),
			'email' => trans('admin_messages.email'),
			'password' => trans('admin_messages.password'),
			'convert_dob' => trans('admin_messages.date_of_birth'),
			'country_code' => trans('admin_messages.country_code'),
			'mobile_number' => trans('admin_messages.mobile_number'),
			'status' => trans('admin_messages.status'),
			'country_code' => trans('admin_messages.country_code'),
			'vehicle_type' => trans('admin_messages.vehicle_type'),
			'vehicle_name' => trans('admin_messages.vehicle_name'),
			'vehicle_number' => trans('admin_messages.vehicle_number'),
		);

		foreach (driver_default_documents() as $value) {
			$rules[$value] = 'required|mimes:jpg,png,jpeg,gif,pdf';
			$attributes[$value] = trans('admin_messages.' . $value);
		}

		$messages = array(
			'convert_dob.before' => 'Age must be 18 or older',
			'convert_dob.date_format' => 'The Date Of Birth does not match the format DD-MM-YYYY.',
			'email.regex' => trans('messages.profile.invalid_email'),
			'email.unique' => trans('messages.profile.email_already_taken'),
			'mobile_number.unique' => trans('messages.profile.mobile_number_already_taken'), 
		);
		
		$validator = Validator::make($all_variables, $rules,$messages,$attributes);

		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		if ($all_variables['date_of_birth']) {
			$all_variables['convert_dob'] = date('Y-m-d', strtotime($all_variables['date_of_birth']));
		}
		

		$driver = new User;
		$driver->name = str_replace(' ','',$request->first_name).'~'.str_replace(' ','',$request->last_name);
		$driver->user_first_name = str_replace(' ','',$request->first_name);
		$driver->user_last_name = str_replace(' ','',$request->last_name);
		$driver->email = $request->email;
		$driver->password = Hash::make($request->password);
		$driver->date_of_birth = $all_variables['convert_dob'];
		$driver->country_code = $country_code;
		$driver->mobile_number = $request->mobile_number;
		$driver->currency_code = DEFAULT_CURRENCY;
		$driver->type = 2;
		$driver->status = $request->status;
		$driver->country_id =  $request->country_code;
		$driver->save();

		$driver_vehicle = new Driver;
		$driver_vehicle->user_id = $driver->id;
		$driver_vehicle->vehicle_type = $request->vehicle_type;
		$driver_vehicle->vehicle_name = $request->vehicle_name;
		$driver_vehicle->vehicle_number = $request->vehicle_number;
		$driver_vehicle->save();

		if ($request->document) {
			foreach ($request->document as $key => $value) {

				$file = $request->file('document')[$key];

				$file_path = $this->fileUpload($file, 'public/images/driver');
				$this->fileSave('driver_' . $key, $driver_vehicle->user_id, $file_path['file_name'], '1');
			}
		}
		session()->forget('driver_pho_code');
		flash_message('success', trans('admin_messages.updated_successfully'));
		return redirect()->route('admin.view_driver');
	}


	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */



	public function edit_driver(Request $request)
	{
		if ($request->getMethod() == 'GET') {
			$this->view_data['form_name'] = trans('admin_messages.edit_driver');
			$this->view_data['form_action'] = route('admin.edit_driver', $request->id);
			$this->view_data['driver'] = User::where('id', $request->id)->firstOrFail();
			$this->view_data['vehicle_type'] = VehicleType::status()->get()->pluck('name', 'id');
			$this->view_data['driver_document'] = driver_default_documents();
			$this->view_data['driver_status'] = $this->view_data['driver']->statusTextArray;
			
			return view('admin/driver/add_driver', $this->view_data);
		}

		$all_variables = $request->all();
		$all_variables['convert_dob'] = $all_variables['date_of_birth'];

		$rules = array(
			'first_name' => 'required',
			'last_name' => 'required',
			'convert_dob' => 'required|before:18 years ago|date_format:d-m-Y',
			'status' => 'required',
			'country_code' => 'required',
			'vehicle_type' => 'required',
			'vehicle_name' => 'required',
			'vehicle_number' => 'required',
			'email' => ['required', 'max:255', 'email', 'regex:/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/', 'unique:user,email,'. $request->id . ',id,type,2'],
			'mobile_number' => 'required|regex:/^[0-9]+$/|min:6|unique:user,mobile_number,' . $request->id . ',id,type,2,country_id,'.$request->country_code,
		);
		if ($request->password) {
			$rules['password'] = 'min:6';
		}

		$file_type = array_keys(driver_default_documents());
		$driver_data = Driver::where('user_id',$request->id)->first();
		$file_list = File::where('source_id',$driver_data->user_id)->whereIn('type',$file_type)->pluck('type')->toArray();

		if ($request->document) {
			foreach ($request->document as $key => $value) {
				$all_variables[$key] = $value;
			}
		}

		// Add Admin User Validation Custom Names
		$attributes = array(
			'first_name' => trans('admin_messages.first_name'),
			'last_name' => trans('admin_messages.last_name'),
			'email' => trans('admin_messages.email'),
			'password' => trans('admin_messages.password'),
			'convert_dob' => trans('admin_messages.date_of_birth'),
			'mobile_number' => trans('admin_messages.mobile_number'),
			'status' => trans('admin_messages.status'),
			'country_code' => trans('admin_messages.country_code'),
		);
		$messages = array(
			'convert_dob.before' => 'Age must be 18 or older',
			'convert_dob.date_format' => 'The Date Of Birth does not match the format DD-MM-YYYY.',
			'email.regex' => trans('messages.profile.invalid_email'),
			'email.unique' => trans('messages.profile.email_already_taken'),
			'mobile_number.unique' => trans('messages.profile.mobile_number_already_taken'),
		);

		if($request->document)
		{
			foreach (driver_default_documents() as $value) {
				if(array_key_exists($value ,$request->document))
				{	
					foreach ($request->document as $key1 => $value1) {
						$rules[$key1] = 'required|mimes:jpg,png,jpeg,gif,pdf';
						$attributes[$key1] = trans('admin_messages.' . $key1);
					}
				}
			}
		}
		else
		{
			foreach (driver_default_documents() as $key => $value) {
				if(!in_array($key, array_values($file_list))){
					$rules[$value] = 'required|mimes:jpg,png,jpeg,gif,pdf';
					$attributes[$value] = trans('admin_messages.' . $value);
				}
			}	
		}
		$validator = Validator::make($all_variables, $rules,$messages,$attributes);

		if ($validator->fails()) {
			return back()->withErrors($validator)->withInput();
		}

		if ($all_variables['date_of_birth']) {
			$all_variables['convert_dob'] = date('Y-m-d', strtotime($all_variables['date_of_birth']));
		}
		// dd($request->all());
		$country_code = str_replace('+', '', $request->text);

		$driver = User::find($request->id);
		$driver->name = str_replace(' ','',$request->first_name).'~'.str_replace(' ','',$request->last_name);
		$driver->user_first_name = str_replace(' ','',$request->first_name);
		$driver->user_last_name = str_replace(' ','',$request->last_name);
		$driver->email = $request->email;
		if ($request->password) {
			$driver->password = Hash::make($request->password);
		}
		$driver->date_of_birth = $all_variables['convert_dob'];
		$driver->country_code = $country_code ;
		$driver->mobile_number = $request->mobile_number;
		$driver->type = 2;
		$driver->status = $request->status;
		$driver->country_id = $request->country_code;
		$driver->save();

		$driver_vehicle = Driver::where('user_id', $driver->id)->first();
		$driver_vehicle->vehicle_type = $request->vehicle_type;
		$driver_vehicle->vehicle_name = $request->vehicle_name;
		$driver_vehicle->vehicle_number = $request->vehicle_number;
		$driver_vehicle->save();

		if ($request->document) {
			foreach ($request->document as $key => $value) {

				$file = $request->file('document')[$key];

				$file_path = $this->fileUpload($file, 'public/images/driver');
				$this->fileSave('driver_' . $key, $driver_vehicle->user_id, $file_path['file_name'], '1');
			}
		}

		flash_message('success', trans('admin_messages.updated_successfully'));
		return redirect()->route('admin.view_driver');
	}

	/**
	 * Delete Driver
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function delete(Request $request)
	{
		$user = User::whereId($request->id)->first();
		if($user->driver) {
	 		$is_order = OrderDelivery::where('driver_id',$user->driver->id)->first();
			if ($is_order) {
				flash_message('danger', 'Sorry, Driver having some Orders. So, Can\'t Delete this Driver.');
			}
			else {
				$user->delete_driver_data();
				flash_message('success', trans('admin_messages.deleted_successfully'));
			}
		}
		return redirect()->route('admin.view_driver');
	}
}
