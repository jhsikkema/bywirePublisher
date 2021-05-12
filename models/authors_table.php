<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once( 'base_table.php' );

class AuthorsTable extends BaseTable {
    public function get_columns(){
        $columns = array(
                    'username'    => esc_html__( 'Username',          ByWire::ENV ),
                    'timestamp'   => esc_html__( 'Timestamp',          ByWire::ENV ),
                );
        return $columns;
    }

    public function no_items() {
	esc_html_e( 'No accounts created yet.', ByWire::ENV );
    }

    public function data_to_row($item) {
         return array("username" =>$item->username,
		      "timestamp"=>$item->timestamp);

    }

    public function display_search_terms($page){
        $data   = ByWireAPI::account_report(($page) ? $page-1 : 0, $this->per_page);
	$display_data = array();
	foreach($data->data->accounts as $key=>$values){
	    $display_data[$key] = $this->data_to_row($values);
	}
	return array("data" => $display_data,
            	     "total"=> $data->count);
    }

}

?>
