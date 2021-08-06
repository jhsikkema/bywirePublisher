<?php
/* Copyright Sikkema Software B.V. 2020. 
*  The copyright holder is cannot be held liable for any damages
*  caused by this program or for it's proper functioning.
*  Copying or modifying the code is not permitted without express
*  written consent from the copyright holder.
*/


class ByWire {
	const ENV          = 'bywire';
	const CSS          = 'bywire';
	const TEST_MODE    = ByWireAPI::API_PORT == 5010;

	private static $initiated = false;
	
	public static function init() {
		if ( ! self::$initiated ) {
		        session_start();
			self::init_hooks();
			ByWireAPI::init();
			ByWireCertificate::init();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;

		add_filter('script_loader_tag',               array( static::class, 'set_form_js_async' ), 10, 3 );
		// Run this early in the pingback call, before doing a remote fetch of the source uri
		add_action('xmlrpc_call',                     array( static::class, 'pre_check_pingback' ) );
		
		// Jetpack compatibility
		add_filter('jetpack_options_whitelist',       array( static::class, 'add_to_jetpack_options_whitelist' ) );
		add_action('update_option_wordpress_api_key', array( static::class, 'updated_option' ), 10, 2 );
		add_action('add_option_wordpress_api_key',    array( static::class, 'added_option' ), 10, 2 );
		
		add_rewrite_rule('^bywire_certificate/([^/]*)$', 'index.php?bywire_certificate=$matches[1]', 'top' );
		add_filter('query_vars',    array( static::class, 'bywire_certificate_query_vars' ));
		add_action('parse_request', array( static::class, 'bywire_certificate_parse_request' ));

		add_filter('the_posts',     array(static::class, 'bywire_certificate_display'));

		$config     = ByWireConfig::instance();

		$post_types = $config->post_types;
		array_push($post_types, "post");
		array_push($post_types, "page");
		foreach($post_types as $key=>$type) {
   		    add_action('publish_'.$type, array( static::class, 'publish_post'), 100, 2);
		}
		add_action( 'get_footer',  array( static::class, 'display_comment_form_privacy_notice' ) );
		add_filter( 'wp_head',     array( static::class, 'add_bywire_login'), 10 );
		add_filter( 'the_content', array( static::class, 'add_bywire_certificate'), 10 );
		add_action( 'wp_footer',   array( static::class, 'display_site_footer' ), 10);
        	$base_path         =  plugin_dir_url( __FILE__ );

        	wp_enqueue_script('bywire_accordion_list', $base_path. '/assets/js/bywire_accordion_list.js', array('jquery'), BYWIRE_VERSION);

	}

	public static function bywire_certificate_query_vars( $query_vars )
	{
		$query_vars[] = 'bywire_certificate';
    		return $query_vars;
	}

	public static function bywire_certificate_display( $posts ) {
	        return ByWireCertificate::bywire_certificate_display( $posts );
 	}

	public static function bywire_certificate_parse_request( &$wp )
	{
    		return true;
	}
	
	public static function add_bywire_login($msg) {
	       return $msg;
	}

	public static function clean_certificates() {
	       ByWireCertificate::clean();
	}

	public static function add_bywire_certificate($content, $id=null) {
	       return ByWireCertificate::add_bywire_certificate($content, $id);
	}

	public static function verify_user( $username, $login) {
	       return True;
	}

	public static function login_user( $user ) {
	       $user->test();
	       $result = ByWireAPI::login();
	       return True;
	}
	
	/**
	 * Add the bywire option to the Jetpack options management whitelist.
	 *
	 * @param array $options The list of whitelisted option names.
	 * @return array The updated whitelist
	 */
	public static function add_to_jetpack_options_whitelist( $options ) {
		$options[] = 'wordpress_api_key';
		return $options;
	}

	/**
	 * When the bywire option is updated, run the registration call.
	 *
	 * This should only be run when the option is updated from the Jetpack/WP.com
	 * API call, and only if the new key is different than the old key.
	 *
	 * @param mixed  $old_value   The old option value.
	 * @param mixed  $value       The new option value.
	 */
	public static function updated_option( $old_value, $value ) {
		// Not an API call
		if ( ! class_exists( 'WPCOM_JSON_API_Update_Option_Endpoint' ) ) {
			return;
		}
		// Only run the registration if the old key is different.
		if ( $old_value !== $value ) {
			self::verify_user( $value );
		}
	}
	
	/**
	 * Treat the creation of an API key the same as updating the API key to a new value.
	 *
	 * @param mixed  $option_name   Will always be "wordpress_api_key", until something else hooks in here.
	 * @param mixed  $value         The option value.
	 */
	public static function added_option( $option_name, $value ) {
		if ( 'wordpress_api_key' === $option_name ) {
			return self::updated_option( '', $value );
		}
	}
	
	public static function is_test_mode() {
		return defined('BYWIRE_TEST_MODE') && BYWIRE_TEST_MODE;
	}
	
	public static function get_ip_address() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null;
	}


