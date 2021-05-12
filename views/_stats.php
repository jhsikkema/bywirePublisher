<style>
.app-main__inner {
    padding: 30px 30px 0;
    flex: 1;
}
.app-main__inner .row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
}
.app-main__inner .col-xl-4 {
    flex: 0 0 33.33333%;
    max-width: 33.33333%;
	position: relative;
    width: 100%;
    padding-right: 15px;
    padding-left: 15px;
}
.app-main__inner .card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 1px solid rgba(26,54,126,0.125);
    border-radius: .25rem;
}
.app-main__inner .bg-midnight-bloom {
    background-image: linear-gradient(-20deg, #2b5876 0%, #4e4376 100%) !important;
}
.app-main__inner .card {
    box-shadow: 0 0.46875rem 2.1875rem rgba(4,9,20,0.03), 0 0.9375rem 1.40625rem rgba(4,9,20,0.03), 0 0.25rem 0.53125rem rgba(4,9,20,0.05), 0 0.125rem 0.1875rem rgba(4,9,20,0.03);
    border-width: 0;
    transition: all .2s;
}
.app-main__inner .widget-content {
    padding: 1rem;
    flex-direction: row;
    align-items: center;
}
.app-main__inner .card.mb-3 {
    margin-bottom: 30px !important;
}
.app-main__inner .text-white {
    color: #fff !important;
}
.app-main__inner .widget-content .widget-content-wrapper {
    display: flex;
    flex: 1;
    position: relative;
    align-items: center;
}
.app-main__inner .widget-content .widget-content-left .widget-heading {
    opacity: .8;
    font-weight: bold;
}
.widget-content .widget-content-left .widget-subheading {
    opacity: .5;
}
.app-main__inner .widget-content .widget-content-right {
    margin-left: auto;
}
.app-main__inner .widget-content .widget-numbers {
    font-weight: bold;
    font-size: 1.8rem;
    display: block;
}
.app-main__inner .bg-arielle-smile {
    background-image: radial-gradient(circle 248px at center, #16d9e3 0%, #30c7ec 47%, #46aef7 100%) !important;
}
.app-main__inner .bg-grow-early {
    background-image: linear-gradient(to top, #0ba360 0%, #3cba92 100%) !important;
}
.app-main__inner .bg-premium-dark {
    background-image: linear-gradient(to right, #434343 0%, black 100%) !important;
}

</style>

<div class="bywire-stats">
    <?php
	function wire_format($amount) {
	    return number_format($amount, 2);
	}
	
        $user = ByWireUser::get();
        if ($user->is_valid()) {
	     $account_info = ByWireAPI::account();
	     
             echo '<div class="bywire-stats row">';
	     if ($account_info->success > 0) { ?>
			<h1><?php echo esc_html__("Wire Account Balance", "bywire"); ?></h1>
			<div class="app-main__inner">
				<div class="row">
					<div class="col-md-6 col-xl-4">
						<div class="card mb-3 widget-content bg-midnight-bloom">
							<div class="widget-content-wrapper text-white">
								<div class="widget-content-left">
									<div class="widget-heading"><?php echo esc_attr_e( 'Amount' , 'bywire' ); ?></div>
								</div>
								<div class="widget-content-right">
									<div class="widget-numbers text-white"><span><?php echo wire_format($account_info->amount); ?></span></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-xl-4">
						<div class="card mb-3 widget-content bg-arielle-smile">
							<div class="widget-content-wrapper text-white">
								<div class="widget-content-left">
									<div class="widget-heading"><?php echo esc_attr_e( 'Processing' , 'bywire' ); ?></div>
								</div>
								<div class="widget-content-right">
									<div class="widget-numbers text-white"><span><?php echo wire_format($account_info->processing); ?></span></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6 col-xl-4">
						<div class="card mb-3 widget-content bg-grow-early">
							<div class="widget-content-wrapper text-white">
								<div class="widget-content-left">
									<div class="widget-heading"><?php echo esc_attr_e( 'WIRE(GBP)' , 'bywire' ); ?></div>
								</div>
								<div class="widget-content-right">
									<div class="widget-numbers text-white"><span><?php echo wire_format($account_info->bid); ?></span></div>
								</div>
							</div>
						</div>
					</div>
					<div class="d-xl-none d-lg-block col-md-6 col-xl-4">
						<div class="card mb-3 widget-content bg-premium-dark">
							<div class="widget-content-wrapper text-white">
								<div class="widget-content-left">
									<div class="widget-heading"><?php echo esc_attr_e( 'Amount(GBP)' , 'bywire' ); ?></div>
								</div>
								<div class="widget-content-right">
									<div class="widget-numbers text-warning"><span><?php echo wire_format($account_info->amount_gbp); ?></span></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>


	         <?php }
             echo '</div>';
	}
    ?>
</div>
   