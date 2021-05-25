<?php
/* Copyright Sikkema Software B.V. 2020. 
*  The copyright holder is cannot be held liable for any damages
*  caused by this program or for it's proper functioning.
*  Copying or modifying the code is not permitted without express
*  written consent from the copyright holder.
*/
require_once("class.singleton.php");

class ByWireUser extends Singleton {
    const USERNAME_KEY       = ByWire::ENV.'-username-key';
    const PASSWORD_KEY       = ByWire::ENV.'-password-key';
    const API_KEY            = ByWire::ENV.'-api-key';
    const ACCEPT_TERMS_KEY   = ByWire::ENV.'-accept-terms-key';

    const ACCESS_TOKEN_KEY   = ByWire::ENV.'-access-token-key';
    const ACCESS_EXPIRY_KEY  = ByWire::ENV.'-access-expiry-key';
    const REFRESH_TOKEN_KEY  = ByWire::ENV.'-refresh-token-key';
    const REFRESH_EXPIRY_KEY = ByWire::ENV.'-refresh-expiry-key';
    
    const USER_CONNECTED     = ByWire::ENV.'-user-connected';
    const USER_STATUS_KEY    = ByWire::ENV.'-user-status-key';
    const USER_RESPONSE_KEY  = ByWire::ENV.'-user-response-key';
    const CONNECTION_TESTED  = ByWire::ENV.'-connection-tested';

    const STATUS_VALID               = 0;
    const STATUS_INVALID             = 1;
    const STATUS_INVALID_CREDENTIALS = 2;
    const STATUS_INVALID_TERMS       = 4;
    const STATUS_SERVER_ERROR        = 8;
    const STATUS_SERVER_TIMEOUT      = 16;
    const STATUS_SERVER_UNAVAILABLE  = 32;
    

    public $username          = "";
    public $password          = "";
    public $api_key           = "";
    public $accept_terms      = false;
    public $response          = "";
    public $connection_tested = 0;
    
    public $connected         = false;
    public $status            = -1;
    
    public $access_token      = "";
    public $access_expiry     = "";
    public $refresh_token     = "";
    public $refresh_expiry    = "";

    public function status_str() {
    	if ($this->status == ByWireUser::STATUS_VALID) {
	   if ($this->is_connected()) {
	   	   return "Connected";
           } else {
	           return "";
	   }
	}
        if ($this->status == ByWireUser::STATUS_INVALID_TERMS) {
	   return "Please Accept Terms & Conditions";
	}
	if ($this->status == ByWireUser::STATUS_INVALID_CREDENTIALS) {
	   return "Invalid Credentials";
	}
	if ($this->status == ByWireUser::STATUS_SERVER_ERROR) {
	   return "Server Error - BywirePublisher will reconnect";
	}
	if ($this->status == ByWireUser::STATUS_SERVER_TIMEOUT) {
	   return "Server Timeout - BywirePublisher will reconnect";
	}
	if ($this->status == ByWireUser::STATUS_SERVER_UNAVAILABLE) {
	   return "Server cannot be reached, please contact bywire to discuss the configuration of your firewall";
	}
	if ($this->status < 0) {
	   return "";
	}	
	return "Unknown Error - ".$this->status;
    }

    public function add_response($response) {
    	$this->response   = json_encode($response);
        $this->status     = ByWireUser::STATUS_INVALID;
    	if($response->success == 0) {
            if ($response->code == "Server Currently Unavailabe") {
                 $this->status     = ByWireUser::STATUS_SERVER_ERROR;
            } else if ($response->code == "Server Timeout") {
                 $this->status     = ByWireUser::STATUS_SERVER_TIMEOUT;
	    } else {
                 $this->status     = ByWireUser::STATUS_INVALID_CREDENTIALS;
		 $this->connected  = false;
	    }
	    $this->store();
	    return;
	}
	if ($response->access_token) {
           $this->status     = ByWireUser::STATUS_VALID;
	   $this->connected  = true;
	}
	$this->access_token  = $response->access_token;
	$this->refresh_token = $response->refresh_token;
	
	if (property_exists($response, "access-expiry")) {
	    $this->access_expiry = gmdate("Y-m-d H:i:s", strtotime($respons->access-expiry)); 
	} else {
	    $this->access_expiry = gmdate("Y-m-d H:i:s", time()+14*60); 
	}
	if (property_exists($response, "refresh-expiry")) {
	    $this->refresh_expiry = gmdate("Y-m-d H:i:s", strtotime($respons->refresh-expiry)); 
	} else {
	    $this->refresh_expiry = gmdate("Y-m-d H:i:s", time()+3*60*60); 
	}
	$this->store();
    }


    public static function from_post() {
        $instance = self::instance();
	$instance->disconnect();
	$instance->username = preg_replace( '/[^A-Za-z0-9]/i', '', $_POST['bywire-username'] );
        $instance->password = $_POST['bywire-password'];
        $instance->api_key  = preg_replace( '/[^A-Za-z0-9]/i', '', $_POST['bywire-api-key'] );
        $instance->accept_terms = (isset($_POST['bywire-accept-terms'] ) && $_POST['bywire-accept-terms'] === "on") ? true : false;
	$instance->status = (($instance->username !== "") && !($instance->accept_terms)) ? ByWireUser::STATUS_INVALID_TERMS : $instance->status;
	$instance->store();

    }

