<?php
/* Copyright Sikkema Software B.V. 2020. 
*  The copyright holder is cannot be held liable for any damages
*  caused by this program or for it's proper functioning.
*  Copying or modifying the code is not permitted without express
*  written consent from the copyright holder.
*/

class ByWireCertificate {
	private static $initiated = false;
	private static $ipfs_url;
	
	public static function init() {
		if ( ! self::$initiated ) {

			self::$initiated = true;
		}
	}

	public static function eos_url($action, $id) {
	    if (ByWire::TEST_MODE) {
	       return "https://local.bloks.io/".$action."/".$id."?nodeUrl=http%3A%2F%2Fjungle3.cryptolions.io&systemDomain=eosio&hyperionUrl=https%3A%2F%2Fjungle3history.cryptolions.io";
	    } else {
	       return "https://bloks.io/".$action."/".$id;
	    }
	}

	public static function ipfs_url($hash) {
	    if (ByWire::TEST_MODE) {
	       return "https://local.bloks.io/".$action."/".$id."?nodeUrl=http%3A%2F%2Fjungle3.cryptolions.io&systemDomain=eosio&hyperionUrl=https%3A%2F%2Fjungle3history.cryptolions.io";
	    } else {
	       return "https://bloks.io/".$action."/".$id;
	    }
	}


    public static function bywire_certificate_display( $posts ) {
        global $wp,$wp_query;
        if (!isset($wp->query_vars) || !array_key_exists("bywire_certificate", $wp->query_vars)) {
            return $posts;
        }
        $base_path            =  plugin_dir_url( __FILE__ );
        $posts                = array();
        $ipfs_hash            = $wp->query_vars['bywire_certificate'];
        $certificate_path     = $base_path . 'certificates/certificate_'.$ipfs_hash.'.php';
        $contents             = file_get_contents($certificate_path);

        $wp_query->post_count = 1;
        $wp_query->posts      = array();
        $post                 = new stdClass;
        $post->ID             = 99999999;
        $post->post_author    = 1;
        $post->post_date      = '0000-00-00 00:00:00';
        $post->post_date_gmt  = '0000-00-00 00:00:00';
        $post->post_content   =  $contents;
        $post->post_title     = '<img src="'.$base_path.'assets/image/logo-full-2x.png" class="img-f>';
        #"ByWire Certificate";#.wp_script_is('bywire_accordion_list', 'enqueued');
        $post->post_excerpt   = "";
        $post->post_status    = 'publish';
        $post->comment_status = 'closed';
        $post->ping_status    = 'closed';
        $post->post_password  = '';
        $post->post_name      = "";
        $post->to_ping        = '';
        $post->pinged         = '';
        $post->post_modified  = '0000-00-00 00:00:00';
        $post->post_modified_gmt = '0000-00-00 00:00:00';
        $post->post_content_filtered = '';
        $post->post_parent    = 0;
        $post->guid           = "";
        $post->menu_order     = 0;
        $post->post_type      = 'post';
        $post->post_mime_type = '';
        $post->comment_count  = 0;
        $post->filter         = 'raw';
        $wp_query->posts[0]   = $post;
        //$post = (object) array_merge((array) $post, (array) $this->args);
        //$posts = NULL;
        $posts[] = $post;

        $wp_query->is_page     = true;
        $wp_query->is_singular = true;
        $wp_query->is_home     = false;
        $wp_query->is_archive  = false;
        $wp_query->is_category = false;
        unset($wp_query->query["error"]);
        $wp_query->query_vars["error"]="";
        $wp_query->is_404      = false;

        return $posts;

    }

    public static function clean() {
        $file_path    = BYWIRE__PLUGIN_DIR . 'certificates/certificate_*.php';
        $certificates = glob($file_path);
        array_map('unlink', $certificates);
    }

