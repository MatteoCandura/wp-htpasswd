<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); ?>
<?php $options = mc_wph_get_options(); ?>
<div class="wrap">
	<h1><?php _e('WP htpasswd settings', 'wp-htpasswd'); ?></h1>
	<div class="card">
		<form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="wph-edit-settings" autocomplete="off">
			<table class="form-table">
				<tbody>
				<tr>
					<th>
						<label class="wph-label"><?php _e('Username', 'wp-htpasswd'); ?></label>
					</th>
					<td>
						<input type="text" name="wph_user" class="wph-input regular-text" placeholder="<?php _e('Username', 'wp-htpasswd'); ?>" value="<?php echo $options->user; ?>">
					</td>
				</tr>
				<tr>
					<th>
						<label class="wph-label"><?php _e('Password', 'wp-htpasswd'); ?></label>
					</th>
					<td>
						<input type="password" name="wph_pass" class="wph-input regular-text" placeholder="<?php _e('Password', 'wp-htpasswd'); ?>" value="<?php echo $options->pass; ?>" autocomplete="new-password">
                        <button type="button" id="wph-password-visibility">
                            <div class="dashicons dashicons-visibility"></div>
                        </button>
                    </td>
				</tr>
				<tr>
					<th>
						<label class="wph-label"><?php _e('Enable', 'wp-htpasswd'); ?></label>
					</th>
					<td>
						<label for="wph_enable">
							<input type="checkbox" value="1" name="wph_enable" class="wph-input regular-text" <?php checked(1, $options->enable); ?>>
							<span class="description"><?php _e('Check me for enable htpasswd protection.', 'wp-htpasswd'); ?></span>
						</label>
					</td>
				</tr>
                <tr>
                    <th>
                        <label class="wph-label"><?php _e('Permission rewrite', 'wp-htpasswd'); ?></label>
                    </th>
                    <td>
                        <label for="wph_permissions">
                            <input type="checkbox" value="1" name="wph_permissions" class="wph-input regular-text" <?php checked(1, $options->permissions); ?>>
                            <span class="description"><?php _e('Improve site secuirity and set .htaccess and .htpasswd permissions to 444.', 'wp-htpasswd'); ?></span>
                            <span class="description"><?php _e('<u>Be sure before do that.</u>', 'wp-htpasswd'); ?></span>
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