<?php

namespace App\Imports;

use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemModifier;
use App\Models\MenuItemModifierItem;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MenuImport implements ToModel, WithValidation, WithHeadingRow, FromView {

	use Importable;

    private $menu_id, $category_id, $store_id, $menu_item_id;

    public function __construct($store_id=null) {
    	$this->store_id = $store_id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row) {

        if($this->store_id) {

            // insert menu
            if($row['menu']) {
                $menuArr = explode("|",$row['menu']);
                foreach ($menuArr as $key => $value) {
                    $menu_lang = explode(":", $value);
                    $menu_local = trim(strtolower($menu_lang[0]));
                    if($menu_local == 'en'){
                        $menu_data['store_id'] = $this->store_id;
                        $menu_data['name']  = $menu_lang[1];
                        $this->menu_id = Menu::insertGetId($menu_data);
                    }
                    if($this->menu_id && $menu_local != 'en'){
                        $menu = Menu::find($this->menu_id);
                        $menu_translation = $menu->getTranslationById($menu_local,$this->menu_id);
                        $menu_translation->name = $menu_lang[1];

                        $menu_translation->save(); 
                    }                   
                }
            }

            // insert menu category
            if($row['category'] && $this->menu_id) {
                $categoryArr = explode("|",$row['category']);
                foreach ($categoryArr as $key => $value) {
                    $category_lang = explode(":", $value);
                    $category_local = trim(strtolower($category_lang[0]));
                    if($category_local == 'en'){
                        $category_data['name']      = $category_lang[1];
                        $category_data['menu_id']   = $this->menu_id;
                        $this->category_id = MenuCategory::insertGetId($category_data);
                    }
                    if($this->category_id && $category_local != 'en'){
                        $category = MenuCategory::find($this->category_id);
                        $category_translation = $category->getTranslationById($category_local,$this->category_id);
                        $category_translation->name = $category_lang[1];
                        $category_translation->save();     
                    }
                }
            }

            // insert menu item
            if($row['item_name'] && $this->menu_id && $this->category_id) {
                $itemArr = explode("|",$row['item_name']);
                $descArr = explode("|",$row['description']);
                
                foreach ($itemArr as $key => $value) {
                    $item_lang = explode(":",$value);
                    $desc_lang = explode(":",$descArr[$key]);
                    $item_local = trim(strtolower($item_lang[0]));
                    $desc_local = trim(strtolower($desc_lang[0]));
                    if($item_local == 'en' && $desc_local == 'en'){
                        $item_data['menu_id'] = $this->menu_id;
                        $item_data['menu_category_id'] = $this->category_id;
                        $item_data['name']          = $item_lang[1];
                        $item_data['price']         = $row['price'];
                        $item_data['currency_code'] = session('currency');
                        $item_data['description']   = $desc_lang[1];
                        $item_data['tax_percentage']= $row['tax_percentage'];
                        $item_data['status']        = $row['status']=='inactive' ? '0':'1';
                        $item_data['type']  = 1;
                        $this->menu_item_id = MenuItem::insertGetId($item_data);
                    }

                    if($this->menu_item_id && $item_local != 'en' && $desc_local != 'en'){
                        $menu_item = MenuItem::find($this->menu_item_id);
                        $item_translation = $menu_item->getTranslationById($item_local,$this->menu_item_id);
                        $item_translation->name = $item_lang[1];
                        $item_translation->description = $desc_lang[1];
                        $item_translation->save();  
                    }
                }
            }
        }
    }

    public function onFailure(Failure ...$failures)
    {
        dd($failures);
    }

    public function rules(): array {
        $rules['menu'] = function($attribute, $value, $onFailure) {
                          if($value !='' && strpos(strtolower($value),"en") !== 0){
                            $onFailure('Name is not Patrick Brouwers');
                          }
                    };
        $rules['category'] = function($attribute, $value, $onFailure) {
                          if($value !='' && strpos(strtolower($value),"en") !== 0){
                            $onFailure('Name is not Patrick Brouwers');
                          }
                    };
        $rules['item_name'] = function($attribute, $value, $onFailure) {
                          if($value !='' && strpos(strtolower($value),"en") !== 0){
                            $onFailure('Name is not Patrick Brouwers');
                          }
                    };
    	$rules['price'] = 'required|regex:/^\d+(\.\d{1,2})?$/';
    	$rules['description'] = 'required';
    	$rules['tax_percentage'] = 'required|regex:/^\d+(\.\d{1,2})?$/';
    	$rules['status'] = 'required|in:active,inactive';

        return $rules;
    }

    public function customValidationMessages() {
	    $customMessages['required'] = 'The :attribute field must be required';
	    return $customMessages;
	}

    public function view(): View {
        return view('Excel.Menu');
    }
}
