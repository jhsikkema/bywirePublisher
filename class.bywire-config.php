<?php
/* Copyright Sikkema Software B.V. 2020. 
*  The copyright holder is cannot be held liable for any damages
*  caused by this program or for it's proper functioning.
*  Copying or modifying the code is not permitted without express
*  written consent from the copyright holder.
*/
require_once("class.singleton.php");


class ByWireConfig extends Singleton {
    const PUBLISH_DIRECT_KEY     = ByWire::ENV.'-publish-direct-key';
    const SHOW_CERTIFICATE_KEY   = ByWire::ENV.'-show-certificate-key';
    const SHOW_LOGIN_KEY         = ByWire::ENV.'-show-login-key';
    const SHOW_FOOTER_KEY        = ByWire::ENV.'-show-footer-key';
    const ALLOW_USE_IMAGES_KEY   = ByWire::ENV.'-allow-use-images-key';
    const ADD_FEED_KEY           = ByWire::ENV.'-add-feed-key';
    const POST_TYPES_KEY         = ByWire::ENV.'-post-types-key';
    const POST_CATEGORIES_KEY    = ByWire::ENV.'-post-categories-key';
    const CONTEXT_KEY            = ByWire::ENV.'-post-context-key';
    public $publish_direct       = true;
    public $show_certificate     = false;
    public $show_login           = true;
    public $show_footer          = false;
    public $add_partner_feed     = true;
    public $allow_use_images     = true;
    public $post_types           = array();
    
    public $custom_post_types = false;
    
    public $all_posts       = true;
    public $all_pages       = true;
    public $post_categories = array();
    
    public $context          = "";

    public static function get_from_post($tag, $default = "") {
         return isset($_POST[$tag]) ? $_POST[$tag] : $default;
    }

    public static function from_post() {
        $config                   = ByWireConfig::instance();

        $config->publish_direct   = isset($_POST[ByWire::ENV.'_publish_direct']);
        $config->show_certificate = isset($_POST[ByWire::ENV.'_show_certificate']);
        $config->allow_use_images = isset($_POST[ByWire::ENV.'_allow_use_images']);
        $config->show_login       = isset($_POST[ByWire::ENV.'_show_login']);
        $config->show_footer      = isset($_POST[ByWire::ENV.'_show_footer']);
        $config->add_feed         = isset($_POST[ByWire::ENV.'_add_feed']);
        $config->post_types       = $config->get_from_post(ByWire::ENV.'post_types', array());
        $config->custom_post_types= $config->get_from_post(ByWire::ENV.'_custom_post_types');
        $config->all_posts        = $config->get_from_post(ByWire::ENV.'_all_posts');
        $config->all_pages        = $config->get_from_post(ByWire::ENV.'_all_pages');
        $config->post_categories  = $config->get_from_post(ByWire::ENV.'_post_categories', array());
        $config->store();
        return $config;
    }

    public static function get_option($tag, $default=true) {
         return apply_filters(ByWire::ENV.$tag, get_option($tag, $default=$default));
    }

    protected function init() {
        $this->publish_direct   = $this->get_option(ByWireConfig::PUBLISH_DIRECT_KEY);
        $this->show_certificate = $this->get_option(ByWireConfig::SHOW_CERTIFICATE_KEY, $this->show_certificate);
        $this->show_login       = $this->get_option(ByWireConfig::SHOW_LOGIN_KEY, $this->show_login);
        $this->show_footer      = $this->get_option(ByWireConfig::SHOW_FOOTER_KEY, $this->show_footer);
        $this->allow_use_images = $this->get_option(ByWireConfig::ALLOW_USE_IMAGES_KEY, $this->allow_use_images);
        $this->add_feed         = $this->get_option(ByWireConfig::ADD_FEED_KEY);
        $this->all_posts        = $this->get_option(ByWire::ENV.'_all_posts');
        $this->all_pages        = $this->get_option(ByWire::ENV.'_all_pages');
        $post_types             = $this->get_option(ByWireConfig::POST_TYPES_KEY);
        $post_types             = isset($post_types) ? explode(",", $post_types) : array();
	$this->post_types       = array_map('trim', $post_types);
        $this->custom_post_types= $this->get_option(ByWire::ENV.'_custom_post_types');
        $this->post_categories  = $this->get_option(ByWire::ENV.'_post_categories');
        $this->context          = $this->get_option(ByWireConfig::CONTEXT_KEY);
    }

    public function is_allowed_post_type($post_type) {
        if ($this->all_posts) {
	    return true;
	}
    	if (count($this->post_types)) {
	        return (in_array($post_type, $this->post_types));
        } else {
            return (in_array($post_type, ["post", "page"]));
        }
    }

    public function is_allowed_post_category($post_catergories) {
        if ($this->all_posts) {
	    return true;
	}
        if (count($this->post_categories) == 0) {
	    return true;
	}
        foreach($post_categories as $key=>$value) {
	    if (in_array($value, $this->post_categories)) {
	        return true;
	    }
	}
	return false;
    }

    public function store() {
        update_option( ByWireConfig::PUBLISH_DIRECT_KEY,   $this->publish_direct);
        update_option( ByWireConfig::POST_TYPES_KEY,       implode(",", $this->post_types));
        update_option( ByWireConfig::SHOW_CERTIFICATE_KEY, $this->show_certificate);
        update_option( ByWireConfig::ALLOW_USE_IMAGES_KEY, $this->allow_use_images);
        update_option( ByWireConfig::SHOW_LOGIN_KEY,       $this->show_login);
        update_option( ByWireConfig::SHOW_FOOTER_KEY,      $this->show_footer);
        update_option( ByWireConfig::ADD_FEED_KEY,         $this->add_feed);
        update_option( ByWireConfig::CONTEXT_KEY,          $this->context);

        update_option( ByWire::ENV.'_custom_post_types', $this->custom_post_types);
        update_option( ByWire::ENV.'_all_posts', $this->all_posts);
        update_option( ByWire::ENV.'_all_pages', $this->all_pages);
        update_option( ByWire::ENV.'_post_categories', $this->post_categories);
    }

    public function update() {
        $this->store();
        return array();
    }


    public static function deactivate()  {
        delete_option(ByWireConfig::PUBLISH_DIRECT_KEY);
        delete_option(ByWireConfig::POST_TYPES_KEY);
        delete_option(ByWireConfig::SHOW_CERTIFICATE_KEY);
        delete_option(ByWireConfig::SHOW_LOGIN_KEY);
        delete_option(ByWireConfig::SHOW_FOOTER_KEY);
        delete_option(ByWireConfig::ALLOW_USE_IMAGES_KEY);
        delete_option(ByWireConfig::ADD_FEED_KEY);
        delete_option(ByWireConfig::CONTEXT_KEY);

        delete_option(ByWire::ENV.'_custom_post_types');
        delete_option(ByWire::ENV.'_all_posts');
        delete_option(ByWire::ENV.'_all_pages');
        delete_option(ByWire::ENV.'_post_categories');
        
    }
}


