<?php

/**
 * Auth Via Google
 *
 * @package     Gofer
 * @subpackage  Services
 * @category    Auth Service
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
*/

namespace App\Services\SMS;

use Illuminate\Http\Request;
use App\Contracts\SMSInterface;

class TwillioSms implements SMSInterface
{
	private $base_url,$token,$sid;

	public function initialize()
	{
		$this->sid    = TWILLO_SID;
		$this->token  = TWILLO_TOKEN;
		$this->from 	= TWILLO_FROM;
		$this->base_url = "https://api.twilio.com/2010-04-01/Accounts/".$this->sid."/SMS/Messages.json";
	}

	protected function sendMessage($data)
	{
		$postData = http_build_query($data);
		$ch = curl_init($this->base_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$this->sid:$this->token");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		$result = curl_exec($ch);

		return json_decode($result,true);
	}

	protected function getResponse($result)
	{
		if(canDisplayCredentials()) {
			return [
				'status' => 'Success',
				'message' => 'Success'
			];
		}
		if ($result['status'] != 'queued') {
			$response = [
				'status' => 'Failed',
				'message' => $result['message']
			];
		}
		else {
			$response = [
				'status' => 'Success',
				'message' => 'Success'
			];
		}

		return $response;
	}

	public function send($to, $text)
	{
		$this->initialize();
		$data = array(
			"Body" => $text,
			"From" => $this->from,
			"To"=> $to
		);

		$result = $this->sendMessage($data);

		return $this->getResponse($result);
	}
}