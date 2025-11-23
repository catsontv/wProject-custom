<?php if (!defined('ABSPATH')) exit;
    $wproject_theme  					= wp_get_theme();
    $theme_version 	  					= $wproject_theme->Version;
    $good                               = '<img src="' . get_template_directory_uri() . '/images/admin/check-circle.svg" />';
    $bad                                = '<img src="' . get_template_directory_uri() . '/images/admin/cross.svg" />';
    $max_upload                         = (int)(ini_get('upload_max_filesize'));
    $max_post                           = (int)(ini_get('post_max_size'));

    /* Get latest theme version from remove JSON */
    $json_path                          = file_get_contents('https://rocketapps.com.au/files/wproject/wproject/info.json');
    $json                               = json_decode($json_path, true);
    $remote_version                     = $json['version'];
    $remote_whats_new                   = $json['whats_new'];

    if (version_compare($theme_version, $remote_version) >= 0) {
        $theme_status = $good;
    } else {
        $theme_status = $bad;
    }

    /* PHP version */
    if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
        $php_status = $good;
    } else {
        $php_status = $bad;
    }

    /* PHP Memory */
    $ram_used               = round(memory_get_usage() / 1024 / 1024, 2);
    $ram_available          = ini_get('memory_limit');
    $the_ram_available      = str_replace(['M', 'G'], '', $ram_available);
    $ram_percentage         = ($ram_used * 100) / $the_ram_available;
    if ($ram_percentage <  90) { 
        $ram_percentage_status = $good;
        $bar_colour = "#2196f3";
    } else {
        $ram_percentage_status = $bad;
        $bar_colour = "#f44336";
    }

    /* WP Memory limit */
    $wp_memory              = WP_MEMORY_LIMIT;
    $the_wp_memory          = str_replace('M', '', $wp_memory);
    if($the_wp_memory > 40) {
        $the_wp_memory_status = $good;
    } else {
        $the_wp_memory_status = $bad;
    }

    /* WP memory used */
    $wp_memory_in_use       = size_format(@memory_get_usage(TRUE), 2);
    $the_wp_memory_in_use   = (int)str_replace('MB', '', $wp_memory_in_use);
    $wp_memory_percentage   = ($the_wp_memory_in_use * 100) / $the_wp_memory;
    $critical_mem_usage     = $the_wp_memory_in_use / $the_wp_memory * 100;
    if($critical_mem_usage < 90) {
        $critical_mem_usage_status = $good;
    } else {
        $critical_mem_usage_status = $bad;
    }

    /* Upload max file size */
    if ($max_upload < $max_post) { 
        $max_upload_status = $good;
    } else {
        $max_upload_status = $bad ;
    }

    /* Post max size */
    if($max_post > $max_upload) {
        $max_post_status = $good;
    } else {
        $max_post_status = $bad;
    }

    /* Clean slate */
    $page_ids = array(100,101,102,103,104,105,106,107,108,109);
    $pages_exist = true;

    foreach ($page_ids as $page_id) {
        if (!get_post($page_id)) {
            $pages_exist = false;
            break; /* Stop as soon as we find a page that doesn't exist */
        }
    }
    if ($pages_exist) {
        $clean_slate_status = $good;
        $clean_slate_message = __('Looking Good', 'wproject');
    } else {
        $clean_slate_status = $bad;
        $clean_slate_message = __('Problematic', 'wproject');
    }
    

?>

