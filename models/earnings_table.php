<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once( 'base_table.php' );

class EarningsTable extends BaseTable {
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

    public function data_to_row($item) {
         return array('post_title'    => $item->article_id,
 	  	      'article_id'    => $item->article_id,
         	      'publish_count' => $item->publish_nr,
            	      'read_count'    => $item->read_nr,
         	      'read_amount'   => $item->read_amount,
            	      'tip_count'     => $item->tip_nr,
            	      'tip_amount'    => $item->tip_amount);
    }

    public function display_search_terms($page){
	    $data = ByWireAPI::publisher_stats($page, $this->per_page);
	    EarningsTable::$data  = $data;
	    $display_data = array();
	    if (isset($data->data)) {
	        foreach($data->data as $key=>$values){
                    $display_data[$key] = $this->data_to_row($values);
                }
	    }
	    return array('data' => $display_data, 'total'=> $data->count);
    }

}

?>

