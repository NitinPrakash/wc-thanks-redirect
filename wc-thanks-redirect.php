<?php
/**
 *
 * @link              https://nitin247.com/plugin/wc-thanks-redirect/
 * @since             1.1
 * @package           WC_Thanks_Redirect
 *
 * @wordpress-plugin
 * Plugin Name:       Thanks Redirect for WooCommerce
 * Plugin URI:        https://nitin247.com/plugin/wc-thanks-redirect/
 * Description:       Thanks Redirect for WooCommerce allows adding Thank You Page or Thank You URL for WooCommerce Products for your Customers, now supports Order Details on Thank You Page.This plugin does not support Multisite.
 * Version:           3.0
 * Author:            Nitin Prakash
 * Author URI:        http://www.nitin247.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-thanks-redirect
 * Domain Path:       /languages/
 * Requires PHP:      5.6
 * WC requires at least: 4.0.0
 * WC tested up to: 5.6
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || die( 'Wordpress Error! Opening plugin file directly' );

define( 'WOOCOMMERCE_THANKS_REDIRECT_PLUGIN_PATH', plugins_url( __FILE__ ) );
define( 'WOOCOMMERCE_THANKS_REDIRECT_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOOCOMMERCE_THANKS_REDIRECT_PLUGIN_VERSION', '3.0' ); 

if ( ! function_exists( 'wc_thanks_redirect_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wc_thanks_redirect_fs() {
        global $wc_thanks_redirect_fs;

        if ( ! isset( $wc_thanks_redirect_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $wc_thanks_redirect_fs = fs_dynamic_init( array(
                'id'                  => '5290',
                'slug'                => 'wc-thanks-redirect',
                'type'                => 'plugin',
                'public_key'          => 'pk_a2ce319e73a5895901df9374e2a05',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                'account'        => false,
                ),
            ) );
        }

        return $wc_thanks_redirect_fs;
    }

    // Init Freemius.
    wc_thanks_redirect_fs();
    // Signal that SDK was initiated.
    do_action( 'wc_thanks_redirect_fs_loaded' );
}

/**
 * Check if WooCommerce is active
 **/


