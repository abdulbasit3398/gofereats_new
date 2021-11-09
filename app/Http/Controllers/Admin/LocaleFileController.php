<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use App;
use Lang;
use File;
class LocaleFileController extends Controller
{

   	private $lang = '';
    private $file;
    private $path;
    private $arrayLang = array();


    //------------------------------------------------------------------------------
	// Read lang file content
	//------------------------------------------------------------------------------

    private function read() 
    {
        if ($this->lang == '') $this->lang = App::getLocale();
        $this->arrayLang = Lang::get(str_replace('.php','',$this->file),[],$this->lang);
        if (gettype($this->arrayLang) == 'string') $this->arrayLang = array();
        //if some data not in array it's store in other array 
        if(isset($this->arrayLang['gofereats']))
                $this->arrayLang['gofereats']['app_name'] = site_setting('site_name');
        $other = $this->arrayLang; 
        foreach ($other as $key => $value) { 
            if (is_array($value))
                unset($other[$key]);
            else
                unset($this->arrayLang[$key]);
        } 
        if(count($other))
        $this->arrayLang['other'] = $other;
    }

    private function get_lang_data($lang='en',$file='messages') 
    {
        $arrayLang = Lang::get($file,[],$lang);
        // dd($arrayLang);
        if (gettype($arrayLang) == 'string') 
            $arrayLang = array();

        //if some data not in array it's store in other array 
        $other = $arrayLang; 
        foreach ($other as $key => $value) { 
            if (is_array($value))
                unset($other[$key]);
            else
                unset($arrayLang[$key]);
        } 
        if(count($other))
        $arrayLang['other'] = $other;
        return $arrayLang;
    }

    //------------------------------------------------------------------------------
	// Save lang file content
	//------------------------------------------------------------------------------

    private function save() 
    {
        $path = base_path().'/resources/lang/'.$this->lang.'/'.$this->file;
        $content = "<?php\n\nreturn\n[\n";

        foreach ($this->arrayLang as $key => $value) 
        {
            if(is_array($value))
            {
            	//save other array ti individual 
                if($key!='other')
                    $content .= "\t'".$key."'=>[\n"; 
                foreach ($value as $sub_key => $sub_value) {
                    $content .= "\t'".$sub_key."' => '".str_replace("'", "\'", $sub_value)."',\n";
                }
                if($key!='other')
                    $content .= "],\n";
            }else{
                $content .= "\t'".$key."' => '".str_replace("'", "\'", $value)."',\n";
            }
        }
        $content .= "];";

        file_put_contents($path, $content);
    }



    public function get_language_data($lang='en',$file='messages.php') 
    {
        // dd($file);
        // Process and prepare you data as you like.
        $this->lang = $lang;
        $this->file = $file;
        // END - Process and prepare your data
        $this->read();
        return $this->arrayLang;
    }

    public function get_locale(Request $request) 
    {
		// Process and prepare you data as you like.
        $this->lang = $request->lang ?? 'en';
        $this->file = 'messages.php';
		// END - Process and prepare your data
        $this->read();
        $language = $this->arrayLang;
        $select_lang = $this->lang;
        $all_lanuage = Language::active()->pluck('name','value');

        return view('admin.language.change_lanuage',compact('language','all_lanuage','select_lang'));
    }

  

    public function update_locale(Request $request) 
    {
        // Process and prepare you data as you like.
        $this->lang = $request->lanuage ?? 'en';
        $this->file = 'messages.php';
        $this->arrayLang = $request->data;
        // END - Process and prepare your data
        $this->save();
        $helper = new App\Http\Start\Helpers;
        $helper->flash_message('success', 'Language update Successfully');
        return redirect()->route('language.locale',['lang'=>$this->lang]);
    }



    public function getLang(Request $request) 
    {
        // Process and prepare you data as you like.
        $this->lang = $request->lang ?? 'en';
        $this->file = 'messages.php';
      
        // END - Process and prepare your data
        $language = $this->arrayLang;
        $select_lang = $this->lang;
        $all_lanuage = Language::active()->pluck('name','value');
        foreach ($all_lanuage as  $key => $value) {
            $lang_data[$key] = $this->get_lang_data($key);
        }
        
        return view('admin/language/api_language',compact('lang_data','all_lanuage','select_lang'));
    }

