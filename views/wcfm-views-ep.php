<?php
global $WCFM, $wp_query;

?>

<div class="collapse wcfm-collapse" id="wcfm_build_listing">
	
	<div class="wcfm-page-headig">
		<span class="wcfmfa fa-cogs"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'ePaisa Integration', 'wcfm-custom-menus' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
		<?php //$WCFM->template->get_template( 'dashboard/wcfm-view-icon-box.php' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php if( get_user_meta( get_current_user_id(), 'wcfmmp_store_name', true ) ) { ?>
			<div class="wcfm_current_store_title wcfm_welcomebox_header"><div class="wcfm_welcomebox_user_details rgt"><h3>Welcome to <?php echo get_user_meta( get_current_user_id(), 'wcfmmp_store_name', true ); ?> Store</h3><?php //martfury_child_get_vendor_name_log(); ?></div></div>
		<?php } ?>
		<?php do_action( 'before_wcfm_build' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e('ePaisa Integration', 'wcfm-custom-menus' ); ?></h2>
			<div class="wcfm-clearfix"></div>
	  </div>
	  <div class="wcfm-clearfix"></div><br />
		

		<div class="wcfm-container">
			<div id="wcfm_build_listing_expander" class="wcfm-content">
				<div class="loader"></div>
				<div id="result"></div>
				<!---- Add Content Here ----->
				<form method="post" action="<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php" id="ep-import-product-form" class="ep-import-product-form">
					<input type="hidden" name="controller" value="wcfm-ep" />
               		<input type="hidden" name="action" value="wcfm_ajax_controller" />
               		<input type="hidden" name="import_product" value="1" />
               		<input type="hidden" name="ep_api_client_name" value="e-Mii" required/>
				</form>
				<form method="post" action="<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php" id="ep-api-settings-form" class="ep-settings-form">

                    <table class="form-table" summary="API Settings">
                    	<input type="hidden" name="controller" value="wcfm-ep" />
                    	<input type="hidden" name="action" value="wcfm_ajax_controller" />
						<input type="hidden" name="ep_api_url" value="https://d2web.epaisa.com/v1"/>
						<input type="hidden" name="ep_api_client_name" value="e-Mii" />
                        <tr valign="top">
                        <th scope="row">Client ID</th>
                        <td><input type="text" name="ep_api_client_id" value="<?php echo get_user_meta( get_current_user_id(), '_ep_api_client_id', true ); ?>" required/></td>
                        </tr>

                        <tr valign="top">
                        <th scope="row">Client Secret</th>
                        <td><input type="text" name="ep_api_client_secret" value="<?php echo get_user_meta( get_current_user_id(), '_ep_api_client_secret', true ); ?>" required/></td>
                        </tr>
                    </table>  
                    <p class="submit"><input type="submit" name="submit" class="button button-primary" value="Save Changes" id="save-button"> <input type="submit" name="submit" class="button button-primary" value="Import Products" id="import-button" form="ep-import-product-form"></p>
                </form>
			
				<div class="wcfm-clearfix"></div>
			</div>
			<div class="wcfm-clearfix"></div>
		</div>
	
		<div class="wcfm-clearfix"></div>
		<?php
		do_action( 'after_wcfm_build' );
		?>
	</div>
</div>