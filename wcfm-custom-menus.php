<?php
/**
 * Plugin Name: ePaisa Integration 
 * Plugin URI: https://www.eigital.com
 * Description: WCFM Extension that fetch data from ePaisa.
 * Author: Eigital
 * Version: 1.0.0
 * Author URI: https://www.eigital.com
 *
 * Text Domain: wcfm-custom-menus
 * Domain Path: /lang/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.2.0
 *
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('WCFM')) return; // Exit if WCFM not installed

/**
 * WCFM - Custom Menus Query Var
 */
function wcfmcsm_query_vars( $query_vars ) {
	$wcfm_modified_endpoints = (array) get_option( 'wcfm_endpoints' );
	
	$query_custom_menus_vars = array(
		'wcfm-ep'               => ! empty( $wcfm_modified_endpoints['wcfm-ep'] ) ? $wcfm_modified_endpoints['wcfm-ep'] : 'ep',
	);
	
	$query_vars = array_merge( $query_vars, $query_custom_menus_vars );
	
	return $query_vars;
}
add_filter( 'wcfm_query_vars', 'wcfmcsm_query_vars', 50 );

/**
 * WCFM - Custom Menus End Point Title
 */
function wcfmcsm_endpoint_title( $title, $endpoint ) {
	global $wp;
	switch ( $endpoint ) {
		case 'wcfm-ep' :
			$title = __( 'Epaisa API settings', 'wcfm-custom-menus' );
		break;
		
	}
	
	return $title;
}
add_filter( 'wcfm_endpoint_title', 'wcfmcsm_endpoint_title', 50, 2 );

/**
 * WCFM - Custom Menus Endpoint Intialize
 */
function wcfmcsm_init() {
	global $WCFM_Query;

	// Intialize WCFM End points
	$WCFM_Query->init_query_vars();
	$WCFM_Query->add_endpoints();
	
	if( !get_option( 'wcfm_updated_end_point_cms' ) ) {
		// Flush rules after endpoint update
		flush_rewrite_rules();
		update_option( 'wcfm_updated_end_point_cms', 1 );
	}
}
add_action( 'init', 'wcfmcsm_init', 50 );

/**
 * WCFM - Custom Menus Endpoiint Edit
 */
function wcfm_custom_menus_endpoints_slug( $endpoints ) {
	
	$custom_menus_endpoints = array(
								'wcfm-ep'        => 'ep',
							);
	
	$endpoints = array_merge( $endpoints, $custom_menus_endpoints );
	
	return $endpoints;
}
add_filter( 'wcfm_endpoints_slug', 'wcfm_custom_menus_endpoints_slug' );

if(!function_exists('get_wcfm_custom_menus_url')) {
	function get_wcfm_custom_menus_url( $endpoint ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_custom_menus_url = wcfm_get_endpoint_url( $endpoint, '', $wcfm_page );
		return $wcfm_custom_menus_url;
	}
}

/**
 * WCFM - Custom Menus
 */
function wcfmcsm_wcfm_menus( $menus ) {
	global $WCFM;
	
	$custom_menus = array( 'wcfm-ep' => array(   'label'  => __( 'ePaisa Integration', 'wcfm-custom-menus'),
		'url'       => get_wcfm_custom_menus_url( 'wcfm-ep' ),
		'icon'      => 'cogs',
		'priority'  => 5.1
	));
	
	$menus = array_merge( $menus, $custom_menus );
		
	return $menus;
}
add_filter( 'wcfm_menus', 'wcfmcsm_wcfm_menus', 20 );

/**
 *  WCFM - Custom Menus Views
 */
function wcfm_csm_load_views( $end_point ) {
	global $WCFM, $WCFMu;
	$plugin_path = trailingslashit( dirname( __FILE__  ) );
	
	switch( $end_point ) {
		case 'wcfm-ep':
			require_once( $plugin_path . 'views/wcfm-views-ep.php' );
		break;
		
	}
}
add_action( 'wcfm_load_views', 'wcfm_csm_load_views', 50 );
add_action( 'before_wcfm_load_views', 'wcfm_csm_load_views', 50 );

// Custom Load WCFM Scripts
function wcfm_csm_load_scripts( $end_point ) {
	global $WCFM;
	$plugin_url = trailingslashit( plugins_url( '', __FILE__ ) );
	
	switch( $end_point ) {
		case 'wcfm-ep':
			wp_enqueue_script( 'wcfm_build_js', $plugin_url . 'js/wcfm-script-ep.js', array( 'jquery' ), $WCFM->version, true );
		break;
	}
}

add_action( 'wcfm_load_scripts', 'wcfm_csm_load_scripts' );
add_action( 'after_wcfm_load_scripts', 'wcfm_csm_load_scripts' );

// Custom Load WCFM Styles
function wcfm_csm_load_styles( $end_point ) {
	global $WCFM, $WCFMu;
	$plugin_url = trailingslashit( plugins_url( '', __FILE__ ) );
	
	switch( $end_point ) {
		case 'wcfm-ep':
			wp_enqueue_style( 'wcfmu_build_css', $plugin_url . 'css/wcfm-style-ep.css', array(), $WCFM->version );
		break;
	}
}
add_action( 'wcfm_load_styles', 'wcfm_csm_load_styles' );
add_action( 'after_wcfm_load_styles', 'wcfm_csm_load_styles' );

/**
 *  WCFM - Custom Menus Ajax Controllers
 */
