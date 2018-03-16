<?php
/*
Plugin Name: WP htpasswd
Plugin URI: https://github.com/MatteoCandura/wp-htpasswd
Description: Implement htpasswd block easily
Version: 1.5
Author: Matteo Candura
Author URI: https://www.linkedin.com/in/macandura/
Text Domain: wp-htpasswd
License: GPL2
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Create plugin admin page
 */
function mc_wph_register_admin_page()
{
	add_menu_page(
		'WP htpasswd settings',
		'WP htpasswd',
		'manage_options',
		'wp-htpasswd',
		function(){
			include_once plugin_dir_path( __FILE__ ) . "admin.php";
		},
		'dashicons-shield-alt',
		75
	);
}
add_action('admin_menu', 'mc_wph_register_admin_page');

/**
 * Redirect to plugin page after activations
 *
 * @param $plugin
 */
function mc_wph_redirect_after_activation( $plugin ) {
	if( $plugin == plugin_basename( __FILE__ ) ) {
		wp_redirect( admin_url('admin.php?page=wp-htpasswd') );
		exit();
	}
}
add_action( 'activated_plugin', 'mc_wph_redirect_after_activation' );


/**
 * Add plugin scripts to head
 */
function mc_wph_admin_head_scripts()
{
	wp_enqueue_style('wph', plugins_url('css/style.css', __FILE__), array(), null);
	wp_enqueue_script('wph', plugins_url('js/scripts.js', __FILE__), array(), '1.0', true);
}
add_action( 'admin_enqueue_scripts', 'mc_wph_admin_head_scripts' );

/**
 * Encrypt password for .htpasswd
 *
 * @param $string
 *
 * @return string
 */
function mc_wph_encrypt_password($string)
{
	$string = sanitize_text_field($string);
	return crypt($string, base64_encode($string));
}

/**
 * Create dynamic nonce string
 *
 * @return string
 */
function mc_wph_get_nonce_string()
{
	return substr(sha1('wp-htpasswd'), 0, 5) . '-'.date("Ymd");
}

/**
 * Redirect to plugin page with notice
 *
 * @param $message
 * @param string $type
 */
function mc_wph_redirect_with_message($message, $type = 'warning')
{
    $message = urlencode($message);
	$url = "admin.php?page=wp-htpasswd&notice-message=$message&notice-type=$type";
	wp_redirect( admin_url( $url ) );
	exit();
}

/**
 * Load plugin options
 *
 * @return object
 */
function mc_wph_get_options()
{
	/**
	 * Load options
	 */
	$options = get_option('wph_options');

	/**
	 * Set defaults
	 */
	$defaults =  array( "user" => '', "pass" => '',  "enable" => 0, "permissions" => 0 );

	/**
	 * Merge defaults and saved if exists
     * use defaults if not exist
	 */
	if( $options ):

		/**
		 * Unserialize options and cast to array
		 */
        $options = (array) unserialize( $options );


		/**
		 * Foreach options set default value
         * if key not exist in saved options
		 */
		foreach ($defaults AS $key => $value ):
			if( !key_exists($key, $options) ):
				$options[$key] = $value;
			endif;
		endforeach;

		/**
		 * Return options
		 */
		return (object) $options;
	else:

		/**
		 * Return defaults
		 */
		return (object) $defaults;

	endif;
}

/**
 * Save plugin settings
 */
