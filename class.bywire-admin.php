<?php
/* Copyright Sikkema Software B.V. 2020. 
*  The copyright holder is cannot be held liable for any damages
*  caused by this program or for it's proper functioning.
*  Copying or modifying the code is not permitted without express
*  written consent from the copyright holder.
*/

class ByWireAdmin {
	const NONCE = ByWire::ENV.'-update-key';

	private static $initiated = false;
	private static $notices   = array();
	private static $allowed   = array(
	    'a' => array(
	        'href' => true,
	        'title' => true,
	    ),
	    'b' => array(),
	    'code' => array(),
	    'del' => array(
	        'datetime' => true,
	    ),
	    'em' => array(),
	    'i' => array(),
	    'q' => array(
	        'cite' => true,
	    ),
	    'strike' => array(),
	    'strong' => array(),
	);

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'bywire-user' ) {
			self::enter_user();
		}
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'bywire-config' ) {
			self::enter_config();
		}
	}


	function show_publish_notice($post, $request, $creating = true){
	    echo '<script type="text/javascript">alert("PUBLISHED")</script>';
 	}



	public static function init_hooks() {
		// Redirect any links that might have been bookmarked or in browser history.
		if ( isset( $_GET['page'] ) && ByWire::ENV.'-stats-display' == $_GET['page'] ) {
			wp_safe_redirect( esc_url_raw( self::get_page_url( 'stats' ) ), 301 );
			die;
		}

		self::$initiated = true;
		// echo "ADDING HOOKS";

		add_action( 'admin_init', array( static::class, 'admin_init' ) );
		add_action( 'admin_menu', array( static::class, 'admin_menu' ), 5 ); # Priority 5, so it's called before Jetpack's admin_menu.
		add_action( 'admin_notices', array( static::class, 'display_notice' ) );
		add_action( 'admin_enqueue_scripts', array( static::class, 'load_resources' ) );
		add_action( 'activity_box_end', array( static::class, 'dashboard_stats' ) );

		add_action( 'jetpack_auto_activate_bywire', array( static::class, 'connect_jetpack_user' ) );
		add_filter( 'plugin_action_links', array( static::class, 'plugin_action_links' ), 10, 2 );
		add_filter( 'plugin_action_links_'.plugin_basename( plugin_dir_path( __FILE__ ) . 'bywire.php'), array( static::class, 'admin_plugin_settings_link' ) );
		
		add_action("admin_enqueue_scripts", array(static::class, "bywire_admin_enqueue_scripts"), 10);

		wp_register_script("bywire-swal-script", "https://unpkg.com/sweetalert/dist/sweetalert.min.js", array("bywire-Chart-js-script"), BYWIRE_VERSION, true);
		wp_enqueue_script("bywire-swal-script");
		$bywire_data = array(
			     "plugin_dir"=>BYWIRE__PLUGIN_URL,
			     "share_images_to_bywire"=>ByWireAdmin::share_images_is_checked(),
			     "publish_to_bywire" => ByWireConfig::instance()->publish_direct);
		$_SESSION["publish_to_bywire"] = $bywire_data["publish_to_bywire"];
		$_SESSION["share_images_to_bywire"] = $bywire_data["share_images_to_bywire"];

		register_post_meta( 'post', 'publish_to_bywire', array(
 		        'type'	       => 'boolean', 
			'single'       => true,
			'default'      => $bywire_data["publish_to_bywire"],
 			'show_in_rest' => true,
 		     ) );
		register_post_meta( 'post', 'share_images_to_bywire', array(
 		        'type'	       => 'boolean', 
			'single'       => true,
			'default'      => $bywire_data["share_images_to_bywire"],
 			'show_in_rest' => true,
 		     ) );
		if( !Util::classic_editor_is_active()) {
		     wp_enqueue_script('bywire_post_button', BYWIRE__PLUGIN_URL.'assets/js/bywire_gutenberg_button.js', array( "jquery", "bywire-swal-script", 'wp-edit-post', 'wp-data', 'wp-components', 'wp-plugins', 'wp-i18n', 'wp-element'), BYWIRE_VERSION);
		     wp_localize_script( 'bywire_post_button', 'bywire_data', $bywire_data);

		     add_action("rest_after_insert_post", array(static::class, "show_publish_notice"), 10);
		} else {
			//wp_register_script("bywire-admin-custom-script", BYWIRE__PLUGIN_URL . "assets/js/bywire_admin_custom.js", array("jquery"), time(), true);
	 	        //wp_localize_script( 'bywire-admin-custom-script', 'bywire_data', $bywire_data);
			
			add_action("post_submitbox_misc_actions", array(static::class, "bywire_post_submitbox_misc_actions"));
		}
		add_action( 'admin_menu', array( static::class, 'bywire_add_extra_menus' ), 10 );

		add_action( 'admin_init', array( static::class, 'active_deactive_user' ) );

		// priority=1 because we need ours to run before core's comment 		// add_action("save_post", array(static::class, "bywire_wp_insert_post_data"), 10, 2);
		//add_filter( 'wp_privacy_personal_data_erasers', array( static::class, 'register_personal_data_eraser' ), 1 );
		//addFilter( 'editor.PostFeaturedImage', MY-NAMESPACE/featured-image-display', setFeaturedImageDisplay	);


	}

	public static function share_images_is_checked() {
		$result  = ByWireConfig::instance()->allow_use_images;
		$post_id = get_the_ID();
		if ($post_id === "" || $post_id === null) {
		    return $result;
		}
		$share_images = get_post_meta($post_id, "_share_images_to_bywire", $single=true);
		//return json_encode($post_id)."*".json_encode($share_images);
		if ($share_images === false) {
		   return $result;
		}
		return $share_images === "1";
	}

	public static function admin_init() {
		if ( get_option( 'Activated'.ByWire::class ) ) {
			delete_option( 'Activated'.ByWire::class );
			if ( ! headers_sent() ) {
 	                       $is_connected = ByWireUser::instance()->is_connected();
	                       $main_page    = ByWire::ENV.(($is_connected) ? '-bywire-dashboard' : '-user-config');
				wp_redirect( add_query_arg( array( 'page' => $main_page ), class_exists( 'Jetpack' ) ? admin_url( 'admin.php' ) : admin_url( 'admin.php' ) ) );
				}
		}

		load_plugin_textdomain( ByWire::ENV );
		add_meta_box( 'bywire-status', __('Comment History', ByWire::ENV), array( static::class, 'comment_status_meta_box' ), 'comment', 'normal' );

		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			wp_add_privacy_policy_content(
				__( 'ByWire', 'bywire' ),
				__( 'This is a privacy notice', 'bywire' )
			);
		}

		if (@session_status() == PHP_SESSION_NONE) {
			@session_start();
		}
	}
	
	public static function admin_menu() {
		if ( class_exists( 'Jetpack' ) )
			add_action( 'jetpack_admin_menu', array( static::class, 'load_menu' ) );
		else
			self::load_menu();
	}

	public static function admin_head() {
		if ( !current_user_can( 'manage_options' ) )
			return;
	}
	
	public static function admin_plugin_settings_link( $links ) { 
  		$settings_link = '<a href="'.esc_url( self::get_page_url() ).'">'.__('Settings', ByWire::ENV).'</a>';
  		array_unshift( $links, $settings_link ); 
  		return $links; 
	}

	public static function load_menu() {
		if ( class_exists( 'Jetpack' ) ) {
   	                $is_connected = ByWireUser::instance()->is_connected();
	                $main_page    = ByWire::ENV.(($is_connected) ? '-bywire-dashboard' : '-user-config');
			$hook = add_submenu_page( 'jetpack', __( 'ByWire Publisher' , ByWire::ENV), __( 'ByWire Publisher' , ByWire::ENV), 'manage_options', $main_page, array( static::class, 'display_user_settings' ) );
			if ( $hook ) {
				add_action( "load-$hook", array( static::class, 'admin_help' ) );
			}
		}
		/* else {
			// $hook = add_options_page( __('ByWire Publisher', ByWire::ENV), __('ByWire Publisher', ByWire::ENV), 'manage_options', ByWire::ENV.'-user-config', array( static::class, 'display_page' ) );
			
		} */
		
		
	}

	public static function load_resources() {
		global $hook_suffix;

		if ( in_array( $hook_suffix, apply_filters( ByWire::ENV.'_admin_page_hook_suffixes', array(
			'index.php', # dashboard
			'edit-comments.php',
			'comment.php',
			'post.php',
			'settings_page_bywire-key-config',
			'jetpack_page_bywire-user-config',
			'plugins.php',
		) ) ) ) {
			wp_register_style( 'bywire.css', plugin_dir_url( __FILE__ ) . 'assets/css/bywire.css', array(), BYWIRE_VERSION );
			wp_enqueue_style( 'bywire.css');

			wp_register_script( 'bywire.js', plugin_dir_url( __FILE__ ) . 'assets/js/bywire.js', array('jquery'), BYWIRE_VERSION );
			wp_enqueue_script( 'bywire.js' );

			$inline_js = array(
				'comment_author_url_nonce' => wp_create_nonce( 'comment_author_url_nonce' ),
				'strings' => array(
					'Remove this URL' => __( 'Remove this URL' , 'bywire'),
					'Removing...'     => __( 'Removing...' , 'bywire'),
					'URL removed'     => __( 'URL removed' , 'bywire'),
					'(undo)'          => __( '(undo)' , 'bywire'),
					'Re-adding...'    => __( 'Re-adding...' , 'bywire'),
				)
			);

			if ( isset( $_GET['bywire_recheck'] ) && wp_verify_nonce( $_GET['bywire_recheck'], 'bywire_recheck' ) ) {
				$inline_js['start_recheck'] = true;
			}

			if ( apply_filters( 'bywire_enable_mshots', true ) ) {
				$inline_js['enable_mshots'] = true;
			}

			wp_localize_script( 'bywire.js', 'WPByWire', $inline_js );
		}
	}

	/**
	 * Add help to the ByWire page
	 *
	 * @return false if not the ByWire page
	 */
	public static function admin_help() {
		$current_screen = get_current_screen();

		// Screen Content
		if ( current_user_can( 'manage_options' ) ) {
			if (( isset( $_GET['view'] ) && $_GET['view'] == 'start' ) ) {
				//setup page
				$current_screen->add_help_tab(
					array(
						'id'		=> 'overview',
						'title'		=> __( 'Overview' , 'bywire'),
						'content'	=>
							'<p><strong>' . esc_html__( 'ByWire Setup' , 'bywire') . '</strong></p>' .
							'<p>' . esc_html__( 'ByWire Publisher allows you to publish to the bywire blockchain.' , 'bywire') . '</p>' .
							'<p>' . esc_html__( 'On this page, you are able to set up the ByWire Publisher.' , 'bywire') . '</p>',
					)
				);

				$current_screen->add_help_tab(
					array(
						'id'		=> 'setup-signup',
						'title'		=> __( 'New to ByWire Publisher' , 'bywire'),
						'content'	=>
							'<p><strong>' . esc_html__( 'ByWire Setup' , 'bywire') . '</strong></p>' .
							'<p>' . esc_html__( 'You need to enter the username and password given to you by bywire to allow publishing to the ByWire blockchain.' , 'bywire') . '</p>' .
							'<p>' . sprintf( __( 'Contact us at %s to get a User Account.' , 'bywire'), '<a href="https://bywire.news/plugin-signup/" target="_blank">ByWire.news</a>' ) . '</p>',
					)
				);

				$current_screen->add_help_tab(
					array(
						'id'		=> 'setup-manual',
						'title'		=> __( 'Enter user account details' , 'bywire'),
						'content'	=>
							'<p><strong>' . esc_html__( 'ByWire Publisher Setup' , 'bywire') . '</strong></p>' .
							'<p>' . esc_html__( 'If you already have a user account' , 'bywire') . '</p>' .
							'<ol>' .
								'<li>' . esc_html__( 'Copy and paste the user account details into the text fields.' , 'bywire') . '</li>' .
								'<li>' . esc_html__( 'Click the Use this Key button.' , 'bywire') . '</li>' .
							'</ol>',
					)
				);
			}
			elseif ( isset( $_GET['view'] ) && $_GET['view'] == 'stats' ) {
				//stats page
				$current_screen->add_help_tab(
					array(
						'id'		=> 'overview',
						'title'		=> __( 'Overview' , 'bywire'),
						'content'	=>
							'<p><strong>' . esc_html__( 'ByWire Stats' , 'bywire') . '</strong></p>' .
							'<p>' . esc_html__( 'ByWire Publishes to the bywire blockchain.' , 'bywire') . '</p>',
					)
				);
			}
			else {
				//configuration page
				$current_screen->add_help_tab(
					array(
						'id'		=> 'overview',
						'title'		=> __( 'Overview' , 'bywire'),
						'content'	=>
							'<p><strong>' . esc_html__( 'Bywire Configuration' , 'bywire') . '</strong></p>' .
							'<p>' . esc_html__( 'ByWire Publisher allows you to publish to the bywire blockchain.', 'bywire') . '</p>',
							)
				);

				$current_screen->add_help_tab(
					array(
						'id'		=> 'settings',
						'title'		=> __( 'Settings' , 'bywire'),
						'content'	=> 
							'<p><strong>' . esc_html__( 'ByWire Configuration' , 'bywire') . '</strong></p>' .
							'<p><strong>' . esc_html__( 'API Key' , 'bywire') . '</strong> - ' . esc_html__( 'Enter/remove an Username.' , 'bywire') . '</p>' .
							'<p><strong>' . esc_html__( 'Comments' , 'bywire') . '</strong> - ' . esc_html__( 'Show the number of approved comments beside each comment author in the comments list page.' , 'bywire') . '</p>' .
							'<p><strong>' . esc_html__( 'Strictness' , 'bywire') . '</strong> - ' . esc_html__( 'Choose to either discard the worst spam automatically or to always put all spam in spam folder.' , 'bywire') . '</p>',
					)
				);

				$current_screen->add_help_tab(
					array(
						'id'		=> 'account',
						'title'		=> __( 'Account' , 'bywire'),
						'content'	=>
							'<p><strong>' . esc_html__( 'ByWire Configuration' , 'bywire') . '</strong></p>' .
							'<p><strong>' . esc_html__( 'Subscription Type' , 'bywire') . '</strong> - ' . esc_html__( 'The ByWire plan' , 'bywire') . '</p>' ,
					)
				);
			}
		}

		// Help Sidebar
		$current_screen->set_help_sidebar(
			'<p><strong>' . esc_html__( 'For more information:' , 'bywire') . '</strong></p>' .
			'<p><a href="https://bywire.news/plugin/" target="_blank">'     . esc_html__( 'ByWire FAQ' , 'bywire') . '</a></p>' .
			'<p><a href="https://bywire.news/plugin/" target="_blank">' . esc_html__( 'ByWire Support' , 'bywire') . '</a></p>'
		);
	}

	public static function enter_user() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( __( 'Cheatin&#8217; uh?', 'bywire' ) );
		}

		if ( !wp_verify_nonce( $_POST['_wpnonce'], self::NONCE ) )
			return false;

		foreach( array( 'bywire_strictness', 'bywire_show_user_comments_approved' ) as $option ) {
			update_option( $option, isset( $_POST[$option] ) && (int) $_POST[$option] == 1 ? '1' : '0' );
		}

		if ( ! empty( $_POST['bywire_privacy_notice'] ) ) {
			self::set_form_privacy_notice_option( $_POST['bywire_privacy_notice'] );
		} else {
			self::set_form_privacy_notice_option( 'hide' );
		}
		$old_user = ByWireUser::instance();
		if ($old_user->is_connected()) {
		   $old_user->disconnect();
		   wp_redirect($_SERVER['HTTP_REFERER']);
		   return false;
		}
		ByWireUser::from_post();
		$new_user = ByWireUser::instance();

		if ( !($new_user->accept_terms > 0 )) {
		    self::$notices[] = "Please accept our terms";
		    return false;
		}
		ByWire::login_user($new_user);
   	        wp_redirect($_SERVER['HTTP_REFERER']);

		return true;
	}
		



	public static function enter_config() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( __( 'Cheatin&#8217; uh?', 'bywire' ) );
		}
		if ( !wp_verify_nonce( $_POST['_wpnonce'], self::NONCE ) )
			return false;

		foreach( array( 'bywire_strictness', 'bywire_show_user_comments_approved' ) as $option ) {
			update_option( $option, isset( $_POST[$option] ) && (int) $_POST[$option] == 1 ? '1' : '0' );
		}

		if ( ! empty( $_POST['bywire_privacy_notice'] ) ) {
			self::set_form_privacy_notice_option( $_POST['bywire_privacy_notice'] );
		} else {
			self::set_form_privacy_notice_option( 'hide' );
		}
		$config = ByWireConfig::from_post();
  	        $_SESSION["publish_to_bywire"] = $config->publish_direct;
	}

	public static function save_key( $api_key ) {
		$key_status = ByWire::verify_key( $api_key );

		if ( $key_status == 'valid' ) {
			$user = self::get_bywire_user( $api_key );
			
			if ( $user ) {				
				if ( in_array( $user->status, array( 'active', 'active-dunning', 'no-sub' ) ) )
					update_option( 'wordpress_api_key', $api_key );
				
				if ( $user->status == 'active' )
					self::$notices['status'] = 'new-key-valid';
				elseif ( $user->status == 'notice' )
					self::$notices['status'] = $user;
				else
					self::$notices['status'] = $user->status;
			}
			else
				self::$notices['status'] = 'new-key-invalid';
		}
		elseif ( in_array( $key_status, array( 'invalid', 'failed' ) ) )
			self::$notices['status'] = 'new-key-'.$key_status;
	}

	public static function dashboard_stats() {
	       return False;
	}

	public static function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( plugin_dir_url( __FILE__ ) . '/bywire.php' ) ) {
			$links[] = '<a href="' . esc_url( self::get_page_url() ) . '">'.esc_html__( 'Settings' , 'bywire').'</a>';
		}

		return $links;
	}

	// Check connectivity between the WordPress blog and ByWire's servers.
	// Returns an associative array of server IP addresses, where the key is the IP address, and value is true (available) or false (unable to connect).
	public static function check_server_connectivity() {
		return True;
	}
	

	// Check the server connectivity and store the available servers in an option. 
	public static function get_server_connectivity($cache_timeout = 86400) {
		return self::check_server_connectivity( $cache_timeout );
	}

	/**
	 * Find out whether any comments in the Pending queue have not yet been checked by ByWire.
	 *
	 * @return bool
	 */
	public static function get_page_url( $page = 'config' ) {
	        $is_connected = ByWireUser::instance()->is_connected();
		$config_key = ByWire::ENV.(($is_connected) ? '-bywire-dashboard' : '-user-config');
		$args = array( 'page' => $config_key );
		
		if ( $page == 'stats' )
			$args = array( 'page' => $config_key, 'view' => 'stats' );
		elseif ( $page == 'delete_key' )
			$args = array( 'page' => $config_key, 'view' => 'start', 'action' => 'delete-key', '_wpnonce' => wp_create_nonce( self::NONCE ) );

		$url = add_query_arg( $args, admin_url( 'admin.php' ) );

		return $url;
	}
	
	public static function get_bywire_user( $username ) {
	       return $username;

	}
	
	public static function get_stats( $api_key ) {
	       return False;
	}
	
	public static function verify_wpcom_key( $api_key, $user_id, $extra = array() ) {
		$account = ByWire::http_post( ByWire::build_query( array_merge( array(
			'user_id'          => $user_id,
			'api_key'          => $api_key,
			'get_account_type' => 'true'
		), $extra ) ), 'verify-wpcom-key' );

		if ( ! empty( $account[1] ) )
			$account = json_decode( $account[1] );

		ByWire::log( compact( 'account' ) );
		
		return $account;
	}
	
	public static function connect_jetpack_user() {
	
		if ( $jetpack_user = self::get_jetpack_user() ) {
			if ( isset( $jetpack_user['user_id'] ) && isset(  $jetpack_user['api_key'] ) ) {
				$user = self::verify_wpcom_key( $jetpack_user['api_key'], $jetpack_user['user_id'], array( 'action' => 'connect_jetpack_user' ) );
							
				if ( is_object( $user ) ) {
					self::save_key( $user->api_key );
					return in_array( $user->status, array( 'active', 'active-dunning', 'no-sub' ) );
				}
			}
		}
		
		return false;
	}

	public static function display_alert() {
		ByWire::view( 'notice', array(
			'type' => 'alert',
			'code' => (int) get_option( ByWire::ENV.'_alert_code' ),
			'msg'  => get_option( ByWire::ENV.'_alert_msg' )
		) );
	}


	public static function display_api_key_warning() {
		ByWire::view( 'notice', array( 'type' => 'plugin' ) );
	}

	public static function display_page() {
	       print_r("This should not occur... Tell jetze@bywire.news");
	       die();
	}


	public static function display_stats_page() {
		ByWire::view( 'stats' );
	}


	public static function display_notice() {
		global $hook_suffix;

		if ( in_array( $hook_suffix, array( 'jetpack_page_bywire-key-config', 'settings_page_bywire-key-config' ) ) ) {
			// This page manages the notices and puts them inline where they make sense.
			return;
		}

		if ( in_array( $hook_suffix, array( 'edit-comments.php' ) ) && (int) get_option( 'bywire_alert_code' ) > 0 ) {
			ByWire::verify_user( ByWireUser::instance() ); //verify that the key is still in alert state
			
			if ( get_option( 'bywire_alert_code' ) > 0 )
				self::display_alert();
		}
		elseif ( ( 'plugins.php' === $hook_suffix || 'edit-comments.php' === $hook_suffix ) && ! ByWireUser::instance() ) {
			// Show the "Set Up ByWire" banner on the comments and plugin pages if no API key has been set.
			self::display_api_key_warning();
		}
		elseif ( $hook_suffix == 'edit-comments.php' && wp_next_scheduled( 'bywire_schedule_cron_recheck' ) ) {
			self::display_spam_check_warning();
		}
		
	}

	public static function display_status() {
		if ( ! self::get_server_connectivity() ) {
			ByWire::view( 'notice', array( 'type' => 'servers-be-down' ) );
		}
		else if ( ! empty( self::$notices ) ) {
			foreach ( self::$notices as $index => $type ) {
				if ( is_object( $type ) ) {
					$notice_header = $notice_text = '';
					
					if ( property_exists( $type, 'notice_header' ) ) {
						$notice_header = wp_kses( $type->notice_header, self::$allowed );
					}
				
					if ( property_exists( $type, 'notice_text' ) ) {
						$notice_text = wp_kses( $type->notice_text, self::$allowed );
					}
					
					if ( property_exists( $type, 'status' ) ) {
						$type = wp_kses( $type->status, self::$allowed );
						ByWire::view( 'notice', compact( 'type', 'notice_header', 'notice_text' ) );
						
						unset( self::$notices[ $index ] );
					}
				}
				else {
					ByWire::view( 'notice', compact( 'type' ) );
					
					unset( self::$notices[ $index ] );
				}
			}
		}
	}

	private static function get_jetpack_user() {
		if ( !class_exists('Jetpack') )
			return false;

		if ( defined( 'JETPACK__VERSION' ) && version_compare( JETPACK__VERSION, '7.7', '<' )  ) {
			// For version of Jetpack prior to 7.7.
			Jetpack::load_xml_rpc_client();
		}

		$xml = new Jetpack_IXR_ClientMulticall( array( 'user_id' => get_current_user_id() ) );

		$xml->addCall( 'wpcom.getUserID' );
		$xml->addCall( 'bywire.getAPIKey' );
		$xml->query();

		ByWire::log( compact( 'xml' ) );

		if ( !$xml->isError() ) {
			$responses = $xml->getResponse();
			if ( count( $responses ) > 1 ) {
				// Due to a quirk in how Jetpack does multi-calls, the response order
				// can't be trusted to match the call order. It's a good thing our
				// return values can be mostly differentiated from each other.
				$first_response_value = array_shift( $responses[0] );
				$second_response_value = array_shift( $responses[1] );
				
				// If WPCOM ever reaches 100 billion users, this will fail. :-)
				if ( preg_match( '/^[a-f0-9]{12}$/i', $first_response_value ) ) {
					$api_key = $first_response_value;
					$user_id = (int) $second_response_value;
				}
				else {
					$api_key = $second_response_value;
					$user_id = (int) $first_response_value;
				}
				
				return compact( 'api_key', 'user_id' );
			}
		}
		return false;
	}

	private static function set_form_privacy_notice_option( $state ) {
		if ( in_array( $state, array( 'display', 'hide' ) ) ) {
			update_option( 'bywire_privacy_notice', $state );
		}
	}
	
	public static function register_personal_data_eraser( $erasers ) {
		$erasers['bywire'] = array(
			'eraser_friendly_name' => __( 'ByWire', 'bywire' ),
			'callback' => array( 'ByWireAdmin', 'erase_personal_data' ),
		);

		return $erasers;
	}
	
	/**
	 * When a user requests that their personal data be removed, ByWire has a duty to discard
	 * any personal data we store outside of the comment itself. Right now, that is limited
	 * to the copy of the comment we store in the bywire_as_submitted commentmeta.
	 *
	 * FWIW, this information would be automatically deleted after 15 days.
	 * 
	 * @param $email_address string The email address of the user who has requested erasure.
	 * @param $page int This function can (and will) be called multiple times to prevent timeouts,
	 *                  so this argument is used for pagination.
	 * @return array
	 * @see https://developer.wordpress.org/plugins/privacy/adding-the-personal-data-eraser-to-your-plugin/
	 */
	public static function erase_personal_data( $email_address, $page = 1 ) {
		$items_removed = false;
		
		$number = 50;
		$page = (int) $page;

		$comments = get_comments(
			array(
				'author_email' => $email_address,
				'number'       => $number,
				'paged'        => $page,
				'order_by'     => 'comment_ID',
				'order'        => 'ASC',
			)
		);

		foreach ( (array) $comments as $comment ) {
			$comment_as_submitted = get_comment_meta( $comment->comment_ID, 'bywire_as_submitted', true );
			
			if ( $comment_as_submitted ) {
				delete_comment_meta( $comment->comment_ID, 'bywire_as_submitted' );
				$items_removed = true;
			}
		}

		// Tell core if we have more comments to work on still
		$done = count( $comments ) < $number;
		
		return array(
			'items_removed' => $items_removed,
			'items_retained' => false, // always false in this example
			'messages' => array(), // no messages in this example
			'done' => $done,
		);
	}

	public static function bywire_admin_enqueue_scripts($hook){
	       if (preg_match('/bywire\-/i', $hook)) {
	       		wp_register_style( 'bywire-admin-custom-css',  BYWIRE__PLUGIN_URL . "assets/css/bywire_admin_custom.css", array(), BYWIRE_VERSION );
			wp_enqueue_style( 'bywire-admin-custom-css');
		}
		
		$valid_pages = array(
			"edit.php",
			"post-new.php",
			"post.php",
			"toplevel_page_bywire-bywire-dashboard",
			"bywire-dashboard_page_bywire-bywire-dashboard",
			"bywire-dashboard_page_bywire-user-config",
            		"bywire-dashboard_page_bywire-bywire-rewards",
            		"bywire-dashboard_page_bywire-bywire-earnings",
			"bywire-dashboard_page_bywire-publishing-stats",
			"bywire-dashboard_page_bywire-blockchain-news",
			"bywire-dashboard_page_bywire-faq-new-design",
            		"bywire-dashboard_page_bywire-democracy",
            		"bywire-dashboard_page_bywire-marketplace",
			"bywire-dashboard_page_bywire-terms-and-conditions"
		);


		if(in_array($hook, $valid_pages)){
			/** chart js style & scripts */
			wp_register_style( 'bywire-chart-js-css', "https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.css", array(), time() );
			wp_enqueue_style( 'bywire-chart-js-css');			
			wp_register_script("bywire-Chart-js-script", "https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js", array("jquery"), BYWIRE_VERSION, true);
			wp_enqueue_script("bywire-Chart-js-script");

			wp_register_style( 'fontello.css',  BYWIRE__PLUGIN_URL . "assets/css/fontello.css", array(), BYWIRE_VERSION);
			wp_enqueue_style( 'fontello.css');

			//update_post_meta(get_the_ID(), "_status3", "Arrived Here");
			$bywire_admin_custom_js_var = array(
			    "post_publish_error" => (isset($_SESSION["bywire_publish_response"]) && !empty($_SESSION["bywire_publish_response"]))? $_SESSION["bywire_publish_response"]: "",
			    "hook" => $hook,
			    "ajax_url" => admin_url('admin-ajax.php'),
			    "plugin_url" => BYWIRE__PLUGIN_URL,
			    "plugin_dir" => BYWIRE__PLUGIN_URL
			);

			wp_enqueue_script("bywire-admin-custom-script", BYWIRE__PLUGIN_URL . "assets/js/bywire_admin_custom.js", array("jquery"), BYWIRE_VERSION, true);
			wp_localize_script("bywire-admin-custom-script", "bywire_admin_custom_js_var", $bywire_admin_custom_js_var);

			//update_post_meta(get_the_ID(), "_status3", "Arrived There".BYWIRE__PLUGIN_URL.json_encode($bywire_admin_custom_js_var));

			if(isset($_SESSION["bywire_publish_response"])){
				unset($_SESSION["bywire_publish_response"]);
			}

			wp_register_script("bywire-accordion", BYWIRE__PLUGIN_URL . "assets/js/bywire_accordion.js", array("jquery"), BYWIRE_VERSION, true);
			wp_enqueue_script("bywire-accordion");

            		wp_register_script("bywire-accordion_list", BYWIRE__PLUGIN_URL . "assets/js/bywire_accordion_list.js", array("jquery"), BYWIRE_VERSION, true);
            		wp_enqueue_script("bywire-accordion_list");
		}
	}


	public static function bywire_post_submitbox_misc_actions($post){

		$html = "";
		if($post && $post->post_type == "post"){
			ob_start();

			?>	
			<?php
				$_ipfs_hash = get_post_meta($post->ID, "_ipfs_hash", true);
				if (!empty($_ipfs_hash)) {
				   echo '<div class="misc-pub-section">';
				   echo '<b>Published on ByWire</b>&nbsp;&nbsp;';
				   echo '</div>';
				 }
		        ?>
			<div class="misc-pub-section">
			<?php 
				$config     = ByWireConfig::instance();
				$checked    = $config->publish_direct;
				$checked    = ($checked) ? "checked" : "";
				$label      = (empty($_ipfs_hash)) ? "Post to Bywire" : "Upload Changes";
				echo '<input type="checkbox" value="1" '.$checked.' name="publish_to_bywire" id="publish_to_bywire"/> '.$label;
			?>
					
			</div>
			<div class="misc-pub-section">
			<?php 
				$_ipfs_hash = get_post_meta($post->ID, "_ipfs_hash", true);
				$checked    = ByWireAdmin::share_images_is_checked();
				$checked    = ($checked) ? "checked" : "";
				echo '<input type="checkbox" value="1" '.$checked.' name="share_images_to_bywire" id="share_images_to_bywire"/> I own the copyrights and share the images of this post with Bywire'.ByWireAdmin::share_images_is_checked();;
			?>
			<input type="hidden" name="bywire_classic_editor" id="bywire_classic_editor" value="1"/>
			</div>
			<?php
			$html = ob_get_clean();
		}
		echo $html;
	}

	//public static function bywire_wp_insert_post_data($post_ID, $post){
	//	if(isset($_POST["post_to_bywire"]) && $_POST["post_to_bywire"] == "0"){
	//		echo "<pre>";
	//		var_dump($post);
	//		echo "</pre>";
	//		$response = ByWireAPI::publish($post);
	//		echo "<pre>";
	//		var_dump($response);
	//		echo "</pre>";
	//	}
	//}

	public static function active_deactive_user() {
		if(isset($_POST["disconnect"])){
			$user = ByWireUser::instance(); 

			if(isset($_GET["action"]) && $_GET["action"] == "delete-key"){
				$user->deactivate();
			}
		}else if(isset($_POST["connect"])){
			ByWireUser::from_post();
			ByWireAPI::login();
		}
	}

	public static function bywire_add_extra_menus(){
	    $is_connected = ByWireUser::instance()->is_connected();
	    $main_page    = ByWire::ENV.(($is_connected) ? '-bywire-dashboard' : '-user-config');
	    add_menu_page(esc_html__('Bywire Dashboard', ByWire::ENV), esc_html__('Bywire Dashboard', ByWire::ENV), 'manage_options',  $main_page, '', 'dashicons-admin-site-alt3');

  	    add_submenu_page(  $main_page, esc_html__('Settings', ByWire::ENV), __('Dashboard', ByWire::ENV), 'manage_options', ByWire::ENV.'-bywire-dashboard', array( static::class, 'bywire_dashboard' ) );

	    add_submenu_page(  $main_page, esc_html__('Settings', ByWire::ENV), __('Settings', ByWire::ENV), 'manage_options', ByWire::ENV.'-user-config', array( static::class, 'bywire_user_settings' ) );

	    add_submenu_page(  $main_page, esc_html__('Settings', ByWire::ENV), __('Rewards', ByWire::ENV), 'manage_options', ByWire::ENV.'-bywire-rewards', array( static::class, 'bywire_rewards' ) );

	    add_submenu_page(  $main_page, esc_html__('Settings', ByWire::ENV), __('Earnings', ByWire::ENV), 'manage_options', ByWire::ENV.'-bywire-earnings', array( static::class, 'bywire_earnings' ) );
	    
	    add_submenu_page( $main_page, esc_html__('Publishing', ByWire::ENV), esc_html__('Publishing ', ByWire::ENV), 'manage_options', ByWire::ENV.'-publishing-stats', array(static::class, "bywire_publishing_stats"));

	    add_submenu_page( $main_page, esc_html__('Blockchain News', ByWire::ENV), esc_html__('Blockchain News', ByWire::ENV), 'manage_options', ByWire::ENV.'-blockchain-news', array(static::class, "bywire_blockchain_news"));

  	    add_submenu_page( $main_page, esc_html__('Democracy', ByWire::ENV), esc_html__('Democracy', ByWire::ENV), 'manage_options', ByWire::ENV.'-democracy', array(static::class, "bywire_democracy"));

	    add_submenu_page( $main_page, esc_html__('Marketplace', ByWire::ENV), esc_html__('Marketplace', ByWire::ENV), 'manage_options', ByWire::ENV.'-marketplace', array(static::class, "bywire_marketplace"));

	    add_submenu_page( $main_page, esc_html__('FAQ', ByWire::ENV), esc_html__('FAQ', ByWire::ENV), 'manage_options', ByWire::ENV.'-faq-new-design', array(static::class, "bywire_faq"));
	    add_submenu_page( $main_page, esc_html__('Terms and Conditions', ByWire::ENV), esc_html__('Terms and Conditions', ByWire::ENV), 'manage_options', ByWire::ENV.'-terms-and-conditions', array(static::class, "bywire_terms_and_conditions"));	     
		
	}


	public static function bywire_dashboard(){
		if (!ByWireUser::instance()->is_connected()) {
    		    ByWire::view_new("login_error");
		    return;
		}

		//require_once(BYWIRE__PLUGIN_DIR . "models/posts_table.php");
		ByWire::view_new("bywire_dashboard");
	}

	public static function bywire_user_settings(){
		ByWire::view_new("config");
	}

	public static function bywire_rewards(){
		if (!ByWireUser::instance()->is_connected()) {
    		    ByWire::view_new("login_error");
		    return;
		}

		//require_once(BYWIRE__PLUGIN_DIR . "models/posts_table.php");
		ByWire::view_new("bywire_rewards");
	}

	public static function bywire_earnings(){
		if (!ByWireUser::instance()->is_connected()) {
    		    ByWire::view_new("login_error");
		    return;
		}

		require_once(BYWIRE__PLUGIN_DIR . "models/earnings_table.php");
		ByWire::view_new("bywire_earnings");
	}

	public static function bywire_publishing_stats(){
		if (!ByWireUser::instance()->is_connected()) {
    		    ByWire::view_new("login_error");
		    return;
		}

		require_once(BYWIRE__PLUGIN_DIR . "models/posts_table.php");
		ByWire::view_new("bywire_publishing");
	}

	public static function bywire_user_register_html(){
		require_once(BYWIRE__PLUGIN_DIR . "models/user_table.php");
		ByWire::view("user_dashboard");
	}

	public static function bywire_democracy(){
		ByWire::view_new( "democracy_dashboard" );
	}

	public static function bywire_marketplace(){
		ByWire::view_new( "marketplace_dashboard" );
	}

	
	public static function bywire_blockchain_news(){
		if (!ByWireUser::instance()->is_connected()) {
    		    ByWire::view_new("login_error");
		    return;
		}
		require_once(BYWIRE__PLUGIN_DIR . "models/blockchain_news_table.php");
		ByWire::view_new("blockchain_news_dashboard");
	}
	
	public static function bywire_faq(){
		ByWire::view_new( "faq" );

	}

	public static function bywire_terms_and_conditions(){
		ByWire::view_new( "terms_and_conditions" );
	}
}
