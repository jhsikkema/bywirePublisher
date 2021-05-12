<?php
/* Copyright Sikkema Software B.V. 2020. 
*  The copyright holder is cannot be held liable for any damages
*  caused by this program or for it's proper functioning.
*  Copying or modifying the code is not permitted without express
*  written consent from the copyright holder.
*/a
WP_CLI::add_command( 'bywire', 'ByWire_CLI' );

/**
 * ByWire 
 */
class ByWire_CLI extends WP_CLI_Command {
	/**
	 *
	 */
	public function check( $args, $assoc_args ) {
	}
}