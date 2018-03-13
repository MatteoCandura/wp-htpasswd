<?php
/*
Plugin Name: WP Htpasswd
Plugin URI: https://github.com/MatteoCandura/wp-htpasswd
Description: Implement htpasswd block easily
Version: 1.0
Author: Matteo Candura
Author URI: http://metteocandura.com
Text Domain: wp-htpasswd
License: GPL2
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function mc_wph_register_admin_page(){
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

function mc_wph_encrypt_password($string){
	$string = sanitize_text_field($string);
	return crypt($string, base64_encode($string));
}
function mc_wph_get_nonce_string(){
	return substr(sha1('wp-htpasswd'), 0, 5) . '-'.date("Ymd");
}
function mc_wph_get_options(){
    $options = get_option('wph_options');
	$defaults =  array( "user" => '', "pass" => '',  "enable" => 0 );
    if($options):
        $options = unserialize( $options );
        foreach ($defaults AS $key => $value ):
	        if( !key_exists($key, $options) ):
		        $options[$key] = $value;
            endif;
        endforeach;
        return (object)$options;
    else:
        return (object)$defaults;
    endif;
}


add_action('admin_post_mc_wph_update_settings', 'mc_wph_update_settings' );
function mc_wph_update_settings(){
	if( !current_user_can('manage_options') )
	    wp_die( __('Access denied for this page.', 'wp-htpasswd') );

	if( !isset( $_POST['_mc_wph_wpnonce'] ) || !wp_verify_nonce( $_POST['_mc_wph_wpnonce'], mc_wph_get_nonce_string() ) ):
		$url = $_POST["_wp_http_referer"] . '&amp;notice-message=' . urlencode( __("Missing params", 'wp-htpasswd') ) . '&amp;notice-type=warning';
		wp_redirect( $url );
	endif;

	$user = sanitize_text_field($_POST['wph_user']);
	$pass = sanitize_text_field( $_POST['wph_pass'] );
	$enable = key_exists('wph_enable', $_POST) && (int)$_POST['wph_enable'] === 1;
	$append_htaccess= '';

	$options = array( "user" => $user, "pass" => $pass );

	if( $enable ):
		$append_htaccess = 'AuthName "'. get_bloginfo('name') . ' - '. __('Restricted area', 'wp-htpasswd') .'"'."\n";
		$append_htaccess.= 'AuthType Basic'."\n";
		$append_htaccess.= 'AuthUserFile '.ABSPATH.'.htpasswd'."\n";
		$append_htaccess.= 'Require valid-user';
		file_put_contents(ABSPATH.'.htpasswd', "$user:" . mc_wph_encrypt_password( $pass ));
		$options['enable'] = true;
	endif;

	insert_with_markers(ABSPATH.'.htaccess', 'WP HTPASSWD', $append_htaccess);

	delete_option( "wph_options" );
	add_option( "wph_options", serialize($options) );

	$url = get_bloginfo('url').$_POST["_wp_http_referer"];
	$url.= '&amp;notice-message=' . urlencode( __("Settings edited with success", 'wp-htpasswd') ) . '&amp;notice-type=success';
	echo "<script>window.location.href='$url';</script>";
	exit();
}

// display custom admin notice
function mc_wph_admin_notice(){
	$screen = get_current_screen();
	if ( $screen->parent_base !== 'wp-htpasswd' || !isset($_GET['notice-message']) ) return false;
	$text = urldecode($_GET['notice-message']);
	$notice_type = urldecode($_GET['notice-type']);
?>
	<div class="notice notice-<?php echo $notice_type; ?> is-dismissible">
		<p><?php echo $text; ?></p>
	</div>
	<script>history.pushState(null, "", window.location.pathname+'?page=<?php echo $_GET["page"]; ?>');</script>
<?php
}
add_action('admin_notices', 'mc_wph_admin_notice');

