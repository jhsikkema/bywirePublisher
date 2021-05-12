<header class="bw-header">
    <div class="container">
        <div class="row align-items-center justify-content-between">
            <div class="bw-header-left col-8">
                <div class="bw-header-logo">
                    <a href="<?php echo add_query_arg( array( 'page' => ByWire::ENV.'-postwise-states' ), admin_url( 'admin.php' )  ); ?>">
                        <img src="<?php echo esc_url( BYWIRE__PLUGIN_URL.'assets/image/logo-full-2x.png' ); ?>" class="img-fluid" alt="ByWire Logo" />
                    </a>
                </div>
                <div class="bw-header-text">
                    <p>Blockchain News Network <?php echo isset($page_heading)? "- ".$page_heading:""; ?></p>
                </div> 
            </div>
            <div class="bw-header-right col-4">
                <div class="bw-button-grp text-right">
                    <a href="<?php echo add_query_arg( array( 'page' => ByWire::ENV.'-bywire-dashboard' ), admin_url( 'admin.php' )  ); ?>" class="bw-btn-dark">Dashboard</a>
                    <a href="<?php echo add_query_arg( array( 'page' => ByWire::ENV.'-user-config' ), admin_url( 'admin.php' )  ); ?>" class="bw-btn-light-danger">Settings</a>
                </div>
            </div>
        </div>
    </div>
</header>
<main class="bw-main">
    <section class="bw-faq">
        <div class="container">
            <div class="row">
<!--                <div class="col-12">-->
<!--                    <div class="bw-head">-->
<!--                        <h2 class="bw-title">Latests Articles Published By Partners on the ByWire Network</h2>-->
<!--                    </div>-->
<!--                </div>-->
                <div class="col-12">
		            <?php
//Create an instance of our package class...
//$table = new ArticlesTable();
//Fetch, prepare, sort, and filter our data...
//$table->prepare_items();

$page = isset($_GET["paged"])? $_GET["paged"]: 0;
$per_page = 9;
$data = ByWireAPI::articles($page, $per_page);
$total = 0;
if(isset($data->count)){
    $total = $data->count;
}

?>


<?php
   $user = ByWireUser::instance();
   if ($user->status !== ByWireUser::STATUS_VALID) {
       echo '<div class="row no-gutters">';
       echo '<div class="bw-head col-md-12">';
       echo '<h2 class="bw-title">'.$user->status_str().'</h2>';
       echo '</div>';
       echo '</div>';
   }
?>

<div class="row no-gutters">
    <div class="bw-head col-12">
        <h2 class="bw-title">We Have Some News For You. Latest Stories from the Bywire News Network</h2>
    </div>
</div>

<div class="row bw-inner-wrapper">
    <div class="col-12 my-5 news-items">

        <div class="row">
            <?php
            if(isset($data->data) && count($data->data) > 0): ?>
                <?php foreach($data->data as $key => $article): ?>
                    <?php bywire_include(BYWIRE__PLUGIN_DIR . 'views/components/news-item.php', ['article' => $article]); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="col-md-12 mb-0 mt-5 append_news_ajax_data">
            <div class="bw-btn-wrapper">
                <input type="hidden" id="total_news" value="<?php echo $total; ?>">
                <input type="hidden" id="paged" value="<?php echo $page; ?>">
                <a href="#" class="btn bw-btn-dark bw-news-loadmore">Load More</a>
            </div>
        </div>
    </div>
</div>

                </div>
	        </div>
        </container>
   </section>
</main>
