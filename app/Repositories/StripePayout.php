<?php

/**
 * Stripe Payout Repository
 *
 * @package     GoferEats
 * @subpackage  Repositories
 * @category    Stripe
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
*/

namespace App\Repositories;

use App\Models\Country;
use App\Models\PayoutPreference;

class StripePayout
{
	/**
     * Intialize Stripe with Secret key
     *
     */	
    public function __construct()
    {
    	$stripe_key = site_setting('stripe_secret_key');
		$api_version = site_setting('stripe_api_version');
		\Stripe\Stripe::setApiKey($stripe_key);
		\Stripe\Stripe::setApiVersion($api_version);

        $paymode  = site_setting('paypal_mode');
        $environment = ($paymode == 'sandbox') ? 'sandbox' : '';
        $this->base_url = "https://api.$environment.paypal.com/v1/";

    }

    protected function getCurrentUser()
    {
        if(isApiRequest()) {
            $user = \JWTAuth::parseToken()->authenticate();
        }
        else {
            $user = auth()->guard('restaurant')->user();
        }

        return $user;
    }

    public function validateRequest($request)
    {
    	$country = $request->payout_country ?? '';
        $user = $this->getCurrentUser();

    	if ($country != 'OT') {
			$country_data = Country::where('code', $country)->first();
            $status_code = "1";
			if (!$country_data) {
                $status_code = "0";
                $status_message = trans('messages.store.service_not_available');
			}

    		// if (!isset($user->dob_array[2]) && $user->type != 2) {
             if (!isset($user->dob_array[2])) {
                $status_code = "0";
                $status_message = trans('messages.store_dashboard.please_complete_profile_step_then_add_payout');
            }
            if ($user->dob_array[0] =='') {
                $status_code = "0";
                $status_message = trans('messages.store_dashboard.please_complete_profile_step_then_add_payout');
            }



            if($status_code == "0") {
                if(isApiRequest()) {
                    return response()->json(compact('status_code','status_message'));
                }

                flash_message('danger', $status_message);
                return back();
            }
		}

    	$rules = array(
            'payout_country'=> 'required',
			'account_number'=> 'required',
			'address1' 		=> 'required',
			'city' 			=> 'required',
			'postal_code' 	=> 'required',
			'document' 		=> 'mimes:png,jpeg,jpg',
            'additional_document' => 'mimes:png,jpeg,jpg',
            'phone_number' => 'required',
        );

		$payout_preference = PayoutPreference::where('user_id', $user->id)->first();

        if($country == 'JP') {
			$rules['bank_name'] = 'required';
			$rules['branch_name'] = 'required';
			$rules['address1'] = 'required';
			$rules['kanji_address1'] = 'required';
			$rules['kanji_city'] = 'required';
			$rules['kanji_state'] = 'required';
			$rules['kanji_postal_code'] = 'required';
			if (!$user->gender) {
				$rules['gender'] = 'required|in:male,female';
			}
        }

		if ($country != 'OT' && !isApiRequest()) {
			$rules['stripe_token'] = 'required';
			if($payout_preference == '' || ($payout_preference != '' && $payout_preference->document_image == '')) {
				$rules['document'] = 'required|mimes:png,jpeg,jpg';
			}

            if($payout_preference == '' || ($payout_preference != '' && $payout_preference->additional_document == '')) {
                $rules['additional_document'] = 'required|mimes:png,jpeg,jpg';
            }
		}
        if ($country != 'OT' && isApiRequest()) {
            $rules['document'] = 'required|mimes:png,jpeg,jpg';
            $rules['additional_document'] = 'required|mimes:png,jpeg,jpg';
        }

		$attributes = array(
			'payout_country' 	=> trans('messages.profile.country'),
			'currency' 			=> trans('messages.store.currency'),
			'routing_number' 	=> trans('messages.store_dashboard.routing_number'),
			'account_number'	=> trans('messages.store.account_number'),
			'holder_name' 		=> trans('messages.store_dashboard.holder_name'),
			'additional_owners' => trans('messages.store_dashboard.additional_owners'),
			'business_name' 	=> trans('messages.store_dashboard.business_name'),
			'business_tax_id' 	=> trans('messages.store_dashboard.business_tax_id'),
			'holder_type' 		=> trans('messages.store_dashboard.holder_type'),
			'stripe_token' 		=> trans('messages.store_dashboard.stripe_token'),
			'address1' 			=> trans('messages.driver.address'),
			'city' 				=> trans('messages.driver.city'),
			'state' 			=> trans('admin_messages.state'),
			'postal_code' 		=> trans('messages.profile.postal_code'),
			'document' 			=> trans('admin_messages.document'),
            'additional_document'=> trans('admin_messages.additional_document'),
		);

		if ($country == 'OT') {
			$attributes['routing_number'] = trans('messages.store.holder_name');
		}
		$messages = array(
			'required' => ':attribute '.trans('messages.driver.is_required'),
			'mimes' => trans('validation.mimes', ['attribute' => trans('admin_messages.document'),'values' => "png,jpeg,jpg"]),
		);
		$validator = \Validator::make($request->all(), $rules, $messages,$attributes);

		if ($validator->fails()) {
            if(isApiRequest()) {
                return response()->json([
                    'status_code' => '0',
                    'status_message' => $validator->messages()->first(),
                ]);
            }
			return back()->withErrors($validator)->withInput();
		}
		return false;
    }