	//update content in lanaguage file 
    public function update_language(Request $request) 
    {   
        $this->lang = $request->lanuage ?? 'en';

        if($request->file == 'api_driver_lang')
        {
            $this->file = 'driver_api_language.php';
        }
        else if($request->file == 'api_user_lang')
        {
            $this->file = 'user_api_language.php';
        }
        else if($request->file == 'api_store_lang')
        {
            $this->file = 'store_api_language.php';
        }
        else if($request->file == 'validation_message')  
        {
            $this->file = 'js_messages.php';
            
        }   
        else
        {
            $this->file = 'messages.php';
        }
        $this->read();
        $available = is_dir(base_path().'/resources/lang/'.$this->lang);
        if($available) {
            $this->arrayLang[$request->main_key][$request->sub_key] = $request->messages;
        }
        else
        {
            $path = base_path().'/resources/lang/'.$this->lang;
            if(is_writable($path))
            {
                if($result)
                {
                    $old_file = umask(0);
                    $path =chmod( base_path().'/resources/'.$this->lang.'/message.php',0777);
                    umask($old_file);
                    if ($old != umask()) {
                        logger('An error occurred while changing back the umask');
                    }
                    $content = "<?php\n\nreturn\n[\n";
                    $content .= "];";
                    file_put_contents($path, $content);
                    if (file_put_contents($path, $content) !== false) {
                        logger("File created (" . basename($path) . ")");
                    } else {
                        logger( "Cannot create file (" . basename($path) . ")");
                    }
                }
            }
        }    
        $this->arrayLang[$request->main_key][$request->sub_key] = $request->messages;
        $this->save();
        $data['convert_message'] = $this->arrayLang[$request->main_key][$request->sub_key] ; 
        $data['key_value'] = $request->lanuage;
        $data['sub_key'] = $request->sub_key;
        \Artisan::call('lang:js');  
        return response()->json(['success' => true,'converted_message'=>$data]);
    }


    public function getUserApiLanguage(Request $request) 
    {
        // Process and prepare you data as you like.
        $this->lang = $request->lang ?? 'en';
        $this->file = 'user_api_language';
        // END - Process and prepare your data
        $language = $this->arrayLang;
        $select_lang = $this->lang;
        $all_lanuage = Language::active()->pluck('name','value');
        foreach ($all_lanuage as  $key => $value) {
            $lang_data[$key] = $this->get_lang_data($key,$this->file);
        }
        
        return view('admin/language/user_api_language',compact('lang_data','all_lanuage','select_lang'));
    }

    public function getDriverApiLanguage(Request $request) 
    {
        // Process and prepare you data as you like.
        $this->lang = $request->lang ?? 'en';
        $this->file = 'driver_api_language';
        // END - Process and prepare your data
        $language = $this->arrayLang;
        $select_lang = $this->lang;
        $all_lanuage = Language::active()->pluck('name','value');
        foreach ($all_lanuage as  $key => $value) {
            $lang_data[$key] = $this->get_lang_data($key,$this->file);
        }
        return view('admin/language/driver_api_language',compact('lang_data','all_lanuage','select_lang'));
    }

    public function getStoreApiLanguage(Request $request) 
    {
        // Process and prepare you data as you like.
        $this->lang = $request->lang ?? 'en';
        $this->file = 'store_api_language';
        // END - Process and prepare your data
        $language = $this->arrayLang;
        $select_lang = $this->lang;
        $all_lanuage = Language::active()->pluck('name','value');
        foreach ($all_lanuage as  $key => $value) {
            $lang_data[$key] = $this->get_lang_data($key,$this->file);
        }
        return view('admin/language/store_api_language',compact('lang_data','all_lanuage','select_lang'));
    }

    public function getValidationLanguage(Request $request) 
    {
        // Process and prepare you data as you like.
        $this->lang = $request->lang ?? 'en';
        $this->file = 'js_messages';
        // END - Process and prepare your data
        $language = $this->arrayLang;
        $select_lang = $this->lang;
        $all_lanuage = Language::active()->pluck('name','value');
        foreach ($all_lanuage as  $key => $value) {
            $lang_data[$key] = $this->get_lang_data($key,$this->file);
        }
        return view('admin/language/api_language',compact('lang_data','all_lanuage','select_lang'));
    }


}