function mc_wph_update_settings()
{
	/**
	 * If user can't manage_options return
	 */
	if( !current_user_can('manage_options') )
		wp_die( __('Access denied for this page.', 'wp-htpasswd') );

	/**
	 * If nonce is invalid
	 */
	if( !isset( $_POST['_mc_wph_wpnonce'] ) || !wp_verify_nonce( $_POST['_mc_wph_wpnonce'], mc_wph_get_nonce_string() ) ):
		mc_wph_redirect_with_message( __("Missing params", 'wp-htpasswd') );
		exit();
	endif;

	/**
	 * Check if can edit htaccess and htpasswd,
     * then set permissions to 644
	 */
	if( !is_writable(ABSPATH.'.htaccess') || !is_writable(ABSPATH.'.htpasswd') ):

		chmod(ABSPATH.'.htaccess', 0644);

        if( file_exists( ABSPATH.'.htpasswd' ) ):
            chmod(ABSPATH.'.htpasswd', 0644);
        endif;

    endif;

	/**
	 * Processing variables
	 */
	$user = sanitize_text_field($_POST['wph_user']);
	$pass = sanitize_text_field( $_POST['wph_pass'] );
	$enable = key_exists('wph_enable', $_POST) && (int)$_POST['wph_enable'] === 1;
	$chmod = key_exists('wph_permissions', $_POST) && (int)$_POST['wph_permissions'] === 1;
	$htaccess_content= '';

	/**
	 * Setting options var
	 */
	$options = array( "user" => $user, "pass" => $pass );

	/**
	 * If enable and user and pass not empty
     * create script for htaccess and
     * insert user and password in htpassword
	 */
	if( $enable && !empty($user) && !empty($pass) ):
		$htaccess_content = 'AuthName "'. get_bloginfo('name') . ' - '. __('Restricted area', 'wp-htpasswd') .'"'."\n";
		$htaccess_content.= 'AuthType Basic'."\n";
		$htaccess_content.= 'AuthUserFile '.ABSPATH.'.htpasswd'."\n";
		$htaccess_content.= 'Require valid-user';
		file_put_contents(ABSPATH.'.htpasswd', "$user:" . mc_wph_encrypt_password( $pass ));
		$options['enable'] = true;
	endif;

	/**
	 * Edit htaccess file
	 */
	insert_with_markers(ABSPATH.'.htaccess', 'WP HTPASSWD', $htaccess_content);

	/**
	 * If settings permission enable change
	 * htaccess and htpasswd permissions to 444
	 */
	if( $chmod ):
		chmod(ABSPATH.'.htaccess', 0444);
		chmod(ABSPATH.'.htpasswd', 0444);
		$options['permissions'] = true;
	endif;

	/**
	 * Update plugin options
	 */
	update_option("wph_options", serialize($options));

	/**
	 * Redirect with message and exit
	 */
	mc_wph_redirect_with_message( __("Settings edited with success", 'wp-htpasswd'), "success" );
	exit();
}
add_action('admin_post_mc_wph_update_settings', 'mc_wph_update_settings' );

/**
 * Display custom admin notice
 *
 * @return bool
 */
function mc_wph_admin_notice()
{
	$screen = get_current_screen();
	if ( $screen->parent_base !== 'wp-htpasswd' ) return false;
	if( isset($_GET['notice-message']) ):
        $text = urldecode($_GET['notice-message']);
        $notice_type = urldecode($_GET['notice-type']);
?>
    <div class="notice notice-<?php echo $notice_type; ?> is-dismissible">
        <p><?php echo $text; ?></p>
    </div>
    <script>history.pushState(null, "", window.location.pathname+'?page=<?php echo $_GET["page"]; ?>');</script>
<?php
    endif;

	/**
	 * Check for htpasswd and htaccess permissions
	 */
    $htaccess_editable = is_writable( ABSPATH.'.htaccess' );
    $htpasswd_editable = is_writable( ABSPATH.'.htpasswd' );

	/**
	 * Show notice if htpasswd or htaccess is not writable
	 */
	if( !$htaccess_editable || ! $htpasswd_editable): ?>
        <div class="notice notice-warning">
            <p>
            <?php
            /**
             * Setting notice text
             */
            if( !$htaccess_editable && $htpasswd_editable ):
                _e(".htaccess permissions are 444.", 'wp-htpasswd');
            elseif ( $htaccess_editable && !$htpasswd_editable ):
                _e(".htpasswd permissions are 444.", 'wp-htpasswd');
            else:
                _e(".htpasswd and .htpasswd permissions are 444.", 'wp-htpasswd');
                $options = mc_wph_get_options();
                $options->permissions = true;
                update_option('wph_options', serialize( $options ) );
            endif;
            ?>
            </p>
        </div>
<?php
	endif;
}
add_action('admin_notices', 'mc_wph_admin_notice');