    protected function getVerificationData($request)
    {
        $user = $this->getCurrentUser();
    	$account_holder_type = 'individual';
    	$country = $request->payout_country;

    	if($country  == 'JP') {
            $address_kana = array(
                'line1'       	=> $request->address1,
                'town'         	=> $request->address2,
                'city'          => $request->city,
                'state'         => $request->state,
                'postal_code'   => $request->postal_code,
                'country'      => $country,
            );
            $address_kanji = array(
                'line1'         => $request->kanji_address1,
                'town'         	=> $request->kanji_address2,
                'city'          => $request->kanji_city,
                'state'         => $request->kanji_state,
                'postal_code'   => $request->kanji_postal_code,
                'country'       => $country,
            );
            $individual = array(
                "first_name_kana" 	=> $request->account_holder_name,
                "last_name_kana" 	=> $request->account_holder_name,
                "first_name_kanji"	=> $request->account_owner_name,
                "last_name_kanji" 	=> $request->account_owner_name,
                "dob" => array(
                    "day"   => $user->dob_array[2] ?? "15",
                    "month" => $user->dob_array[1] ?? "04",
                    "year"  => (isset($user->dob_array[0]) && $user->dob_array[0] != '') ? $user->dob_array[0] : "1996",
                ),
                "first_name"    => $user->first_name,
                "last_name"     => $user->last_name,
                "phone"         => $request->phone_number,
                "email"         => $user->email,
                "gender"        => $request->gender ?? $user->gender ?? 'Male',
                "address" => array(
                    "line1" 	=> $request->address1,
                    "line2" 	=> $request->address2 ?? null,
                    "city" 		=> $request->city,
                    "country" 	=> $country,
                    "state" 	=> $request->state ?? null,
                    "postal_code" => $request->postal_code,
                ),
                "address_kana" 	=> $address_kana,
                "address_kanji" => $address_kanji,
            );
        }
        else {
        	$individual = [ 
                "address" => array(
                    "line1" 	=> $request->address1,
                    "line2"     => $request->address2 ?? null,
                    "city" 		=> $request->city,
                    "postal_code"=> $request->postal_code,
                    "state" 	=> strtoupper($request->state)
                ),
                "dob" => array(
                    "day" 	=> $user->dob_array[2] ?? "15",
                    "month" => $user->dob_array[1] ?? "04",
                    "year" 	=> (isset($user->dob_array[0]) && $user->dob_array[0] != '') ? $user->dob_array[0] : "1996",
                ),
                "first_name" 	=> $user->first_name,
                "last_name" 	=> $user->last_name,
                "phone" 		=> $request->phone_number,
                "email"			=> $user->email,
            ];

            if($country == 'US') {
                $individual['ssn_last_4'] = $request->ssn_last_4;
            }

            if(in_array($country,['SG','CA','IN'])) {
                $individual['id_number'] =  $request->personal_id;
            }
        }

        $capability_countries = ['US','AU','AT','BE','CZ','DK','EE','FI','FR','DE','GR','IE','IT','LV','LT','LU','NL','NZ','NO','PL','PT','SK','SI','ES','SE','CH','GB','IN'];
        
        $url = url('/');
        if(strpos($url, "localhost") > 0) {
        	$url = 'http://gofereats.trioangle.com';
        }
        $verification = array(
            "country"       => $country,
            "business_type" => "individual",
            "business_profile" => array(
                'mcc' => 5814,
                'url' => $url,
            ),
            "tos_acceptance"=> array(
                "date"  => time(),
                "ip"    => $_SERVER['REMOTE_ADDR']
            ),
            "type"          => "custom",
            "individual"    => $individual,
        );
        
        if(in_array($country, $capability_countries)) {
            $verification["requested_capabilities"] = ["transfers","card_payments"];
        }

        return $verification;
    }