    public static function get_option($tag, $default=true) {
         return apply_filters(ByWire::ENV.$tag, get_option($tag, $default=$default));
    }

    protected function init() {
        $this->username          = $this->get_option(ByWireUser::USERNAME_KEY,       $this->username);
        $this->password          = $this->get_option(ByWireUser::PASSWORD_KEY,       $this->password);
        $this->api_key           = $this->get_option(ByWireUser::API_KEY,            $this->api_key);
        $this->accept_terms      = $this->get_option(ByWireUser::ACCEPT_TERMS_KEY,   $this->accept_terms);
        $this->access_token      = $this->get_option(ByWireUser::ACCESS_TOKEN_KEY,   $this->access_token);
        $this->access_expiry     = $this->get_option(ByWireUser::ACCESS_EXPIRY_KEY,  $this->access_expiry);
        $this->refresh_token     = $this->get_option(ByWireUser::REFRESH_TOKEN_KEY,  $this->refresh_token);
        $this->refresh_expiry    = $this->get_option(ByWireUser::REFRESH_EXPIRY_KEY, $this->refresh_expiry);
        $this->connected         = $this->get_option(ByWireUser::USER_CONNECTED,     $this->connected);
        $this->status            = $this->get_option(ByWireUser::USER_STATUS_KEY,    $this->status);
        $this->response          = $this->get_option(ByWireUser::USER_RESPONSE_KEY,  $this->response);
        $this->connection_tested = $this->get_option(ByWireUser::CONNECTION_TESTED,  $this->connection_tested);

	if ($this->connection_tested === 0) {
	   $this->test();
	}

    }

    public function test() {
    	$this->connection_tested = (ByWireAPI::test()) ? 1 : -1;
	$this->status = ($this->connection_tested > 0) ? $this->status : ByWireUser::STATUS_SERVER_UNAVAILABLE;
	$this->store();
    }


    public function connect() {
        $this->connected    = true;
    }

    public function disconnect() {
    	$this->connected        = false;
    	$this->access_token     = "";
    	$this->access_expiry    = "";
    	$this->refresh_token    = "";
    	$this->refresh_expiry   = "";
	$this->store();
    }

    public function is_registered() {
        return $this->connected;
    }

    public function is_connected() {
    	if ($this->username === "" || $this->password === "") {
	    return false;
	}
	if (!$this->connected) {
	    return false;
	}
	if ($this->expired()) {
	    ByWireAPI::login();
	}
        return $this->connected > 0;
    }

    public function expired() {
        if (empty($this->access_expiry)) {
            return True;
        }
        $current_time = strtotime(gmdate("Y-m-d H:i:s", time()));
        $expiry       = strtotime($this->access_expiry);
        return $expiry < $current_time;
    }
    
    public function changed($other) {
        if (!isset($other)) {
	   return true;
	}
    	if (gettype($other) != static::class) {
	   return true;
	}
        return (strcmp($this->username, $other->username) !== 0 ||
	       strcmp($this->password, $other->password)  !== 0 ||
	       strcmp($this->api_key, $other->api_key)    !== 0 ||
	       strcmp($this->accept_terms, $other->accept_terms) !== 0 
	       );
    }

    public function store() {
        update_option( ByWireUser::USERNAME_KEY,          $this->username);
        update_option( ByWireUser::PASSWORD_KEY,          $this->password);
        update_option( ByWireUser::API_KEY,               $this->api_key);
        update_option( ByWireUser::ACCEPT_TERMS_KEY,      $this->accept_terms);
        update_option( ByWireUser::ACCESS_TOKEN_KEY,      $this->access_token);
        update_option( ByWireUser::ACCESS_EXPIRY_KEY,     $this->access_expiry);
        update_option( ByWireUser::REFRESH_TOKEN_KEY,     $this->refresh_token);
        update_option( ByWireUser::REFRESH_EXPIRY_KEY,    $this->refresh_expiry);
        update_option( ByWireUser::USER_CONNECTED,        $this->connected);
        update_option( ByWireUser::USER_STATUS_KEY,       $this->status);
        update_option( ByWireUser::USER_RESPONSE_KEY,     $this->response);
        update_option( ByWireUser::CONNECTION_TESTED,     $this->connection_tested);
    }

    public function update($new_user) {
        $notices = array();
        if (!$this->changed($new_user)) {
	    return array();
	}
	$new_user->store();

	if (count($notices) == 0) {
	    array_push($notices, "user-update-success");
	}
	
	return $notices;
    }


    public static function deactivate()  {
        delete_option(ByWireUser::USERNAME_KEY);
        delete_option(ByWireUser::PASSWORD_KEY);
        delete_option(ByWireUser::API_KEY);
        delete_option(ByWireUser::ACCEPT_TERMS_KEY);
        delete_option(ByWireUser::ACCESS_TOKEN_KEY);
        delete_option(ByWireUser::ACCESS_EXPIRY_KEY);
        delete_option(ByWireUser::REFRESH_TOKEN_KEY);
        delete_option(ByWireUser::REFRESH_EXPIRY_KEY);
        delete_option(ByWireUser::USER_CONNECTED);
        delete_option(ByWireUser::USER_STATUS_KEY);
        delete_option(ByWireUser::USER_RESPONSE_KEY);
        delete_option(ByWireUser::CONNECTION_TESTED);
    }
}


