<?php if ( ! defined( 'ABSPATH' ) ) { exit; }
    $my_message_count       = message_count();
    $wproject_settings      = wProject();
    $enable_time            = $wproject_settings['enable_time'];
    $allow_pm_admin_access  = $wproject_settings['allow_pm_admin_access'];
    $user                   = wp_get_current_user();
    $user_id                = get_current_user_id();
    $fav_tasks              = get_user_meta( $user_id, 'fav_tasks' , true );
    $dark_mode              = get_user_meta( $user_id, 'dark_mode' , true );
    $user_role              = !empty($user->roles) ? $user->roles[0] : '';
    $current_url            = $wproject_settings['current_url'];
?>
<ul class="icons">
    <?php if(wp_is_mobile()) { ?>
        <li class="toggle-search"><i data-feather="search"></i></li>
    <?php } ?>
    
        <?php if($enable_time) { 
            task_in_progress();
            }
        ?>

        <?php if($fav_tasks) { ?>
        <li class="my-favs" title="<?php _e('Followed tasks', 'wproject'); ?>">
            <span class="icon-container fade">
            <i data-feather="star"></i>
            </span>
            <?php get_template_part('inc/my-follows') ?>
        </li>
        <?php } ?>

        <?php if($user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member' || $user_role == 'observer') { ?>
            
            <?php if($user_role != 'observer') { ?>
            <li class="notify" title="<?php _e('Notifications', 'wproject'); ?>">
                <b <?php if($my_message_count['count'] < 1) { echo 'class="fade"'; } ?>>
                    <i data-feather="bell"></i>
                </b>
                <?php if($my_message_count['count'] > 0) { ?>
                    <span class="message-count"><?php echo $my_message_count['count']; ?></span>
                    <?php get_template_part('inc/notifications'); ?>
                <?php } ?>
            </li>
            <?php } ?>

            <li class="notify-comment" title="<?php _e('Recent comments', 'wproject'); ?>">
                <i data-feather="message-circle"></i>
                <?php get_template_part('inc/recent-comments'); ?>
            </li>

            <?php if(!wp_is_mobile()) { ?>
                <li class="print" onclick="return printPopUp('<?php echo $current_url ; ?>?print=yes')" title="<?php _e('Print', 'wproject'); ?>"><i data-feather="printer"></i></li>
            <?php } ?>

            <li class="toggle-help" title="<?php _e('Help', 'wproject'); ?>"><i data-feather="help-circle"></i></li>

            <?php if(!wp_is_mobile()) { ?>
            <form id="switch-theme-form" class="switch-theme-form-facade" method="post" enctype="multipart/form-data">
                <li class="toggle-mode">
                    <i data-feather="sun"></i>
                    <i data-feather="moon"></i>
                </li>
                <input type="hidden" name="dark_mode" id="dark_mode" value="" />
            </form>
            <script>
                $('#switch-theme-form').click(function(){
                    $(this).removeClass('switch-theme-form-facade');
                });
            </script>
            <?php } ?>
        
        <?php } ?>

    <?php if(!wp_is_mobile()) { ?>
        <li title="<?php _e('Log out', 'wproject'); ?>"><a href="<?php echo wp_logout_url( home_url() ); ?>" onclick="return confirm('<?php _e('Really log out?', 'wproject'); ?>')"><i data-feather="log-out"></i></a></li>
    <?php } ?>
    
    <li class="toggle-sidebar <?php if(!wp_is_mobile()) { echo 'active'; } ?>" title="<?php _e('Toggle sidebar', 'wproject'); ?>"><i data-feather="sidebar"></i></li>
    
    <?php if(!wp_is_mobile()) {
    if($user_role == 'administrator' || $allow_pm_admin_access == 'on' && $user_role == 'project_manager') { ?>
        <li title="<?php _e('Settings', 'wproject'); ?>"><a href="<?php echo admin_url(); ?>admin.php?page=wproject-settings" target="_blank"><i data-feather="settings"></i></a></li>
    <?php }
    } ?>
</ul>

<button class="hamburger">
    <span></span>
    <span></span>
    <span></span>
</button>

