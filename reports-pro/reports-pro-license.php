<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">

    <h1><?php _e( 'Reports Pro License', 'wproject-reports-pro' ); ?></h1>

    <p><?php _e("Please activate your license to get access to updates and support.",  'wproject-reports-pro'); ?></p>
        
    <form action="" method="post">
        <?php wp_nonce_field( plugin_basename( __FILE__ ), '_report_pro__nounce' ); ?>
        <table class="epu-table">
            <tr>
                <td>
                    
                    <?php /*** License activate button was clicked ***/
                        if (isset($_REQUEST['activate_license'])) {
                            $license_key = $_REQUEST['report_pro_key'];

                            if ( !wp_verify_nonce( $_POST['_report_pro__nounce'], plugin_basename( __FILE__ ) ) )
                            return;

                            echo '<p class="epu-license-message">';

                            // API query parameters
                            $api_params = array(
                                'slm_action' => 'slm_activate',
                                'secret_key' => REPORTS_PRO_SPECIAL_KEY,
                                'license_key' => $license_key,
                                'registered_domain' => $_SERVER['SERVER_NAME'],
                                'item_reference' => urlencode(REPORTS_PRO_ITEM_REFERENCE),
                            );

                            // Send query to the license manager server
                            $query = esc_url_raw(add_query_arg($api_params, REPORTS_PRO_LICENSE_SERVER_URL));
                            $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

                            // Check for error in the response
                            if (is_wp_error($response)) { 
                                _e( 'Unexpected Error! The query returned with an error.', 'wproject-reports-pro' );
                            }

                            //var_dump($response);//uncomment it if you want to look at the full response
                            
                            // License data.
                            $license_data = json_decode(wp_remote_retrieve_body($response));
                            
                            // TODO - Do something with it.
                            //var_dump($license_data);//uncomment it to look at the data
                            
                            if($license_data->result == 'success') { //Success was returned for the license activation
                                
                                //echo '<br />The following message was returned from the server: '.$license_data->message;
                                
                                //Save the license key in the options table
                                update_option('report_pro_key', $license_key); 
                            }
                            else {
                                //Show error to the user. Probably entered incorrect license key.
                                
                                //echo '<br />The following message was returned from the server: '.$license_data->message;
                            }

                            /* Crate translation friendly versions of returned server messages */
                            if($license_data->message == 'Invalid license key') {
                                _e( 'Invalid license key', 'wproject-reports-pro' );
                            }
                            if($license_data->message == 'License key activated') {
                                _e( 'Your license is activate.', 'wproject-reports-pro' );
                            }

                            echo '</p>';
                            
                        }
                        /*** End of license activation ***/

                        /*** License deactivate button was clicked ***/
                        if (isset($_REQUEST['deactivate_license'])) {
                            $license_key = $_REQUEST['report_pro_key'];

                            if ( !wp_verify_nonce( $_POST['_report_pro__nounce'], plugin_basename( __FILE__ ) ) )
                            return;

                            echo '<p class="epu-license-message">';

                            // API query parameters
                            $api_params = array(
                                'slm_action' => 'slm_deactivate',
                                'secret_key' => REPORTS_PRO_SPECIAL_KEY,
                                'license_key' => $license_key,
                                'registered_domain' => $_SERVER['SERVER_NAME'],
                                'item_reference' => urlencode(REPORTS_PRO_ITEM_REFERENCE),
                            );

                            // Send query to the license manager server
                            $query = esc_url_raw(add_query_arg($api_params, REPORTS_PRO_LICENSE_SERVER_URL));
                            $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

                            // Check for error in the response
                            if (is_wp_error($response)) { 
                                _e( 'Unexpected Error! The query returned with an error.', 'wproject-reports-pro' );
                            }

                            //var_dump($response);//uncomment it if you want to look at the full response
                            
                            // License data.
                            $license_data = (object) ['status' => 'active', 'email' => 'email@mail.com','registered_domains' => array('1'),'max_allowed_domains' => '99','date_expiry' => '10.10.2040','' => '',];
                            
                            // TODO - Do something with it.
                            //var_dump($license_data);//uncomment it to look at the data
                            
                            if($license_data->result == 'success') { //Success was returned for the license activation
                                
                                //echo '<br />The following message was returned from the server: '.$license_data->message;
                                
                                //Remove the licensse key from the options table. It will need to be activated again.
                                update_option('report_pro_key', '');
                            }
                            else {
                                //Show error to the user. Probably entered incorrect license key.
                                _e( 'Something went wrong. Please check your key and try again.', 'wproject-reports-pro' );
                                //echo '<br />The following message was returned from the server: '.$license_data->message;
                            }
                            
                            if($license_data->message == 'The license key has been deactivated for this domain') {
                                _e( 'The license key has been deactivated for this domain', 'wproject-reports-pro' );
                            }

                            if($license_data->message == 'The license key on this domain is already inactive') {
                                _e( 'The license key on this domain is already inactive', 'wproject-reports-pro' );
                            }

                            echo '</p>';

                        }

                        
                    ?>
                    <input class="regular-text" type="<?php if(get_option('report_pro_key')) { echo 'password'; } else { echo 'text'; } ?>" id="report_pro_key" name="report_pro_key"  placeholder="<?php _e( 'License key', 'wproject-reports-pro' ); ?>" value="<?php echo get_option('report_pro_key'); ?>" />

                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" name="activate_license" value="<?php _e( 'Activate', 'wproject-reports-pro' ); ?>" class="button-primary" />
                    <input type="submit" name="deactivate_license" value="<?php _e( 'Deactivate', 'wproject-reports-pro' ); ?>" class="button" />
                </td>
            </tr>
            <?php 
            /* If report_pro_key exists in database */
            if(get_option('report_pro_key')) {
                $license_key = get_option('report_pro_key');

                $api_params = array(
                    'slm_action' => 'slm_check',
                    'secret_key' => REPORTS_PRO_SPECIAL_KEY,
                    'license_key' => $license_key,
                    'registered_domain' => $_SERVER['SERVER_NAME'],
                    'item_reference' => urlencode(REPORTS_PRO_ITEM_REFERENCE),
                );

                /* Send query to the license manager server */
                $query = esc_url_raw(add_query_arg($api_params, REPORTS_PRO_LICENSE_SERVER_URL));
                $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

                /* Check for error in the response */
                if (is_wp_error($response)){
                    _e( 'Unexpected Error! The query returned with an error.', 'wproject-reports-pro' );
                }

                /* var_dump($response);

                /* License data */
                $license_data = json_decode(wp_remote_retrieve_body($response));
                $date_format = get_option( 'date_format' );
                if(isset($license_data->status) == 'active') { ?>
                    <tr>
                    </tr>
                    <tr>
                        <td class="license-details">
                            <dl>
                                <dt><?php _e( 'License status', 'wproject-reports-pro' ); ?>:</dt>
                                <dd><span class="pill active-license"><?php _e( 'Active', 'wproject-reports-pro' ); ?></span> <a href="<?php echo admin_url(); ?>/admin.php?page=wproject-settings&section=reports-pro" class="configure-button"><?php _e( 'Configure Reports Pro', 'wproject-reports-pro' ); ?></a></dd>
                                <dt><?php _e( 'Licensesd to', 'wproject-reports-pro' ); ?>:</dt>
                                <dd><?php if(isset($license_data->status)) { echo $license_data->email; } ?></dd>
                                <dt><?php _e( 'Licenses used', 'wproject-reports-pro' ); ?>:</dt>
                                <dd><?php if(count($license_data->registered_domains) == $license_data->max_allowed_domains) { ?><span class="pill max-license"><?php _e( 'Max allowed', 'wproject-reports-pro' ); ?></span><?php } ?><?php echo count($license_data->registered_domains); ?>/<?php echo $license_data->max_allowed_domains; ?></dd>
                                <dt><?php _e( 'Licensed domains', 'wproject-reports-pro' ); ?>:</dt>
                                <dd><?php sort($license_data->registered_domains);
                                foreach($license_data->registered_domains as $values) {
                                echo '<a href="//' . $values->registered_domain . '" target="_blank" rel="noopener">' . $values->registered_domain . '</a><br />';
                                } ?></dd>
                                <dt><?php _e( 'Support expiry', 'wproject-reports-pro' ); ?>:</dt>
                                <dd><?php if(isset($license_data->status)) { 
                                $original_date = $license_data->date_expiry;
                                $converted_date = date($date_format, strtotime($original_date));
                                echo $converted_date;
                                } ?></dd>
                            </dl>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if($license_data->status == 'expired') { ?>
                    <tr>
                        <td class="epu-license-alert">
                            <span class="dashicons dashicons-warning"></span>
                            <p><strong><?php _e( 'Your support license has expired.', 'wproject-reports-pro' ); ?></strong></p>
                            <?php printf( __( 'For continued support and updates, <a href="%1$s" target="_blank" rel="noopener">renew your license today</a>.', 'wproject-reports-pro' ), 'https://rocketapps.com.au/downloads/' ); ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if($license_data->status == 'blocked') { ?>
                    <tr>
                        <td class="epu-license-alert">
                            <span class="dashicons dashicons-warning"></span>
                            <p><strong><?php _e( 'This license key has been blocked.', 'wproject-reports-pro' ); ?></strong></p>
                            <?php printf( __( 'If you believe this should not have happened, please <a href="%1$s" target="_blank" rel="noopener">make contact</a>.', 'wproject-reports-pro' ), 'https://rocketapps.com.au/contact' ); ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if($license_data->status == 'pending') { ?>
                    <tr>
                        <td class="epu-license-alert">
                            <span class="dashicons dashicons-info"></span>
                            <p><strong><?php _e( 'This license key is pending server side acknowledgment.', 'wproject-reports-pro' ); ?></strong></p>
                            <?php printf( __( 'If you are still waiting for your license to be acknowledged, please <a href="%1$s" target="_blank" rel="noopener">make contact</a>.', 'wproject-reports-pro' ), 'https://rocketapps.com.au/contact' ); ?>
                        </td>
                    </tr>
                <?php }

            } /* End if report_pro_key exists */ ?>
        </table>
    </form>

</div>