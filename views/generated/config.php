<header class="bw-header">
    <div class="container">
        <div class="row align-items-center justify-content-between">
            <div class="bw-header-left col-8">
                <div class="bw-header-logo">
                    <a href="<?php echo add_query_arg( array( 'page' => ByWire::ENV.'-postwise-states' ), admin_url( 'admin.php' )  ); ?>">
                        <img src="<?php echo esc_url( BYWIRE__PLUGIN_URL.'assets/image/logo-full-2x.png' ); ?>" class="img-fluid" alt="ByWire Logo" />
                    </a>
                </div>
                <div class="bw-header-text">
                    <p>Blockchain News Network <?php echo isset($page_heading)? "- ".$page_heading:""; ?></p>
                </div> 
            </div>
            <div class="bw-header-right col-4">
                <div class="bw-button-grp text-right">
                    <a href="<?php echo add_query_arg( array( 'page' => ByWire::ENV.'-bywire-dashboard' ), admin_url( 'admin.php' )  ); ?>" class="bw-btn-dark">Dashboard</a>
                    <a href="<?php echo add_query_arg( array( 'page' => ByWire::ENV.'-user-config' ), admin_url( 'admin.php' )  ); ?>" class="bw-btn-light-danger">Settings</a>
                </div>
            </div>
        </div>
    </div>
</header>
<main class="bw-main">
    <section class="bw-faq">
        <div class="container">
            <div class="row">
<!--                <div class="col-12">-->
<!--                    <div class="bw-head">-->
<!--                        <h2 class="bw-title">Publisher Settings</h2>-->
<!--                    </div>-->
<!--                </div>-->
                <div class="col-12">
		            <?php
$user    = ByWireUser::instance();
$config  = ByWireConfig::instance(); 

if (!function_exists('curl_version')) {
    echo "You need to enable curl before using this plugin.";
    echo "Please contact the bywire team to help you setup curl";
}

$post_categories = array();
if(!empty($config->post_categories) && is_array($config->post_categories)){
    $post_categories = $config->post_categories;
}

$valid_categories = get_categories(array(
   'hide_empty' => false,
));

$post_types = get_post_types();
$post_types = array_filter($post_types, function ($x) { return !in_array($x, array("attachment", "nav_menu_item", "custom_css", "customize_changeset", "oembed_cache", "user_request", "wp_block")); });
$stored_post_types = array();
if(!empty($config->post_types)){
    $stored_post_types = $config->post_types;
}




function format_checkbox($config, $id, $label) {
    $text = '<div class="check-group">';
    $text = $text.'<label class="check-label">';
    $text = $text.'<input type="checkbox" class="check-input" name="bywire_'.$id.'" id="bywire_'.$id.'" onchange="this.form.submit()" '. ($config->{$id} ? "checked" : "") . ' /> ';
    $text = $text.$label;
    $text = $text.'</label>';
    $text = $text.'</div>';
    return $text;
}


global $wp;
$link = $_SERVER["REQUEST_URI"];
$link = preg_replace("/options-general.php/i", "admin.php", $link);
$link = preg_replace("/=[a-z\-]*$/i", "=bywire-terms-and-conditions", $link);
$terms_and_conditions = '<a href="'.$link.'">Terms & Conditions</a>';

$user = ByWireUser::instance();
$page = ($user->connected) ? "delete_key" : "config";

$connection_str = ($user->is_connected())  ? '<div class="col-md-6 form-text-success">Connected</div>' : '<div class="col-md-6 form-text-danger">Disconnected - '.$user->status_str().'</div>';


?>

<div class="row no-gutters">
    <div class="bw-head col-12">
        <h2 class="bw-title">Publisher Settings</h2>
    </div>
