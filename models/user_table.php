<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if(isset($_POST) && !empty($_POST)){
	$response = ByWireAPI::register($_POST);
	echo "<pre>";
	var_dump($response);
	echo "</pre>";
	die;
}

?>