if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    if(is_multisite() ){
            add_action( 'admin_notices', 'wc_thanks_redirect_multisite_admin_notice' );
    }else{
            add_action( 'admin_notices', 'wc_thanks_redirect_install_admin_notice' );
    }        
    
}else{

    add_action( 'init', 'wc_thanks_redirect_load_textdomain' );
  
    /**
     * Load plugin textdomain.
     */
    function wc_thanks_redirect_load_textdomain() {
      load_plugin_textdomain( 'wc-thanks-redirect', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
    }

    // Add submenu under woocommerce
    add_action( 'admin_menu', 'wc_thanks_redirect_submenu_entry', 100 );

    function wc_thanks_redirect_submenu_entry() {
        add_submenu_page(
            'woocommerce',
            __( 'Thanks Redirect' ),
            __( 'Thanks Redirect' ).' '.WOOCOMMERCE_THANKS_REDIRECT_PLUGIN_VERSION,
            'manage_woocommerce', // Required user capability
            'admin.php?page=wc-settings&tab=products&section=wctr'        
        );

        add_submenu_page(
            'woocommerce' // Use the parent slug as usual.
            , __( 'Documentation', 'wc-thanks-redirect' )
            , ''
            , 'manage_woocommerce'
            , 'wctr-docs'
            , 'wc_thanks_redirect_wctr_docs'
        );

    }
    
    /**
    * Create the section beneath the products tab
    **/
    
   add_filter( 'woocommerce_get_sections_products', 'wc_thanks_redirect_add_section' );
   function wc_thanks_redirect_add_section( $sections ) {

           $sections['wctr'] = __( 'Thanks Redirect '. WOOCOMMERCE_THANKS_REDIRECT_PLUGIN_VERSION , 'wc-thanks-redirect' );
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
                   $settings_url[] = array(
                        'name' => __( 'Thanks Redirect Settings', 'wc-thanks-redirect-pro'),
                        'type' => 'title',
                        'desc' => __( 'You can check <a href="?page=wctr-docs">Plugin Documentation</a> here.<br/>The following options are used to configure WC Thanks Redirect PRO', 'wc-thanks-redirect-pro'),
                        'id'   => 'wctr',
                    );
                   
                   // Add first checkbox option
                   
                   $settings_url[] = array(
                           'name'     => __( 'Global Redirect Settings', 'wc-thanks-redirect' ),
                           'desc_tip' => __( 'This will add redirect for orders', 'wc-thanks-redirect' ),
                           'id'       => 'wctr_global',
                           'type'     => 'checkbox',
                           'css'      => 'min-width:300px;',
                           'desc'     => __( 'Enable Global Thanks Redirect', 'wc-thanks-redirect' ),
                   );
                   
                   // Add second text field option
                   
                   $settings_url[] = array(
                           'name'     => __( 'Thanks Redirect URL', 'wc-thanks-redirect' ),
                           'desc_tip' => __( 'This will add a redirect URL for successful orders', 'wc-thanks-redirect' ),
                           'id'       => 'wctr_thanks_redirect_url',
                           'type'     => 'text',
                           'desc'     => __( 'Enter Valid URL!', 'wc-thanks-redirect' ),
                   );
                   
                   // Add third text field option

                   $settings_url[] = array(
                           'name'     => __( 'Order Failure Redirect URL', 'wc-thanks-redirect' ),
                           'desc_tip' => __( 'This will add a redirect URL for failed orders', 'wc-thanks-redirect' ),
                           'id'       => 'wctr_failed_redirect_url',
                           'type'     => 'text',
                           'desc'     => __( 'Enter Valid URL!', 'wc-thanks-redirect' ),
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
       
       if( isset( $wctr_global ) && strtolower($wctr_global) == 'yes'   ) {                          
           
           $thank_you_url = get_option( 'wctr_thanks_redirect_url');
           $fail_url = get_option( 'wctr_failed_redirect_url');     

           $thank_you_url = wp_parse_url( $thank_you_url );              

           $order_string = "&order=$order_id";

           $thanks_url = $thank_you_url['scheme'].'://'.$thank_you_url['host'].$thank_you_url['path'].'?'.$thank_you_url['query'].$order_string;          

           if ( $order_status != 'failed' ) {
               // Check If URL is valid
               //if( filter_var($thanks_url, FILTER_VALIDATE_URL) ){
                   wp_redirect( $thanks_url );
                   exit;                   
               //}
               
           }else{
               // Check If URL is valid
               //if( filter_var($fail_url, FILTER_VALIDATE_URL) ){
                   wp_redirect( $fail_url );
                   exit;                   
               //}
           }

       } else{         
           
           $items = $order->get_items();              

           foreach($items as $key => $this_product){

                 $product_id = $this_product['product_id'];
                 $product_meta_thanks_url = get_post_meta($product_id,'wc_thanks_redirect_custom_thankyou',true);

                 if( !empty( $product_meta_thanks_url ) ){

                       $order_string = "&order=$order_id";
                       $thank_you_url = wp_parse_url( get_post_meta($product_id,'wc_thanks_redirect_custom_thankyou',true) );  

                       $product_thanks = $thank_you_url['scheme'].'://'.$thank_you_url['host'].$thank_you_url['path'].'?'.$thank_you_url['query'].$order_string; 
                       $product_failed = get_post_meta($product_id,'wc_thanks_redirect_custom_failure',true);                       
                       
                       if ( $order_status != 'failed' ) {
                           // Check If URL is valid
                           if( filter_var($product_thanks, FILTER_VALIDATE_URL) ){
                               wp_redirect( $product_thanks );
                               exit;                   
                           }
                           
                       }else{
                           // Check If URL is valid
                           if( filter_var($product_failed, FILTER_VALIDATE_URL) ){
                               wp_redirect( $product_failed );
                               exit;                   
                           }
                           
                       }

                 }
                 
           }                                         
                      
       }   
      
   });
   
   // add the settings under ‘General’ sub-menu
   add_action( 'woocommerce_product_options_general_product_data', 'wc_thanks_redirect_add_custom_settings' );
   
   function wc_thanks_redirect_add_custom_settings() {
    global $woocommerce, $post;
    echo '<div class="options_group">';        

    // Create a text field, for Custom Thank You
    woocommerce_wp_text_input(
      array(
       'id'                => 'wc_thanks_redirect_custom_thankyou',
       'label'             => __( 'Thank You URL', 'wc-thanks-redirect' ),
       'placeholder'       => '',
       'desc_tip'    => 'true',
       'description'       => __( 'Enter Valid URL.', 'wc-thanks-redirect' ),
       'type'              => 'text'
       ));
    
    // Create a text field, for Custom Thank You
    woocommerce_wp_text_input(
      array(
       'id'                => 'wc_thanks_redirect_custom_failure',
       'label'             => __( 'Failure Redirect', 'wc-thanks-redirect' ),
       'placeholder'       => '',
       'desc_tip'    => 'true',
       'description'       => __( 'Enter Valid URL.', 'wc-thanks-redirect' ),
       'type'              => 'text'
       ));

      echo '</div>';
    }
    
    add_action( 'woocommerce_process_product_meta', 'wc_thanks_redirect_save_custom_settings' );
    
    function wc_thanks_redirect_save_custom_settings( $post_id ){
    
    //echo "<pre>";print_r( $_POST ); echo "</pre>"; exit;
    // save custom fields
    $wc_thanks_redirect_custom_thankyou =  sanitize_text_field( $_POST['wc_thanks_redirect_custom_thankyou'] );
    $wc_thanks_redirect_custom_failure =  sanitize_text_field( $_POST['wc_thanks_redirect_custom_failure'] );
    
    if( !empty( $wc_thanks_redirect_custom_thankyou ) )
        update_post_meta( $post_id, 'wc_thanks_redirect_custom_thankyou', esc_attr( $wc_thanks_redirect_custom_thankyou) ); 
    else      
        delete_post_meta( $post_id, 'wc_thanks_redirect_custom_thankyou' ); 

    if( !empty( $wc_thanks_redirect_custom_failure ) )
        update_post_meta( $post_id, 'wc_thanks_redirect_custom_failure', esc_attr( $wc_thanks_redirect_custom_failure) );
    else
        delete_post_meta( $post_id, 'wc_thanks_redirect_custom_failure' );     
    }
    
}

/* Admin notice if WooCommerce is not installed or active */

function wc_thanks_redirect_install_admin_notice(){
    echo '<div class="notice notice-error">';
    echo '<p>'. _e( 'Thanks Redirect for WooCommerce requires active WooCommerce Installation, please install and activate WooCommerce plugin!', 'wc-thanks-redirect' ).'</p>';
    echo '</div>';
}

function wc_thanks_redirect_multisite_admin_notice(){
    echo '<div class="notice notice-error">';
    echo '<p>'. _e( 'Thanks Redirect for WooCommerce is not designed for Multisite, you may need to buy this short plugin. <a target="_blank" href="https://bit.ly/2RwaIQB">Thanks Redirect for WooCommerce PRO</a>!', 'wc-thanks-redirect' ).'</p>';
    echo '</div>';
}

function wc_thanks_redirect_action_links( $links ) {
	$links = array_merge( array(
	'<a href="' . esc_url( site_url().'/wp-admin/admin.php?page=wc-settings&tab=products&section=wctr' ) . '">' . __( 'Settings', 'wc-thanks-redirect' ) . '</a>',
    '<a target="_blank" style="color:green;font-weight:bold;" href="' . esc_url( 'https://bit.ly/2RwaIQB' ) . '">' . __( 'Go PRO!', 'wc-thanks-redirect' ) . '</a>',
    '<a target="_blank" href="' . esc_url( 'https://nitin247.com/buy-me-a-coffe' ) . '">' . __( 'Donate', 'wc-thanks-redirect' ) . '</a>',
    '<a href="' . esc_url( site_url().'/wp-admin/admin.php?page=wctr-docs' ) . '">' . __( 'Documentation', 'wc-thanks-redirect-pro'
 ) . '</a>',
    '<a target="_blank" href="' . esc_url( 'https://nitin247.com/support/' ) . '">' . __( 'Plugin Support', 'wc-thanks-redirect' ) . '</a>',
	), $links );
	return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_thanks_redirect_action_links' );

/* Add Plugin shortcode */

add_shortcode( 'TRFW_ORDER_DETAILS', 'wc_thanks_redirect_short_code_order_details' );
function wc_thanks_redirect_short_code_order_details( $atts ) {

    $order_id = $_GET['order'];
    $order = wc_get_order( $order_id );     

    if ( ! $order ) {
      return;
    }

    $order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
    $show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
    $show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
    $downloads             = $order->get_downloadable_items();
    $show_downloads        = $order->has_downloadable_item() && $order->is_download_permitted();
    ob_start();
    if ( $show_downloads ) {
      wc_get_template(
        'order/order-downloads.php',
        array(
          'downloads'  => $downloads,
          'show_title' => true,
        )
      );
    }
    ?>
      <div class="woocommerce">
      <div class="woocommerce-order">
          <section class="woocommerce-order-details">
            <?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>
            <header class="entry-header">
              <h1 class="entry-title" itemprop="headline"><?php esc_html_e( "Order received", 'wc-thanks-redirect' ); ?></h1>     
            </header>
            <p>&nbsp;</p>
            <ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

              <li class="woocommerce-order-overview__order order">
                <?php esc_html_e( "Order number", 'wc-thanks-redirect' ); ?>: <strong><?php echo $order_id;?></strong>
              </li>

              <li class="woocommerce-order-overview__order order">
                <?php
                      $wctr_date_format = get_option( 'date_format' ); 
                      esc_html_e( "Date", 'wc-thanks-redirect' ); ?>: <strong><?php echo wp_date($wctr_date_format,strtotime( $order->date_created ) );?></strong>
              </li>

              <li class="woocommerce-order-overview__order order">
                <?php esc_html_e( "Name", 'wc-thanks-redirect' ); ?>: <strong><?php echo $order->get_formatted_billing_full_name();?></strong>
              </li>

              <li class="woocommerce-order-overview__order order">
                <?php esc_html_e( "Payment Method", 'wc-thanks-redirect' ); ?>: <strong><?php echo $order->payment_method_title;?></strong>
              </li>                            
              
            </ul>
            <h2 class="woocommerce-order-details__title"><?php esc_html_e( "Order details", 'wc-thanks-redirect' ); ?></h2>            

            <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">

              <thead>
                <tr>
                  <th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'wc-thanks-redirect' ); ?></th>
                  <th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Total', 'wc-thanks-redirect' ); ?></th>
                </tr>
              </thead>

              <tbody>
                <?php
                do_action( 'woocommerce_order_details_before_order_table_items', $order );

                foreach ( $order_items as $item_id => $item ) {
                  $product = $item->get_product();

                  wc_get_template(
                    'order/order-details-item.php',
                    array(
                      'order'              => $order,
                      'item_id'            => $item_id,
                      'item'               => $item,
                      'show_purchase_note' => $show_purchase_note,
                      'purchase_note'      => $product ? $product->get_purchase_note() : '',
                      'product'            => $product,
                    )
                  );
                }

                do_action( 'woocommerce_order_details_after_order_table_items', $order );
                ?>
              </tbody>

              <tfoot>
                <?php
                foreach ( $order->get_order_item_totals() as $key => $total ) {
                  ?>
                    <tr>
                      <th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
                      <td><?php echo ( 'payment_method' === $key ) ? esc_html( $total['value'] ) : wp_kses_post( $total['value'] ); ?></td>
                    </tr>
                    <?php
                }
                ?>
                <?php if ( $order->get_customer_note() ) : ?>
                  <tr>
                    <th><?php esc_html_e( 'Note', 'wc-thanks-redirect' ); ?>:</th>
                    <td><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
                  </tr>
                <?php endif; ?>
              </tfoot>
            </table>

            <?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
          </section>
        </div>
      </div>

    <?php
    /**
     * Action hook fired after the order details.
     *
     * @since 4.4.0
     * @param WC_Order $order Order data.
     */
    do_action( 'woocommerce_after_order_details', $order );

    if ( $show_customer_details ) {
      wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
    }

    $shortcode_output = ob_get_clean();
    return $shortcode_output;
    
}

function wc_thanks_redirect_wctr_docs(){

    //Get the active tab from the $_GET param
  $default_tab = 'docs';
  $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

  ?>

<div class="wrap">    
    <!-- Here are our tabs -->
    <nav class="nav-tab-wrapper">
      <a href="<?php echo site_url().'/wp-admin/admin.php?page=wc-settings&tab=products&section=wctr';?>" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">
            <?php echo __( 'Thank You Page Settings', 'wc-thanks-redirect-pro');?>
      </a>

      <a href="?page=wctr-docs&tab=docs" class="nav-tab <?php if($tab==='docs'):?>nav-tab-active<?php endif; ?>">
            <?php echo __( 'Documentation', 'wc-thanks-redirect-pro');?>
      </a>

    </nav>  

    <div class="tab-content">

        <?php if( $tab == 'docs' ) {                      
            include_once( WOOCOMMERCE_THANKS_REDIRECT_PLUGIN_DIR_PATH.'readme.html' );           
        } ?>
    
    </div>
  </div>  

    <?php
}
