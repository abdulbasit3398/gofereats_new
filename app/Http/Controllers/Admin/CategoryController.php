<?php
/**
 * CuisineController
 *
 * @package     Gofer Delivery All
 * @subpackage  Controller
 * @category    Admin
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use App\DataTables\CuisineDataTable;
use App\Http\Controllers\Controller;
use App\Models\Cuisine;
use App\Models\StoreCuisine;
use Illuminate\Http\Request;
use Validator;
use App\Traits\FileProcessing;
use Storage;
use App\Models\Language;
use App\Models\ServiceType;

class CategoryController extends Controller {
	
	use FileProcessing;
	
	public function __construct() {
		parent::__construct();
	}

	/**jpg,png,jpeg,gif
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add(Request $request) {
		
		if ($request->getMethod() == 'GET') {
			$this->view_data['languages']  = Language::pluck('name', 'value');
			$this->view_data['form_action'] = route('admin.add_category');
			$this->view_data['form_name'] = trans('admin_messages.add_cuisine');
			return view('admin/category/category_form', $this->view_data);
		} 
		else 
		{
			$rules = array(
				'name' => 'required',				
				'status' => 'required',
				'description' => 'required',
				'image' => 'required|mimes:jpg,png,jpeg,gif',
				'dietary_icon' => 'mimes:jpg,png,jpeg,gif',
			);
			
			// Validation Custom Names
			$niceNames = array(
				'name' => trans('admin_messages.name'),
				'description' => trans('admin_messages.description'),
				'status' => trans('admin_messages.status'),
			);
			
			foreach($request->translations ?: array() as $k => $translation){
                $rules['translations.'.$k.'.locale'] = 'required | nullable ';
                $rules['translations.'.$k.'.name'] = 'required';
                $rules['translations.'.$k.'.description'] = 'required';

                $niceNames['translations.'.$k.'.locale'] = 'Language';
                $niceNames['translations.'.$k.'.name'] = 'Name';
                $niceNames['translations.'.$k.'.description'] = 'Description';
            }
            
            if($request->file('image'))
            {
				$ext = $request->file('image')->getClientOriginalExtension();
			    $image_validation = $this->validateExtension($ext);
			    if($image_validation == false){
			    	return back()->withErrors(['image' => 'The image must be a file of type: jpg, png, jpeg, gif.'])->withInput();
			    }
			}

			$validator = Validator::make($request->all(), $rules);
			$validator->setAttributeNames($niceNames);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
				 // Form calling with Errors and Input values
			} else {
				$cuisine = new Cuisine;
				$cuisine->name 			= $request->name;
				$cuisine->description 	= $request->description;
				$cuisine->status 		= $request->status;
				$cuisine->is_dietary 	= $request->is_dietary=='yes'?1:'';
				$cuisine->service_type	= 1;
				$cuisine->is_top 		= 0;
				$cuisine->most_popular	= 0;
				$cuisine->save();

				foreach($request->translations ?: array() as $translation_data) {  
                    $translation = $cuisine->getTranslationById(@$translation_data['locale'], $cuisine->id);
                    $translation->name = $translation_data['name'];                    
                    $translation->description = $translation_data['description'];
                    $translation->save();
                }

				if ($request->file('image')) {
					$file = $request->file('image');
					$file_path = $this->fileUpload($file, 'public/images/cuisine_image');
					$this->fileSave('cuisine_image', $cuisine->id, $file_path['file_name'], '1');
					$orginal_path = Storage::url($file_path['path']);
					$this->fileCrop($orginal_path, get_image_size('cuisine_image_size')['width'], get_image_size('cuisine_image_size')['height']);
				}
				if ($request->file('dietary_icon') && $request->is_dietary=='yes') {
					$file = $request->file('dietary_icon');
					$file_path = $this->fileUpload($file, 'public/images/cuisine_image');
					$this->fileSave('dietary_icon', $cuisine->id, $file_path['file_name'], '1');
					$orginal_path = Storage::url($file_path['path']);
					$this->fileCrop($orginal_path, get_image_size('dietary_icon_size')['width'], get_image_size('dietary_icon_size')['height']);
				}
				flash_message('success', trans('admin_messages.updated_successfully'));
				return redirect()->route('admin.cuisine');
			}

		}
	}



	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function view(CuisineDataTable $dataTable) {
		$this->view_data['form_name'] = trans('admin_messages.cuisine_management');
		return $dataTable->render('admin.category.view', $this->view_data);
	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function delete(Request $request) {
		$storer_cuisine = StoreCuisine::where('cuisine_id', $request->id)->get()->count();
		if($storer_cuisine>0){
			flash_message('danger', 'Sorry, Some store use this cuisine so can\'t delete this');
		}
		else{
			StoreCuisine::where('cuisine_id', $request->id)->forcedelete();
			Cuisine::find($request->id)->forcedelete();
			flash_message('success', trans('admin_messages.deleted_successfully'));
		}
		return redirect()->route('admin.cuisine');
	}


	public function change_status(Request $request) 
	{
		$column = $request->column;
		$cuisine = Cuisine::find($request->id);
		if($cuisine->$column==1){
			$cuisine->$column = 0;
		}
		else{
			$cuisine->$column = 1;			
		} 
		$cuisine->save(); 
		flash_message('success', trans('admin_messages.updated_successfully'));
		return redirect()->route('admin.cuisine');
	}
	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request) {
		// dd($request->getMethod());
		if ($request->getMethod() == 'GET') {
			$this->view_data['languages']  = Language::pluck('name', 'value');	
			$this->view_data['form_name'] = trans('admin_messages.edit_cuisine');
			$this->view_data['form_action'] = route('admin.edit_category', $request->id);
			$this->view_data['cuisine'] = Cuisine::findOrFail($request->id);
			return view('admin/category/category_form_edit', $this->view_data);
		} else {

			$rules = array(
				'name' => 'required',
				'description'=>'required',
				'status' => 'required',
				'image' => 'mimes:jpg,png,jpeg,gif',
				'dietary_icon' => 'mimes:jpg,png,jpeg,gif',
			);

			// Validation Custom Names
			$niceNames = array(
				'name' => trans('admin_messages.name'),
				'description' => trans('admin_messages.description'),
				'status' => trans('admin_messages.status'),
			);
			
			if($request->file('image')){
				$ext = $request->file('image')->getClientOriginalExtension();
			    $image_validation = $this->validateExtension($ext);
			    if($image_validation == false)
			    	return back()->withErrors(['image' => 'The image must be a file of type: jpg, png, jpeg, gif.'])->withInput();
			}

			foreach($request->translations ?: array() as $k => $translation){
                $rules['translations.'.$k.'.locale'] = 'required';
                $rules['translations.'.$k.'.name'] = 'required';
                $rules['translations.'.$k.'.description'] = 'required';
                $niceNames['translations.'.$k.'.locale'] = 'Language';
                $niceNames['translations.'.$k.'.name'] = 'Name';
                $niceNames['translations.'.$k.'.description'] = 'Name';
            }
           
			$validator = Validator::make($request->all(), $rules);
			$validator->setAttributeNames($niceNames);
			if ($validator->fails()) {
				return back()->withErrors($validator)->withInput();
				// Form calling with Errors and Input values
			} 
			else 
			{
				$cuisine 				= Cuisine::find($request->id);
				$cuisine->name 			= $request->name;
				$cuisine->description 	= $request->description;
				$cuisine->status 		= $request->status;
				$cuisine->is_dietary 	= $request->is_dietary=='yes'?1:'';
				$cuisine->service_type	= 1;
				$cuisine->save();

				$removed_translations = explode(',', $request->removed_translations);
                foreach(array_values($removed_translations) as $id) {
                    $cuisine->deleteTranslationById($id);
                }

                foreach($request->translations ?: array() as $translation_data) {  
                    $translation = $cuisine->getTranslationById(@$translation_data['locale'], $translation_data['id']);
                    $translation->name = $translation_data['name'];$translation->description = $translation_data['description'];
                    $translation->save();
                }

				if ($request->file('image')) {
					$file = $request->file('image');
					$file_path = $this->fileUpload($file, 'public/images/cuisine_image');
					$this->fileSave('cuisine_image', $cuisine->id, $file_path['file_name'], '1');
					$orginal_path = Storage::url($file_path['path']);
					$this->fileCrop($orginal_path, get_image_size('cuisine_image_size')['width'], get_image_size('cuisine_image_size')['height']);
				}
				flash_message('success', trans('admin_messages.updated_successfully'));
				return redirect()->route('admin.cuisine');
			}
		}
	}
}
