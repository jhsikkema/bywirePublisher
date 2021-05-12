<?php
/**
 * @package ByWire
 */
/* Copyright Sikkema Software B.V. 2020. 
*  The copyright holder is cannot be held liable for any damages
*  caused by this program or for it's proper functioning.
*  Copying or modifying the code is not permitted without express
*  written consent from the copyright holder.
*/

class ByWire_Widget extends WP_Widget {

	function __construct() {
		load_plugin_textdomain( 'bywire' );
		
		parent::__construct(
			'bywire_widget',
			__( 'ByWire Widget' , 'bywire'),
			array( 'description' => __( 'Publish to the ByWire BlockChain' , 'bywire') )
		);
	}


	function form( $instance ) {
		if ( $instance && isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}
		else {
			$title = __( 'Spam Blocked' , 'bywire' );
		}
?>

		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:' , 'bywire'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function widget( $args, $instance ) {
		$count = get_option( 'bywire_publish_count' );

		if ( ! isset( $instance['title'] ) ) {
			$instance['title'] = __( 'Spam Blocked' , 'bywire' );
		}

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo esc_html( $instance['title'] );
			echo $args['after_title'];
		}
?>

	<div class="a-stats">
		<a href="https://bywire.news" target="_blank" title=""><?php printf( _n( '<strong class="count">%1$s spam</strong> published by <strong>ByWire</strong>', '<strong class="count">%1$s spam</strong> published by <strong>ByWire</strong>', $count , 'bywire'), number_format_i18n( $count ) ); ?></a>
	</div>

<?php
		echo $args['after_widget'];
	}
}

function bywire_register_widgets() {
	register_widget( 'ByWire_Widget' );
}

add_action( 'widgets_init', 'bywire_register_widgets' );
