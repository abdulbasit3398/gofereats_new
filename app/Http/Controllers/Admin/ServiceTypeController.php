<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataTables\ServiceTypeDataTable;
use App\Models\Language;
use App\Models\ServiceType;
use Validator;
use App\Models\Store;
use App\Models\StoreCuisine;
use App\Traits\FileProcessing;
use App\Models\Cuisine;
use App\Models\ServiceTypeTranslation;
use App\Models\Order;

class ServiceTypeController extends Controller
{
    //
	use FileProcessing;

	public function __construct() {
		parent::__construct();
	}

    public function index(ServiceTypeDataTable $dataTable) 
    {
		$this->view_data['form_name'] = trans('admin_messages.home_banner');
		return $dataTable->render('admin.service_type.view', $this->view_data);
	}

	public function update(Request $request,$id) 
	{
		if ($request->getMethod() == 'GET') 
		{
			$this->view_data['languages'] = Language::pluck('name', 'value');
			$this->view_data['form_action'] =route('admin.edit_home_banner', $request->id);
			$this->view_data['form_name'] = trans('admin_messages.edit_home_banner');
			$this->view_data['result']= ServiceType::findOrFail($id);
			return view('admin/service_type/edit_service_type_form', $this->view_data);
		} 
		else {
			
			$rules = array(
				'service_name' 			=> 'required|unique:service_type,service_name,'.$id,
				'service_description' 	=> 'required',
				'service_image' =>'mimes:jpg,png,jpeg,gif',
				'service_type_banner_image' =>'mimes:jpg,png,jpeg,gif',
			);
			
			foreach($request->translations ?: array() as $k => $translation)
            {
                $rules['translations.'.$k.'.locale'] = 'required';
                $rules['translations.'.$k.'.name'] = 'required';
                $rules['translations.'.$k.'.description'] = 'required';

                $niceNames['translations.'.$k.'.locale'] = 'Language';
                $niceNames['translations.'.$k.'.name'] = 'Name';
                $niceNames['translations.'.$k.'.description'] = 'Description';
            }

			$validator = Validator::make($request->all(), $rules);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput(); 
				// Form calling with Errors and Input values
			} 
			else {

				$service 				= ServiceType::find($request->id);
				$service->service_name 	= $request->service_name;
				$service->service_description = $request->service_description;
				$service->has_addon = 'Yes';
				$service->status = 1;

				if ($request->file('service_image')) {
					$file = $request->file('service_image');
					$folder = 'service_type';
					$file_path = $this->fileUpload($file, 'public/images/'.$folder);
					$this->fileSave($folder, $service->id, $file_path['file_name'], '1');
				}

				if ($request->file('service_type_banner_image')) {
					$file = $request->file('service_type_banner_image');
					$folder = 'service_type_banner_image';
					$file_path = $this->fileUpload($file, 'public/images/'.$folder);
					$this->fileSave($folder, $service->id, $file_path['file_name'], '1');
				}

				$service->save();
				$data['locale'][0] = 'en';
                foreach($request->translations ?: array() as $translation_data) {  
                    if($translation_data){
                         	$get_val = ServiceTypeTranslation::where('service_type_id',$service->id)->where('locale',$translation_data['locale'])->first();
                            if($get_val)
                                $help_category_lang = $get_val;
                            else
	                        $help_category_lang            		= new ServiceTypeTranslation;
	                        $help_category_lang->name      		= $translation_data['name'];
	                        $help_category_lang->description    = $translation_data['description'];
	                        $help_category_lang->locale    = $translation_data['locale'];
	                        $help_category_lang->service_type_id = $service->id;
	                        $help_category_lang->save();
	                        $data['locale'][] = $translation_data['locale'];
                    }
                }
                if(@$data['locale']){
                	ServiceTypeTranslation::where('service_type_id',$service->id)->whereNotIn('locale',$data['locale'])->delete();
                }
                flash_message('success', 'Updated Successfully'); // Call flash message 
				return redirect()->route('admin.home_banner');
			}
		}
	}

	public function categoryType(Request $request)
	{
		if(is_null($request->service_type_id))
		{
			return $data['value'] = 'null';
		}
		$cusine = Cuisine::Active()->where('service_type',$request->service_type_id)->pluck('name','id');
		$data['value'] = $cusine;
		return $data;
	}


	public function canServiceInActivate($serviceId)
	{
		$order = Order::whereIn('status',[3,5,8])->where('service_type',$serviceId)->get()->count();
		return $order;
	}

}
