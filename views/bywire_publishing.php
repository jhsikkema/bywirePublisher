<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
$data = ByWireAPI::account();


if (!ByWireUser::instance()->is_connected()) {
   return;
}

require_once(BYWIRE__PLUGIN_DIR . "models/posts_table.php");
//Create an instance of our package class...
$published_table = new PostsTable();
        
//Fetch, prepare, sort, and filter our data...
$published_table->prepare_items();



function formatBalance($tag, $amount) {
    echo '<tr>';
    echo '<td class="text-left">'.$tag.'</td>';
    echo '<td class="text-right wirebitRight"><large><img src="'.esc_url( BYWIRE__PLUGIN_URL.'assets/image/database-icon.png').'" class="img-fluid bw-db-icon" style="height: 25px;" alt="">  '.$amount.'</large></td>';
    echo '</tr>';
}
$report = ByWireAPI::publisher_report();

$monthly_data   = array();
$weekly_data   = array();
$daily_data   = array();
if (isset($report->data)) {
    foreach(get_object_vars($report->data->month) as $key=>$value) {
        preg_match("/^([A-Za-z]+)\-20([0-9]+)$/i", $key, $matches);
        $key = (count($matches) > 0) ? $matches[1]." ".$matches[2] : $key;
        $monthly_data[$key] = $value->publish_nr;
    }
    foreach(get_object_vars($report->data->week) as $key=>$value) {
        preg_match("/^([A-Za-z]+)\-[0-9]+$/i", $key, $matches);
        $key = (count($matches) > 0) ? $matches[1] : $key;
        $weekly_data[$key] = $value->publish_nr;
    }
    foreach(get_object_vars($report->data->day) as $key=>$value) {
        preg_match("/^([0-9]+)\-[0-9]+$/i", $key, $matches);
        $key = (count($matches) > 0) ? $matches[1] : $key;
        $daily_data[$key] = $value->publish_nr;
    }
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
        <h2 class="bw-title">Wirebit Publishing Centre</h2>

        <ul class="list-unstyled list-inline bw-head-list" role="tabs">
            <li><a href="#day" data-tab="day">Day</a></li>
            <li><a href="#week" data-tab="week">Week</a></li>
            <li><a href="#month" data-tab="month">Month</a></li>
        </ul>
    </div>
</div>

<div class="row bw-inner-wrapper">
  <div class="col-12">
    <div class="row mb-5 no-gutters">
      <div class="col-12">
        <div role="tablist">
          <div class="show" role="tabs" id="month">
             <?php bywire_include(BYWIRE__PLUGIN_DIR . "views/components/bar-chart.php", [ 'bar_chart_data' => $monthly_data ]); ?>
          </div>
          <div role="tabs" id="week">
             <?php bywire_include(BYWIRE__PLUGIN_DIR . "views/components/bar-chart.php", [ 'bar_chart_data' => $weekly_data ]); ?>
          </div>
          <div role="tabs" id="day">
             <?php bywire_include(BYWIRE__PLUGIN_DIR . "views/components/bar-chart.php", [ 'bar_chart_data' => $daily_data ]); ?>
          </div>
        </div>
      </div>
    </div>
  </div>

    <div class="bw-head col-12">
        <h2 class="bw-title">Published title</h2>
    </div>
    <div class="col-12 mb-5">
        <div class="form-group row no-gutters">
            <?php $published_table->display(); ?>
        </div>
    </div>
</div>


