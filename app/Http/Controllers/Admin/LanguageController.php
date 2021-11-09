<?php

/**
 * Language Controller
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
use App\DataTables\LanguageDataTable;
use App\Models\language;
use Validator;
use App;
use Lang;
use File;

class LanguageController extends Controller
{
    /**
     * Load Datatable for Bed Type
     *
     * @param array $dataTable  Instance of LanguageDataTable
     * @return datatable
     */
    public function index(LanguageDataTable $dataTable)
    {
    	$this->view_data['form_name'] = trans('admin_messages.language_management');
		return $dataTable->render('admin.language.view', $this->view_data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    	$this->view_data['form_action'] = route('admin.store_language');
		$this->view_data['form_name'] = trans('admin_messages.add_language');
		return view('admin/language/language_form', $this->view_data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate Data
        $validate_return = $this->validate_request_data($request->all());
        
        if($validate_return) {
            return $validate_return;
        }

        $language = new Language;
        $language->name        = $request->name;
        $language->value       = $request->value;
        $language->status      = $request->status;
        $language->save();
        $path = base_path().'/resources/lang/'.$language->value;
        $old = umask(0);
        $result = File::makeDirectory($path,0777);
        $source = base_path().'/resources/lang/en/js_messages.php';
        $destination = base_path().'/resources/lang/'.$language->value.'/js_messages.php';
        \File::copy($source,$destination);
         \Artisan::call('lang:js');

        flash_message('success','Language Added Successfully');
        return redirect()->route('admin.languages');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->view_data['form_action'] = route('admin.update_language',['id' => $id]);
		$this->view_data['form_name'] = trans('admin_messages.edit_language');
		$this->view_data['language_select'] = Language::findOrFail($id);

		return view('admin/language/language_form', $this->view_data);
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
        // Validate Data
        $validate_return = $this->validate_request_data($request->all(),$id);
        if($validate_return) {
            return $validate_return;
        }
        $language = Language::find($id);

        $language->name        = $request->name;
        $language->value       = $request->value;
        $language->status      = $request->status;
        if($language->default_language != 1)
        {
            $language->save();
            \Artisan::call('lang:js');  
            flash_message('success','Language Updated Successfully');
        }
        else
        {
             flash_message('danger','Cannot Updated Default Language');
        }

       
        return redirect()->route('admin.languages');
    }

    /**
     * Validate Given Request Data.
     *
     * @param  Array  $request_data
     * @param  int  $id
     * @return \Illuminate\Http\Response | void
     */
    protected function validate_request_data($request_data, int $id = 0)
    {
        $rules  = array(
            'name' => 'required',
            'value' => 'required|regex:/^[a-z]*$/|unique:language,value,'.$id,
            'status' => 'required',
        );

        $messages = array(

        );

        $attributes = array(
            'status' => 'Status',
        );

        $validator = Validator::make($request_data, $rules, $messages, $attributes);

        $lang_count = Language::whereNotIn('id',[$id])->where('name',$request_data['name'])->count();

        if($lang_count > 0) {
            flash_message('danger', 'This Name already exists');
            return redirect()->route('admin.languages');
        }

        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
    }

   /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $can_delete = $this->canDestroy($id);

        if($can_delete['status'] == 0) {
            flash_message('danger',$can_delete['message']);
        }
        else {
            $language = Language::find($id);
            if($language->default_language == 1)
            {
                flash_message('danger','Cannot able to Deleted Default Language');
            }
            else
            {
                $language->delete();
                flash_message('success','Language Deleted Successfully');
            }
            
        }

        return redirect()->route('admin.languages');
    }

    /**
     * Validate Lanaguage can destroyed or not.
     *
     * @param  int  $id
     * @return Array
     */
    protected function canDestroy(int $id)
    {
        $active_count   = Language::whereNotIn('id',[$id])->where('status','Active')->count();
        if($active_count <= 0) {
            return ['status' => 0, 'message' => 'Atleast one Active Language in admin panel. So can\'t delete this'];
        }

        $language = Language::find($id);
        $has_translation  = $this->hasLanguageTranslation($language->value);
        if($has_translation['status']) {
            return ['status' => 0, 'message' => 'Sorry, This language has '. $has_translation['type'] .' Translation. So, cannot delete this.'];
        }

        return ['status' => 1, 'message' => ''];
    }

    /**
     * Check Given Language Already used in any translation
     *
     * @param String $code
     * @return Array $return Contains status and type
     */
    public function hasLanguageTranslation($code)
    {
        $trans_count = \App\Models\MenuTranslations::where('locale',$code)->count();
        if($trans_count > 0) {
            return ['status' => 1, 'type' => 'Menu'];
        }

        $trans_count = \App\Models\MenuCategoryTranslations::where('locale',$code)->count();
        if($trans_count > 0) {
            return ['status' => 1, 'type' => 'Menu Category'];
        }

        $trans_count = \App\Models\MenuItemTranslations::where('locale',$code)->count();
        if($trans_count > 0) {
            return ['status' => 1, 'type' => 'Menu Item'];
        }

        $static_page = \App\Models\PagesTranslations::where('locale',$code)->count();
        if($static_page > 0) {
            return ['status' => 1, 'type' => 'Static Page'];
        } 

        $HelpCategoryLang = \App\Models\HelpCategoryLang::where('locale',$code)->count();  
        if($HelpCategoryLang > 0) {
            return ['status' => 1, 'type' => 'Help Category'];
        }  

        $HelpSubCategoryLang = \App\Models\HelpSubCategoryLang::where('locale',$code)->count();  
        if($HelpSubCategoryLang > 0) {
            return ['status' => 1, 'type' => 'Help Sub Category'];
        } 


        $menu_addOn = \App\Models\MenuItemModifierTranslations::where('locale',$code)->count();  
        if($menu_addOn > 0) {
            return ['status' => 1, 'type' => 'Modifier Menu'];
        } 

        return ['status' => 0, 'type' => ''];
    }
}