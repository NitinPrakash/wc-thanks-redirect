<?php
/**
 *
 * @link              http://www.webcurries.com/plugin/wc-thanks-redirect/
 * @since             1.1
 * @package           WC_Thanks_Redirect
 *
 * @wordpress-plugin
 * Plugin Name:       WC Thanks Redirect
 * Plugin URI:        http://www.webcurries.com/plugin/wc-thanks-redirect/
 * Description:       WC Thanks Redirect allows adding redirect URL for WooCommerce Products for your Customers.
 * Version:           2.0
 * Author:            Nitin Prakash
 * Author URI:        https://github.com/nitinprakash/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc_thanks_redirect
 * Domain Path:       /languages/
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || die( 'Wordpress Error! Opening plugin file directly' );

define( 'PLUGIN_PATH', plugins_url( __FILE__ ) ); 


/**
 * Check if WooCommerce is active
 **/

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    add_action( 'admin_notices', 'wc_thanks_redirect_install_admin_notice' );
    
}else{
    
    /**
    * Create the section beneath the products tab
    **/
    
   add_filter( 'woocommerce_get_sections_products', 'wc_thanks_redirect_add_section' );
   function wc_thanks_redirect_add_section( $sections ) {

           $sections['wctr'] = __( 'WC Thanks Redirect', 'wc_thanks_redirect' );
           return $sections;

   }

   /**
    * Add settings to the specific section created before
    */
   
   add_filter( 'woocommerce_get_settings_products', 'wc_thanks_redirect_settings', 10, 2 );

   function wc_thanks_redirect_settings( $settings, $current_section ) {
       
           /**
            * Check the current section 
            **/
       
           if ( $current_section == 'wctr' ) {
               
                   $settings_url = array();
                   
                   // Add Title to the Settings
                   
                   $settings_url[] = array( 'name' => __( 'Thanks Redirect Settings', 'wc_thanks_redirect' ), 'type' => 'title', 'desc' => __( 'The following options are used to configure WC Thanks Redirect', 'wc_thanks_redirect' ), 'id' => 'wctr' );
                   
                   // Add first checkbox option
                   
                   $settings_url[] = array(
                           'name'     => __( 'Global Redirect Settings', 'wc_thanks_redirect' ),
                           'desc_tip' => __( 'This will add redirect for orders', 'wc_thanks_redirect' ),
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
                   
                   // Add third text field option

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

   /**
    * action Redirect to thank you
    */
   
   add_action( 'woocommerce_thankyou', function( $order_id ){

       $wctr_global = get_option( 'wctr_global' );   
       
       $order = new WC_Order( $order_id ); 
       
       $order_status = $order->get_status();        
       
      // echo '<pre>';print_r($product_id);echo '</pre>';
       
       if( isset( $wctr_global ) && strtolower($wctr_global) == 'yes'   ) {    
           
           
           
           $thanks_url = get_option( 'wctr_thanks_redirect_url');
           $fail_url = get_option( 'wctr_failed_redirect_url');          

           if ( $order_status != 'failed' ) {
               // Check If URL is valid
               if( filter_var($thanks_url, FILTER_VALIDATE_URL) ){
                   echo "<script type=\"text/javascript\">window.location = '".$thanks_url."'</script>";
               }
               
           }else{
               // Check If URL is valid
               if( filter_var($fail_url, FILTER_VALIDATE_URL) ){
                   echo "<script type=\"text/javascript\">window.location = '".$fail_url."'</script>";
               }
           }

       } else{         
           
           $items = $order->get_items();       

           foreach($items as $key => $this_product){
                 $product_id = $this_product['product_id'];
                 continue;
           }                      
           
           $product_thanks = get_post_meta($product_id,'wc_thanks_redirect_custom_thankyou',true);

           $product_failed = get_post_meta($product_id,'wc_thanks_redirect_custom_failure',true);
           
           if ( $order_status != 'failed' ) {
               // Check If URL is valid
               if( filter_var($product_thanks, FILTER_VALIDATE_URL) ){
                   echo "<script type=\"text/javascript\">window.location = '".$product_thanks."'</script>";
               }
               
           }else{
               // Check If URL is valid
               if( filter_var($product_failed, FILTER_VALIDATE_URL) ){
                   echo "<script type=\"text/javascript\">window.location = '".$product_failed."'</script>";
               }
               
           }
           
       }   
      
   });
   
   // add the settings under ‘General’ sub-menu
   add_action( 'woocommerce_product_options_general_product_data', 'wc_thanks_redirect_add_custom_settings' );
   
   function wc_thanks_redirect_add_custom_settings() {
    global $woocommerce, $post;
    echo '<div class="options_group">';
    
    // Create a checkbox for product purchase status
    /*  
     * woocommerce_wp_checkbox(
     
       array(
       'id'            => 'wc_thanks_redirect_override',
       'label'         => __('Use Custom ThankYou', 'wc_thanks_redirect' ),
       'desc_tip'    => 'true',
       'description'       => __( 'Override Global redirect settings and use Custom Settings', 'wc_thanks_redirect' ),    
       ));
     * 
     */

    // Create a text field, for Custom Thank You
    woocommerce_wp_text_input(
      array(
       'id'                => 'wc_thanks_redirect_custom_thankyou',
       'label'             => __( 'Thank You URL', 'wc_thanks_redirect' ),
       'placeholder'       => '',
       'desc_tip'    => 'true',
       'description'       => __( 'Enter Valid URL.', 'wc_thanks_redirect' ),
       'type'              => 'text'
       ));
    
    // Create a text field, for Custom Thank You
    woocommerce_wp_text_input(
      array(
       'id'                => 'wc_thanks_redirect_custom_failure',
       'label'             => __( 'Failure Redirect', 'wc_thanks_redirect' ),
       'placeholder'       => '',
       'desc_tip'    => 'true',
       'description'       => __( 'Enter Valid URL.', 'wc_thanks_redirect' ),
       'type'              => 'text'
       ));

      echo '</div>';
    }
    
    add_action( 'woocommerce_process_product_meta', 'wc_thanks_redirect_save_custom_settings' );
    
    function wc_thanks_redirect_save_custom_settings( $post_id ){
    
    // save custom fields
    $wc_thanks_redirect_custom_thankyou = $_POST['wc_thanks_redirect_custom_thankyou'];
    $wc_thanks_redirect_custom_failure = $_POST['wc_thanks_redirect_custom_failure'];
    
    if( !empty( $wc_thanks_redirect_custom_thankyou ) )
        update_post_meta( $post_id, 'wc_thanks_redirect_custom_thankyou', esc_attr( $wc_thanks_redirect_custom_thankyou) );   
    
    if( !empty( $wc_thanks_redirect_custom_failure ) )
        update_post_meta( $post_id, 'wc_thanks_redirect_custom_failure', esc_attr( $wc_thanks_redirect_custom_failure) ); 
    
    }
    
}

/* Admin notice if WooCommerce is not installed or active */

function wc_thanks_redirect_install_admin_notice(){
    echo '<div class="notice notice-error">';
    echo     '<p>'. _e( 'WC Thanks Redirect requires active WooCommerce Installation!', 'wc_thanks_redirect' ).'</p>';
    echo '</div>';
}




