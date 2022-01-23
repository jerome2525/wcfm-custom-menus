<?php
/**
 * WCFM plugin controllers
 *
 * Plugin Products Custom Menus EP Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmcsm/controllers
 * @version   1.0.0
 */

class WCFM_EP_Controller {
	
	public function __construct() {
		global $WCFM, $WCFMu;
		
		$this->processing();
	}
	public function error_notification( $status, $label ) {
		echo '<div class="ep-message ' . $status . '" tabindex="-1"><span class="wc-icon wcicon-status-' . $status . '">' . $label . '</span></div>';
		
	}
	public function update_api_details() {
		if( !empty( $_POST['ep_api_url'] ) && !empty( $_POST['ep_api_client_id'] ) && !empty( $_POST['ep_api_client_name'] ) && !empty( $_POST['ep_api_client_secret'] ) ) {
			update_user_meta( get_current_user_id(), '_ep_api_url', $_POST['ep_api_url'] );
			update_user_meta( get_current_user_id(), '_ep_api_client_id', $_POST['ep_api_client_id'] );
			update_user_meta( get_current_user_id(), '_ep_api_client_name', $_POST['ep_api_client_name'] );
			update_user_meta( get_current_user_id(), '_ep_api_client_secret', $_POST['ep_api_client_secret'] );
			$this->error_notification( 'completed', 'Settings Saved!' );
		}
		else {
			$this->error_notification( 'failed', 'Error!' );
		}
	}

	public function get_token() {
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

    public function update_user_store_meta_id() {
        $url = get_user_meta( get_current_user_id(), '_ep_api_url', true ) . '/profile';
        $curl = curl_init( $url );
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

        $headers = array(
            'Client: ' . get_user_meta( get_current_user_id(), '_ep_api_client_name', true ),
            'Authorization: ' . $this->get_token()
        );

        curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true );

        $curl_result = curl_exec( $curl );
        curl_close( $curl );
        $results = json_decode( $curl_result );
        if( !empty( $results ) ) {
            if( $results->store->id ) {
                update_user_meta( get_current_user_id(), '_ep_user_store_id', $results->store->id );
                return true;
            }
        }
    }

    public function insert_product( $product_id, $merchant_id, $store_id, $type, $categories, $product_name, $product_mrp, $product_selling_price, $product_buying_price, $product_taxes, $product_stock, $product_quantity_dec, $product_quantity, $product_tax_type, $product_status, $product_created_at, $product_updated_at, $product_company_id, $product_unit_conversion_rate ) {
        if( $product_id && $product_name && $store_id ) {
            $args = array(
                'post_title'    => $product_name,
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_type'   => 'product',
            );
             
            // Insert the post into the database.
            $post_id = wp_insert_post( $args );
            if( !is_wp_error( $post_id ) ) {     
                add_post_meta( $post_id, '_ep_product_id', $product_id, true ); 
                add_post_meta( $post_id, '_ep_product_merchant_id', $merchant_id, true ); 
                add_post_meta( $post_id, '_ep_product_store_id', $store_id, true );
                add_post_meta( $post_id, '_ep_product_type', $type, true );
                add_post_meta( $post_id, '_ep_product_mrp', $product_mrp, true );
                add_post_meta( $post_id, '_price', $product_selling_price, true );
                add_post_meta( $post_id, '_regular_price', $product_selling_price, true );
                add_post_meta( $post_id, '_sale_price', $product_buying_price, true );
                if( $categories ) {
                    foreach( $categories as $category ) {
                        $cat_id = $category->id;
                        $cat_name = $category->name;
                        $cat_local_id = $category->localId;
                        $term_id = wp_create_term(
                            $cat_name, 
                            'product_cat'
                        );
                        wp_set_post_terms( $post_id, $term_id, 'product_cat' );
                        update_term_meta( $term_id['term_id'], '_ep_product_cat_id', $cat_id );
                        update_term_meta( $term_id['term_id'], '_ep_product_cat_local_id', $cat_local_id );
                    }
                }
                add_post_meta( $post_id, '_stock',  $product_quantity_dec );
                if( $product_quantity_dec > 0 ) {
                    add_post_meta( $post_id, '_manage_stock', 'yes');
                }
                add_post_meta( $post_id, '_ep_product_tax_type', $product_tax_type, true );
                add_post_meta( $post_id, '_ep_product_status', $product_status, true );
                add_post_meta( $post_id, '_ep_product_created_at', $product_created_at, true );
                add_post_meta( $post_id, '_ep_product_updated_at', $product_updated_at, true );
                add_post_meta( $post_id, '_ep_product_company_id', $product_company_id, true );
                add_post_meta( $post_id, '_ep_product_convert_rate', $product_unit_conversion_rate, true );
                
            } 
        }
    }