<div class="wproject-dash-box-container">

    <ul class="wproject-tabs">
        <li class="whats-new active"><?php _e( "What's New", 'wproject' ); ?></li>
        <li class="health-check"><?php _e( "Health Check", 'wproject' ); ?></li>
        <li class="support"><?php _e( "Support", 'wproject' ); ?></li>
    </ul>

    <section class="wproject-section whats-new active">

        <h2><?php _e( "What's New in", 'wproject' ); ?> v<?php echo $remote_version; ?></h2>
        <div class="json">
            <?php echo $remote_whats_new ; ?>
        </div>

        <a href="https://rocketapps.com.au/product/wproject/#changelog" target="_blank" rel="noopener noreferrer" class="wproject-button"><?php _e( "View complete historical changelog", 'wproject' ); ?></a>

    </section>

    <section class="wproject-section health-check">

        <h2><?php _e( "Health Check", 'wproject' ); ?></h2>
        <p style="margin: -15px 0 20px 0"><?php printf( __(' <a href="%1$s" target="_blank" rel="noopener">Learn more</a> about this health check.', 'wproject'), 'https://rocketapps.com.au/wproject/the-wproject-health-check/'); ?></p>

        <ul class="wproject-health">
            <li>
                <?php _e( 'Theme version', 'wproject' ); ?>
                 <span>
                    <?php echo $theme_version; ?>
                </span>
                <?php if($theme_status == $bad) { ?>
                    <a href="<?php echo admin_url(); ?>themes.php?theme=wproject" class="dash-button"><?php _e( 'Update Now', 'wproject' ); ?></a>
                <?php } ?>
            </li>
            <li>
                <?php _e( 'PHP version', 'wproject' ); ?>
                <span>
                    <?php echo $php_status; ?><?php echo phpversion(); ?>
                </span>  
            </li>

            <li>
                <?php _e( 'PHP memory', 'wproject' ); ?>
                <span>
                    <?php echo round($ram_used, 1); ?>MB / <?php echo $ram_available; ?>B (<?php echo round($ram_percentage, 1); ?>%) <?php _e( 'in use', 'wproject' ); ?>
                </span>
                <div title="<?php echo round($ram_percentage, 1); ?>%" class="progress-bar">
                    <em style="width:<?php echo round($ram_percentage, 1); ?>%; background:<?php echo $bar_colour; ?>"></em>
                </div>
            </li>

            <li>
                <?php _e( 'WP memory limit', 'wproject' ); ?>
                <span><?php echo $the_wp_memory_status; ?><?php echo $the_wp_memory; ?>MB</span>  
            </li>

            <li>
                <?php _e( 'WP memory', 'wproject' ); ?>
                <span>
                    <?php echo $critical_mem_usage_status; ?><?php echo $the_wp_memory_in_use; ?>MB / <?php echo $the_wp_memory; ?>MB (<?php echo round($wp_memory_percentage, 1); ?>%) <?php _e( 'in use', 'wproject' ); ?>
                </span>  
                <div title="<?php echo round($wp_memory_percentage, 1); ?>%" class="progress-bar">
                    <em style="width:<?php echo round($wp_memory_percentage, 1); ?>%; background:<?php echo $bar_colour; ?>"></em>
                </div>
            </li>

            <li>
                <?php _e( 'Upload max filesize', 'wproject' ); ?>
                <span>
                    <?php echo $max_upload_status; ?><?php echo $max_upload; ?>MB
                </span>
            </li>

            <li>
                <?php _e( 'Post max size', 'wproject' ); ?>
                <span>
                    <?php echo $max_post_status; ?><?php echo $max_post; ?>MB
                </span>  
            </li>

            <li>
                <?php _e( 'Clean slate', 'wproject' ); ?>
                <span>
                    <?php echo $clean_slate_status; ?><?php echo $clean_slate_message; ?>
                </span>  
            </li>
            
        </ul>

    </section>

    <section class="wproject-section support">

        <h2><?php _e( "Support", 'wproject' ); ?></h2>

        <a href="https://rocketapps.com.au/faq/?faq=wproject" target="_blank" rel="noopener noreferrer" class="wproject-button"><?php _e( "Read the wProject FAQ", 'wproject' ); ?></a>
    
        <a href="https://rocketapps.com.au/log-ticket" target="_blank" rel="noopener noreferrer" class="wproject-button"><?php _e( "Contact support", 'wproject' ); ?></a>
       
    </section>

    <script>
        jQuery('.wproject-tabs li').click(function() {
            var section = jQuery(this).attr('class');
        
            jQuery('.wproject-tabs li').removeClass('active');
            jQuery(this).addClass('active');

            jQuery('.wproject-section').removeClass('active');
            jQuery('.wproject-section.'+section).addClass('active');
        });
    </script>

</div>
