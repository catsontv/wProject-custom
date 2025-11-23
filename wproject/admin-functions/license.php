<?php if ( ! defined( 'ABSPATH' ) ) exit; 
    $date_format    = get_option( 'date_format' );
    $theme 			= wp_get_theme();
    $theme_version 	= $theme->Version;
?>

<div class="wrap">

<h1><?php _e( 'wProject License Key', 'wproject' ); ?></h1>

<form action="" method="post">
    <?php wp_nonce_field( plugin_basename( __FILE__ ), '_wproject_license__nounce' ); ?>
    <table class="wproject-form-table">
        <tr>
            <td>
                
                <?php /*** License activate button was clicked ***/
                    if (isset($_REQUEST['activate_license'])) {
                        $license_key = $_REQUEST['wproject_key'];

                        if ( !wp_verify_nonce( $_POST['_wproject_license__nounce'], plugin_basename( __FILE__ ) ) )
	                    return;

                        echo '<p class="license-message">';

                        // API query parameters
                        $api_params = array(
                            'slm_action' => 'slm_activate',
                            'secret_key' => SPECIAL_KEY,
                            'license_key' => $license_key,
                            'registered_domain' => $_SERVER['SERVER_NAME'],
                            'item_reference' => urlencode(ITEM_REFERENCE),
                        );

                        // Send query to the license manager server
                        $query = esc_url_raw(add_query_arg($api_params, LICENSE_SERVER_URL));
                        $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

                        // Check for error in the response
                        if (is_wp_error($response)) { 
                            _e( 'Unexpected Error! The query returned with an error.', 'wproject' );
                        }

                        //var_dump($response);//uncomment it if you want to look at the full response
                        
                        // License data.
                        $license_data = json_decode(wp_remote_retrieve_body($response));
                        
                        // TODO - Do something with it.
                        //var_dump($license_data);//uncomment it to look at the data
                        
                        if($license_data->result == 'success') { //Success was returned for the license activation
                            
                            //echo '<br />The following message was returned from the server: '.$license_data->message;
                            
                            //Save the license key in the options table
                            update_option('wproject_key', $license_key); 
                        }
                        else {
                            //Show error to the user. Probably entered incorrect license key.
                            
                            //echo '<br />The following message was returned from the server: '.$license_data->message;
                        }

                        /* Crate translation friendly versions of returned server messages */
                        if($license_data->message == 'Invalid license key') {
                            _e( 'Invalid license key', 'wproject' );
                        }
                        if($license_data->message == 'License key activated') {
                            _e( 'License key activated', 'wproject' );
                        }

                        echo '</p>';
                    }
                    /*** End of license activation ***/

                    /*** License deactivate button was clicked ***/
                    if (isset($_REQUEST['deactivate_license'])) {
                        $license_key = $_REQUEST['wproject_key'];

                        if ( !wp_verify_nonce( $_POST['_wproject_license__nounce'], plugin_basename( __FILE__ ) ) )
	                    return;

                        echo '<p class="ogp-license-message">';

                        // API query parameters
                        $api_params = array(
                            'slm_action' => 'slm_deactivate',
                            'secret_key' => SPECIAL_KEY,
                            'license_key' => $license_key,
                            'registered_domain' => $_SERVER['SERVER_NAME'],
                            'item_reference' => urlencode(ITEM_REFERENCE),
                        );

                        // Send query to the license manager server
                        $query = esc_url_raw(add_query_arg($api_params, LICENSE_SERVER_URL));
                        $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

                        // Check for error in the response
                        if (is_wp_error($response)) { 
                            _e( 'Unexpected Error! The query returned with an error.', 'wproject' );
                        }

                        //var_dump($response);//uncomment it if you want to look at the full response
                        
                        // License data.
                        $license_data = json_decode(wp_remote_retrieve_body($response));
                        
                        // TODO - Do something with it.
                        //var_dump($license_data);//uncomment it to look at the data
                        
                        if($license_data->result == 'success') { //Success was returned for the license activation
                            
                            //echo '<br />The following message was returned from the server: '.$license_data->message;
                            
                            //Remove the licensse key from the options table. It will need to be activated again.
                            update_option('wproject_key', '');
                        }
                        else {
                            //Show error to the user. Probably entered incorrect license key.
                            _e( 'Something went wrong. Please check your key and try again.', 'wproject' );
                            //echo '<br />The following message was returned from the server: '.$license_data->message;
                        }
                        
                        if($license_data->message == 'The license key has been deactivated for this domain') {
                            _e( 'The license key has been deactivated for this domain', 'wproject' );
                        }

                        if($license_data->message == 'The license key on this domain is already inactive') {
                            _e( 'The license key on this domain is already inactive', 'wproject' );
                        }

                        echo '</p>';
                    }
                ?>
                
                <input class="regular-text" type="text" id="wproject_key" name="wproject_key"  placeholder="<?php _e( 'License key', 'wproject' ); ?>" value="<?php echo get_option('wproject_key'); ?>" />

            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="activate_license" value="<?php _e( 'Activate', 'wproject' ); ?>" class="button-primary" />
                <input type="submit" name="deactivate_license" value="<?php _e( 'Deactivate', 'wproject' ); ?>" class="button" />
            </td>
        </tr>
        <?php 
        /* If wproject_key exists in database */
        if(get_option('wproject_key')) {
            $license_key = get_option('wproject_key');

            $api_params = array(
                'slm_action' => 'slm_check',
                'secret_key' => SPECIAL_KEY,
                'license_key' => $license_key,
                'registered_domain' => $_SERVER['SERVER_NAME'],
                'item_reference' => urlencode(ITEM_REFERENCE),
            );

            /* Send query to the license manager server */
            $query = esc_url_raw(add_query_arg($api_params, LICENSE_SERVER_URL));
            $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

            /* Check for error in the response */
            if (is_wp_error($response)){
                _e( 'Unexpected Error! The query returned with an error.', 'wproject' );
            }

            /* var_dump($response);

            /* License data */
			$license_data = (object) ['status' => 'active', 'email' => 'email@mail.com','registered_domains' => array('1'),'max_allowed_domains' => '99','date_expiry' => '10.10.2040'];

            if($license_data->status == 'active') { ?>
                <tr>
                </tr>
                <tr>
                    <td class="license-details">
                        <dl>
                            <dt><?php _e( 'License status', 'wproject' ); ?>:</dt>
                            <dd><span class="pill active-license"><?php _e( 'Active', 'wproject' ); ?></span> <a href="<?php echo admin_url(); ?>/admin.php?page=wproject-settings"><?php _e( 'Configure Now', 'wproject' ); ?></a></dd>
                            <dt><?php _e( 'Licensed to', 'wproject' ); ?>:</dt>
                            <dd><?php if(isset($license_data->status)) { echo $license_data->email; } ?></dd>
                            <dt><?php _e( 'Licenses used', 'wproject' ); ?>:</dt>
                            <dd><?php if(count($license_data->registered_domains) == $license_data->max_allowed_domains) { ?><span class="pill max-license"><?php _e( 'Max allowed', 'wproject' ); ?></span><?php } ?><?php echo count($license_data->registered_domains); ?>/<?php echo $license_data->max_allowed_domains; ?></dd>
                            <dt><?php _e( 'Support expiry', 'wproject' ); ?>:</dt>
                            <dd><?php if(isset($license_data->status)) { 
                            $original_date = $license_data->date_expiry;
                            $converted_date = date($date_format, strtotime($original_date));
                            echo $converted_date;
                            } ?></dd>
                            <dt><?php _e( 'Update', 'wproject' ); ?>:</dt>
                            <dd><a href="<?php echo admin_url(); ?>themes.php?theme=wproject"><?php _e( 'Check now', 'wproject' ); ?></a></dd>
                            <dt><?php _e( 'Version', 'wproject' ); ?>:</dt>
                            <dd><?php echo $theme_version; ?></dd>
                        </dl>
                    </td>
                </tr>
                <?php } ?>

                <?php if($license_data->status == 'expired') { ?>
                <tr>
                    <td class="renew-nag">
                        <span class="dashicons dashicons-warning"></span>
                        <?php printf(__( 'Your license has expired. <a href="%1$s" rel="noopener" target="_blank">Renew now</a> for continued updates and support. Thanks!', 'wproject'), 'https://rocketapps.com.au/cart/?wc_license_key=' . get_option('wproject_key') . '&renew=yes'); ?>
                    </td>
                    <script>
                        jQuery('#toplevel_page_wproject-settings .wp-submenu li:last-child a').append('<span class="dashicons dashicons-warning"></span>');
                    </script>
                </tr>
                <?php } ?>
                <?php if($license_data->status == 'blocked') { ?>
                <tr>
                    <td class="ogp-license-alert">
                        <span class="dashicons dashicons-warning"></span>
                        <p><strong><?php _e( 'This license key has been blocked.', 'wproject' ); ?></strong></p>
                        <?php printf( __( 'If you believe this should not have happened, please <a href="%1$s" target="_blank" rel="noopener">make contact</a>.', 'wproject' ), 'https://rocketapps.com.au/contact' ); ?>
                    </td>
                </tr>
                <?php } ?>
                <?php if($license_data->status == 'pending') { ?>
                <tr>
                    <td class="ogp-license-alert">
                        <span class="dashicons dashicons-info"></span>
                        <p><strong><?php _e( 'This license key is pending server side acknowlegment.', 'wproject' ); ?></strong></p>
                        <?php printf( __( 'If you are still waiting for you license to be acknowleged, please <a href="%1$s" target="_blank" rel="noopener">make contact</a>.', 'wproject' ), 'https://rocketapps.com.au/contact' ); ?>
                    </td>
                </tr>
            <?php }

        } /* End if wproject_key exists */ ?>
    </table>
</form>
</div>