<script>   
    $('.hamburger').click(function() {
        $(this).toggleClass('animate');
        $('.left').toggleClass('move');
    });

    $( document ).ready(function() {

        /* Hide filters is using the top icons */
        $('.icons .my-favs, .icons .notify, .icons .notify-comment, .icons .toggle-help').click(function() {
            $('.filter-selection').hide();
            $('.filters').removeClass('open');
            $('.filter-row').hide();
            $('.project-page .body-rows li').show();
        });

        /* Sidebar toggle */
        function showSidebar() {
            $('.right').addClass('move');
            $('.middle').addClass('wide');
            $('.toggle-sidebar').removeClass('active');
            Cookies.set('right-sidebar', 'hidden');
        }
        function hideSidebar() {
            $('.right').removeClass('move');
            $('.middle').removeClass('wide');
            $('.toggle-sidebar').addClass('active');
            Cookies.set('right-sidebar', 'visible');
        }
        
        var sidebar_state = Cookies.get('right-sidebar');

        $('header .icons .toggle-sidebar').click(function() {
            $('.help').removeClass('move');  
            
            if ($( 'section.right' ).hasClass('move')) {
                hideSidebar();
            } else {
                showSidebar();
            }
            
            //return (this.tog = !this.tog) ? showSidebar() : hideSidebar();
        });

        if(sidebar_state == 'hidden') {
            $('.right').addClass('move');
            $('.middle').addClass('wide');
            $('.toggle-sidebar').removeClass('active');
        } else {
            $('.right').removeClass('move');
            $('.middle').removeClass('wide');
            <?php if(!wp_is_mobile()) { ?>
            $('.toggle-sidebar').addClass('active');
            <?php } ?>

        }

        <?php /* Never keep the sidebar open on small screens */
        if(wp_is_mobile()) { ?>
            if($(window).width() < 960) {
                $( document ).ready(function() {
                    $('.right').removeClass('move');
                });
            }
        <?php } ?>


        <?php if(!wp_is_mobile()) { ?>
        /* Left sidebar toggle */
        function showSideBars() {
            $('.toggle-sidebars').removeClass('active');
            $('section.left').removeClass('repos');
            $('.logout').fadeIn();
            $('header').removeClass('repos');
            $('section.middle').removeClass('repos');
            $('.middle').removeClass('wide');
            $('section.right').removeClass('move');
            $('.toggle-sidebar').removeClass('disabled');
            Cookies.set('all-sidebars', 'visible');
            Cookies.set('right-sidebar', 'visible');
            //console.log('show all sections');
        }
        function hideSideBars() {
            $('.toggle-sidebars').addClass('active');
            $('section.left').addClass('repos');
            $('.logout').fadeOut();
            $('header').addClass('repos');
            $('section.middle').addClass('repos');
            $('.middle').addClass('wide');
            $('section.right').addClass('move');
            $('.toggle-sidebar').addClass('disabled');
            Cookies.set('all-sidebars', 'hidden');
            //console.log('hide all sections');
        }
        
        var left_sidebar_state = Cookies.get('all-sidebars');

        $('.toggle-sidebars').click(function() {
            $('.help').removeClass('move');  
            
            if ($( 'section.right' ).hasClass('move') && !$( 'section.left' ).hasClass('repos')) {
                hideSideBars();
            } else if ($( 'section.right' ).hasClass('move') && $( 'section.left' ).hasClass('repos')) {
                showSideBars();
            } else if (!$( 'section.right' ).hasClass('move') && !$( 'section.left' ).hasClass('repos')) {
                hideSideBars();
            } else {
                hideSideBars();
            }
        
        });

        if(left_sidebar_state == 'hidden') {
            $('.toggle-sidebars').addClass('active');
            $('section.left').addClass('repos');
            $('.logout').fadeOut();
            $('header').addClass('repos');
            $('section.middle').addClass('repos');
            $('section.right').addClass('move');
            $('.toggle-sidebar').addClass('disabled');
        }
        <?php } ?>
       
    });


    <?php if(!wp_is_mobile()) { ?>
    /* Toggle Dark and Light modes */
    function light_css() {
         /* Add all dark CSS here */
        $('#wproject-dark-style-css, #gantt_pro_css_dark-css, #contacts_pro_dark_css-css').remove();
        $('.reports-pro').removeClass('dark'); /* Reports Pro handles dark CSS with a class instead of a dedicated stylesheet */

        $('.toggle-mode .feather-sun').show();
        $('.toggle-mode .feather-moon').hide();
        $('#dark_mode').val('no');
        $('#switch-theme-form').attr('title', '<?php _e('Switch to dark mode', 'wproject'); ?>');
        
        setTimeout(function() { 
            $('#switch-theme-form').submit();
        }, 250)
    }
    function dark_css() {
        /* Add all dark stylesheets here */
        $('#wproject-style-css').after('<link rel="stylesheet" id="wproject-dark-style-css" href="<?php echo get_template_directory_uri();?>/css/dark.css?id='+ new Date().getMilliseconds()+'" type="text/css" media="all" /><link rel="stylesheet" id="gantt_pro_css_dark-css" href="<?php echo home_url();?>/wp-content/plugins/gantt-pro/gantt/frappe-gantt-dark.css?id='+ new Date().getMilliseconds()+'" type="text/css" media="all" /><link rel="stylesheet" id="contacts_pro_dark_css-css" href="<?php echo home_url();?>/wp-content/plugins/contacts-pro/css/contacts-pro-dark.css?id='+ new Date().getMilliseconds()+'" type="text/css" media="all" />');
        $('.reports-pro').addClass('dark'); /* Reports Pro handles dark CSS with a class instead of a dedicated stylesheet */
        
        $('.toggle-mode .feather-moon').show();
        $('.toggle-mode .feather-sun').hide();
        $('#dark_mode').val('yes');
        $('#switch-theme-form').attr('title', '<?php _e('Switch to light mode', 'wproject'); ?>');


        setTimeout(function() { 
            $('#switch-theme-form').submit();
        }, 250)
    }

    $( document ).ready(function() {
        <?php if($dark_mode == 'yes') { ?>
            $('.toggle-mode .feather-sun').hide();
            $('.toggle-mode .feather-moon').show();
            $('#switch-theme-form').attr('title', '<?php _e('Switch to light mode', 'wproject'); ?>');

            $('.toggle-mode').click(function() { 
                return (this.tog = !this.tog) ? light_css() : dark_css();
            });
        <?php } else { ?>
            $('.toggle-mode .feather-sun').show();
            $('.toggle-mode .feather-moon').hide();
            $('#switch-theme-form').attr('title', '<?php _e('Switch to dark mode', 'wproject'); ?>');

            $('.toggle-mode').click(function() { 
                return (this.tog = !this.tog) ? dark_css() : light_css();
            });
        <?php } ?>
    });
    <?php } ?>
</script>