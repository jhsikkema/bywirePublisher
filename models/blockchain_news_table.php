<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once( 'base_table.php' );

class ArticlesTable extends BaseTable {
    
    public function get_columns(){
        $columns = array(
                    'publisher' => esc_html__( 'Publisher',          ByWire::ENV ),
                    'author'    => esc_html__( 'Author',          ByWire::ENV ),
                    'timestamp' => esc_html__( 'Timestamp',          ByWire::ENV ),
                    'ipfs_hash' => esc_html__( 'BlockChain ID',          ByWire::ENV ),
                    'title'     => esc_html__( 'Title',          ByWire::ENV ),
                );
        return $columns;
    }

    public function no_items() {
	esc_html_e( 'No News from Partners at the moment.', ByWire::ENV );
    }

    public function data_to_row($item) {
        return array('publisher' => $item->publisher,
                     'author'    => $item->author,
            	     'timestamp' => $item->timestamp,
            	     'ipfs_hash' => $item->ipfs_hash,
            	     'title'     => $item->title);
    }


    public function display_search_terms($page){
        $data         = ByWireAPI::articles(($page) ? $page-1 : 0, $this->per_page);
	if (!isset($data->data)) {
	return array("data" => array(),
	             "total"=> 0);
		
	}
	$display_data = array();
	foreach($data->data as $key=>$values){
	    $display_data[$key] = $this->data_to_row($values);
	}
	return array("data" => $display_data,
	             "total"=> $data->count);
    }

}

?>
