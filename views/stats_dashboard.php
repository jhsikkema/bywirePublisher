<?php
//Create an instance of our package class...
$table = new StatsTable();
	
//Fetch, prepare, sort, and filter our data...
$table->prepare_items();
?>
<div class="wrap bywire-box">
    <?php ByWire::view( '_header' );?>
	
    <h2>
    	<?php esc_html_e( 'Display Postwise views', ByWire::ENV ); ?>
    </h2>
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="eqsa_filter" method="get">
        
    	<!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<input type="hidden" name="_wp_http_referer" value="">
        <!-- Search Title -->
        <?php $table->search_box( esc_html__( 'Search', ByWire::ENV ), 'published_table_search' ); ?>
        
        <!-- Now we can render the completed list table -->
        <?php $table->display(); ?>
        
    </form>
	        
</div>