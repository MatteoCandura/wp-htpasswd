<?php
    defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
    $options = mc_wph_get_options();
?>
<div class="wrap">
	<h1><?php _e('WP htpasswd settings', 'wp-htpasswd'); ?></h1>
	<div class="card">
		<form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="wph-edit-settings" autocomplete="off">
			<table class="form-table">
				<tbody>
				<tr>
					<th>
						<label class="wph-labelfield"><?php _e('Username', 'wp-htpasswd'); ?></label>
					</th>
					<td>
						<input type="text" name="wph_user" class="wph-textfield regular-text" placeholder="<?php _e('Username', 'wp-htpasswd'); ?>" value="<?php echo $options->user; ?>">
					</td>
				</tr>
				<tr>
					<th>
						<label class="wph-labelfield"><?php _e('Password', 'wp-htpasswd'); ?></label>
					</th>
					<td>
						<input type="password" name="wph_pass" class="wph-textfield regular-text" placeholder="<?php _e('Password', 'wp-htpasswd'); ?>" value="<?php echo $options->pass; ?>" autocomplete="new-password">
					</td>
				</tr>
				<tr>
					<th>
						<label class="wph-labelfield"><?php _e('Enable', 'wp-htpasswd'); ?></label>
					</th>
					<td>
						<label for="wph_enable">
							<input type="checkbox" value="1" name="wph_enable" class="wph-textfield regular-text" <?php checked(1, $options->enable, true); ?>>
							<span class="description"><?php _e('Check me for enable site protection.', 'wp-htpasswd'); ?></span>
						</label>
					</td>
				</tr>
				</tbody>
			</table>
			<?php wp_nonce_field( mc_wph_get_nonce_string(), '_mc_wph_wpnonce' );?>
			<input type="hidden" name="action" value="mc_wph_update_settings" />
			<?php submit_button(); ?>
		</form>
	</div>
</div>