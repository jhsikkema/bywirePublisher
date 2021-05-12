<?php
/* Copyright Sikkema Software B.V. 2020. 
*  The copyright holder is cannot be held liable for any damages
*  caused by this program or for it's proper functioning.
*  Copying or modifying the code is not permitted without express
*  written consent from the copyright holder.
*/
require_once("class.singleton.php");


class ByWireNews extends Singleton {

	public function bywire_load_more_news(){
		$send_data = array();
		$send_data["success"] = false;
		$send_data["html"] = __("Too Smart!", "bywire");
//		if(isset($_POST) && isset($_POST["paged"]) && !empty($_POST["paged"])){
			$page = isset($_POST["paged"])? $_POST["paged"]: 1;
			$per_page = 9;

			$data = ByWireAPI::articles($page, $per_page);

			$return_data = array();

			if(isset($data->data)){
				$index = 0;
				foreach($data->data as $ddk){
					ob_start();

					bywire_include(BYWIRE__PLUGIN_DIR . 'views/components/news-item.php', ['article' => $ddk]);

                    $return_data[] = ob_get_contents();
                    ob_end_clean();

					$index++;
				}
			}
			$send_data["success"] = true;
			$send_data["html"] = implode($return_data);

//		}

		wp_send_json($send_data);
		die;
	}

	protected function init(){
		add_action("wp_ajax_bywire_load_more_news", array($this, "bywire_load_more_news"));

	}
}
$bywire_news = ByWireNews::instance();