	public static function publish_gutenberg($id) {
	       //global wp;
	       $post = get_post();
	       return $post;
	}


	public static function publish_post($id, $post) {
	    
	    $post_to_bywire = (isset($_POST["bywire_classic_editor"])) ? isset($_POST["publish_to_bywire"]) : $_SESSION["publish_to_bywire"];
	    $share_images   = (isset($_POST["bywire_classic_editor"])) ? isset($_POST["share_images_to_bywire"]) : $_SESSION["share_images_to_bywire"];
	    $_SESSION["share_images_to_bywire"];
	    update_post_meta($post->ID,  "_publish_to_bywire", $post_to_bywire);
	    update_post_meta($post->ID, "_share_images_to_bywire", $share_images);
	    update_post_meta($post->ID, "_status_post", json_encode($_POST));
	    if (!$post_to_bywire) {
	        return;
	    }
	    $config         = ByWireConfig::instance();
	    $valid_status   = 'publish';
	    $valid_status   = true;
	    $valid_type     = $config->is_allowed_post_type($post->post_type);
	    $valid_category = $config->is_allowed_post_category($_POST["post_category"]);
	    if (!($valid_status && $valid_type && $valid_category)) {
	        return;
	    }
	    $user               = ByWireUser::instance();
	    $post->post_author  = $user->username;
	    $post->share_images = $share_images;
	    // The following is necessary when the featured image is set but the post not saved
	    if (isset($_SESSION["bywire_featured_image"])) {
	    	    $image_id           = $_SESSION["bywire_featured_image"];
		    unset($_SESSION["bywire_featured_image"]);
	    } else {
	    	    $image_id           = get_post_meta($post->ID, '_thumbnail_id', true);
	    }
	    if ($image_id) {
	    	    $image               = get_post($image_id);
		    $post->image         = $image->guid;
		    $post->image_caption = $image->post_excerpt;
	    } else {
		    $post->image         = "";
		    $post->image_caption = "";
	    }

	    if($response->success){
	    	update_post_meta($post->ID, "_ipfs_hash", $response->{'ipfs_hash'});
	    } else {
	        $ipfs_hash = get_post_meta($post->ID, "_ipfs_hash");
		if ($ipfs_hash === "" || $ipfs_hash === null) {
		   update_post_meta($post->ID, "_ipfs_hash", "error");
		}
	    }
	    
	    $response                  = ByWireAPI::publish($post);
	    
	    $response->post_id         = $post->ID;

	    if ($response->success) {
	      $response->message = "Thank you for submitting your post to the Bywire network.";
	    }
 	    if (strcmp($response->code, "PUBLISH_DUPLICATE_ARTICLE") == 0) {
	      $response->message = "Article Published, no update to the blockchain was necessary";
	      $response->success = true;

 	    }
	    $_SESSION["bywire_publish_response"]     = $response;
 	    update_post_meta($post->ID, "_status", "Publish".json_encode($response));
	    wp_redirect($_SERVER['HTTP_REFERER']);
	    return;
	}


	//public static function publish_post_manually($post) {
	//	return $response = ByWireAPI::publish($post);
	//}
	
	private static function get_user_agent() {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null;
	}

	private static function get_referer() {
		return isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : null;
	}

	// return a comma-separated list of role names for the given user
	public static function get_user_roles( $user_id ) {
		$roles = false;

		if ( !class_exists('WP_User') )
			return false;

		if ( $user_id > 0 ) {
			$comment_user = new WP_User( $user_id );
			if ( isset( $comment_user->roles ) )
				$roles = join( ',', $comment_user->roles );
		}

		if ( is_multisite() && is_super_admin( $user_id ) ) {
			if ( empty( $roles ) ) {
				$roles = 'super_admin';
			} else {
				$comment_user->roles[] = 'super_admin';
				$roles = join( ',', $comment_user->roles );
			}
		}

		return $roles;
	}

	public static function load_form_js() {
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return;
		}

		if ( ! ByWire::get_user() ) {
			return;
		}