function wcfm_csm_ajax_controller() {
	global $WCFM, $WCFMu;
	
	$plugin_path = trailingslashit( dirname( __FILE__  ) );
	
	$controller = '';
	if( isset( $_POST['controller'] ) ) {
		$controller = $_POST['controller'];
		
		switch( $controller ) {
			case 'wcfm-ep':
				require_once( $plugin_path . 'controllers/wcfm-controller-ep.php' );
				new WCFM_EP_Controller();
			break;
		}
	}
}
add_action( 'after_wcfm_ajax_controller', 'wcfm_csm_ajax_controller' );

/**
 *  WCFM - update product filter
 */

function wcfm_get_token() {
    $url = get_user_meta( get_current_user_id(), '_ep_api_url', true ) . '/authenticate';

    $curl = curl_init( $url );
    curl_setopt( $curl, CURLOPT_URL, $url );
    curl_setopt( $curl, CURLOPT_POST, true);
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
       "Content-Type: application/json",
    );

    curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );

    $data = json_encode([
        "client" => get_user_meta( get_current_user_id(), '_ep_api_client_name', true ),
        "clientId" => get_user_meta( get_current_user_id(), '_ep_api_client_id', true ),
        "clientSecret" => get_user_meta( get_current_user_id(), '_ep_api_client_secret', true )
    ]);

    curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true );

    $curl_result = curl_exec( $curl );
    curl_close( $curl );
    $res = json_decode( $curl_result );
    if( !empty( $res ) ) {
        return $res->token;
    }
}

function fake_token() {
	$url = "https://d2web.epaisa.com/v1/authenticate";
    //$url = get_user_meta( get_current_user_id(), '_ep_api_url', true ) . '/authenticate';

    $curl = curl_init( $url );
    curl_setopt( $curl, CURLOPT_URL, $url );
    curl_setopt( $curl, CURLOPT_POST, true);
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
       "Content-Type: application/json",
    );

    curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );

    $data = json_encode([
        "client" => "e-Mii",
        "clientId" => "0019604773998.FCFBFABBFCD.1622474530",
        "clientSecret" => "0wFhHlqCQ341aacDPFIQoypRJSw4X2O9J4sfV4w64DazXHm9NIjyYP8vZnBxxbMCLnnIbJ3g5+cQ734zyp4z/g=="
    ]);

    curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true );

    $curl_result = curl_exec( $curl );
    curl_close( $curl );
    $res = json_decode( $curl_result );
    if( !empty( $res ) ) {
        return $res->token;
    }
}

function wcfm_get_product_cat_data( $pid, $type ) {
	$terms = get_the_terms( $pid, 'product_cat' );                  
	if ( $terms && ! is_wp_error( $terms ) ) {
	    $draught_links = array();
	    foreach ( $terms as $term ) {
	        return $draught_links[] = $term->$type;
	    }
	}
}

function wcfm_update_product( $product_id, $wcfm_products_manage_form_data ) {
	$product = wc_get_product( $product_id );
	$url = get_user_meta( get_current_user_id(), '_ep_api_url', true ) . '/products';
    $token = wcfm_get_token();
    $client_name = get_user_meta( get_current_user_id(), '_ep_api_client_name', true );
    $product_cat_list = get_the_terms( $post->ID, 'taxonomy' );
	$terms_string = join(', ', wp_list_pluck($product_cat_list, 'name'));
	$curl = curl_init( $url );
	curl_setopt( $curl, CURLOPT_URL, $url );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'PUT' );

	$headers = array(
	   "Client: $client_name",
	   "Authorization: $token",
	   "Content-Type: application/json",
	);

	curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );

	$data = 
	'{
		"itemId": "",
	    "id": "' . get_post_meta( $product_id, '_ep_product_id', true ) . '",
	    "companyId": "' . get_post_meta( $product_id, '_ep_product_company_id', true ) . '",
	    "storeId": "' . get_post_meta( $product_id, '_ep_product_store_id', true ) . '",
	    "type": "' . get_post_meta( $product_id, '_ep_product_type', true ) . '",
	    "categories": [{
	        "id": "' . get_term_meta( wcfm_get_product_cat_data( $product_id, 'term_id' ), '_ep_product_cat_id', true ) . '",
	        "name": "' . wcfm_get_product_cat_data( $product_id, 'name' ) . '",
	        "localId": ""
	    }],
	    "name": "' . get_the_title( $product_id ) . '",
	    "description": "' . $product->get_description() . '",
	    "mrp": "' . get_post_meta( $product_id, '_ep_product_mrp', true ) . '",
	    "sellingPrice": "' . get_post_meta( $product_id, '_regular_price', true ) . '",
	    "buyingPrice": "' . get_post_meta( $product_id, '_sale_price', true ) . '",
	    "quantity": 3
	}';

	curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );

	curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true );

	$result = curl_exec( $curl );
	curl_close( $curl );

	//var_dump($resp);
}
add_action( 'after_wcfm_products_manage_meta_save', 'wcfm_update_product', 10, 2 );

function wcfm_after_payment_checkout( $order_id ) {

    //create an order instance
    $order = wc_get_order( $order_id);
    //$payment_method = $order->payment_method_title;
    //$status = $order->get_status();

    update_user_meta(69, 'test_process', $order_id );

    // write your custom logic over here.
}
add_action( 'woocommerce_checkout_order_processed', 'wcfm_after_payment_checkout' );


// tester only
add_shortcode('tester', 'tester');
function tester() {
	ob_start();	
		$order_id = get_user_meta(69, 'test_process', true );
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		foreach ( $items as $item ) {
		    echo $product_name = $item->get_name();
		    echo $product_id = $item->get_product_id();
		    $product_variation_id = $item->get_variation_id();
		}
	return ob_get_clean();
}





