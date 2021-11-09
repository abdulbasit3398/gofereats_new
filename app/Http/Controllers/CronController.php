<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use App\Models\Payout;

class CronController extends Controller {

    private function exchangeRate($to,$to_name) {
        $googleQuery = '1 USD '.$to;
        $googleQuery = urlEncode($googleQuery);
        $ch = curl_init();
        $timeout = 5; // set to zero for no timeout
        curl_setopt($ch, CURLOPT_URL, 'http://www.google.com/search?q='.$googleQuery);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        $matches = array();

        preg_match('/(([0-9]|\.|,|\ )*) '.$to_name.'/', $file_contents, $matches);
        return isset($matches[1]) ? $matches[1] : false;
    }
    
    public function currency_cron() {
       // Get all currencies from Currency table
        $result = Currency::all();  
        // Update Currency rate by using Code as where condition
        foreach($result as $row) {
            $rate = 0;
            if($row->code != 'USD') {
                \Log::info("cron checking".$row->code);
                $name = explode(' ',$row->name)[0];
                $rate = $this->exchangeRate($row->code,$name);
                // \Log::info($rate);
            } else {
                $rate = 1;
            }
            if($rate!=0) {
                Currency::where('code',$row->code)->update(['rate' => $rate]);
            }
        } 
    }

    /** 
    * Update Paypal Payout Status
    * 
    **/
    public function updatePaypalPayouts()
    {
        $pending_payouts = Payout::where('status',2)->get();
        if($pending_payouts->count() == 0) {
            return response()->json(['status' => false, 'status_message' => 'No Pending Payouts found']);
        }
        
        $paypal_payout = resolve('App\Repositories\StripePayout');
        $pending_payouts->each(function($pending_payout) use($paypal_payout) {
            $batch_id = $pending_payout->transaction_id;
            $payment_data = $paypal_payout->fetchPayoutViaBatchId($batch_id);
            if($payment_data['status']) {
                $payout_data = $paypal_payout->getPayoutStatus($payment_data['data']);

                if($payout_data['status']) {
                    if($payout_data['payout_status'] == 'SUCCESS') {
                       $pending_payout->status = 1;
                            $pending_payout->transaction_id = $payout_data['transaction_id'];
                    }

                    if(in_array($payout_data['payout_status'], ['FAILED','RETURNED','BLOCKED'])) {
                        $pending_payout->status = 2;
                    }

                    $pending_payout->save();
                }
            }
        });
        return response()->json(['status' => true, 'status_message' => 'updated successfully']);
    }
}
