<?php
/**
 * StoreController
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
use App\Models\Cuisine;
use App\Models\File;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemModifier;
use App\Models\MenuItemModifierItem;
use App\Models\MenuTime;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayoutPreference;
use App\Models\Store;
use App\Models\StoreCuisine;
use App\Models\StoreDocument;
use App\Models\StoreOffer;
use App\Models\StorePreparationTime;
use App\Models\StoreTime;
use App\Models\User;
use App\Models\SiteSettings;
use App\Models\Payout;
use App\Models\UserAddress;
use App\Models\UserPaymentMethod;
use App\Models\UsersPromoCode;
use App\Models\Wishlist;
use App\Traits\FileProcessing;
use DataTables;
use Hash;
use Illuminate\Http\Request;
use Storage;
use Validator;
use App\Models\MenuTranslations;
use App\Models\MenuCategoryTranslations;
use App\Models\MenuItemTranslations;
use App\Models\ServiceType;
use DB;
use Session;

use App\Imports\MenuImport;
use App\DataTables\StoreOweAmountDataTable;

class StoreController extends Controller
{

	use FileProcessing;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add_store(Request $request) 
	{
		if ($request->getMethod() == 'GET') 
		{
			Session::forget('admin_store_phone_code');
			$this->view_data['form_action'] = route('admin.add_restaurant');
			$this->view_data['form_name'] = trans('admin_messages.add_restaurant');
			$this->view_data['cuisine'] = Cuisine::with('service_type_cuisine')->where('status', 1)->pluck('name', 'id');
			$this->view_data['store_cuisine'] = array();
			$this->view_data['delivery_typ'] = ['delivery'=>'Delivery','takeaway'=>'Take Away'];
			return view('admin/restaurant/add_store', $this->view_data);
		} 
		else 
		{
			session()->put('admin_store_phone_code', $request->get('country_code'));
			$all_variables = request()->all();
			$all_variables['convert_dob'] = $all_variables['date_of_birth'];

			$rules = array(
				'first_name' => 'required',
				'last_name' => 'required',
				'store_name' => 'required',
				'store_description' => 'required',
				'cuisine' => 'required',
				'currency_code' => 'required',
				'password' => 'required|min:6',
				'convert_dob' => 'required|before:18 years ago|date_format:d-m-Y',
				'phone_country_code' => 'required',
				'store_status' => 'required',
				'user_status' => 'required',
				'price_rating' => 'required',
				'address' => 'required',
				'email' => ['required', 'max:255', 'email', 'regex:/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/', 'unique:user,email,NULL,id,type,1'],
				'mobile_number' => 'required|regex:/^[0-9]+$/|min:6|unique:user,mobile_number,NULL,id,type,1',
				'delivery_type' =>'required',
				'banner_image' => 'image|mimes:jpg,png,jpeg,gif',
			);

			// Add Admin User Validation Custom Names
			$niceNames = array(
				'first_name' => trans('admin_messages.first_name'),
				'last_name' => trans('admin_messages.last_name'),
				'store_name' => trans('admin_messages.store_name'),
				'store_description' => trans('admin_messages.store_description'),
				'email' => trans('admin_messages.email'),
				'password' => trans('admin_messages.password'),
				'convert_dob' => trans('admin_messages.date_of_birth'),
				'phone_country_code' => trans('admin_messages.country_code'),
				'mobile_number' => trans('admin_messages.mobile_number'),
				'store_status' => trans('admin_messages.store_status'),
				'price_rating' => trans('admin_messages.price_rating'),
				'user_status' => trans('admin_messages.user_status'),
				'address' => trans('admin_messages.address'),
				'cuisine' => trans('admin_messages.cuisine'),
				'currency_code' => trans('admin_messages.currency'),
				'delivery_type' =>trans('admin_messages.delivery_type'),
			);

			if ($request->document)
			{
				foreach ($request->document as $key => $value) {
					$rules['document.' . $key . '.name'] = 'required';
					$rules['document.' . $key . '.document_file'] = 'required|mimes:jpg,png,jpeg,pdf';

					$niceNames['document.' . $key . '.name'] = trans('admin_messages.name');
					$niceNames['document.' . $key . '.document_file'] = 'Please upload the file like jpg,png,jpeg,pdf format';
				}
			}

			$messages = array(
				'convert_dob.before' => 'Age must be 18 or older',
				'convert_dob.date_format' => 'The Date Of Birth does not match the format DD-MM-YYYY.',
				'email.regex' => trans('messages.profile.invalid_email'),
				'email.unique' => trans('messages.profile.email_already_taken'),
				'mobile_number.unique' => trans('messages.profile.mobile_number_already_taken'),
			);
			
			$validator = Validator::make($all_variables, $rules,$messages);
			$validator->setAttributeNames($niceNames);
			$validator->after(function($validator) use($request) {
				if($request->latitude == '' || $request->longitude == '') {
					$validator->errors()->add('address', 'Invalid address');
				}
			});

			if ($validator->fails()) 
			{
				return back()->withErrors($validator)->withInput();
				 // Form calling with Errors and Input values
			} 
			else 
			{
				if($all_variables['date_of_birth']) {
					$all_variables['convert_dob'] = date('Y-m-d', strtotime($all_variables['date_of_birth']));
				}

				$store 					= new User;
				$store->user_first_name = $request->first_name;
				$store->user_last_name 	= $request->last_name;
				$store->name 			= $request->first_name.'~'.$request->last_name;
				$store->email 			= $request->email;
				$store->password 		= Hash::make($request->password);
				$store->date_of_birth 	= date('Y-m-d', strtotime($request->date_of_birth));
				$store->country_id 		= $request->phone_country_code;
				$store->country_code 	= ltrim($request->text,'+');
				$store->currency_code 	= $request->currency_code;
				$store->mobile_number 	= $request->mobile_number;
				$store->type 			= 1;
				$store->status 			= $request->user_status;
				$store->save();

				$new_store 					= new Store;
				$new_store->user_id			= $store->id;
				$new_store->name 			= html_entity_decode($request->store_name); 
				$new_store->description 	= $request->store_description;
				$new_store->max_time 		= '00:50:00';
				$new_store->currency_code 	= $request->currency_code;
				$new_store->price_rating 	= $request->price_rating;
				$new_store->status 			= $request->store_status;
				$new_store->service_type 	= 1;
				$new_store->delivery_type 	= implode(",",$request->delivery_type);
				$new_store->save();

				$cuisine_data = $request->cuisine ?? [];
				foreach ($cuisine_data as $value) {
					if ($value) {
						$cuisine = StoreCuisine::where('store_id', $new_store->id)->where('cuisine_id', $value)->firstOrNew();
						$cuisine->store_id = $new_store->id;
						$cuisine->cuisine_id = $value;
						$cuisine->status = 1;
						$cuisine->save();
					}
				}

				$address 				= new UserAddress;
				$address->user_id	 	= $store->id;
				$address->address 		= $request->address;
				$address->country_code 	= $request->country_code;
				$address->postal_code 	= $request->postal_code;
				$address->city 			= $request->city;
				$address->state 		= $request->state;
				$address->street 		= $request->street;
				$address->latitude 		= $request->latitude;
				$address->longitude 	= $request->longitude;
				$address->default 		= 1;
				$address->save();

				if ($request->file('banner_image'))
				{
					$file 		= $request->file('banner_image');
					$file_path 	= $this->fileUpload($file, 'public/images/store/' . $new_store->id);
					$this->fileSave('store_banner', $new_store->id, $file_path['file_name'], '1');
					$orginal_path = Storage::url($file_path['path']);
					$size = get_image_size('store_image_sizes');
					foreach ($size as $value) {
						$this->fileResize($orginal_path, $value['width'], $value['height']);
					}
				}

				if ($request->document)
				{
					foreach ($request->document as $key => $value)
					{
						$file 			= $value['document_file'];
						$file_path 		= $this->fileUpload($file, 'public/images/store/' . $new_store->id . '/documents');
						$file_id 		= $this->fileSave('store_document', $new_store->id, $file_path['file_name'], '1', 'multiple');
						$store_document 				= new StoreDocument;
						$store_document->name 			= $value['name'];
						$store_document->document_id 	= $file_id;
						$store_document->store_id 		= $new_store->id;
						$store_document->save();
					}
				}
				flash_message('success', trans('admin_messages.added_successfully'));
				return redirect()->route('admin.view_restaurant');
			}
		}
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function view() 
	{
		$this->view_data['form_name'] = trans('admin_messages.store_management');
		return view('admin/restaurant/view', $this->view_data);
	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */

	public function all_restaurants() 
	{
		$stores = User::where('user.type','=',1)->join('store', function($join) {
                      $join->on('user.id', '=', 'store.user_id');
                  })
				  ->join('service_type', function($join) {
                      $join->on('service_type.id', '=', 'store.service_type');
                  });
		
		$filter_type = request()->filter_type;
		$from = date('Y-m-d' . ' 00:00:00', strtotime(change_date_format(request()->from_dates)));
		
		if(request()->to_dates != ''){
			$to = date('Y-m-d' . ' 23:59:59', strtotime(change_date_format(request()->to_dates)));			
			$stores = $stores->where('user.created_at', '>=', $from)->where('user.created_at', '<=', $to);
		}
			
		$stores->select('store.id as id','user.id as user_id','user.name as name','user.email as email','store.name as store_name',DB::raw('CASE WHEN (CHAR_LENGTH(delivery_type) - CHAR_LENGTH(REPLACE(delivery_type, ",", "")) + 1) = 2 THEN "both" ELSE delivery_type END as delivery_type'),'user.status',DB::raw('CASE WHEN user.status = 0  THEN  "Inactive" WHEN user.status = 1  THEN  "Active" WHEN user.status = 2  THEN  "Vehicle Details" WHEN user.status = 3  THEN  "Document Upload" WHEN user.status = 4  THEN  "Pending" WHEN user.status = 5  THEN  "Waiting for Approval"  ELSE "Pending" END as user_status'),'service_type.service_name as service_type',DB::raw('CASE WHEN store.status = 1  THEN  "online"  ELSE "offline" END as store_status'),'store.recommend',DB::raw('CASE WHEN store.recommend = 1 THEN "Yes" ELSE "No" END as recommend'));
		
		$datatable = DataTables::of($stores)
		->addColumn('recommend', function ($stores) {
			if ($stores->status && $stores->status!='Pending'&& $stores->status!='Waiting for Approval' ) {
				$class = $stores->recommend == 'Yes' ? "success" : "danger";
				return '<a class="' . $class . '"  href="' . route('admin.recommend', ['id' => $stores->id]) . '" ><span>' . $stores->recommend . '</span></a>';
			}
			return $stores->recommend;
		})
		->addColumn('action', function ($stores) {
			return '<a title="' . trans('admin_messages.edit_preparation_time') . '" href="' . route('admin.edit_preparation_time', $stores->user_id) . '" ><i class="material-icons">alarm_add</i></a>&nbsp;<a title="' . trans('admin_messages.menu_category') . '" href="' . route('admin.menu_category', $stores->user_id) . '" ><i class="material-icons">category</i></a>&nbsp;<a title="' . trans('admin_messages.edit_open_time') . '" href="' . route('admin.edit_open_time', $stores->user_id) . '" ><i class="material-icons">alarm_on</i></a>&nbsp;<a title="' . trans('admin_messages.edit') . '" href="' . route('admin.edit_restaurant', $stores->user_id) . '" ><i class="material-icons">edit</i></a>&nbsp;<a title="' . trans('admin_messages.delete') . '" href="javascript:void(0)" class="confirm-delete" data-href="' . route('admin.delete_restaurant', $stores->user_id) . '"><i class="material-icons">close</i></a>';
		})
		->escapeColumns('recommend');

			$datatable->filterColumn('user_status', function($query, $keyword) {
				$query->whereRaw('CASE WHEN user.status = 0  THEN  "Inactive" WHEN user.status = 1  THEN  "Active" WHEN user.status = 2  THEN  "Vehicle Details" WHEN user.status = 3  THEN  "Document Upload" WHEN user.status = 4  THEN  "Pending" WHEN user.status = 5  THEN  "Waiting for Approval"  ELSE "Pending" END  LIKE "%'.$keyword.'%"');
            })->filterColumn('store_status', function($query, $keyword) {
				$query->whereRaw('CASE WHEN store.status = 1  THEN  "online"  ELSE "offline" END  LIKE "%'.$keyword.'%"');
            })->filterColumn('recommend', function($query, $keyword) {
				$query->whereRaw('CASE WHEN store.recommend = 1 THEN "Yes" ELSE "No" END  LIKE "%'.$keyword.'%"');
            })->filterColumn('delivery_type', function($query, $keyword) {
				$query->whereRaw('CASE WHEN (CHAR_LENGTH(delivery_type) - CHAR_LENGTH(REPLACE(delivery_type, ",", "")) + 1) = 2 THEN "both" ELSE delivery_type END  LIKE "%'.$keyword.'%"');
            });

		$columns = ['id', 'name', 'store_name', 'email', 'user_status', 'store_status', 'recommend', 'created_at'];
		$base = new EloquentDataTableBase($stores, $datatable, $columns, 'Stores');
		return $base->render(null);

	}

	public function recommend(Request $request) 
	{
		$store = Store::find($request->id);
		$store->recommend = ($store->recommend == 1) ? 0 : 1;
		$store->save();

		flash_message('success', trans('admin_messages.updated_successfully'));
		return redirect()->route('admin.view_restaurant');
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function delete(Request $request) 
	{
		$store_id = Store::where('user_id', $request->id)->first();
		$order = Order::where('store_id', $store_id->id)->get();
		if($order->count() > 0 ) {
			flash_message('danger', 'You can\'t delete this store user. This store has some orders');
			return redirect()->route('admin.view_restaurant');
		}

		if (!empty($store_id)) {

			$menu_item_modifier_item = [];
			$menu_item_modifier = [];
			$menu_item = [];
			$menu_time = [];
			$menu_category = [];

			//menu and menu category
			$menu = Menu::where('store_id', $store_id->id)->get();
			if ($menu->count() > 0) {
				foreach ($menu as $key => $value) {
					$menu_category[$key] = MenuCategory::where('menu_id', $value->id)->get();
				}
			}

			//menu time
			if (!empty($menu)) {
				foreach ($menu as $key => $value) {
					$menu_time[$key] = MenuTime::where('menu_id', $value->id)->get();
				}
			}

			//menu item
			if (!empty($menu_category)) {
				
				foreach ($menu_category as $key => $value) {

					foreach ($value as $key1 => $value1) {

						$menu_item[$key][$key1] = MenuItem::where('menu_category_id', $value1->id)->get();
					}
				}
			}
			//menu item modifier
			if (!empty($menu_item)) {
				foreach ($menu_item as $key => $value) {

					foreach ($value as $key1 => $value1) {

						foreach ($value1 as $key2 => $value2) {

							$menu_item_modifier[$key][$key1][$key2] = MenuItemModifier::where('menu_item_id', $value2->id)->get();
						}
					}
				}
			}
			//menu item modifier item
			if (!empty($menu_item_modifier) ) {
				foreach ($menu_item_modifier as $key => $value) {
					foreach ($value as $key1 => $value1) {
						foreach ($value1 as $key2 => $value2) {
							foreach ($value2 as $key3 => $value3) {
								$menu_item_modifier_item[$key][$key1][$key2][$key3] = MenuItemModifierItem::where('menu_item_modifier_id', $value3->id)->get();
							}
						}
					}
				}
			}

			$store_cuisine = StoreCuisine::where('store_id', $store_id->id)->get();
			$store_document = StoreDocument::where('store_id', $store_id->id)->get();
			$store_offer = StoreOffer::where('store_id', $store_id->id)->get();
			$store_preparation_time = StorePreparationTime::where('store_id', $store_id->id)->get();
			$store_time = StoreTime::where('store_id', $store_id->id)->get();
			$wishlist = Wishlist::where('store_id', $store_id->id)->get();

			//delete fetched details

			//menu item modifier item
			if (!empty($menu_item_modifier_item) ) {
				foreach ($menu_item_modifier_item as $key => $value) {

					foreach ($value as $key1 => $value1) {

						foreach ($value1 as $key2 => $value2) {

							foreach ($value2 as $key3 => $value3) {

								foreach ($value3 as $key4 => $value4) {

									if (isset($value4)) {
										$value4->delete($value4->id);
									}

								}
							}

						}
					}
				}
			}

			//menu item modifier
			if (!empty($menu_item_modifier) ) {
				foreach ($menu_item_modifier as $key => $value) {

					foreach ($value as $key1 => $value1) {

						foreach ($value1 as $key2 => $value2) {

							foreach ($value2 as $key3 => $value3) {

								if (!empty($value3) ) {
									$value3->delete($value3->id);
								}
							}
						}
					}
				}
			}

			//menu item
			if (isset($menu_item) > 0) {
				foreach ($menu_item as $key => $value) {
					foreach ($value as $key1 => $value1) {
						foreach ($value1 as $key2 => $value2) {
							if (!empty($value2) ) {
								$value2->delete($value2->id);
							}
						}
					}
				}
			}

			//menu time
			if (isset($menu_time) ) {
				foreach ($menu_time as $key => $value) {
					foreach ($value as $key1 => $value1) {

						if (!empty($value1)) {
							$value1->delete($value1->id);
						}
					}
				}
			}

			// menu category
			if (isset($menu_category) ) {
				foreach ($menu_category as $key => $value) {
					foreach ($value as $key1 => $value1) {
						if (!empty($value1)) {
							$value1->delete($value1->id);
						}
					}
				}
			}

			// menu
			if (isset($menu)) {
				foreach ($menu as $key => $value) {
					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			// store cuisine
			if (!empty($store_cuisine)) {
				foreach ($store_cuisine as $key => $value) {
					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}
			
			// store document
			if (!empty($store_document)) {
				foreach ($store_document as $key => $value) {
					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			// store offer
			if ($store_offer->count() > 0) {
				foreach ($store_offer as $key => $value) {

					if (!empty($value) ) {
						$value->delete($value->id);
					}
				}
			}
			
			// store preparation time
			if (!empty($store_preparation_time)) {
				foreach ($store_preparation_time as $key => $value) {

					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			// store time
			if (isset($store_time)) {
				foreach ($store_time as $key => $value) {
					if (!empty($value) ) {
						$value->delete($value->id);
					}
				}
			}

			//wishlist
			if ($wishlist->count() > 0) {
				foreach ($wishlist as $key => $wish) {
					$wish->delete($wish->id);
				}
			}

			// store
			if (isset($store_id)) {
				$store_id->delete($store_id->id);
			}
		}

		//user details
		$user = User::whereId($request->id)->first();

		if (!empty($user) ) {

			$payout_preference = PayoutPreference::where('user_id', $request->id)->get();
			$payout =  Payout::where('user_id',$request->id)->get();
			$user_payment_method = UserPaymentMethod::where('user_id', $request->id)->get();
			$user_promo_code = UsersPromoCode::where('user_id', $request->id)->get();
			$user_address = UserAddress::where('user_id', $request->id)->get();

			//payout preference
			if ($payout_preference->count() > 0) {
				foreach ($payout_preference as $key => $value) {

					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			//payout 
			if ($payout->count() > 0) {
				foreach ($payout as $key => $value) {
					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}


			//user payment method
			if ($user_payment_method->count() > 0) {
				foreach ($user_payment_method as $key => $value) {

					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			//user promo code
			if ($user_promo_code->count() > 0) {
				foreach ($user_promo_code as $key => $value) {

					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			//user address
			if ($user_address->count() > 0) {
				foreach ($user_address as $key => $value) {

					if (!empty($value)) {
						$value->delete($value->id);
					}
				}
			}

			$user->delete($request->id);
			flash_message('success', trans('admin_messages.deleted_successfully'));
			return redirect()->route('admin.view_restaurant');

		}

	}

	public function UpdateCurrency($stor_id,$currency_code)
	{
		$modifier_item_id = DB::table('menu')->select('menu_item_modifier_item.id')
			->join('menu_item', function($join) {
                            $join->on('menu.id', '=', 'menu_item.menu_id');
                    })
			->join('menu_item_modifier', function($join) {
                            $join->on('menu_item.id', '=', 'menu_item_modifier.menu_item_id');
                    })
			->join('menu_item_modifier_item', function($join) {
                            $join->on('menu_item_modifier.id', '=', 'menu_item_modifier_item.menu_item_modifier_id');
                    })
			->where('menu.store_id', $stor_id)->pluck('id')->toArray();
		// update currency in menu_item_modifier_item table
			DB::table('menu_item_modifier_item')->whereIn('id',$modifier_item_id)->update(['currency_code'=> $currency_code]);

		$item_id = DB::table('menu')->select('menu_item.id')
			->join('menu_item', function($join) {
                            $join->on('menu.id', '=', 'menu_item.menu_id');
                    })
			->where('store_id', $stor_id)->pluck('id')->toArray();
		// update currency in menu_item_modifier_item table
			DB::table('menu_item')->whereIn('id',$item_id)->update(['currency_code'=> $currency_code]);
	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit_store(Request $request) 
	{
		if ($request->getMethod() == 'GET') 
		{
			$this->view_data['form_name'] 	= trans('admin_messages.edit_store');
			$this->view_data['form_action'] = route('admin.edit_restaurant', $request->id);
			$this->view_data['store'] 		= User::where('id', $request->id)->firstOrFail();
			$this->view_data['cuisine'] 	= Cuisine::where('status', 1)->where('service_type',$this->view_data['store']->store->service_type)->pluck('name', 'id');
			$this->view_data['delivery_typ'] = ['delivery'=>'Delivery','takeaway'=>'Take Away'];
			$this->view_data['store']->store()->firstOrFail();
			$this->view_data['store_document'] = $this->view_data['store']->store->store_document()->with('file')->get();
			$this->view_data['store_cuisine'] = $this->view_data['store']->store->store_cuisine()->pluck('cuisine_id')->toArray();
			return view('admin/restaurant/add_store', $this->view_data);
		} 
		else 
		{
			$all_variables = request()->all();
			$all_variables['convert_dob'] = $all_variables['date_of_birth'];
			
			$rules = array(
				'first_name' => 'required',
				'last_name' => 'required',
				'store_name' => 'required',
				'store_description' => 'required',
				'email' => 'required|email|unique:user,email,' . $request->id,
				'convert_dob' => 'required|before:18 years ago|date_format:d-m-Y',
				'store_status' => 'required',
				'price_rating' => 'required',
				'cuisine' => 'required',
				'currency_code' => 'required',
				'user_status' => 'required',
				'phone_country_code' => 'required',
				'address' => 'required',
				'email' => ['required', 'max:255', 'email', 'regex:/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/', 'unique:user,email,'.$request->id.',id,type,1'],
				'mobile_number' => 'required|regex:/^[0-9]+$/|min:6|unique:user,mobile_number,' . $request->id.',id,type,1',
				'delivery_type' =>'required',
				'banner_image' => 'image|mimes:jpg,png,jpeg,gif',
			);

			if($request->password) {
				$rules['password'] = 'min:6';
			}

			// Add Admin User Validation Custom Names
			$niceNames = array(
				'first_name' => trans('admin_messages.first_name'),
				'last_name' => trans('admin_messages.last_name'),
				'store_name' => trans('admin_messages.store_name'),
				'store_description' => trans('admin_messages.store_description'),
				'email' => trans('admin_messages.email'),
				'password' => trans('admin_messages.password'),
				'convert_dob' => trans('admin_messages.date_of_birth'),
				'phone_country_code' => trans('admin_messages.country_code'),
				'mobile_number' => trans('admin_messages.mobile_number'),
				'store_status' => trans('admin_messages.store_status'),
				'price_rating' => trans('admin_messages.price_rating'),
				'user_status' => trans('admin_messages.user_status'),
				'address' => trans('admin_messages.address'),
				'cuisine' =>trans('admin_messages.cuisine'),
				'currency_code' =>trans('admin_messages.currency'),
				'delivery_type' =>trans('admin_messages.delivery_type'),
			);

			if ($request->document) {
				foreach ($request->document as $key => $value) {
					$rules['document.' . $key . '.name'] = 'required';
					if (@$value['document_file'] && $value['id'] != '') {
						$rules['document.' . $key . '.document_file'] = 'mimes:jpg,png,jpeg,pdf';
					} elseif ($value['id'] == '') {
						$rules['document.' . $key . '.document_file'] = 'required|mimes:jpg,png,jpeg,pdf';
					}
					$niceNames['document.' . $key . '.name'] = trans('admin_messages.document_name');
					$niceNames['document.' . $key . '.document_file'] = 'Please upload the file like jpg,png,jpeg,pdf format';
				}
			}

			$messages = array(
				'convert_dob.before' => 'Age must be 18 or older',
				'convert_dob.date_format' => 'The Date Of Birth does not match the format DD-MM-YYYY.',
				'email.regex' => trans('messages.profile.invalid_email'),
				'email.unique' => trans('messages.profile.email_already_taken'),
				'mobile_number.unique' => trans('messages.profile.mobile_number_already_taken'),
			);
			
			$validator = Validator::make($all_variables, $rules,$messages);
			$validator->setAttributeNames($niceNames);
			$validator->after(function ($validator) use ($request) {
				if($request->latitude == '' || $request->longitude == '') {
					$validator->errors()->add('address', 'Invalid address');
				}
			});

			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
				 // Form calling with Errors and Input values
			} 
			else 
			{
				if($all_variables['date_of_birth']) {
					$all_variables['convert_dob'] = date('Y-m-d', strtotime($all_variables['date_of_birth']));
				}

				$store 						= User::find($request->id);
				$store->user_first_name 	= strip_tags($request->first_name);
				$store->user_last_name 		= strip_tags($request->last_name);
				$store->name 				= strip_tags($request->first_name).'~'.strip_tags($request->last_name);
				$store->email = $request->email;
				if ($request->password) {
					$store->password = Hash::make($request->password);
				}
				$store->currency_code 	= $request->currency_code;
				$store->date_of_birth 	= $all_variables['convert_dob'];
				$store->country_id 		= $request->phone_country_code;
				$store->country_code 	= ltrim($request->text,'+');
				$store->mobile_number 	= $request->mobile_number;
				$store->status 			= $request->user_status;				
				$store->type = 1;
				$store->save();

				$new_store 					= Store::where('user_id', $store->id)->first();
				$new_store->name 			= strip_tags($request->store_name) ;
				$new_store->description 	= $request->store_description;
				$new_store->currency_code 	= $request->currency_code;
				$new_store->price_rating 	= $request->price_rating;
				$new_store->status 			= $request->store_status;
				$new_store->service_type 	= 1;
				$new_store->delivery_type 	= implode(",",$request->delivery_type);
				if ($request->user_status == 0) {
					$new_store->recommend = 0;
				}
				$new_store->save();

				//update currency in menu tables 
				$this->UpdateCurrency($new_store->id,$request->currency_code);
				$cuisine_data = $request->cuisine ?? [];
				foreach ($cuisine_data as $value) {
					if ($value) {
						$cuisine = StoreCuisine::where('store_id', $new_store->id)->where('cuisine_id', $value)->firstOrNew();
						$cuisine->store_id = $new_store->id;
						$cuisine->cuisine_id = $value;
						$cuisine->status = 1;
						$cuisine->save();
					}
			   	}

				//delete cousine
				$store_time = StoreCuisine::where('store_id', $new_store->id)->whereNotIn('cuisine_id', $cuisine_data)->delete();

				$address = UserAddress::where('user_id', $store->id)->default()->first();
				if ($address == '') {
					$address = new UserAddress;
				}
				$address->user_id = $store->id;
				$address->address = $request->address;
				$address->country_code = $request->country_code;
				$address->postal_code = $request->postal_code;
				$address->city = $request->city;
				$address->state = $request->state;
				$address->street = $request->street;
				$address->latitude = $request->latitude;
				$address->longitude = $request->longitude;
				$address->default = 1;
				$address->save();

				if ($request->file('banner_image')) 
				{
					$file = $request->file('banner_image');
					$file_path = $this->fileUpload($file, 'public/images/store/' . $new_store->id);
					$this->fileSave('store_banner', $new_store->id, $file_path['file_name'], '1');
					$orginal_path = Storage::url($file_path['path']);
					$size = get_image_size('store_image_sizes');
					foreach ($size as $value) {
						$this->fileResize($orginal_path, $value['width'], $value['height']);
					}

				}
				if ($request->document){
					$avaiable_id = array_column($request->document, 'id');
				} else {
					$avaiable_id = array();
				}

				$avaiable_id = array_filter($avaiable_id);
				StoreDocument::whereNotIn('id', $avaiable_id)->where('store_id',$new_store->id)->delete();
				if ($request->document) {
					foreach ($request->document as $key => $value) {
						if ($value['id']) {
							$store_document = StoreDocument::find($value['id']);
						} else {
							$store_document = new StoreDocument;
						}
						if (@$value['document_file']) {
							$file = $value['document_file'];
							$file_path = $this->fileUpload($file, 'public/images/store/' . $new_store->id . '/documents');
							$file_id = $this->fileSave('store_document', $new_store->id, $file_path['file_name'], '1', 'multiple');
							$store_document->document_id = $file_id;
						}
						$store_document->name = $value['name'];
						$store_document->store_id = $new_store->id;
						$store_document->save();
					}
				}
				flash_message('success', trans('admin_messages.updated_successfully'));
				return redirect()->route('admin.view_restaurant');
			}
		}
	}

	public function get_item(Request $request)
	{
		$item = MenuItem::where('menu_category_id',$request->category_id)->paginate(20);
		$locale='en';
		$data['item'] = $item->map(function ($item)  use ($locale) {
			$item->setDefaultLocale($locale);
			$menu_item_modifier = $item->menu_item_modifier->map(function ($item) use ($locale) {
			$item->setDefaultLocale($locale);
				$menu_item_modifier_item = $item->menu_item_modifier_item->map(function($item)  use ($locale) {
					$item->setDefaultLocale($locale);
						return [
							'id' 	=> $item->id,
							'name' 	=> $item->name,
							'price' => $item->price,
						];
				})->toArray();
						
				$min_count = ($item->count_type == 1) ? $item->count_type : '';
				return [
					'id' 	=> $item->id,
					'name'		=> $item->name,
					'count_type'=> $item->count_type,
					'is_multiple'=> $item->is_multiple,
					'min_count'	=> $min_count,
					'max_count'	=> $item->max_count,
					'is_required'	=> $item->is_required,
					'menu_item_modifier_item' => $menu_item_modifier_item
				];
			})->toArray();

			return [
				'menu_item_id' 		=> $item->id,
				'menu_item_name' 	=> $item->name,
				'menu_item_desc' 	=> $item->description,
				'menu_item_org_name'=> $item->org_name,
				'menu_item_org_desc'=> $item->org_description,
				'menu_item_price' 	=> $item->price,
				'menu_item_tax' 	=> $item->tax_percentage,
				'menu_item_type' 	=> $item->type===NULL ? '':$item->type,
				'menu_item_status' 	=> $item->status,
				'item_image' 		=> $item->menu_item_thump_image,
				'menu_item_modifier'=> $menu_item_modifier,
			];
		})->toArray();

		return $data;
	}

	public function get_category(Request $request)
	{
		$menucategory = MenuCategory::where('menu_id',$request->menu_id)->paginate(5);
		$locale='en';
		$data['menucategory'] = $menucategory->map(function ($item) use ($locale) {
				$item->setDefaultLocale($locale);
				$menuitem  = $item->all_menu_item()->paginate(20);
				$menu_item = $menuitem->map(function ($item)  use ($locale) {
					$item->setDefaultLocale($locale);
					$menu_item_modifier = $item->menu_item_modifier->map(function ($item)  use ($locale) {
						$item->setDefaultLocale($locale);
						$menu_item_modifier_item = $item->menu_item_modifier_item->map(function($item)  use ($locale) {
							$item->setDefaultLocale($locale);
							return [
								'id' 	=> $item->id,
								'name' 	=> $item->name,
								'price' => $item->price,
							];
						})->toArray();
						$min_count = ($item->count_type == 1) ? $item->count_type : '';
						return [
							'id' 	=> $item->id,
							'name'		=> $item->name,
							'count_type'=> $item->count_type,
							'is_multiple'=> $item->is_multiple,
							'min_count'	=> $min_count,
							'max_count'	=> $item->max_count,
							'is_required'	=> $item->is_required,
							'menu_item_modifier_item' => $menu_item_modifier_item
						];
					})->toArray();
					return [
						'menu_item_id' 		=> $item->id,
						'menu_item_name' 	=> $item->name,
						'menu_item_desc' 	=> $item->description,
						'menu_item_org_name'=> $item->org_name,
						'menu_item_org_desc'=> $item->org_description,
						'menu_item_price' 	=> $item->price,
						'menu_item_tax' 	=> $item->tax_percentage,
						'menu_item_type' 	=> $item->type===NULL ? '':$item->type,
						'menu_item_status' 	=> $item->status,
						'item_image' 		=> $item->menu_item_thump_image,
						'menu_item_modifier'=> $menu_item_modifier,
					];
				})->toArray();
				return [
					'menu_category_id' 	=> $item->id,
					'menu_category' 	=> $item->name,
					'menu_item' 		=> $menu_item,
					'total_item' 		=> $menuitem->lastPage(),
				];
		})->toArray();
			
		return $data;
	}


	protected function getMenu($store_id,$locale)
	{
		$menu = Menu::where('store_id', $store_id)->get();
		
		$menu = $menu->map(function ($item) use ($locale) {
			$item->setDefaultLocale($locale);
			//Pagination for menu category
			$menucategory=$item->menu_category()->paginate(5);
			
			$menu_category = $menucategory->map(function ($item)  use ($locale) {
				$item->setDefaultLocale($locale);
				//Pagination for menu item
				$menuitem = $item->all_menu_item()->paginate(20);
				$menu_item = $menuitem->map(function ($item)  use ($locale) {
					$item->setDefaultLocale($locale);
					
					$menu_item_modifier = $item->menu_item_modifier->map(function ($item)  use ($locale) {
						$item->setDefaultLocale($locale);
						
						$menu_item_modifier_item = $item->menu_item_modifier_item->map(function($item)  use ($locale) {
							$item->setDefaultLocale($locale);
							return [
								'id' 	=> $item->id,
								'name' 	=> $item->name,
								'price' => $item->price,
							];
						})->toArray();

						$min_count = ($item->count_type == 1) ? $item->count_type : '';
						return [
							'id' 	=> $item->id,
							'name'		=> $item->name,
							'count_type'=> $item->count_type,
							'is_multiple'=> $item->is_multiple,
							'min_count'	=> $min_count,
							'max_count'	=> $item->max_count,
							'is_required'	=> $item->is_required,
							'menu_item_modifier_item' => $menu_item_modifier_item
						];
					})->toArray();

					return [
						'menu_item_id' 		=> $item->id,
						'menu_item_name' 	=> $item->name,
						'menu_item_desc' 	=> $item->description,
						'menu_item_org_name'=> $item->org_name,
						'menu_item_org_desc'=> $item->org_description,
						'menu_item_price' 	=> $item->price,
						'menu_item_tax' 	=> $item->tax_percentage,
						'menu_item_type' 	=> $item->type===NULL ? '':$item->type,
						'menu_item_status' 	=> $item->status,
						'item_image' 		=> $item->menu_item_thump_image,
						'menu_item_modifier'=> $menu_item_modifier,
					];
				})->toArray();

				return [
					'menu_category_id' 	=> $item->id,
					'menu_category' 	=> $item->name,
					'menu_item' 		=> $menu_item,
					'total_item' 		=> $menuitem->lastPage(),
				];
			})->toArray();

			return [
				'menu_id' 			=> $item->id,
				'menu' 				=> $item->name,
				'menu_category' 	=> $menu_category,
				'total_category' 	=> $menucategory->lastPage(),
			];
		});

		return $menu;
	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function menu_category()
	{
		if(env("APP_ENV")=='live'){
			flash_message('danger', 'Data add,edit & delete Operation are restricted in live.');
		}
		$this->view_data['form_name'] = trans('admin_messages.store_menu');
		$store_id = request()->id;
		$this->view_data['store'] = $store = Store::where('user_id', $store_id)->first();
		$this->view_data['service_type'] = ServiceType::where('id',$store->service_type)->first();
		$this->view_data['menu'] = $this->getMenu($store->id,'en');
		return view('admin/menu_category', $this->view_data);
	}

	public function menu_locale(Request $request)
	{
		$menu = $this->getMenu($request->store_id,$request->locale);

		return response()->json(compact('menu'));
	}

	public function update_category(Request $request)
	{
		$locale = $request->locale;

		if($locale == 'en') {
			if ($request->action == 'edit') {
				$category = MenuCategory::find($request->id);
			}
			else {
				$category = new MenuCategory;
				$category->menu_id 	= $request->menu_id;
				$category->name = $request->name;
				$category->save();
				$data['category_id'] = $category->id;
			}
		}
		else {
			if(!$request->id) {
				return [
					'status' => false,
					'status_message' => __('messages.add_english_lang_first'),
				];
			}
			$category = MenuCategory::find($request->id);
			$category = $category->getTranslationById($locale, $category->id);
		}

		$category->name = $request->name;
		$category->save();

		$data['category_name'] = $category->name;
		return $data;
	}

	public function menu_time(Request $request)
	{
		$data['menu_time'] = MenuTime::where('menu_id', $request->id)->get();
		$data['translations'] = Menu::where('id', $request->id)->get();
		return $data;
	}

	public function update_menu_time()
	{
		$request = request();
		$locale = $request->locale;
		$store_id = $request->store_id;
		$menu_time = $request->menu_time;
		$menu_id = $request->menu_id;
		if($locale == 'en') {
			if($menu_id) {
				$store_menu = Menu::where('store_id', $store_id)->where('id', $menu_id)->first();
			}
			else {
				$store_menu = new Menu;
				$store_menu->store_id = $store_id;
			}
		}
		else {
			if(!$menu_id) {
				return [
					'status' => false,
					'status_message' => __('messages.add_english_lang_first'),
				];
			}
			$store_menu = Menu::where('store_id', $store_id)->where('id', $menu_id)->first();
			$store_menu = $store_menu->getTranslationById($locale, $store_menu->id);
		}
		$store_menu->name = $request->menu_name;
		$store_menu->save();
		$in_day = array_column($menu_time, 'day');
		foreach ($menu_time as $time) {				
			if ($time['id'] != '') {
				$menu_time = MenuTime::find($time['id']);
			}
			else {
				$menu_time = new MenuTime;
				if($request->menu_id == '')
				{
					$menu_time->menu_id=$store_menu->id;
				}
				else
				{
					$menu_time->menu_id = $menu_id;
				}		
				$menu_time->store_id = $store_id;
			}
			$menu_time->day = $time['day'];
			$menu_time->start_time = $time['start_time'];
			$menu_time->end_time = $time['end_time'];
			$menu_time->save();
		}
		MenuTime::where('menu_id', $menu_id)->whereNotIn('day', $in_day)->delete();
		if ($request->menu_id) {
			return ['message' => 'success', 'menu_name' => $store_menu->name];
		}
		$data['menu'] = $this->getMenu($store_id,$locale);
		return $data;
	}

	public function update_menu_item(Request $request)
	{
		$user_id = $request->store_id;
		$locale = $request->locale;
		$store_details = Store::where('user_id', $user_id)->first();
		$store_id = $store_details->id;
		$currency_code = $store_details->currency_code;
		try {
			\DB::beginTransaction();
			$update_data =[
				'price' => $request->menu_item_price,
				'currency_code' => $currency_code,
				'tax_percentage' => $request->menu_item_tax,
				'type' => 1,
				'status' => $request->menu_item_status,
			];
			$update_data['name'] = $request->menu_item_name;
			$update_data['description'] = $request->menu_item_desc;
			$update_data['type'] =1;
			if($locale == 'en') {
				if ($request->menu_item_id) {
					$update_data['name'] = $request->menu_item_name;
					$update_data['description'] = $request->menu_item_desc;
					$update_data['price'] = $request->menu_item_price;
					$menu_item = MenuItem::where('id',$request->menu_item_id)
					->update($update_data);
					$menu_item_id = $request->menu_item_id;
					$menu_item = MenuItem::find($request->menu_item_id);
					$data['edit_menu_item_image'] = $menu_item->menu_item_thump_image;
				}
				else {
					$update_data['menu_id'] = $request->menu_id;
					$update_data['menu_category_id'] = $request->category_id;
					$menu_item_id = MenuItem::insertGetId($update_data);
					$menu_item = MenuItem::find($menu_item_id);
					$data = [
						'menu_item_id' => $menu_item->id,
						'menu_item_name' => $menu_item->name,
						'menu_item_desc' => $menu_item->description,
						'menu_item_org_name' => $menu_item->org_name,
						'menu_item_org_desc' => $menu_item->org_description,
						'menu_item_price' => $menu_item->price,
						'menu_item_status' => $menu_item->status,
						'menu_item_type' => $menu_item->type,
						'menu_item_tax' => $menu_item->tax_percentage,
						'item_image' => $menu_item->menu_item_thump_image,
					];
				}
				$data['edit_menu_item_name'] = $menu_item->name;
			}
			else {
				
				if(!$request->menu_item_id) {
					return [
						'status' => false,
						'status_message' => __('messages.add_english_lang_first'),
					];
				}
				$menu_item_id = $request->menu_item_id;
				// MenuItem::where('id',$menu_item_id)->update($update_data);
				$menu_item = MenuItem::find($menu_item_id);
				$translation = $menu_item->getTranslationById($locale,$menu_item->id);
				$translation->name = $request->menu_item_name;
				$translation->description = $request->menu_item_desc;
				$translation->save();

				$data['edit_menu_item_image'] = $menu_item->menu_item_thump_image;


				$data['edit_menu_item_image'] = $menu_item->menu_item_thump_image;
				$data['edit_menu_item_name'] = $translation->name;
			}

			if ($request->file('file')) {
				$this->uploadStoreImage($request->file('file'),$menu_item->id,$store_id);
			}

			$data['edit_menu_item_image'] = $menu_item->menu_item_thump_image;
			$data['edit_menu_item_name'] = $menu_item->name;

			$item_modifiers = json_decode($request->item_modifiers,true);
			if(!isset($item_modifiers)) {
				$item_modifiers = array();
			}
			$modifier_update_ids = array();
			foreach ($item_modifiers as $modifier) {
				if($locale == 'en') {
					if(isset($modifier['id']) && $modifier['id'] != '') {
						$menu_modifier = MenuItemModifier::find($modifier['id']);
					}
					else {
						$menu_modifier = new MenuItemModifier;
					}
					$menu_modifier->menu_item_id = $menu_item->id;
					$menu_modifier->count_type = $modifier['count_type'];
					$menu_modifier->is_multiple = $modifier['is_multiple'] ?? 0;
					$menu_modifier->is_required = $modifier['is_required'] ?? 1;
					if($modifier['count_type'] == 0) {
						$menu_modifier->min_count = 0;
						$menu_modifier->max_count = $modifier['max_count'];
					}
					else {
						$menu_modifier->min_count = $modifier['min_count'];
						$menu_modifier->max_count = $modifier['max_count'] ?? null;	
					}
				}
				else {
					if(!isset($modifier['id']) || $modifier['id'] == '') {
						return [
							'status' => false,
							'status_message' => __('messages.add_english_lang_first'),
						];
					}
					$menu_modifier = MenuItemModifier::find($modifier['id']);
					$menu_modifier = $menu_modifier->getTranslationById($locale, $menu_modifier->id);
				}			
				$menu_modifier->name = $modifier['name'];
				$menu_modifier->save();

				if($locale == 'en') {
					$menu_modifier_id = $menu_modifier->id;
				}
				else {
					$menu_modifier_id = $menu_modifier->menu_item_modifier_id;
				}
				array_push($modifier_update_ids, $menu_modifier_id);

				$modifier_item_update_ids = array();

				foreach ($modifier['menu_item_modifier_item'] as $modifier_item) {
					if($locale == 'en') {
						if(isset($modifier_item['id']) && $modifier_item['id'] != '') {
							$menu_modifier_item = MenuItemModifierItem::find($modifier_item['id']);
						}
						else {
							$menu_modifier_item = new MenuItemModifierItem;
						}
						$menu_modifier_item->menu_item_modifier_id = $menu_modifier_id;
						$menu_modifier_item->price =  isset($modifier_item['price']) ? $modifier_item['price']:0;
						$menu_modifier_item->is_visible = $modifier_item['is_visible'] ?? '1';
						$menu_modifier_item->currency_code = $currency_code;
					}
					else {
						if(!isset($modifier_item['id']) || $modifier_item['id'] == '') {
							return [
								'status' => false,
								'status_message' => __('messages.add_english_lang_first'),
							];
						}
						
						$menu_modifier_item = MenuItemModifierItem::find($modifier_item['id']);
						$menu_modifier_id = $menu_modifier_item->id;
						$menu_modifier_item = $menu_modifier_item->getTranslationById($locale, $menu_modifier_item->id);
					}
					
					$menu_modifier_item->name = $modifier_item['name'];				
					$menu_modifier_item->save();

					if($locale == 'en') {
						$menu_modifier_item_id = $menu_modifier_item->id;
					}
					else {
						$menu_modifier_item_id = $menu_modifier_item->menu_item_modifier_item_id;
					}	
					array_push($modifier_item_update_ids, $menu_modifier_item_id);

				}
				MenuItemModifierItem::where('menu_item_modifier_id',$menu_modifier_id)->whereNotIn('id',$modifier_item_update_ids)->delete();
			}

			// Delete Menu Item Modifier

			$menu_modifier_ids = MenuItemModifier::where('menu_item_id',$menu_item_id)->whereNotIn('id',$modifier_update_ids)->pluck('id')->toArray();
			MenuItemModifierItem::whereIn('menu_item_modifier_id',$menu_modifier_ids)->delete();
			MenuItemModifier::where('menu_item_id',$menu_item_id)->whereNotIn('id',$modifier_update_ids)->delete();
			$data['status'] = true;
			$menu_item = MenuItem::MenuRelations()->find($menu_item->id);
			$data['menu_item_modifier'] = $menu_item->menu_item_modifier;

			\DB::commit();
			return $data;
		}
		catch (\Exception $e) {
			\DB::rollback();
			
			if($request->menu_item_id) {
				$menu_item = MenuItem::with('menu_item_modifier.menu_item_modifier_item')->where('id',$request->menu_item_id)->limit(1)->get();

				$data['menu_item'] = $menu_item->map(function ($item)  use ($locale) {
					$item->setDefaultLocale($locale);

					$menu_item_modifier = $item->menu_item_modifier->map(function ($item)  use ($locale) {
						$item->setDefaultLocale($locale);
						
						$menu_item_modifier_item = $item->menu_item_modifier_item->map(function($item)  use ($locale) {
							$item->setDefaultLocale($locale);
							return [
								'id' 	=> $item->id,
								'name' 	=> $item->name,
								'price' => $item->price,
							];
						})->toArray();

						return [
							'id' 	=> $item->id,
							'name'		=> $item->name,
							'count_type'=> $item->count_type,
							'is_multiple'=> $item->is_multiple,
							'min_count'	=> $item->min_count,
							'max_count'	=> $item->max_count,
							'is_required'	=> $item->is_required,
							'menu_item_modifier_item' => $menu_item_modifier_item
						];
					})->toArray();

					return [
						'menu_item_id' 		=> $item->id,
						'menu_item_name' 	=> $item->name,
						'menu_item_desc' 	=> $item->description,
						'menu_item_org_name'=> $item->org_name,
						'menu_item_org_desc'=> $item->org_description,
						'menu_item_price' 	=> $item->price,
						'menu_item_tax' 	=> $item->tax_percentage,
						'menu_item_type' 	=> $item->type,
						'menu_item_status' 	=> $item->status,
						'item_image' 		=> is_object($item->menu_item_thump_image) ? '' : $item->menu_item_thump_image,
						'menu_item_modifier'=> $menu_item_modifier,
					];
				})->first();
			}

			$data['status'] = false;
			$data['error_message'] = $e->getMessage();
			$data['status_message'] = trans('messages.store.this_item_use_in_order_so_cant_delete');
			return $data;
		}
	}

	public function remove_menu_time(Request $request)
	{
		$menu_time = MenuTime::find($request->id);
		if ($menu_time) {
			$menu_time->delete();
		}
	}



	public function delete_menu(Request $request)
	{
		try {
			\DB::beginTransaction();
			if ($request->category == 'item') {
				$key = $request->key;

				$menu_item_id = $request->menu['menu_category'][$request->category_index]['menu_item'][$key]['menu_item_id'];

				$menu_modifier_ids = MenuItemModifier::whereIn('menu_item_id',[$menu_item_id])->pluck('id')->toArray();
				MenuItemModifierItem::whereIn('menu_item_modifier_id',$menu_modifier_ids)->delete();
				MenuItemModifier::whereIn('menu_item_id',[$menu_item_id])->delete();
				MenuItem::find($menu_item_id)->delete();
				$data['status'] = 'true';
				\DB::commit();

				return $data;
			}
			else if ($request->category == 'category') {
				$key = $request->key;

				$menu_category_id = $request->menu['menu_category'][$key]['menu_category_id'];
				$delete_menu_item = MenuItem::where('menu_category_id', $menu_category_id)->get();

				foreach ($delete_menu_item as $key => $value) {
					MenuItem::find($value->id)->delete();
				}

				$delete_menu_item = MenuCategory::find($menu_category_id)->delete();

			}
			else if ($request->category == 'modifier') {
				$key = $request->key;
				$menu_modifier = MenuItemModifier::find($key);
				$menu_modifier_item = MenuItemModifierItem::where('menu_item_modifier_id',$menu_modifier->id)->get();

				MenuItemModifierItem::where('menu_item_modifier_id',$menu_modifier->id)->delete();
				$menu_modifier->delete();

				$data['status'] = 'true';
				\DB::commit();
				return $data;
			}
			else {

				$key = $request->key;

				$menu_id = $request->menu['menu_id'];

				$delete_menu_item = MenuItem::where('menu_id', $menu_id)->get();

				//delete item
				MenuItem::whereIn('id', $delete_menu_item->pluck('id'))->delete();
				//delete category
				MenuCategory::where('menu_id', $menu_id)->delete();
				//delete time
				MenuTime::where('menu_id', $menu_id)->delete();
				//delete menu
				Menu::where('id', $menu_id)->delete();
				MenuTranslations::where('menu_id',$menu_id)->delete();
				$data['status'] = 'true';
				\DB::commit();
				return $data;
			}
			\DB::commit();
		}
		catch (\Exception $e) {
			\DB::rollback();
			$data['status'] = 'false';
			$data['error_message'] = $e->getMessage();
			if ($request->category == 'modifier') {
				$data['status_message'] = 'This Modifier used in order so can\'t delete this';
			}
			else {
				$data['status_message'] = 'Some modifiers used in order so can\'t Modify/delete.';

			}
			return $data;
		}

	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function open_time() {
		$request = request();
		$this->view_data['store'] = Store::where('user_id', $request->store_id)->firstOrFail();
		if ($request->getMethod() == 'GET') {

			$this->view_data['form_action'] = route('admin.edit_open_time', $request->store_id);
			$this->view_data['form_name'] = trans('admin_messages.edit_open_time');
			$this->view_data['open_time'] = StoreTime::where('store_id', $request->store_id)->first();

			$this->view_data['open_time'] = (count($this->view_data['store']->store_all_time) > 0) ? $this->view_data['store']->store_all_time()->get()->toArray() : [array('day' => '')];
			return view('admin/restaurant/open_time', $this->view_data);
		} else {

			$req_time_id = array_filter($request->time_id);
			if (count($req_time_id)) {
				StoreTime::whereNotIn('id', $req_time_id)->where('store_id', $this->view_data['store']->id)->delete();
			}
			foreach ($request->day as $key => $time) {
				if (isset($req_time_id[$key])) {
					$store_insert = StoreTime::find($req_time_id[$key]);
				} else {
					$store_insert = new StoreTime;
				}
				$store_insert->start_time = ($request->start_time[$key]);
				$store_insert->end_time = ($request->end_time[$key]);
				$store_insert->day = $request->day[$key];
				$store_insert->status = $request->status[$key];
				$store_insert->store_id = $this->view_data['store']->id;
				$store_insert->save();
			}

		}

		flash_message('success', 'Updated successfully');
		return redirect()->route('admin.view_restaurant');

	}

	/**
	 * Manage site setting
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function preparation_time() {
		$request = request();
		$this->view_data['store'] = Store::where('user_id', $request->store_id)->firstOrFail();
		if ($request->getMethod() == 'GET') {

			$this->view_data['preparation'] = StorePreparationTime::where('store_id', $this->view_data['store']->id)->get();

			$this->view_data['max_time'] = convert_minutes(Store::where('id', $this->view_data['store']->id)->first()->max_time);

			$this->view_data['form_action'] = route('admin.edit_preparation_time', $request->store_id);
			$this->view_data['form_name'] = trans('admin_messages.edit_preparation_time');

			return view('admin/restaurant/preparation_time', $this->view_data);
		} else {
			$store = Store::find($this->view_data['store']->id);
			$store->max_time = convert_format($request->overall_max_time);
			$store->save();
			if (isset($request->day)) {
				foreach ($request->day as $key => $time) {
					if (isset($request->id[$key])) {
						$store_update = StorePreparationTime::find($request->id[$key]);
					} else {
						$store_update = new StorePreparationTime;
					}
					$store_update->from_time = $request->from_time[$key];
					$store_update->to_time = $request->to_time[$key];
					$store_update->max_time = convert_format($request->max_time[$key]);
					$store_update->day = $request->day[$key];
					$store_update->status = $request->status[$key];
					$store_update->store_id = $this->view_data['store']->id;
					$store_update->save();
					$available_id[] = $store_update->id;
				}

				if (isset($available_id)) {
					StorePreparationTime::whereNotIn('id', $available_id)->delete();
				}

				flash_message('success', 'Updated Successfully');
			}
			else{
				$store = StorePreparationTime::where('store_id',$this->view_data['store']->id)->delete();
				flash_message('success', 'Updated Successfully');
			}
			return redirect()->route('admin.view_restaurant');

		}

	}

	protected function uploadStoreImage($file,$menu_item_id,$store_id)
	{
		$file_path = $this->fileUpload($file, 'public/images/store/' . $store_id . '/menu_item');		$this->fileSave('menu_item_image', $menu_item_id, $file_path['file_name'], '1');
		$orginal_path = Storage::url($file_path['path']);

		$size = get_image_size('item_image_sizes');
		foreach ($size as $new_size) {
			$this->fileCrop($orginal_path, $new_size['width'], $new_size['height']);
		}
	}


	public function import_menu(Request $request) {

		$rules['import_file'] = 'required|mimes:xls,xlsx,csv';
		$niceNames['import_file'] = trans('admin_messages.import_file');

		$validator = Validator::make($request->all(), $rules);
		$validator->setAttributeNames($niceNames);
		if($validator->fails()) {
			return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
		} else {
			try{
				\Excel::import(new MenuImport($request->post('store_id')), $request->file('import_file'));
			}catch(\Exception $e){
				flash_message('danger', 'Please import data with valid format.');
				return redirect()->back();
			}
			flash_message('success', 'Data Imported successfully.');
			return redirect()->back();
		}
	}

	public function export_sample_menu() {
		return \Excel::download(new MenuImport, 'Menu.xlsx');
	}

	public function oweAmount(StoreOweAmountDataTable $dataTable)
	{
		$this->view_data['form_name'] = trans('admin_messages.owe_store_amount_management');
		return $dataTable->render('admin.restaurant.owe_amount', $this->view_data);
	}

	public function deleteDocuments(Request $request)
	{
		$data = StoreDocument::where('document_id', $request->document_id)->delete();
	}

}
