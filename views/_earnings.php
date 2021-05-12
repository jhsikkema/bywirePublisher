<?php
$user = ByWireUser::instance();
if (!$user->is_connected()) {
   return;
}

$report  = (!isset($report)) ? ByWireAPI::publisher_report() : $report;
$earnings = $report->read_amount+$report->tip_amount - $report->publish_amount;

$account = EarningsTable::$data;
   
if (!($account->success > 0)) {
   return;
}
?>

<div class="stat-panel my-5 table-responsive">
<div class="row">
   <div class="col-md-3">
       <div class="row">
       <div class="col-md-12 text-center card m-1 p-0 bw-wirebit-light">
       <div class="card-body">
         <h4 class="card-title"><?php echo Util::format_number($report->read_amount, true, 0); ?></h4>
         <p class="card-text"><h5><?php echo esc_attr_e( 'Earnings' , 'bywire' ); ?></h5></p>
         <p class="card-text"><h5><?php echo esc_attr_e( 'Total Read' , 'bywire' ); ?></h5></p>
       </div>
       </div>
       </div>
   </div>
   
   <div class="col-md-3">
       <div class="row">
       <div class="col-md-12 text-center card m-1 p-0 bw-wirebit-light">
       <div class="card-body">
         <h4 class="card-title"><?php echo Util::format_number($report->publish_amount, true, 0); ?></h4>
         <p class="card-text"><h5><?php echo esc_attr_e( 'Expenses' , 'bywire' ); ?></h5></p>
         <p class="card-text"><h5><?php echo esc_attr_e( 'Published' , 'bywire' ); ?></h5></p>
       </div>
       </div>
       </div>
   </div>

   <div class="col-md-3">
       <div class="row">
       <div class="col-md-12 text-center card m-1 p-0 bw-wirebit-light">
       <div class="card-body">
         <h4 class="card-title bw-static-item"><?php echo Util::format_number($report->tip_amount, true, 0); ?></h4>
         <p class="card-text bw-static-item"><h5><?php echo esc_attr_e( 'Earnings' , 'bywire' ); ?></h5></p>
         <p class="card-text bw-static-item"><h5><?php echo esc_attr_e( 'Total Tipped' , 'bywire' ); ?></h5></p>
       </div>
       </div>
       </div>
   </div>

   <div class="col-md-3">
       <div class="row">
       <div class="col-md-12 text-center card m-1 p-0 bw-wirebit-light">
       <div class="card-body">
         <h4 class="card-title"><?php echo Util::format_number($earnings, true, 0); ?></h4>
         <p class="card-text"><h5><?php echo esc_attr_e( 'Net' , 'bywire' ); ?></h5></p>
         <p class="card-text"><h5><?php echo esc_attr_e( 'Earnings' , 'bywire' ); ?></h5></p>
      </div>
      </div>
      </div>
  </div>
</div>
</div>