    public function update_product( $product_id, $merchant_id, $store_id, $type, $categories, $product_name, $product_mrp, $product_selling_price, $product_buying_price, $product_taxes, $product_stock, $product_quantity_dec, $product_quantity, $product_tax_type, $product_status, $product_created_at, $product_updated_at, $product_company_id, $product_unit_conversion_rate, $post_id ) {
        if( $product_id && $product_name && $store_id ) {
            $args = array(
                'ID'            => $post_id,
                'post_title'    => $product_name,
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_type'   => 'product',
            );
             
            // Insert the post into the database.
            wp_update_post( $args );
            if( !is_wp_error( $post_id ) ) {     
                update_post_meta( $post_id, '_ep_product_merchant_id', $merchant_id ); 
                update_post_meta( $post_id, '_ep_product_store_id', $store_id );
                update_post_meta( $post_id, '_ep_product_type', $type );
                update_post_meta( $post_id, '_ep_product_mrp', $product_mrp );
                update_post_meta( $post_id, '_price', $product_selling_price );
                update_post_meta( $post_id, '_regular_price', $product_selling_price );
                update_post_meta( $post_id, '_sale_price', $product_buying_price );
                if( $categories ) {
                    foreach( $categories as $category ) {
                        $cat_id = $category->id;
                        $cat_name = $category->name;
                        $cat_local_id = $category->localId;
                        $term_id = wp_create_term(
                            $cat_name, 
                            'product_cat'
                        );
                        wp_set_post_terms( $post_id, $term_id, 'product_cat' );
                        update_term_meta( $term_id['term_id'], '_ep_product_cat_id', $cat_id );
                        update_term_meta( $term_id['term_id'], '_ep_product_cat_local_id', $cat_local_id );
                    }
                }
                update_post_meta( $post_id, '_stock',  $product_quantity_dec );
                if( $product_quantity_dec > 0 ) {
                    update_post_meta( $post_id, '_manage_stock', 'yes');
                }
                update_post_meta( $post_id, '_ep_product_tax_type', $product_tax_type );
                update_post_meta( $post_id, '_ep_product_status', $product_status );
                update_post_meta( $post_id, '_ep_product_created_at', $product_created_at );
                update_post_meta( $post_id, '_ep_product_updated_at', $product_updated_at );
                update_post_meta( $post_id, '_ep_product_company_id', $product_company_id );
                update_post_meta( $post_id, '_ep_product_convert_rate', $product_unit_conversion_rate );
            } 
        }
    }

    public function product_check_exist( $product_id ) {
        if( $product_id ) {
            $args = array(
                'post_type'     => 'product',
                'post_status'   => 'publish',
                'posts_per_page' => -1,
            );

            $meta_query[] = array(
                'relation' => 'AND',
                array(
                    'key'   =>  '_ep_product_id',
                    'value' => $product_id,
                    'compare' => '=',
                )
            );
            $args['meta_query'] = $meta_query;

            $wp_query = new WP_Query( $args );

            if ( $wp_query->have_posts() ) { 
                while ( $wp_query->have_posts() ) {
                    $wp_query->the_post();    
                    return get_the_ID();
                }
            }
        }
    } 
                                                                  
    public function import_products() {
        $url = get_user_meta( get_current_user_id(), '_ep_api_url', true ). '/products?storeId='. get_user_meta( get_current_user_id(), '_ep_user_store_id', true );
        $curl = curl_init( $url );
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

        $headers = array(
            'Client: ' . get_user_meta( get_current_user_id(), '_ep_api_client_name', true ),
            'Authorization: ' . $this->get_token()
        );

        curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, true );

        $curl_result = curl_exec( $curl );
        curl_close( $curl );
        $results = json_decode( $curl_result );
        if( !empty( $results ) ) {
            foreach( $results->data as $result ) { 
                $product_id = $result->id;
                $merchant_id = $result->merchantId;
                $store_id = $result->storeId;
                $type = $result->type;
                $categories = $result->categories;
                $product_name = $result->name;
                $product_mrp = $result->mrp;
                $product_selling_price = $result->sellingPrice;
                $product_buying_price = $result->buyingPrice;
                $product_taxes = $result->taxes;
                if( $product_taxes ) {
                    foreach( $product_taxes as $product_tax ) {
                        $product_tax_id = $product_tax->id;
                        $product_tax_name = $product_tax->name;
                        $product_tax_type = $product_tax->taxType;
                        $product_tax_value = $product_tax->taxValue;
                        $product_tax_display_name = $product_tax->displayName;
                        $product_tax_localId = $product_tax->localId;
                    }
                }
                $product_stock = $result->stockMaintenance;
                $product_quantity_dec = $result->quantityDecimalPlaces;
                $product_quantity = $result->quantity;
                $product_tax_type = $result->taxType;
                $product_status = $result->status;
                $product_created_at = $result->createdAt;
                $product_updated_at = $result->updatedAt;
                $product_company_id = $result->companyId;
                $product_unit_conversion_rate = $result->unitConversionRateText;
                if( empty( $this->product_check_exist( $product_id ) ) ) {
                    $this->insert_product( $product_id, $merchant_id, $store_id, $type, $categories, $product_name, $product_mrp, $product_selling_price, $product_buying_price, $product_taxes, $product_stock, $product_quantity_dec, $product_quantity, $product_tax_type, $product_status, $product_created_at, $product_updated_at, $product_company_id, $product_unit_conversion_rate );
                }
                else {
                    $this->update_product( $product_id, $merchant_id, $store_id, $type, $categories, $product_name, $product_mrp, $product_selling_price, $product_buying_price, $product_taxes, $product_stock, $product_quantity_dec, $product_quantity, $product_tax_type, $product_status, $product_created_at, $product_updated_at, $product_company_id, $product_unit_conversion_rate, $this->product_check_exist( $product_id ) );
                    update_user_meta(get_current_user_id(), '_ep_user_product_exist', 1 );
                }
            }
            if( get_user_meta( get_current_user_id(), '_ep_user_product_exist', true ) ) {
                $this->error_notification( 'completed', 'Product Imported and some were Updated!' ); 
                update_user_meta( get_current_user_id(), '_ep_user_product_exist', 0 );  
            }
            else {
                $this->error_notification( 'completed', 'Product Imported!' );
            }
        }
        else {
            $this->error_notification( 'failed', 'API Error!' );
        }
    }

	public function processing() {
		global $WCFM, $WCFMu, $wpdb, $_POST;
			if( isset( $_POST['import_product'] ) ) {
                if( $this->update_user_store_meta_id() ) {
                    $this->import_products();
                }
			}
			else {
	  			$this->update_api_details();
	  		}
	  	die;
	}
}