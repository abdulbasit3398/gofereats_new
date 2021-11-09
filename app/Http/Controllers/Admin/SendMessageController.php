<?php

/**
 * Send Message Controller
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
use App\Models\User;
use Validator;
use App\Mail\CustomEmail;
use App\Mail\PayoutInfoMail;

class SendMessageController extends Controller
{
	public function index(Request $request)
    {   
        $users_list = User::select('id','name','email','mobile_number','type')->where('status',1)->paginate(200);
        $data['users_list'] = $users_list->map(function($user){
                        return[
                            'id'            => $user->id,
                            'name'          => $user->name,
                            'email'         => $user->email,
                            'phone_number'  => $user->phone_number,
                            'type_text'     => $user->type_text,
                        ];
                     })->toArray();
        $data['last_page']  = $users_list->lastPage();
    	return view('admin.send_message',$data);
    }

    public function userList(Request $request)
    {   
        $users_list = User::select('id','name','email','mobile_number','type')->where('status',1)->paginate(200);
        $data['users_list'] = $users_list->map(function($user){
                        return[
                            'id'            => $user->id,
                            'name'          => $user->name,
                            'email'         => $user->email,
                            'phone_number'  => $user->phone_number,
                            'type_text'     => $user->type_text,
                        ];
                     })->toArray();
        return $data;
    }

    public function sendMessage(Request $request)
    {
    	// Send Email Validation Rules
        $rules = array(
            'message_type' => 'required|in:email,push_notification,sms',
            'to'        => 'required|in:to_all,to_specific,to_type',
            'subject' => 'required_unless:message_type,push_notification'
        );

        if($request->message_type == 'email') {
            $rules['message'] = 'required';
        }
        else {
            $rules['push_message'] = 'required';
        }

        if($request->to == 'to_type') {
            $rules['user_type'] = 'required';
        }
        if($request->to == 'to_specific') {
            $rules['email'] = 'required';
        }

        // Send Email Validation Custom Names
        $attributes = array(
            'subject' => 'Subject',
            'message' => 'Message',
            'user_type' => 'User Type',
            'email'   => 'Email',
        );

        $validator = Validator::make($request->all(), $rules, $attributes);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        if($request->to == 'to_specific') {
        	$results = User::whereIn('id',$request->email);
        	$user_list = $results->get();
        }
        else {
            if($request->to == 'to_type') {
                $user_list = User::where('type',$request->user_type)->where('status',1)->get();
            }
            else {
                $user_list = User::where('status',1)->get();
            }
        }

        // dd($request->all());

        $send_message_fun = snakeToCamel('send_'.$request->message_type);
        $message = $request->message_type == 'email' ? $request->message : $request->push_message;

        $message_data = [
            'message' => $message,
            'subject' => $request->subject,
        ];

        $this->$send_message_fun($user_list,$message_data);

		return redirect()->route('admin.send_message');
    }

    protected function sendEmail($user_list,$message_data)
    {
        try {
            $user_list->each(function($user) use ($message_data) {
                $data['first_name'] = $user->name;
                $data['content']    = str_replace("&nbsp;", "", $message_data['message']);
                $data['content']    = $data['content'];
                $data['subject']    = str_replace("&nbsp;", "", $message_data['subject']);
                try {
                    \Mail::to($user->email,$user->name)->queue(new CustomEmail($data));
                } catch (\Exception $e) {
                    flash_message("danger",$e->getMessage());
                }
            });
            flash_message('success', "Email Sent Successfully");
        }
        catch(\Exception $e) {
            flash_message('danger', "Failed to send email");
        }
    }

    protected function sendPushNotification($user_list,$message_data)
    {
        $title = 'Message from '.site_setting('site_name');
        $message_data['push_data'] = [
            'type' => 'custom_message',
            'message' => $message_data['message'],
        ];
        $user_list->each(function($user) use ($message_data,$title) {
            push_notification($user->device_type, $title, $message_data['push_data'], $user->type, $user->device_id);
        });
        flash_message('success', "Push notification Sent Successfully");
    }

    protected function sendSms($user_list,$message_data)
    {
        $message = $message_data['subject'].' : '.$message_data['message'];

        $user_list->each(function($user) use ($message) {
            $phone_number = $user->country_code.$user->mobile_number;
            if(!canDisplayCredentials()){
                sendOtp($phone_number, $message);
            } 
        });
        flash_message('success', "Sms Sent Successfully");
    }

    public function need_payout_info(Request $request)
    {
        $user = User::find($request->user_id);
        $data['user_name'] = $user->name;
        $data['subject']   = "Information Needed: It's time to get paid!";
        $data['url'] = url('/').'/';
        $data['type'] = $request->type;
        try {
            \Mail::to($user->email, $user->name)->queue(new PayoutInfoMail($data));
        } catch (\Exception $e) {
            flash_message("danger",$e->getMessage());
        }
        flash_message('success', "Payout Info Email Sent Successfully");
        return back();
    }
}