    protected function createStripeAccount($verification)
    {

    	try {
	    	$recipient = \Stripe\Account::create($verification);
	    	return array(
	    		'status' => true,
	    		'recipient' => $recipient,
	    	);
	    }
	    catch(\Exception $e) {
        	return array(
        		'status' => false,
        		'status_message' => $e->getMessage(),
        	);
        }
    }

    public function uploadAdditonalDocument($stripe_document,$a_document_path,$recipient_id,$personal_id){
        try {
            
            $additonal_stripe_file = \Stripe\File::create(
                array(
                    "purpose"   => "identity_document",
                    "file"      => fopen($a_document_path, 'r')
                ),
                array('stripe_account' => $recipient_id)
            );

            $stripe_addtional_document = $additonal_stripe_file->id;
            $new_stripe_document = ($stripe_document=='') ? $stripe_addtional_document : $stripe_document;

             $stripe_document_update = \Stripe\Account::updatePerson($recipient_id,$personal_id,
                  array('verification' => array(
                        'document' =>array('front' => $new_stripe_document),
                        'additional_document'=>array('front' => $stripe_addtional_document)
                            )
                        )
            );

            return array(
                'status' => true,
                'status_message' => 'document uploaded',
            );

        } catch(\Exception $e) {
            return array(
                'status' => false,
                'status_message' => $e->getMessage(),
            );
        }
    }

    public function uploadDocument($document_path,$recipient_id){

        try {
            $stripe_file = \Stripe\File::create(
            	array(
                	"purpose" 	=> "identity_document",
                	"file" 		=> fopen($document_path, 'r')
              	),
              	array('stripe_account' => $recipient_id)
            );
            $stripe_document = $stripe_file->id;
            return array(
	    		'status'			=> true,
	    		'status_message' 	=> 'document uploaded',
	    		'stripe_document' 	=> $stripe_document,
	    	);
        }
        catch(\Exception $e) {
        	return array(
        		'status' => false,
        		'status_message' => $e->getMessage(),
        	);
        }
    }

    public function createStripeToken($bank_account)
    {
        try {
            $stripe_token = \Stripe\Token::create(
                array("bank_account" => $bank_account)
            );
            return [
                'status' => true,
                'token'  => $stripe_token,
            ];
        }
        catch(\Exception $e) {
            return [
                'status'         => false,
                'status_message' => $e->getMessage(),
            ];
        }        
    }

    public function createPayoutPreference($request)
    {
    	$verification = $this->getVerificationData($request);
    	$recipient_data = $this->createStripeAccount($verification);
    	if(!$recipient_data['status']) {
    		return array(
	    		'status' => false,
	    		'status_message' => $recipient_data['status_message'],
	    	);
    	}
    	$recipient = $recipient_data['recipient'];
        $user = $this->getCurrentUser();
    	$recipient->email = $user->email;
        try {
            $recipient->external_accounts->create(
            	array("external_account" => $request->stripe_token)
            );
        }
        catch(\Exception $e) {
        	return array(
	    		'status' => false,
	    		'status_message' => $e->getMessage(),
	    	);
        }
        
        $recipient->save();
        
    	return array(
    		'status'			=> true,
    		'recipient' 		=> $recipient,
    	);
    }

    public function makeTransfer($pay_data)
    {
        try {
            $response = \Stripe\Transfer::create($pay_data);
        }
        catch (\Exception $e) {
            return array(
                'status' => false,
                'status_message' => $e->getMessage(),
            );
        }
        return array('status' => true);
    }

