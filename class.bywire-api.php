<?php
/* Copyright Sikkema Software B.V. 2020. 
*  The copyright holder is cannot be held liable for any damages
*  caused by this program or for it's proper functioning.
*  Copying or modifying the code is not permitted without express
*  written consent from the copyright holder.
*/

class ByWireAPI {
    /**
     * Handles calling the ByWire Rest API
     */
    const API_HOST     = 'ec2-99-81-187-79.eu-west-1.compute.amazonaws.com';
    const API_PORT     = 80;
    const API_ROUTE    = '/bywire/api/v1/';
    
    const METHOD_POST = "POST";
    const METHOD_GET  = "GET";
    private static $routes     = array();

    public static function init() {
	self::$routes['login']	  = array('route'=> '/login',
					  'method'=>ByWireAPI::METHOD_POST);
	self::$routes['validate'] = array('route'=> '/validate',
					  'method'=>ByWireAPI::METHOD_GET);
	self::$routes['certify']  = array('route'=> '/certify',
					  'method'=>ByWireAPI::METHOD_GET);
	self::$routes['publish']  = array('route'=> '/publish',
					  'method'=>ByWireAPI::METHOD_POST);
	self::$routes['transfer'] = array('route'=> '/transfer',
					  'method'=>ByWireAPI::METHOD_POST);
	self::$routes['articles'] = array('route'=> '/articles',
					  'method'=>ByWireAPI::METHOD_GET);
	self::$routes['account']  = array('route'=> '/account',
					  'method'=>ByWireAPI::METHOD_GET);
	self::$routes['stakes']	  = array('route'=> '/stakes',
					  'method'=>ByWireAPI::METHOD_GET);
	self::$routes['register'] = array('route'=> '/register',
					  'method'=>ByWireAPI::METHOD_POST);
	self::$routes['publisherstats'] = array('route'=> '/publisherstats',
					  'method'=>ByWireAPI::METHOD_GET);
	self::$routes['publisherreport'] = array('route'=> '/publisherreport',
					  'method'=>ByWireAPI::METHOD_GET);
	self::$routes['accountreport'] = array('route'=> '/accountreport',
					  'method'=>ByWireAPI::METHOD_GET);
    }

    public static function test() {
	$connection = @fsockopen(ByWireAPI::API_HOST, ByWireAPI::API_PORT, $errno, $errmsg, 1);
	if (is_resource($connection)) {
	    fclose($connection);
	    return true;
	}
	return false;
    }


    public static function login() {
	// Logs the publisher onto the bywire network
	$route = self::$routes['login'];
	$user = ByWireUser::instance();
	if (!($user->connection_tested) > 0) {
	   return $user;
	}
	
	if (!($user->accept_terms > 0)) {
	    $user->access_token = "";
	    return $user;
	}
	$user->response = "Loggin in";
	$request = array("username"=>$user->username,
			 "password"=>$user->password,
			 "version"=>"BywirePublisher-".BYWIRE_VERSION
			 );
	$response = ByWireAPI::http_request($request, $route['route'], $route['method'], null);
	$user->add_response($response);
	return $user;
    }

    public static function validate($hash) {
	// Checks if an article is on the blockchain
	$route = self::$routes['validate'];
	$request = array("article"=>$hash);
	$response = ByWireAPI::http_request($request, $route['route'], $route['method']);
	return $response;
    }

    public static function account() {
	// Gets the account information for a user
	$route = self::$routes['account'];
	$user = ByWireUser::instance();
	if (!($user->connection_tested) > 0 || !$user->is_connected()) {
	     print("INVALID");
	     return false;
	}
	if ($user->expired()) {
	    $user = ByWireAPI::login();
	}
	$request = array("name"=>$user->username);
	$response = ByWireAPI::http_request($request, $route['route'], $route['method'], $user->access_token);
	return $response;
    }
    
    public static function transfer($amount, $to) {
	$route = self::$routes['transfer'];
	$user = ByWireUser::instance();
	if (!($user->connection_tested) > 0 || !$user->is_connected()) {
	     print("INVALID");
	     return false;
	}
	if ($user->expired()) {
	    $user = ByWireAPI::login();
	}
	$request = array("fromUser"  =>$user->username,
			 "amount"    =>$amount,
			 "toWallet"  =>$to);
	$response = ByWireAPI::http_request($request, $route['route'], $route['method'], $user->access_token);
	return $response;
    }