    public static function generate_certificate($ipfs_hash, $settings, $certificate, $return = false) {
        $base_path         =  plugin_dir_url( __FILE__ );
        //$base_path         = preg_replace("/.*(\/[^\/]*\/wp-content\/plugins)/i", "\\1", BYWIRE__PLUGIN_DIR);
        $link_path         = $base_path . 'certificates/certificate_'.$ipfs_hash.'.php';
        $template_path     = BYWIRE__PLUGIN_DIR . 'views/certificate.php';
        $certificate_path  = BYWIRE__PLUGIN_DIR . 'certificates/certificate_'.$ipfs_hash.'.php';

        //if (file_exists($certificate_path)) {
        //   return $link_path;
        //}
        $certificate_txt = file_get_contents($template_path);

        $revisions                   = $certificate->revisions;
        $settings["plugin_url"]      = $base_path;
        $settings["first-timestamp"] = (count($revisions) > 0) ? $revisions[0]->timestamp : "";
        $pattern   = "/{revision:([^}]*?)}/i";
        preg_match($pattern, $certificate_txt, $matches, PREG_OFFSET_CAPTURE);
        $replacement = "";
        $settings["last-timestamp"] = "";
        foreach($revisions as $key=>$values) {
            $tmp = @$matches[0][0];
            $link = '<a href="'.$values->ipfs_url_global.'">'.$values->timestamp.'</a>';
            $author_url = ByWireCertificate::eos_url("account", $values->author);
            $tmp = preg_replace("/#author-url/i", $author_url, $tmp);
            $tmp = preg_replace("/#author/i",    $values->author, $tmp);
            $tmp = preg_replace("/#timestamp/i", $values->timestamp, $tmp);

            $tmp = preg_replace("/#ipfs_hash/i", $link, $tmp);
            $settings["last-timestamp"] = $values->timestamp;
            $settings["article_title"] = $values->title;
            $replacement = $replacement.$tmp;
        }
        $certificate_txt = preg_replace($pattern, $replacement, $certificate_txt);
        $certificate_txt = preg_replace($pattern, "\\1", $certificate_txt);

        $pattern   = "/{authors:([^}]*?)}/i";
        preg_match($pattern, $certificate_txt, $matches, PREG_OFFSET_CAPTURE);
        $replacement = "";
        $authors = array();
        foreach($revisions as $key=>$values) {
	    
            $tmp = $matches[0][0];
            if (!in_array($values->author, $authors)) {
                $tmp = preg_replace("/#author-url/i", ByWireCertificate::eos_url("account", $values->author), $tmp);
                $tmp = preg_replace("/#author/i", $values->author, $tmp);
                $replacement = $replacement.$tmp;
                array_push($authors, $values->author);
            }
        }
        $certificate_txt = preg_replace($pattern, $replacement, $certificate_txt);
        $certificate_txt = preg_replace($pattern, "\\1", $certificate_txt);

        $settings["back-url"] = $_SERVER['REQUEST_URI'];

        $certificate_txt = ByWireCertificate::replace_keywords($certificate_txt, $settings);
        if(!$return) {
            file_put_contents($certificate_path, $certificate_txt);
            return $link_path;
        }

        return $certificate_txt;
    }

    public static function replace_keywords($str, $settings) {
        $result = $str;
        foreach($settings as $key=>$value) {
            $result = preg_replace("/{".$key."}/i", $value, $result);
        }
        return $result;
    }

    public static function add_bywire_certificate($content, $id=null) {
        global $wp;

        if (!( is_singular() && in_the_loop() && is_main_query() ) || array_key_exists("bywire_certificate", $wp->query_vars)) {
            return $content;
        }

        $config = ByWireConfig::instance();
        if (!$config->show_certificate) {
            return $content;
        }
        $post_id     = get_the_ID();
        $ipfs_hash   = get_post_meta($post_id, "_ipfs_hash");

        $settings = [

        ];

        if (count($ipfs_hash) != 0 && $ipfs_hash !== "error") {
            $ipfs_hash   = $ipfs_hash[0];

            $block       = ByWireAPI::validate($ipfs_hash);
            $certificate = ByWireAPI::certify($ipfs_hash);

            $settings["ipfs_hash"] = $ipfs_hash;

            if (!isset($block->success) || !$block->success > 0) {
                $blockchain_status_message = 'Waiting for block chain verification';

                $settings += [
		    "status"    => '<span class="not-verified">waiting for verification</span>',
		    "status_image" => "waiting.png",
		    "status_text"  => '<span class="not-verified">Not Verified</span>',
                    "block_num" => "",
                    "timestamp" => "",
                    "publisher" => "",
                    "transaction_id" => "",
                    "block-url" => "#",
                    "transaction_url"  => "#",
                ];
            } else {
                $blockchain_status_message   = "Verified on the block chain network";
                $data      = $block->message;
                $publisher = (isset($data->actions[0]->data->publisher)) ? $data->actions[0]->data->publisher : "";
                $publisher = ($publisher) ? $publisher : $data->actions[0]->authorization[0]->actor;

                $settings += [
		    "status"    => '<span class="verified">verified</span>',
		    "status_image" => "success.png",
		    "status_text"  => '<span class="verified">Confirmed</span>',
                    "block_num" => $data->block_num,
                    "timestamp" => $data->expiration,
                    "publisher" => $publisher,
                    "transaction_id"  => $data->transaction_id,
                    "block_url"       => ByWireCertificate::eos_url("block", "block_num"),
                    "publisher_url"   => ByWireCertificate::eos_url("block", "publisher"),
                    "transaction_url" => ByWireCertificate::eos_url("transaction", $data->transaction_id),
                ];

            }
            $certificate =  ByWireCertificate::generate_certificate($ipfs_hash, $settings, $certificate, true);
        }

        //add_action('wp_footer', function() use($certificate){
        //    echo $certificate;
        //});

        $certificate_container = ByWire::load_view('certificate_nonce', array("message"=>$blockchain_status_message));
        return $certificate_container . $content;
    }

}