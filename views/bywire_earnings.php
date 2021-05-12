<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//Create an instance of our package class...
$earnings_table = new EarningsTable();
	
//Fetch, prepare, sort, and filter our data...
$earnings_table->prepare_items();


?>

<?php
   $user = ByWireUser::instance();
   if (!$user->is_connected() && $user->status !== ByWireUser::STATUS_VALID) {
       echo '<div class="row no-gutters">';
       echo '<div class="bw-head col-md-12">';
       echo '<h2 class="bw-title">'.$user->status_str().'</h2>';
       echo '</div>';
       echo '</div>';
   }
?>

<div class="row no-gutters">
    <div class="bw-head col-12">
        <h2 class="bw-title">Earnings & Statistics</h2>
    </div>
</div>

<div class="row bw-inner-wrapper">
    <div class="col-12">
        <?php ByWire::view( '_earnings' ) ;?>
    </div>

    <div class="col-12 mt-5 bw-head">
        <h2 class="bw-title"><?php echo esc_html__("Earnings per article", "bywire"); ?></h2>
    </div>
    <div class="col-12 mb-5">
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="eqsa_filter" method="get">

            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <input type="hidden" name="_wp_http_referer" value="">
            <!-- Search Title -->
            <?php $earnings_table->search_box( esc_html__( 'Search', ByWire::ENV ), 'earnings_table_search' ); ?>

            <!-- Now we can render the completed list table -->
            <?php $earnings_table->display(); ?>

        </form>

    </div>
</div>
