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
