<?php 

/**
 * PaymentProcess Trait
 *
 * @package     GoferEats
 * @subpackage  PaymentProcess Trait
 * @category    PaymentProcess
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Traits;

use App\Models\Wallet;
use App\Models\Payout;
use App\Models\PayoutPreference;

trait PaymentProcess
{
	/**
	 * Payout yo user
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function admin_payout_to_user($user_id,$order_id)
	{
		$payout = Payout::with('user.payout_preference')->where('user_id', $user_id)->where('order_id', $order_id)->first();
		$payout_preference = PayoutPreference::where('user_id', $payout->user->id)->where('default','yes')->first();
		$payout_data = array();

        if($payout_preference->payout_method == 'Paypal') {
            $payout_currency = PAYPAL_CURRENCY_CODE;
            $amount = floatval($payout->amount);
            $convert_amount = currencyConvert(DEFAULT_CURRENCY, $payout_currency, $amount);
            $receiver = $payout->user->payout_id;
            $data = [
                'sender_batch_header' => [
                    'email_subject' => urlencode('PayPal Payment'),    
                ],
                'items' => [
                    [
                        'recipient_type' => "EMAIL",
                        'amount' => [
                            'value' => "$convert_amount",
                            'currency' => "$payout_currency"
                        ],
                        'receiver' => "$receiver",
                        'note' => 'payment of commissions',
                    ],
                ],
            ];
            $payout_data = json_encode($data);
        }

        if($payout_preference->payout_method == 'Stripe') {
            $payout_data['currency'] = currency_symbol();
            $payout_data['amount'] = floatval($payout->amount);
        }

        $data = $this->payout_to_users($payout_data, $payout->user->payout_id ,$payout_preference->payout_method);
        

		if ($data['success'] == true) {
			$payout->status = 1;
			$payout->transaction_id = $data['transaction_id'];
			$payout->save();
			$response['success'] = true;
			$response['message'] = trans('admin_messages.updated_successfully');
		}
		else {
			$response['success'] = false;
			$response['message'] = $data['message'];
		}

		return $response;
	}

	public function payout_to_users($payout_data,$payout_account,$method)
	{

		if($method =="BankTransfer"){
			return array(
	            'success' => true,
	            'transaction_id' => '',
	        );
		}
		
		$stripe_payout = resolve('App\Repositories\StripePayout');
		if($method == 'Stripe'){
			
			$pay_data = array(
				"amount" => $payout_data['amount'] * 100,
	            "currency" => $payout_data['currency'],
	            "destination" => $payout_account,
				"transfer_group"=> "ORDER_95",
			);

		$transfer = $stripe_payout->makeTransfer($pay_data);
		
		if(!$transfer['status']) {
			return array(
                'success' => false,
                'message' => $transfer['status_message'],
            );
		}

			$pay_data = array(
				"amount" 	=> $payout_data['amount'] * 100,
				"currency" 	=> $payout_data['currency'],
			);

		$payout = $stripe_payout->makePayout($payout_account,$pay_data);

		if(!$payout['status']) {
			return array(
                'success' => false,
                'message' => $payout['status_message'],
            );
		}

			return array(
	            'success' => true,
	            'transaction_id' => $payout['transaction_id'],
	        );
		}else{
			$payout = $stripe_payout->makePaypalPayout($payout_data);
			if(!$payout['status']) {
				return array(
	                'success' => false,
	                'message' => $payout['status_message'],
	            );
			}
			return array(
	            'success' => true,
	            'is_pending' => true,
	            'transaction_id' => $payout['transaction_id'],
	        );
		}
	}

	public function refund_to_users($amount, $transaction_id)
	{
		$stripe_payment = resolve('App\Repositories\StripePayment');
		$amount = $amount * 100;
		
		$refund = $stripe_payment->refundPayment($transaction_id,$amount);
		
		if ($refund->status == 'success') {
			$data['success'] = true;
			$data['message'] = true;
			$data['transaction_id'] = $refund->intent_id;
		}
		else {
			$data['success'] = false;
			$data['message'] = $refund->status_message;
		}
		return $data;
	}

	public function refund_to_wallet($user_id, $amount)
	{
		$wallet = Wallet::where('user_id', $user_id)->first();

		if ($wallet == '') {
			$wallet = new Wallet;
		}
		$wallet->user_id = $wallet->user_id;
		$wallet->amount = $wallet->amount + $amount;
		$wallet->save();
	}
}