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
<!--                        <h2 class="bw-title">Bywire Rewards</h2>-->
<!--                    </div>-->
<!--                </div>-->
                <div class="col-12">
		            <?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
require_once(BYWIRE__PLUGIN_DIR . "class.util.php");

$stakes = ByWireAPI::stakes();


$report = ByWireAPI::publisher_report();

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


$transfer_msg     = "";
$transfer_amount  = 0;
$transfer_success = FALSE;
if (isset($_POST["amount"])) {
    $amount           = $_POST["amount"];
    $to               = $_POST["eos-wallet"];
    $msg              = ByWireAPI::transfer($amount, $to);
    $transfer_success = $msg->success;
    $transfer_msg     = ($transfer_success) ? "Transfer Successful" : "An error has occurred (".$msg->message.")";
    $transfer_amount  = ($transfer_success) ? floatval($amount) : 0;
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
        <h2 class="bw-title">Wirebit Rewards Centre</h2>

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
                        <?php bywire_include(BYWIRE__PLUGIN_DIR . "views/components/bar-chart-neg.php", [
                            'bar_chart_data' => $monthly_data
                        ]); ?>
                    </div>
                    <div role="tabs" id="week">
                        <?php bywire_include(BYWIRE__PLUGIN_DIR . "views/components/bar-chart-neg.php", [
                            'bar_chart_data' => $weekly_data
                        ]); ?>
                    </div>
                    <div role="tabs" id="day">
                        <?php bywire_include(BYWIRE__PLUGIN_DIR . "views/components/bar-chart-neg.php", [
                            'bar_chart_data' => $daily_data
                        ]); ?>
                    </div>
                </div>

            </div>
        </div>

        <div class="row">
                <div class="row no-internal-gutters mb-5">
                    <div class="col-md-6 table-responsive">
                        <div class="row bw-wirebit-light p-4" style="height: 100%;">
                            <div class="col-12 pt-4 pb-4">
                                <p class="bw-wirebit-item-title">Total Wirebit Bal</p>
                                <h4 class="text-center" style="font-size: 2.1em; font-weight: 800;"><?php echo Util::format_number($report->balance-$transfer_amount); ?></h4>

                            </div>

                            <div class="col-6 mb-2">
                                <label>Staked</label>
                            </div>
			    <div class="col-6 mb-2">
			        <?php echo Util::format_number(0.0); ?>
			    </div>

                            <div class="col-6 mb-2">
                                <label>Processing</label>
                            </div>
			    <div class="col-6 mb-2">
			        <?php echo Util::format_number($report->processing); ?>
                            </div>

                            <div class="col-6 mb-2">
                                <label>Tipped Rewards</label>
                            </div>
			    <div class="col-6 mb-2">
			        <?php echo Util::format_number($report->tip_amount); ?>
                            </div>

                            <div class="col-6 mb-2">
                                <label>Reads Rewards</label>
                            </div>
			    <div class="col-6 mb-2">
			        <?php echo Util::format_number($report->read_amount); ?>
                            </div>


                            <div class="col-6 mb-2">
                                <label>Publish Expenses</label>
                            </div>
			    <div class="col-6 mb-2">
			        <?php echo Util::format_number($report->publish_amount); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 border-top border-bottom border-right p-4 table-response">
		      <form class="col-12 bw-wirebit-atm-form " action="<?php menu_page_url(ByWire::ENV.'-bywire-rewards');?>" method="post">

                        <div class="row">
                            <div class="col-12 pt-4 pb-4">
                                <p class="bw-wirebit-item-title">Wirebit ATM</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 pt-4 pb-4">
			    </div>
                            <div class="col-md-8">
                                <label><?php echo $transfer_msg ?></label>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Account Name</label>
			    </div>
                            <div class="col-md-8">
                                <p class="d-inline-block float-right">
                                    <large><a href="<?php echo ByWireAPI::wallet_url($stakes->wallet); ?>" target="_blank"><?php echo $stakes->wallet; ?></a></large>
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Send to</label>
			    </div>
                            <div class="col-md-8">
                                <span class="d-inline-block float-right">
                                    <input type="text" class="form-input form-input" placeholder="EOS Address Name" name="eos-wallet" id="eos-wallet" />
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Memo</label>
			    </div>
                            <div class="col-md-8">
                                <span class="d-inline-block float-right">
                                    <input type="text" class="form-input form-input" placeholder="recommended (beneficiary)" name="memo" id="memo" />
                                </span>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label>Amount</label>
			    </div>
                            <div class="col-md-8">
                                <span class="d-inline-block float-right">
                                    <input type="text" class="form-input" placeholder="0.00" name="amount" id="amount" />
                                </span>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-10 offset-1">
                                <button type="submit" class="bw-btn-succes w-100">SEND</button>
                            </div>
                        </div>

                        <div class="row wirebit-atm-table text-muted mt-0 pt-0">
                            <div class="col-md-6">
                                <span class="pb-0 mb-0"><small>EOS Network Fee</small></span>
                            </div>
                            <div class="col-md-6 text-right">
                                <span class="pb-0 mb-0"><small>0.0 WIRE</small></span>
                            </div>
                            <div class="col-md-6">
                                <span class="pb-0 mb-0"><small>Available Balance</small></span>
                            </div>
                            <div class="col-md-6 text-right">
                                <span class="pb-0 mb-0"><small>0.0 WIRE</small></span>
                            </div>
                        </div>
                      </form>

                    </div>
                </div>

        </div>




    </div>
</div>

                </div>
	        </div>
        </container>
   </section>
</main>
