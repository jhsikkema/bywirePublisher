<?php
/* Copyright Sikkema Software B.V. 2020. 
*  The copyright holder is cannot be held liable for any damages
*  caused by this program or for it's proper functioning.
*  Copying or modifying the code is not permitted without express
*  written consent from the copyright holder.
*/



class Util {
    public static function format_number($amount, $show_currency=true, $nr_digits=2) {
    	$text = "";
    	//$text = $text.(($in_table) ? '<div class="col-2">' : '');
        $text = $text.(($show_currency) ? '<img src="'.esc_url( BYWIRE__PLUGIN_URL.'assets/image/database-icon.png' ).'" class="img-fluid bw-db-icon pr-2 mr-4" alt="">' : "");
	//$text = $text.(($in_table) ? '</div><div class="col-4 text-right">' : '');
	$text = $text.number_format($amount, $nr_digits);
	//$text = $text.(($in_table) ? '</div>' : '');
 	return $text;
    }


    static function gutenberg_is_active() {
        // Gutenberg plugin is installed and activated.
        $gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

	// Block editor since 5.0.
    	$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

    	if ( ! $gutenberg && ! $block_editor ) {
           return false;
    	}

    	if ( ByWireAdmin::classic_editor_is_active() ) {
           $editor_option       = get_option( 'classic-editor-replace' );
           $block_editor_active = array( 'no-replace', 'block' );
	       
           return in_array( $editor_option, $block_editor_active, true );
    	}

	return true;
      }

      /**
       * Check if Classic Editor plugin is active.
       *
       * @return bool
       */
      static function classic_editor_is_active() {
    	 if ( ! function_exists( 'is_plugin_active' ) ) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
         }

         if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
                return true;
         }

         return false;
      }

}




