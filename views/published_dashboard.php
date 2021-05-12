<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

//Create an instance of our package class...
$published_table = new PublishedTable();
	
//Fetch, prepare, sort, and filter our data...
$published_table->prepare_items();


?>

<div class="wrap">
	
    <h2>
    	<?php esc_html_e( 'Earnings & Statistics', ByWire::ENV ); ?>
    </h2>
    
    <?php ByWire::view( '_earnings' ) ;?>

    <h1><?php echo esc_html__("Earnings per article", "bywire"); ?></h1>
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="bywire_filter" method="get">
        
    	<!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<input type="hidden" name="_wp_http_referer" value="">
        <!-- Search Title -->
        <?php $published_table->search_box( esc_html__( 'Search', ByWire::ENV ), 'published_table_search' ); ?>
        
        <!-- Now we can render the completed list table -->
        <?php $published_table->display(); ?>
        
    </form>
	        
</div>