    public static function stakes() {
	// Gets the account information for a user
	$route = self::$routes['stakes'];
	$user = ByWireUser::instance();
	if (!($user->connection_tested) > 0 || !$user->is_connected()) {
	     print("INVALID");
	     return false;
	}
	if ($user->expired()) {
	    $user = ByWireAPI::login();
	}
	$request = array("name"=>$user->username);
	$response = ByWireAPI::http_request($request, $route['route'], $route['method'], $user->access_token);
	return $response;
    }


    public static function certify($hash) {
	// Gets the certificate for an ipfs_hash or article_id 
	$route = self::$routes['certify'];
	$request = array("article_ipfs_hash"=>$hash);
	$response = ByWireAPI::http_request($request, $route['route'], $route['method']);
	return $response;
    }

    public static function publisher_matched($articles) {
	// Obtains statics on publishes/reads for the user.
	$route = self::$routes['publisherstats'];
	$user = ByWireUser::instance();
	if (!($user->connection_tested) > 0 || !$user->is_connected()) {
	     print("INVALID");
	     return false;
	}
	if ($user->expired()) {
	     $user = ByWireAPI::login();
	}
	$request = array("since"=>"",
			 "articles"=>json_encode($articles));
	$response = ByWireAPI::http_request($request, $route['route'], $route['method'], $user->access_token);
	return $response;
    }

    public static function publisher_stats($page, $page_size) {
	// Obtains statics on publishes/reads for the user.
	$route = self::$routes['publisherstats'];
	$user = ByWireUser::instance();
	if (!($user->connection_tested) > 0 || !$user->is_connected()) {
	     print("INVALID");
	     return false;
	}
	if ($user->expired()) {
	     $user = ByWireAPI::login();
	}
	$request = array("since"=>"",
			 "page"=>$page,
			 "page-size"=>$page_size);
	$response = ByWireAPI::http_request($request, $route['route'], $route['method'], $user->access_token);
	return $response;
    }

    public static function publisher_report($add_today=false) {
	// Obtains statics on publishes/reads for the user.
	$route = self::$routes['publisherreport'];
	$user = ByWireUser::instance();
	if (!($user->connection_tested) > 0 || !$user->is_connected()) {
	     print("INVALID");
	     return false;
	}
	if ($user->expired()) {
	     $user = ByWireAPI::login();
	}
	$request = array("since"=>"",
		 "add_today"=>$add_today);
	$response = ByWireAPI::http_request($request, $route['route'], $route['method'], $user->access_token);
	return $response;
    }

    public static function account_report($page, $nr_results) {
	// Obtains statics on publishes/reads for the user.
	$route = self::$routes['accountreport'];
	$user = ByWireUser::instance();
	if (!($user->connection_tested) > 0 || !$user->is_connected()) {
	     print("INVALID");
	     return false;
	}
	if ($user->expired()) {
	     $user = ByWireAPI::login();
	}
	$request = array("page"=>$page,
			 "nr_results"=>$nr_results);
	$response = ByWireAPI::http_request($request, $route['route'], $route['method'], $user->access_token);
	return $response;
    }

    public static function wallet_url($wallet) {
	if (!ByWire::TEST_MODE) {
	   return "https://bloks.io/account/".$wallet;
	} else {
	   return "https://local.bloks.io/account/".$wallet."?nodeUrl=http%3A%2F%2Fjungle3.cryptolions.io&systemDomain=eosio&hyperionUrl=https%3A%2F%2Fjungle3history.cryptolions.io";
	}

    }

    
    public static function articles($page, $nr_results) {
	// Obtains statics on publishes/reads for the user.
	$route = self::$routes['articles'];
	$user = ByWireUser::instance();
	if (!($user->connection_tested) > 0 || !$user->is_connected()) {
	     print("INVALID");
	     return false;
	}
	if ($user->expired()) {
	     $user = ByWireAPI::login();
	}
	$request = array(
	    "page"=>$page,
			"nr_results"=>$nr_results
	);
	$response = ByWireAPI::http_request($request, $route['route'], $route['method'], $user->access_token);

	return $response;
    }


