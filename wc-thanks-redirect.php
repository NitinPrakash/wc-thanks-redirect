<?php
/**
 *
 * @link              http://www.webcurries.com
 * @since             1.0.0
 * @package           Wc_Thanks_Redirect
 *
 * @wordpress-plugin
 * Plugin Name:       WC Thanks Redirect
 * Plugin URI:        http://www.webcurries.com/plugins/wc-thanks-redirect
 * Description:       WC Thanks Redirect allows to add redirect settings for WooCommerce Products
 * Version:           1.0.0
 * Author:            Nitin Prakash
 * Author URI:        http://www.webcurries.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc_thanks_redirect
 * Domain Path:       /languages
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || die( 'Wordpress Error! Opening plugin file directly' );

/**
 * Check if WooCommerce is active
 **/
if ( !class_exists( 'WooCommerce' ) ) {
   // wp_die('Not Exists');
    //add_action( 'admin_notices', 'install_admin_notice' );
} else{
   // wp_die('Exists');
}

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action( 'admin_notices', 'install_admin_notice' );
}

function install_admin_notice(){
    echo '<div class="notice notice-error">';
    echo     '<p>'. _e( 'WC Thanks Redirect requires active WooCommerce Installation!', 'wc_thanks_redirect' ).'</p>';
    echo '</div>';
}

/**
 * Create the section beneath the products tab
 **/
add_filter( 'woocommerce_get_sections_products', 'wc_thanks_redirect_add_section' );
function wc_thanks_redirect_add_section( $sections ) {
	
	$sections['wctr'] = __( 'WC Thanks Redirect Settings', 'wc_thanks_redirect' );
	return $sections;
	
}

/**
 * Add settings to the specific section we created before
 */
add_filter( 'woocommerce_get_settings_products', 'wc_thanks_redirect_settings', 10, 2 );
function wc_thanks_redirect_settings( $settings, $current_section ) {
	/**
	 * Check the current section is what we want
	 **/
	if ( $current_section == 'wctr' ) {
		$settings_url = array();
		// Add Title to the Settings
		$settings_url[] = array( 'name' => __( 'Thanks Redirect Settings', 'wc_thanks_redirect' ), 'type' => 'title', 'desc' => __( 'The following options are used to configure WC Thanks Redirect', 'wc_thanks_redirect' ), 'id' => 'wctr' );
		// Add first checkbox option
		$settings_url[] = array(
			'name'     => __( 'Global Redirect Settings', 'wc_thanks_redirect' ),
			'desc_tip' => __( 'This will add redirect for successful orders', 'wc_thanks_redirect' ),
			'id'       => 'wctr_global',
			'type'     => 'checkbox',
			'css'      => 'min-width:300px;',
			'desc'     => __( 'Enable Global Redirect', 'wc_thanks_redirect' ),
		);
		// Add second text field option
		$settings_url[] = array(
			'name'     => __( 'Thanks Redirect URL', 'wc_thanks_redirect' ),
			'desc_tip' => __( 'This will add a redirect URL for successful orders', 'wc_thanks_redirect' ),
			'id'       => 'wctr_thanks_redirect_url',
			'type'     => 'text',
			'desc'     => __( 'Enter Valid URL!', 'wc_thanks_redirect' ),
		);
                
                $settings_url[] = array(
			'name'     => __( 'Order Failure Redirect URL', 'wc_thanks_redirect' ),
			'desc_tip' => __( 'This will add a redirect URL for failed orders', 'wc_thanks_redirect' ),
			'id'       => 'wctr_failed_redirect_url',
			'type'     => 'text',
			'desc'     => __( 'Enter Valid URL!', 'wc_thanks_redirect' ),
		);
		
		$settings_url[] = array( 'type' => 'sectionend', 'id' => 'wctr' );
		return $settings_url;
	
	/**
	 * If not, return the standard settings
	 **/
	} else {
		return $settings;
	}
}


