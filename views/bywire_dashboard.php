<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
require_once(BYWIRE__PLUGIN_DIR . "class.util.php");

$account = ByWireAPI::account();
$stakes  = ByWireAPI::stakes();

$report = ByWireAPI::publisher_report(true);

$monthly_data   = array();
$weekly_data   = array();
$daily_data   = array();
if (isset($report->data)) {
   foreach(get_object_vars($report->data->month) as $key=>$value) {
       preg_match("/^([A-Za-z]+)\-20([0-9]+)$/i", $key, $matches);
       $key = (count($matches) > 0) ? $matches[1]." ".$matches[2] : $key;
       $monthly_data[$key] = $value->read_amount+$value->tip_amount-$value->publish_amount;
   }
   foreach(get_object_vars($report->data->week) as $key=>$value) {
       preg_match("/^([A-Za-z]+)\-[0-9]+$/i", $key, $matches);
       $key = (count($matches) > 0) ? $matches[1] : $key;
       $weekly_data[$key] = $value->read_amount+$value->tip_amount-$value->publish_amount;
   }
   foreach(get_object_vars($report->data->day) as $key=>$value) {
       preg_match("/^([0-9]+)\-[0-9]+$/i", $key, $matches);
       $key = (count($matches) > 0) ? $matches[1] : $key;
       $daily_data[$key] = $value->read_amount+$value->tip_amount-$value->publish_amount;
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
    <div class="bw-head col-md-12">
        <h2 class="bw-title">Your Blockchain News Statistics</h2>
      	<ul class="list-unstyled list-inline bw-head-list" role="tabs">
            <li><a href="#day" data-tab="day">Day</a></li>
            <li><a href="#week" data-tab="week">Week</a></li>
            <li><a href="#month" data-tab="month">Month</a></li>
        </ul>
    </div>
</div>

<div class="bw-statistic-inner bw-inner-wrapper">
<div class="col-md-12">
  <div class="row mb-5 no-gutters">
    <div class="col-md-12">
      <div role="tablist">
        <div class="show" role="tabs" id="month">
           <?php bywire_include(BYWIRE__PLUGIN_DIR . "views/components/bar-chart-neg.php", [ 'bar_chart_data' => $monthly_data ]); ?>
        </div>
        <div role="tabs" id="week">
           <?php bywire_include(BYWIRE__PLUGIN_DIR . "views/components/bar-chart-neg.php", [ 'bar_chart_data' => $weekly_data ]); ?>
        </div>
        <div role="tabs" id="day">
           <?php bywire_include(BYWIRE__PLUGIN_DIR . "views/components/bar-chart-neg.php", [ 'bar_chart_data' => $daily_data ]); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="bw-statistic-result row no-internal-gutters">
    <div class="col-md-3">
      <div class="bw-statistic-item bw-statistic-read">
        <p class="bw-statistic-item-title">Stories read today</p>
        <h4><?php echo $report->today_read_nr; ?></h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="bw-statistic-item bw-statistic-dark">
        <p class="bw-statistic-item-title">Today's rewards</p>
        <h4><?php echo Util::format_number($report->today_read_amount); ?></h4>
      </div>
    </div>
    <div class="col-md-6">
      <div class="bw-statistic-item bw-statistic-most-read">
        <p class="bw-statistic-item-title">Today's most read</p>
        <div class="row">
          <div class="col-md-8">
            <p><?php echo $report->today_article_title; ?></p>
          </div>
          <div class="col-md-4 text-center">
            <h4><?php echo $report->today_article_nr; ?>
	    <span class="text-muted">Readers</span></h4>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="bw-statistic-result my-0 row no-internal-gutters">
    <div class="col-md-12 my-0">
      <div class="bw-statistic-detail my-0">
        <a href="<?php menu_page_url(ByWire::ENV.'-bywire-rewards');?>"  class="bw-btn-light-danger">Details</a>
      </div>
    </div>
  </div>
</div>
</div>

<script type="text/javascript">
var eos_gbp = "<?php echo Util::format_number($account->amount_gbp, false); ?>";
var eos_usd = "<?php echo Util::format_number($account->amount_usd, false); ?>";
var eos_eur = "<?php echo Util::format_number($account->amount_eur, false); ?>";
var values = {"USD": {"label": "Total USD Balance",
    	              "value": "$ "+eos_usd},
              "GBP": {"label": "Total GBP Balance",
    	              "value": "£ "+eos_gbp},
              "EUR": {"label": "Total EUR Balance",
    	              "value": "€ "+eos_eur}}

function setCurrency(currency) {
    document.getElementById("currency_label").innerHTML = values[currency]["label"];
    document.getElementById("currency_value").innerHTML = values[currency]["value"];
}

</script>

<div class="bw-head">
  <h2 class="bw-title">Your Blockchain News Statistics</h2>
  <ul class="list-unstyled list-inline bw-head-list">
    <li><a href='javascript:setCurrency("GBP");'>£ GBP</a></li>
    <li><a href='javascript:setCurrency("EUR");'>€ EUR</a></li>
    <li><a href='javascript:setCurrency("USD");'>$ USD</a></li>
  </ul>
</div>
<div class="bw-wirebit-wrapper bw-inner-wrapper">
  <div class="bw-wirebit-inner row no-internal-gutters">
    <div class="col-md-6">
      <div class="bw-wirebit-item bw-wirebit-light">
        <p class="bw-wirebit-item-title">Total Wirebit Bal</p>
        <h4><?php echo Util::format_number($account->amount); ?></h4>
        <div class="table-responsive">
          <table class="table" style="width: 100%;">
            <tbody style="width: 100%;">
              <tr>
                <td class="text-left">Staked</td>
                <td class="text-right wirebitRight"><?php echo Util::format_number(0); ?></td>
              </tr>
              <tr>
                <td class="text-left">Processing</td>
                <td class="text-right wirebitRight"><?php echo Util::format_number($report->processing); ?></td>
              </tr>
              <tr>
                <td class="text-left">Tipped Rewards</td>
                <td class="text-right wirebitRight"><?php echo Util::format_number($report->tip_amount); ?></td>
              </tr>
              <tr>
                <td class="text-left">Reads Rewards</td>
                <td class="text-right wirebitRight"><?php echo Util::format_number($report->read_amount); ?></td>
              </tr>
              <tr>
                <td class="text-left">Publish Expenses</td>
                <td class="text-right wirebitRight"><?php echo Util::format_number($report->publish_amount); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="bw-wirebit-item bw-wirebit-dark">
        <p class="bw-wirebit-item-title">Blockchain Account</p>
        <h4><?php echo $stakes->wallet; ?></h4>
        <div class="table-responsive">
          <table class="table" style="width: 100%;">
            <tbody style="width: 100%;">
              <tr>
                <td class="text-left">EOS Balance</td>
                <td class="text-right"><?php echo Util::format_number($stakes->eos, false); ?></td>
              </tr>
              <tr>
                 <td class="text-left" id="currency_label"></td>
                 <td class="text-right" id="currency_value"></td>
               </tr>
               <tr>
                 <td class="text-left">EOS Available</td>
                 <td class="text-right"><?php echo Util::format_number($stakes->eos_free, false); ?></td>
                </tr>
                <tr>
                  <td class="text-left">CPU <span><?php echo Util::format_number($stakes->cpu, false); ?></span></td>
                  <td class="text-right">.NET <span><?php echo Util::format_number($stakes->net, false); ?></span></td>
                </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<p><small><?php echo $account->notice; ?>.</small></p>
<script type="text/javascript">
  setCurrency("GBP");
</script>