<?php
/**
 * FoodReceiverController
 *
 * @package     Gofer Delivery All
 * @subpackage  Controller
 * @category    Admin
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use App\DataTables\FoodReceiverDataTable;
use App\Http\Controllers\Controller;
use App\Models\FoodReceiver;
use App\Models\FoodReceiverTranslations;
use Illuminate\Http\Request;
use Validator;
use App\Models\Language;


class FoodReceiverController extends Controller {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add(Request $request)
    {


        if ($request->getMethod() == 'GET')
        {
            $this->view_data['languages']  = Language::pluck('name', 'value');
            if ($request->id) {
				$this->view_data['form_name'] = trans('admin_messages.edit_food_receiver');
				$this->view_data['form_action'] = route('admin.edit_food_receiver', $request->id);
				$this->view_data['food_receiver'] = FoodReceiver::findOrFail($request->id);
			} else {
				$this->view_data['form_action'] = route('admin.add_food_receiver');
				$this->view_data['form_name'] = trans('admin_messages.add_food_receiver');
			}
			return view('admin/food_receiver/food_receiver_form', $this->view_data);
        }
        else
        {
            // Add Help Subcategory Validation Rules
            $rules = array(
                    'name'    => 'required',
                    );

            // Add Help Subcategory Validation Custom Names
            $niceNames = array(
                        'name'    => 'Name',
                        );
            foreach($request->translations ?: array() as $k => $translation)
            {
                $rules['translations.'.$k.'.locale'] = 'required';
                $rules['translations.'.$k.'.name'] = 'required';

                $niceNames['translations.'.$k.'.locale'] = 'Language';
                $niceNames['translations.'.$k.'.name'] = 'Name';
            }
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($niceNames); 

            if ($validator->fails()) 
            {
                return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
            }
            else
            {
                if ($request->id) {
					$food_receiver = FoodReceiver::find($request->id);
				} else {
					$food_receiver = new FoodReceiver;
				}

                $food_receiver->name        = $request->name;            
                $food_receiver->save();

                $data['locale'][0] = 'en';
                foreach($request->translations ?: array() as $translation_data) {  
                    if($translation_data){
                         $get_val = FoodReceiverTranslations::where('food_receiver_id',$food_receiver->id)->where('locale',$translation_data['locale'])->first();
                            if($get_val)
                                $food_lang = $get_val;
                            else
                                $food_lang = new FoodReceiverTranslations;
                        $food_lang->name        = $translation_data['name'];
                        $food_lang->locale      = $translation_data['locale'];
                        $food_lang->food_receiver_id     = $food_receiver->id;
                        $food_lang->save();
                        $data['locale'][] = $translation_data['locale'];
                    }
                }
                if(@$data['locale'])
                FoodReceiverTranslations::where('food_receiver_id',$food_receiver->id)->whereNotIn('locale',$data['locale'])->delete();

				if ($request->id) {
					flash_message('success', trans('admin_messages.updated_successfully'));
				} else {
					flash_message('success', trans('admin_messages.added_successfully'));
				}
                
                return redirect()->route('admin.food_receiver');
            }
        }
    }

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function view(FoodReceiverDataTable $dataTable) {
		$this->view_data['form_name'] = trans('admin_messages.food_receiver_management');
		return $dataTable->render('admin.food_receiver.view', $this->view_data);
	}

	/**
	 * Manage
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function delete(Request $request) {
	
			FoodReceiver::find($request->id)->delete();
			flash_message('success', trans('admin_messages.deleted_successfully'));
		return redirect()->route('admin.food_receiver');
	}

}
