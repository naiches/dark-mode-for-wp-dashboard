<?php
/**
 * Plugin Name: Dark Mode for WP Dashboard
 * Plugin URI: https://wordpress.org/plugins/dark-mode-for-wp-dashboard/
 * Description: Enable dark mode for the WordPress dashboard
 * Author: Naiche
 * Author URI: https://profiles.wordpress.org/naiches/
 * Text Domain: dark-mode-dashboard
 * Version: 1.2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    die();
}
define('DARK_MODE_DASHBOARD_VERSION', '1.2.3');
define('DARK_MODE_DASHBOARD_PLUGIN_PATH', plugin_dir_url(__FILE__));



/**
* Add styles
*/
function dark_mode_dashboard_add_styles() {
    /**
    * Check if dark mode is disable for the current user
    */
    if(wp_get_current_user()->dark_mode_dashboard != 1) {
        $dark_mode_dashboard_style = apply_filters( 'dark_mode_dashboard_css', DARK_MODE_DASHBOARD_PLUGIN_PATH . '/assets/css/dark-mode-dashboard.css' );
        wp_register_style( 'dark-mode-dashboard', $dark_mode_dashboard_style, array(), DARK_MODE_DASHBOARD_VERSION );
        wp_enqueue_style( 'dark-mode-dashboard');
    }
}

add_action( 'admin_enqueue_scripts', 'dark_mode_dashboard_add_styles' );



/**
* Add field to user profile page
*/
add_action( 'show_user_profile', 'dark_mode_dashboard_user_profile_fields' );
add_action( 'edit_user_profile', 'dark_mode_dashboard_user_profile_fields' );

function dark_mode_dashboard_user_profile_fields( $user ) { ?>
    <h3><?php _e("Dark Mode for WP Dashboard", "blank"); ?></h3>

    <table class="form-table">
        <tr>
            <th><label for="darkmode"><?php _e("Disable darkmode?"); ?></label></th>
            <td>
                <input type="checkbox" name="dark_mode_dashboard" id="darkmode" value="1" <?php checked($user->dark_mode_dashboard, true, true); ?>>
            </td>
        </tr>
    </table>
<?php }



/**
* Save data from user profile field to database
*/
add_action( 'personal_options_update', 'dark_mode_dashboard_save_user_profile_fields' );
add_action( 'edit_user_profile_update', 'dark_mode_dashboard_save_user_profile_fields' );

function dark_mode_dashboard_save_user_profile_fields( $user_id ) {
    if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update-user_' . $user_id ) ) {
        return;
    }
    
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
    }

    update_user_meta( $user_id, 'dark_mode_dashboard', $_POST['dark_mode_dashboard'] );
}



/**
* Admin toolbar add toggle
*/
function dark_mode_dashboard_toolbar_link($wp_admin_bar) {
    $args = array(
        'id' => 'dark-mode-dashboard',
        'title' => 'Dark Mode Toggle',
        'href' => '#',
        'meta' => array(
            'class' => 'dark-mode-dashboard', 
            'title' => 'Dark Mode Toggle'
        )
    );
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'dark_mode_dashboard_toolbar_link', 999);



/**
* Admin toolbar toggle, trigger the ajax handler function using jQuery
*/
add_action( 'admin_footer', 'dark_mode_dashboard_toolbar_change_js' );
function dark_mode_dashboard_toolbar_change_js() { ?>
  <script type="text/javascript" >
    jQuery("li#wp-admin-bar-dark-mode-dashboard .ab-item").on( "click", function() {
        var data = {'action': 'dark_mode_dashboard_change_user_profile_mode',};

        /* since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php */
        jQuery.post(ajaxurl, data, function(response){
            document.location.reload(true);
        });
    });
  </script>
  <style>
    #wpadminbar #wp-admin-bar-dark-mode-dashboard .ab-item:before {
        content: "\f339";
        top: 2px;
    }
  </style> <?php
}



/**
* Admin toolbar toggle, hook and define ajax handler function
*/
add_action( 'wp_ajax_dark_mode_dashboard_change_user_profile_mode', 'dark_mode_dashboard_change_user_profile_mode' );
function dark_mode_dashboard_change_user_profile_mode() {
    global $wpdb; // this is how you get access to the database

    $user_id = get_current_user_id();

    if ( !current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
    }

    if(get_user_meta($user_id, 'dark_mode_dashboard', true) == 1) {
        update_user_meta( $user_id, 'dark_mode_dashboard', '' );
    } else {
        update_user_meta( $user_id, 'dark_mode_dashboard', 1 );
    }

    wp_die(); // this is required to terminate immediately and return a proper response
}