    public function makePayout($payout_account,$pay_data)
    {
        try {
            $response = \Stripe\Payout::create(
                $pay_data,
                array("stripe_account" => $payout_account)
            );
        }
        catch (\Exception $e) {
            return array(
                'status' => false,
                'status_message' => $e->getMessage(),
            );
        }
        return array('status' => true,'transaction_id' => $response->id);
    }

    public function makePaypalPayout($pay_data)
    {
        try {
            $authorization = $this->getAuthorizationHeader();

            if(!$authorization['status']) {
                return array(
                    'status' => false,
                    'status_message' => $authorization['status_message'],
                );
            }
            $batch_response = $this->sendBatchRequest($pay_data,$authorization['access_token']);
            
            if(!$batch_response['status']) {
                return array(
                    'status' => false,
                    'status_message' => $batch_response['status_message'],
                );
            }

            $payout_response = $batch_response['data'];
            if(@$payout_response->batch_header->batch_status == "PENDING") {
                $payout_batch_id = $payout_response->batch_header->payout_batch_id;
                
                $payout_data = $this->fetchPayoutViaBatchId($payout_batch_id,$authorization['access_token']);
                if(!$payout_data['status']) {
                    return array(
                        'status' => false,
                        'status_message' => $payout_data['status_message'],
                    );
                }

                return array(
                    'status' => true,
                    'is_pending' => true,
                    'transaction_id' => $payout_batch_id,
                    'status_message' => "Payout Process initiated",
                );
            }

            return array(
                'status' => false,
                'status_message' => $payout_response->name,
            );
        }
        catch (\Exception $e) {
            return array(
                'status' => false,
                'status_message' => $e->getMessage(),
            );
        }
    }

    protected function getAuthorizationHeader()
    {
        $client  = site_setting('paypal_client');
        $secret  = site_setting('paypal_secret');

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->base_url."oauth2/token");
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($curl, CURLOPT_USERPWD, $client.":".$secret);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $result = curl_exec($curl);
        $response = json_decode($result);
        curl_close($curl);
        
        if(isset($response->error)) {
            return array('status' => false,"status_message" => $response->error_description);
        }
        return array('status' => true, "access_token" => $response->access_token);
    }

    public function fetchPayoutViaBatchId($batch_id, $access_token = '')
    {
        if($access_token == '') {
            $authorization = $this->getAuthorizationHeader();
            if(!$authorization['status']) {
                return array(
                    'status' => false,
                    'status_message' => $authorization['status_message'],
                );
            }
            $access_token = $authorization['access_token'];
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->base_url."payments/payouts/$batch_id");
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Authorization: Bearer ".$access_token,""));

        $result = curl_exec($curl);
        $response = json_decode($result);
        curl_close($curl);

        if(isset($response->error)) {
            return array('status' => false,"status_message" => $response->error_description);
        }

        return array('status' => true, "data" => $response);
    }

    public function fetchPayoutViaItemId($item_id, $access_token = '')
    {
        if($access_token == '') {
            $authorization = $this->getAuthorizationHeader();
            if(!$authorization['status']) {
                return array(
                    'status' => false,
                    'status_message' => $authorization['status_message'],
                );
            }
            $access_token = $authorization['access_token'];
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->base_url."payments/payouts-item/$item_id");
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Authorization: Bearer ".$access_token,""));

        $result = curl_exec($curl);
        $response = json_decode($result);
        curl_close($curl);

        if(isset($response->error)) {
            return array('status' => false,"status_message" => $response->error_description);
        }

        return array('status' => true, "data" => $response);
    }

    protected function sendBatchRequest($pay_data,$access_token)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->base_url."payments/payouts");
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $pay_data); 
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Authorization: Bearer ".$access_token,""));

        $result = curl_exec($curl);
        $response = json_decode($result);
        curl_close($curl);

        if(isset($response->error)) {
            return array('status' => false,"status_message" => $response->error_description);
        }

        return array('status' => true, "data" => $response);
    }

    public function getPayoutStatus($payout_data)
    {
        if(!isset($payout_data->items[0])) {
            return array('status' => false, "status_message" => "Requested Payment Not Found");
        }

        return array(
            'status' => true,
            'payout_status' => $payout_data->items[0]->transaction_status,
            'transaction_id' => $payout_data->items[0]->transaction_id,
            'status_message' => "Payout Processed",
        );
    }
}
