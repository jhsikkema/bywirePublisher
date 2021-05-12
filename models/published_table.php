<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once( 'base_table.php' );
class PublishedTable extends BaseTable {

	public function get_columns(){
        $columns = array(
                    'post_title'    => esc_html__( 'Post',          ByWire::ENV ),
                    'article_id'    => esc_html__( 'Article ID',    ByWire::ENV ),
                    'publish_count' => esc_html__( 'Publish Count', ByWire::ENV ),
                    'read_count'    => esc_html__( 'Read Count',    ByWire::ENV ),
                    'read_amount'   => esc_html__( 'Read Amount',   ByWire::ENV ),
                    'tip_count'     => esc_html__( 'Tip Count',     ByWire::ENV ),
                    'tip_amount'    => esc_html__( 'Tip Amount',    ByWire::ENV ),
                );
        return $columns;
	}
	public function no_items() {
	    esc_html_e( 'No data. Probably nothing published yet.', ByWire::ENV );
	}
	
	public function display_search_terms($page){
		$posts_result = PublishedTable::$data->data;
		$display_post_data = array();
		foreach($posts_result as $key=>$values){
			// $pr->ID;
			$display_post_data[$key]                  = array();
				$display_post_data[$key]['post_title']    = $values->article_id;
				$display_post_data[$key]['article_id']    = $values->article_id;
				$display_post_data[$key]['publish_count'] = $values->publish_nr;
				$display_post_data[$key]['read_count']    = $values->read_nr;
				$display_post_data[$key]['read_amount']   = $values->read_amount;
				$display_post_data[$key]['tip_count']     = $values->tip_nr;
				$display_post_data[$key]['tip_amount']    = $values->tip_amount;
		}
		$data_res = array();
        $data_res['data'] = $display_post_data;
        $data_res['total'] = count($display_post_data);
        return $data_res;
	}

}