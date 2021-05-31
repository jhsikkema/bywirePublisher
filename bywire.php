<?php
/**
 * @package ByWire
 */
/*
Plugin Name: Bywire Publisher
Plugin URI: bywire.news/plugin
Description: Used by large news organizations, <strong>Bywire Publisher</strong> is your gateway to publish articles to the bywire decentralized news universe. To get started: activate the Bywire Publisher plugin and then go to your Bywire Settings page to set up your account.
Version: 0.0.9
Author: Jetze Sikkema
Author URI: https://www.sikkemasoftware.nl
License: GPLv2 or later
Text Domain: bywire
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2020 SikkemaSoftware
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'BYWIRE_VERSION', '0.0.9' );
define( 'BYWIRE__MINIMUM_WP_VERSION', '4.4' );
define( 'BYWIRE__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BYWIRE__PLUGIN_URL', plugin_dir_url(__FILE__) );

register_activation_hook( __FILE__,   array( 'ByWire', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'ByWire', 'plugin_deactivation' ) );

function bywire_include($path, $variables){
    extract($variables);
    include($path);
} //function keeps the variables scoped.

require_once( BYWIRE__PLUGIN_DIR . 'class.bywire-news.php' );
require_once( BYWIRE__PLUGIN_DIR . 'class.bywire.php' );
require_once( BYWIRE__PLUGIN_DIR . 'class.bywire-user.php' );
require_once( BYWIRE__PLUGIN_DIR . 'class.bywire-config.php' );
require_once( BYWIRE__PLUGIN_DIR . 'class.bywire-certificate.php' );
require_once( BYWIRE__PLUGIN_DIR . 'class.bywire-widget.php' );
require_once( BYWIRE__PLUGIN_DIR . 'class.bywire-api.php' );
require_once( BYWIRE__PLUGIN_DIR . 'class.util.php' );

add_action( 'init', array( 'ByWire', 'init' ) );
add_action( 'rest_api_init', array( 'ByWireAPI', 'init' ) );

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once( BYWIRE__PLUGIN_DIR . 'class.bywire-admin.php' );
	add_action( 'init', array( 'ByWireAdmin', 'init' ) );
}

//add wrapper class around deprecated bywire functions that are referenced elsewhere
require_once( BYWIRE__PLUGIN_DIR . 'wrapper.php' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( BYWIRE__PLUGIN_DIR . 'class.bywire-cli.php' );
}