</div>
<form action="<?php echo esc_url( ByWireAdmin::get_page_url() ); ?>" method="post">
    <?php wp_nonce_field( ByWireAdmin::NONCE ); ?>
    <input type="hidden" name="action" value="bywire-config"/>

    <div class="row bw-publisher-setting-inner">
        <div class="col-12 bw-inner-wrapper pt-3 pb-3 table-responsive">

            <div class="row m-1">
                <div class="bw-head col-md-12">
                    <h2 class="bw-title mb-4">Blockchain Publishing Control Center</h2>
                </div>
	    </div>
            <div class="row mb-2">
                <div class="col-md-6 ">
                    <?php echo format_checkbox($config, "publish_direct", "Publish direct to blockchain"); ?>
                    <?php echo format_checkbox($config, "show_certificate", "Display verification certificate"); ?>
                </div>
                <div class="col-md-6">
                    <?php echo format_checkbox($config, "allow_use_images", "Allow partners to use images *)"); ?>
                    <?php echo format_checkbox($config, "show_footer", "Show Footer"); ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12">
		    *) Make sure you have the appropriate copyrights to share images. You can overwrite this on individual articles
		</div>
	    </div>


            <div class="row m-1">
                <div class="bw-head col-md-12 mb-2 mt-2">
                    <h5 class="strong">What type of content do you want to publish to the network?</h5>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6 form-group">
                    <?php echo format_checkbox($config, "all_posts", "All Pages"); ?>
                </div>
                <div class="col-md-6 form-group">
                    <?php echo format_checkbox($config, "all_pages", "All Posts"); ?>
                </div>
            </div>

            <div class="row m-1">
                <div class="bw-head col-md-12">
                    <p class="mb-4 mt-2">You can also customise what to publish to the network further by selecting multiple options from each dropdown menu below</>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6 form-group">
                    <select class="form-control" multiple name="bywire_post_categories[]" id="bywire_post_categories">
                        <option disabled value=""><?php echo esc_attr_e( 'Post Categories…' , 'bywire' ); ?></option>
                        <?php foreach($valid_categories as $ctk) {
			    $selected = in_array($ctk->name, $config->post_types);
                            echo '<option value="'.$ctk->term_id.'" '.($selected ? "selected" : "").'>'.$ctk->name.'</option>';
			    }
			?>
                    </select>

                </div>
                <div class="col-md-6 form-group">
                    <select class="form-control" multiple name="bywire_post_types[]" id="bywire_post_types">
                        <option disabled value=""><?php echo esc_attr_e( 'Post Types…' , 'bywire' ); ?></option>
                        <?php foreach($post_types as $ctk) {
			    $selected = in_array($ctk, $config->post_categories);
                            echo '<option value="'.$ctk.'" '.($selected ? "selected" : "").'>'.$ctk.'</option>';
			    }
			?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>

<form action="<?php echo esc_url( ByWireAdmin::get_page_url() ); ?>" method="post">
    <?php wp_nonce_field( ByWireAdmin::NONCE ); ?>
    <input type="hidden" name="action" value="bywire-user"/>
    <div class="row bw-publisher-setting-inner">
        <div class="col-12 bw-inner-wrapper pt-5 pb-5 table-responsive">
            <div class="row">
                <div class="bw-head col-md-12">
                    <h2 class="bw-title mb-4">Network Connection</h2>
                </div>
	    </div>
		
            <!--<div class="row mt-5">
                <div class="bw-head col-md-12 mb-3 mt-5">
                    <h2 class="bw-title">Network Connection</h2>
                </div>
            </div>-->
            <div class="row mb-4">
                <div class="col-md-6 pt-2">
                    <label class="form-label">Status</label>
                </div>
		<div class="col-md-6 text-uppercase text-left">
                     <?php echo $connection_str; ?>
               </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6 pt-2">
                    <label class="form-label">Network Username</label>
                </div>
                <div class="col-md-6">
                    <input name="bywire-username" type="text" id="bywire-username" class="form-input" placeholder="Network Username" value="<?php echo $user->username; ?>" />
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6 pt-2">
                    <label class="form-label">Network Password</label>
                </div>
                <div class="col-md-6">
                    <input type="password" name="bywire-password" id="bywire-password" class="form-input" placeholder="******************************" value="<?php echo $user->password; ?>" />
                    <p class="float-right" style="font-size: 0.9em;"><a href="#" class="text-muted" role="toggle-password-visibility">Show Password</a></p>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6 pt-2">
                    <label class="form-label">Network API Key</label>
                </div>
                <div class="col-md-6">
                    <input type="text" name="bywire-api-key" id="bywire-api-key" class="form-input" placeholder="gi020yhj0924hjwhw0gnmaqqg49yg9hsjgh" value="<?php echo $user->api_key; ?>" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Accept <?php echo $terms_and_conditions ?></label>
                </div>
                <div class="col-md-6">
                    <input type="checkbox" class="check-input" name="bywire-accept-terms" id="bywire-accept-terms" <?php echo ($user->accept_terms ? "checked" : ""); ?> />
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12 form-btn-group">
                    <?php
                    if ($user->is_registered()) {
                        echo '<button type="submit" name="disconnect" id="submit" class="bw-btn-danger">Disconnect from Bywire Blockchain News Network</button>';
                    } else {
                        echo '<button type="submit" name="connect" id="submit" class="bw-btn-succes">Connect to the Bywire Blockchain News Network</button>';
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>
</form>

                </div>
	        </div>
        </container>
   </section>
</main>
