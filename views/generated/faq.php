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
<!--                        <h2 class="bw-title">Frequently Asked Questions</h2>-->
<!--                    </div>-->
<!--                </div>-->
                <div class="col-12">
		            <?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
?>

<div class="row no-gutters">
    <div class="bw-head col-12">
        <h2 class="bw-title">Frequently Asked Questions and Support</h2>
    </div>
</div>

<div class="row bw-inner-wrapper no-gutters faq-container" style="border: none !important;">
    <div class="col-12">

        <div class="row">
            <div class="col-12 text-center mb-4 mt-5">
                <div class="bw-head">
                    <h2 class="bw-title w-100 text-center">How can we help?</h2>
                </div>
            </div>
            <div class="col-8 offset-2">
                <ul class="accordion-list">
                    <li class="active">
                        <h3>How do I install the plugin?</h3>
                        <div class="accordion-card-body p-3">
                            <p>If you are here you already managed to install the plugin. If you got any errors during installation we would very much like to hear about it and you can contact the development team at jetze@bywire.news</p>
                        </div>
                    </li>
                    <li>
                        <h3>Troubleshoot you connection to the Bywire Network</h3>
                        <div class="accordion-card-body p-3">
                            <p>Since we are in active development small outages might still occur. Please try to reconnect again after 1 minute or more. If this is not working have a quick look at <a href="https://bywire.news">bywire.news</a>. If our webpage loads fine please check the email with your publisher credentials. For security reasons the plugin just mentions if there is a problem with either the username or password. However we are happy to help if these obvious fixes are of no use, please contact the development team at jetze@bywire.news.</p>
                        </div>
                    </li>
                    <li>
                        <h3>My articles are not published to the blockchain</h3>
                        <div class="accordion-card-body p-3">
                            <p>writing it now</p>
                        </div>
                    </li>
                    <li>
                        <h3>How do I get wirebits</h3>
                        <div class="accordion-card-body p-3">
                            <p>We would like to make sure early adopters are well provided we provide enough wirebits to publish. If we forgot to deposit enough wirebits or you need more feel free to contact our development team at jetze@bywire.news and we will make sure enough wirebits are provided to you.</p>
                        </div>
                    </li>
                    <li>
                        <h3>Next Problem</h3>
                        <div class="accordion-card-body p-3">
                            <p>writing it now</p>
                        </div>
                    </li>
                </ul>
                <div class="bw-btn-wrapper mb-5">
                    <a href="mailto:info@bywire.news?subject=Plugin" class="bw-btn-dark">Contact Us</a>
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
