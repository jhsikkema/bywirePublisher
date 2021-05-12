<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once( 'base_table.php' );

class PostsTable extends BaseTable {
    public function get_columns(){
        $columns = array(
                    'post_title' => esc_html__( 'Post', ByWire::ENV ),
                    'blockchain' => esc_html__( 'BlockChain:', ByWire::ENV ),
                    'publish' => esc_html__( 'Publish Count', ByWire::ENV ),
                    'read' => esc_html__( 'Read Count', ByWire::ENV ),
                    'tip' => esc_html__( 'Tip Count', ByWire::ENV ),
                );
        return $columns;
    }

    public function no_items() {
	esc_html_e( 'No posts found.', ByWire::ENV );
    }


    public function get_posts() {
        global $wpdb;
        $offset = ( $this->get_pagenum() - 1 ) * $this->per_page;

        $search 	= isset( $_GET['s'] ) ? sanitize_text_field( trim($_GET['s']) ) : null;

	$posts_args = array(
		"post_type"   => "post",
		"post_status" => "publish",
		"orderby"     => "date",
		"order"       => "DESC",
		"posts_per_page" => $this->per_page,
		"offset" => $offset
	);
	if(!empty($search)){
		$posts_args["s"] = $search;
	}
 
	$post_results = new WP_Query($posts_args);

	if (empty($post_results->posts)) {
	    return array();
	}
	return $post_results->posts;

    }

    public function data_to_row($item) {
	return array("post_title"    => $item->post_title,
	            'blockchain'     => "",
                    'publish'        => $item->publish,
                    'read'           => $item->read,
                    'tip'            => $item->tip,
		    );
    }

    public function display_search_terms($page){
	$post_results = $this->get_posts();
	$article_ids  = array();
	foreach($post_results as $key=>$post) {
	    array_push($article_ids, $post->post_name);
	}
	$articles     = array();
	$matched      = ByWireAPI::publisher_matched($article_ids);
	$matched      = (isset($matched->data)) ? $matched->data : array();
	foreach($matched as $key=>$values) {
	    $articles[$values->article_id] = $values;
	}
	$display_data = array();
	foreach($post_results as $key=>$pr) {
		$name           = $pr->post_name;
		$pr->publish    = "";
		$pr->read       = "";
		$pr->tip        = "";
		$pr->blockchain = "";
		if (array_key_exists($name, $articles)) {
		   $pr->blockchain = 1;
		   $article = $articles[$name];
		   $pr->publish = $article->publish_nr;
		   $pr->read    = $article->read_nr;
		   $pr->tip     = $article->tip_nr;
		}

		$display_data[$pr->ID] = $this->data_to_row($pr);
	}
	$total_posts = wp_count_posts();
	return array('data' => $display_data,
	             'total'=> $total_posts->publish);
    }

}

?>