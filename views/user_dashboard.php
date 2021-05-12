<?php
$config = ByWireConfig::get();
?>
<div class="bywire-box">
    <?php ByWire::view( '_header' );?>
    <h1><?php echo esc_attr_e( 'Create Account with ByWire' , 'bywire' ); ?></h1>
    <form action="" method="post">
	<?php wp_nonce_field( ByWireAdmin::NONCE ); ?>

	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="bywire_username"><?php echo esc_attr_e( 'Username' , 'bywire' ); ?></label></th>
				<td class="forminp forminp-text">
					<input type="text" name="bywire_username" id="bywire_username" value="" placeholder="Enter username" />
				</td>
			</tr>

			<tr>
			<th scope="row"><label for="bywire_password"><?php echo esc_attr_e( 'Password' , 'bywire' ); ?></label></th>
				<td class="forminp forminp-text">
					<input type="password" name="bywire_password" id="bywire_password" value="" placeholder="******" />
				</td>
			</tr>

		</tbody>
	</table>
	<p class="bywire-config-field">
		<input type="submit" name="submit" id="submit" class="bywire-button button button-primary" value="<?php esc_attr_e( 'Create Account', 'bywire' );?>">
	</p>
</form>
</div>