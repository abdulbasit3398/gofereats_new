<?php

/**
 * Firebase Service
 *
 * @package     Gofer
 * @subpackage  Services
 * @category    Service
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
*/

namespace App\Services;

use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Kreait\Firebase\ServiceAccount;
use app\models\SiteSettings;

class FirebaseService
{
	/**
	 * Constructor
	 * 
	 */
	
	public function __construct()
	{
		$service_account = SiteSettings::where('name','service_account')->value('value');
		$database_url = SiteSettings::where('name','database_url')->value('value');
		$filePath = base_path().$service_account;
		try {
			$serviceAccount = ServiceAccount::fromValue($filePath);
	        $this->firebase = (new Factory())->withServiceAccount($serviceAccount)->withDatabaseUri($database_url);
	        $this->database = $this->firebase->createDatabase(); 
		}
		catch(\Exception $e) {
			$this->firebase = $this->database = null;
		}
	}

	/**
	 * get Database With Reference
	 *
	 * @param String $[reference] [reference path]
	 * @return reference
	 */
	public function getDatabaseWithReference($reference)
    {
    	if(!isset($this->database)) {
    		return null;
    	}
    	$base_path = env('FIREBASE_PREFIX','live').'/';
    	// dd($this->database->getReference($base_path.$reference));
		return $this->database->getReference($base_path.$reference);

	}

	/**
	 * Update Reference in Database
	 *
	 * @param String $[reference] [reference path]
	 * @param Json $[data]
	 * @return reference
	 */
	public function updateReference($reference,$data)
    {
    	$reference = $this->getDatabaseWithReference($reference);
    	if(!isset($reference)) {
    		return null;
    	}
    	// dd($reference->set($data));
		return $reference->set($data);
	}

	/**
	 * Delete Reference from Database
	 *
	 * @param String $[reference] [reference path]
	 * @return reference
	 */
	public function deleteReference($reference)
    {
    	$reference = $this->getDatabaseWithReference($reference);
    	if(!isset($reference)) {
    		return null;
    	}
		return $reference->set(null);
	}

	/**
	 * Create Custom Token
	 *
	 * @param User unique id
	 * @return token
	 */
	public function createCustomToken($user_id) {
        $auth =  $this->firebase->createAuth();
        $customToken = $auth->createCustomToken($user_id);
        $customTokenString = (string) $customToken;
        return $customTokenString;
	}
}