    public static function publish($post) {
	// Publishes to the blockchain and returns the ipfs hash
	    /* print("API - PUBLISH"); */

	$route = self::$routes['publish'];
	$user = ByWireUser::instance();
	if (!$user->is_registered()) {
	    $result = new stdClass();
	    $result->message = "Please configure Bywire Publisher";
	    $result->success = false;
	    $result->code    = "Unable to login";
	    return $result;
	} 
	if ($user->expired()) {
	    $user = ByWireAPI::login();
	}

	$article_id = $user->username.$post->ID;

	// Do something complicated with article

	$request = array("article"=>$post->post_name,
			 "writer"=>$user->username,
			 "publisher"=>$user->username,
			 "article-title"=>$post->post_title,
			 "article-content"=>$post->post_content,
			 "article-share-images"=>$post->share_images,
			 "article-image"=>$post->image,
			 "article-image-caption"=>$post->image_caption,
			 "article-author"=>$post->post_author
			);
	$config = ByWireConfig::instance();
	$config->context = "publish".implode(",", $request);
	$config->store(); 
	$response = ByWireAPI::http_request($request, $route['route'], $route['method'], $user->access_token);
	return $response;
    }

    public static function register($account_info) {
	$route = self::$routes['register'];
	
	// Do something complicated with article
	$request = array("username"=>$account_info["bywire_username"],
			"password"=>$account_info["bywire_password"]);
	$config = ByWireConfig::instance();
	$config->context = "publish".implode(",", $request);
	$config->store(); 

	$response = ByWireAPI::http_request($request, $route['route'], $route['method']);

	return $response;
    }

    public static function build_query( $args ) {
	$request_str = "";
	foreach($args as $key=>$value) {
	    $request_str = $request_str."&{$key}={$value}";
	}
	return substr($request_str, 1);
    }


    public static function http_request( $request, $route, $method, $token=null , $timeout=10) {
	$curl = curl_init();
	$url = "http://".self::API_HOST.":".self::API_PORT.self::API_ROUTE.$route;
	if ($method == ByWireAPI::METHOD_POST) {
	     curl_setopt($curl, CURLOPT_POST, 1);
	     curl_setopt($curl, CURLOPT_POSTFIELDS, ByWireAPI::build_query($request));
	} else if ($method == ByWireAPI::METHOD_GET) {
	    curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, ByWireAPI::METHOD_GET );
	    curl_setopt($curl, CURLOPT_POSTFIELDS, ByWireAPI::build_query($request));	
	}
	
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	if (!empty($token)) {
	    $headers = [
		'Authorization: Bearer '.$token,
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Encoding: gzip, deflate',
		'Accept-Language: en-US,en;q=0.5',
		'Cache-Control: no-cache',
		'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
		'Host: '.ByWireAPI::API_HOST,
		'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0'
	    ];

	    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	} else {
	    $headers = [
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Encoding: gzip, deflate',
		'Accept-Language: en-US,en;q=0.5',
		'Cache-Control: no-cache',
		'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
		'Host: '.ByWireAPI::API_HOST,
		'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0'
	    ];

	    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	}


	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($curl, CURLOPT_TIMEOUT,	   $timeout);

	$response = curl_exec($curl);
	if ($response === false) {
	    $result = new stdClass();
	    $result->message = curl_error($curl);
	    $result->success = false;
	    $result->code    = curl_errno($curl);
	    if ($result->code === 7) {
		$user = ByWireUser::instance();
		$user->status = ByWireUser::STATUS_SERVER_ERROR;
		$result->message = "Please publish the article again later.";
		$result->code	 = "Server Currently Unavailabe";
		$user->store();
	    } else if ($result->code === 28) {
		$user = ByWireUser::instance();
		$user->status = ByWireUser::STATUS_SERVER_TIMEOUT;
		$result->message = "Please publish the article again later.";
		$result->code	 = "Server Timeout";
		$user->store();
	    }
	    return $result;
	}
	curl_close($curl);

	$response = json_decode($response);
	return $response;
    }


}