		wp_register_script( ByWire::CSS.'-form', plugin_dir_url( __FILE__ ) . 'assets/js/form.js', array(), BYWIRE_VERSION, true );
		wp_enqueue_script( ByWire::CSS.'-form' );
	}
	
	/**
	 * Mark form.js as async. Because nothing depends on it, it can run at any time
	 * after it's loaded, and the browser won't have to wait for it to load to continue
	 * parsing the rest of the page.
	 */
	public static function set_form_js_async( $tag, $handle, $src ) {
		if ( 'bywire-form' !== $handle ) {
			return $tag;
		}
		
		return preg_replace( '/^<script /i', '<script async="async" ', $tag );
	}
	
	private static function bail_on_activation( $message, $deactivate = true ) {
		print_r($message);
		exit;
	}

	public static function clean_generated_views() {
		$file_path    = BYWIRE__PLUGIN_DIR . 'views/generated/*.php';
		$views = glob($file_path);
		array_map('unlink', $views);
	}

	public static function generate_views() {
		ByWire::generate_view("terms_and_conditions",      "Terms and Conditions");
		ByWire::generate_view("faq",                       "Frequently Asked Questions");
		ByWire::generate_view("democracy_dashboard",       "Democracy Dashboard");
		ByWire::generate_view("marketplace_dashboard",     "Marketplace Dashboard");
		ByWire::generate_view("config",                    "Publisher Settings");
		ByWire::generate_view("bywire_dashboard",          "Bywire Dashboard");
		ByWire::generate_view("bywire_rewards",            "Bywire Rewards");
		ByWire::generate_view("bywire_earnings",           "Bywire Earnings");
		ByWire::generate_view("blockchain_news_dashboard", "Latests Articles Published By Partners on the ByWire Network");
		ByWire::generate_view("bywire_publishing",         "Publishing Dashboard");
		ByWire::generate_view("login_error",           "Publisher Settings", "error", $args=["error_message"=> "Please connect to the bywire network first"]);
		}


	public static function generate_view($name, $title, $body="", $args = array()) {
	        $template_path = BYWIRE__PLUGIN_DIR . 'views/template.php';
		$body_path     = BYWIRE__PLUGIN_DIR . 'views/'.(($body == "") ? $name : $body).'.php';
	        $template      = file_get_contents($template_path);
		$body_content  = file_get_contents($body_path);
		$params        = ['page_body'  => $body_content,
		                  'page_title' => $title];
		foreach ( $params AS $key => $val ) {
			$template = preg_replace( '/{\\$'.$key.'}/i', $val, $template);
		}
		foreach ( $args AS $key => $val ) {
			$template = preg_replace( '/{\\$'.$key.'}/i', $val, $template);
		}
		$path = BYWIRE__PLUGIN_DIR . 'views/generated/'.$name.'.php';
	        file_put_contents($path, $template);
	}


	public static function publish_success() {
	    //global $pagenow;
	    //if ( $pagenow == 'options-general.php' ) {
            echo '<div class="notice notice-warning is-dismissible">
     	            <p>This is an example of a notice that appears on the settings page.</p>
                 </div>';
             //}
	     }


	public static function view_new($name, $args = array()) {
	       // Generate all views for testing
	       if (ByWire::TEST_MODE) {
	       	       ByWire::generate_views();
	       }
	       load_plugin_textdomain( ByWire::ENV );

	       $file = BYWIRE__PLUGIN_DIR . 'views/generated/'. $name . '.php';
	       include( $file );
	}

	public static function load_view($name, $args = array() ) {
	    $path     = BYWIRE__PLUGIN_DIR . 'views/'. $name.'.php';
	    $content  = file_get_contents($path);
	    foreach ( $args AS $key => $val ) {
	 	$content = preg_replace( '/{\\$'.$key.'}/i', $val, $content);
	    }
	    return $content;
	}


	public static function view( $name,array $args = array() ) {
		$args = apply_filters( 'bywire_view_arguments', $args, $name );
		
		foreach ( $args AS $key => $val ) {
			$$key = $val;
		}
		
		load_plugin_textdomain( ByWire::ENV );

		$file = BYWIRE__PLUGIN_DIR . 'views/'. $name . '.php';

		include( $file );
	}

	
	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation() {
	        $config = ByWireConfig::instance();
		$config->store();
		
		if ( version_compare( $GLOBALS['wp_version'], BYWIRE__MINIMUM_WP_VERSION, '<' ) ) {
			load_plugin_textdomain( ByWire::ENV );
			
			$message = '<strong>'.sprintf(esc_html__( 'ByWire Publisher %s requires WordPress %s or higher.' , 'bywire'), BYWIRE_VERSION, BYWIRE__MINIMUM_WP_VERSION ).'</strong> '.sprintf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version.', 'bywire'), 'https://codex.wordpress.org/Upgrading_WordPress', 'bywire.news/plugin/');

			ByWire::bail_on_activation( $message );
		} elseif ( ! empty( $_SERVER['SCRIPT_NAME'] ) && false !== strpos( $_SERVER['SCRIPT_NAME'], '/wp-admin/plugins.php' ) ) {
			add_option( 'Activated'.ByWire::class, true );
		}
		if ( !function_exists('curl_version')) {
			load_plugin_textdomain( ByWire::ENV );
			
			$message = '<strong>'.esc_html__( 'ByWire Publisher requires curl installed. Please contact the bywire team if you need help to install this.' , 'bywire').'</strong>';
			ByWire::bail_on_activation( $message );
		}
		ByWire::generate_views();
	}

	/**
	 * Removes all connection options
	 * @static
	 */
	public static function plugin_deactivation( ) {
	    ByWire::clean_generated_views();
	    ByWire::clean_certificates();
	    ByWireUser::deactivate();
	    ByWireConfig::deactivate();
	}
	
	/**
	 * Essentially a copy of WP's build_query but one that doesn't expect pre-urlencoded values.
	 *
	 * @param array $args An array of key => value pairs
	 * @return string A string ready for use as a URL query string.
	 */
	public static function build_query( $args ) {
		return _http_build_query( $args, '', '&' );
	}

	/**
	 * Log debugging info to the error log.
	 *
	 * Enabled when WP_DEBUG_LOG is enabled (and WP_DEBUG, since according to
	 * core, "WP_DEBUG_DISPLAY and WP_DEBUG_LOG perform no function unless
	 * WP_DEBUG is true), but can be disabled via the bywire_debug_log filter.
	 *
	 * @param mixed $message The data to log.
	 */
	public static function log( $message ) {
		if ( apply_filters( ByWire::ENV.'_debug_log', defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && defined( 'BYWIRE_DEBUG' ) && BYWIRE_DEBUG ) ) {
			error_log( print_r( compact( 'message' ), true ) );
		}
	}

	/**
	 * Controls the display of a privacy related notice underneath the comment form using the `bywire_comment_form_privacy_notice` option and filter respectively.
	 * Default is top not display the notice, leaving the choice to site admins, or integrators.
	 */
	public static function display_comment_form_privacy_notice() {
                $privacy_notice = ByWire::ENV.'_comment_form_privacy_notice';
		if ( 'display' !== apply_filters( $privacy_notice, get_option( $privacy_notice, 'hide' ) ) ) {
			return;
		}
		echo apply_filters(
			$privacy_notice, ByWire::view('privacy_notice'));
	}

	public static function display_site_footer() {
		    $config = ByWireConfig::instance();
	    if (!$config->show_footer) {
	         return;
	    }
	    //@todo uncomment to show bywire footerx
	    //echo ByWire::load_view('privacy_notice');
	}
}

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
   session_start();
   $response = array("message"=> "No Data", "has_data"=> false);
   if (!isset($_POST["action"])) {
       return;
   }
   if ($_POST["action"] === "check-publish") {
       if (isset($_SESSION["bywire_publish_response"])) {
           $response["has_data"] = true;

	   $message              = $_SESSION["bywire_publish_response"]->message;
	   $success              = $_SESSION["bywire_publish_response"]->success;
	   //if ($success) {
	   //   $message = "Thank you for submitting your post to the bywire network.";
	   //}	  
	   if (strcmp($_SESSION["bywire_publish_response"]->code, "PUBLISH_DUPLICATE_ARTICLE") == 0) {
	      $message = "Article Published, no update to the blockchain was necessary";
	      $success = true;

	   }

           $response["message"]       = $message;
           $response["success"]       = $success;
           $response["error_code"]    = $_SESSION["bywire_publish_response"]->code;
           //$response["checked"]     = $_SESSION["publish_to_bywire"];
           unset($_SESSION["bywire_publish_response"]);
       }
   } else if ($_POST["action"] === "set-publish") {
        $_SESSION["publish_to_bywire"]        = $_POST["value"] === "true";
	$response["set"]                      = $_POST["value"];
   } else if ($_POST["action"] === "set-share-images") {
        $_SESSION["share_images_to_bywire"] = $_POST["value"] === "true";
	$response["set"]                      = $_POST["value"];
   } else if ($_POST["action"] === "set-featured-image") {
        $_SESSION["bywire_featured_image"]    = $_POST["value"];
	$response["set"]                      = $_POST["value"];
   } 

   echo json_encode($response);
}




?>