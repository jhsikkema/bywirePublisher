<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class BaseTable extends WP_List_Table {
    public $per_page;
    public static $data;

    
    public function __construct(){
        //Set parent defaults
        parent::__construct( array(
                            'singular'  => 'Postwise View',     //singular name of the listed records
                            'plural'    => 'Postwise Views',    //plural name of the listed records
                            'ajax'      => false        //does this table support ajax?
			                            ) );
	$this->columns = array();

	$this->per_page	    = 10; 
	BaseTable::$data    = array();
	$this->page         = 1;
	$this->prepare_items();
    }

    public function column_default( $item, $column_name ){
            return $item[ $column_name ];
    }

    public function get_columns(){
	die("ByWire.models.base_table.get_columns - This is a virtual base class - please implement get_columns");
        $columns = array(
                    'post_title' => esc_html__( 'Post', ByWire::ENV ),
                    'post_count' => esc_html__( 'Count', ByWire::ENV ),
                    'on_blockchain' => esc_html__( 'On BlockChain', ByWire::ENV ),
                );
        return $columns;
    }

    public function no_items() {
	die("ByWire.models.base_table.no_items - This is a virtual base class - please implement no_items");
	esc_html_e( 'No posts found.', ByWire::ENV );
    }


    public function data_to_row($item) {
	die("BaseTable.data_to_row - This is a virtual base class - please implement get_columns");
    }


    public function display_search_terms($page){
    //helper
	die("BaseTable.display_search_term - This is a virtual base class - please implement get_columns");
	$post_results = $this->get_posts();
	$tmp          = ByWireAPI::publisher_report()->data;
	$articles     = array();
	foreach($tmp as $key=>$values) {
	    $articles[$values->article_id] = $values;
	}
	foreach($post_results as $key=>$pr) {
		$display_post_data[$pr->ID]["post_title"] = $pr->post_title;
		$display_post_data[$pr->ID]["post_count"] = 1;
		$name = $pr->post_name;
		$on_blockchain = array_key_exists($name, $articles);
		$display_post_data[$pr->ID]["on_blockchain"] = $on_blockchain;
	}

        $data_res['data']	= $display_post_data;
        
		//Get Total count of Items
        
        $data_res['total'] = $post_results->found_posts;
        
        return $data_res;
    }

    public function prepare_items() {
        //implement
        $_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );
       
        // Get All, Hidden, Sortable columns
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = array();
        
	// Get final column header
        $this->_column_headers = array($columns, $hidden, $sortable);
	// Get Data of particular page
        $current_page   = $this->get_pagenum();
	if ($current_page != $this->page) {
            $this->page = $current_page;
	    $data   	= $this->display_search_terms($current_page);
	}
	// Get total count
        $total_items    = $data['total'];
        
        // Get page items
        $this->items    = $data['data'];
        
	// We also have to register our pagination options & calculations.
        $this->set_pagination_args( array(
                        'total_items' => $total_items, 
                        'per_page'    => $this->per_page, 
                        'total_pages' => ceil($total_items/$this->per_page)
        ) );
    }


}

?>