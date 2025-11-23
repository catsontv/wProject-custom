<?php $wproject_settings    = wProject();
    $project_access         = $wproject_settings['project_access'];
    $system_busy_blur       = $wproject_settings['system_busy_blur'];
    $system_busy_disable_ui = $wproject_settings['system_busy_disable_ui'];
    $context_label_colour   = $wproject_settings['context_label_colour'];
    $user                   = wp_get_current_user();
    $user_role              = !empty($user->roles) ? $user->roles[0] : '';

    /* If user is a client and the Clients Pro plugin is not activated, don't let the client in. */
    if($user_role == 'client' && !function_exists('add_clients_pro_settings_page')) {
        exit;
    }

    /* If user is am operator, redirect to wProject settings page. */
    if($user_role == 'operator') {
        wp_redirect(admin_url() . 'admin.php?page=wproject-settings');
        exit;
    }

if(!is_user_logged_in()) {
	header('location: ' . wp_login_url());
}
?>
<!DOCTYPE HTML>
<?php 
$options                = get_option( 'wproject_settings' );
$task_comments_enabled  = isset($options['task_comments_enabled']) ? $options['task_comments_enabled'] : '';
$page_comments_enabled  = isset($options['page_comments_enabled']) ? $options['page_comments_enabled'] : '';
$avatar_style           = isset($options['avatar_style']) ? $options['avatar_style'] : '';
$bypass_google          = isset($options['bypass_google']) ? $options['bypass_google'] : '';

$license_key            = get_option('wproject_key');
if(empty($avatar_style)) {
    $the_avatar_style = 'rounded-corners';
} else if($avatar_style == 'rounded-corners') {
    $the_avatar_style = 'rounded-corners';
} else if($avatar_style == 'circular') {
    $the_avatar_style = 'circular';
} else if($avatar_style == 'square') {
    $the_avatar_style = 'square';
} else {
    $the_avatar_style = $avatar_style;
}
?>
<html>
<head>
<meta charset="UTF-8" />
<title>
    <?php 
        if(is_tax()) {
            echo single_cat_title();
        } else if(is_singular('task')) {
            echo get_the_title();
        } else {
            echo get_bloginfo( 'name' );
        }
    ?>
</title>

<?php do_action( 'before_wp_head' ); ?>    

<?php 
    wp_head();
    if(is_singular('task') && $task_comments_enabled == "on" || is_page() &&  $page_comments_enabled == "on") {
        wp_enqueue_script( 'comment-reply' );
    }
?>

<?php do_action( 'after_wp_head' ); ?>  
  
<?php if(!empty($avatar_style)) { ?>
    <script>
        $( document ).ready(function() {
            $('.avatar, #avatar, .body-rows .avatar img, .all-comments .comment-img img').addClass('<?php echo $the_avatar_style; ?>');
        });
    </script>
    <link rel='stylesheet' id='avatars-css'  href='<?php echo get_template_directory_uri();?>/css/avatars.css' type='text/css' media='all' />
<?php } ?>

<?php if($system_busy_blur) { ?>
    <style>
        .blur {
            filter: blur(3px);
        }
    </style>
<?php } ?>
<?php if($system_busy_disable_ui) { ?>
    <style>
        .disable_ui {
            pointer-events: none;
            opacity: .5;
        }
    </style>
<?php } ?>

<?php if($context_label_colour) { ?>
    <style>

        <?php if($bypass_google == 'on') { ?>
            /* latin-400-normal */
            @font-face {
                font-family: 'Quicksand';
                font-style: normal;
                font-weight: 400;
                font-stretch: 100%;
                font-display: swap;
                src: url('<?php echo get_template_directory_uri(); ?>/fonts/quicksand-latin-400-normal.woff2') format('woff2');
                unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
            }

            /* latin-500-normal */
            @font-face {
                font-family: 'Quicksand';
                font-style: normal;
                font-weight: 500;
                font-stretch: 100%;
                font-display: swap;
                src: url('<?php echo get_template_directory_uri(); ?>/fonts/quicksand-latin-500-normal.woff2') format('woff2');
                unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
            }

            /* latin-700-normal */
            @font-face {
                font-family: 'Quicksand';
                font-style: normal;
                font-weight: 700;
                font-stretch: 100%;
                font-display: swap;
                src: url('<?php echo get_template_directory_uri(); ?>/fonts/quicksand-latin-700-normal.woff2') format('woff2');
                unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0300-0301, U+0303-0304, U+0308-0309, U+0323, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
            }
        <?php } else { ?>
            @import url('https://fonts.googleapis.com/css?family=Quicksand:400,500,700');
        <?php } ?>

        .tabby-project .body-rows .more-details li.context-label,
        .single .context-label,
        .page .context-label,
        .kanban-context-label .context-label {
            background: <?php echo $context_label_colour; ?> !important;
            color: #fff;
        }
        .tabby-project .body-rows .more-details li.context-label a,
        .kanban-context-label .context-label,
        .single .context-label a {
            color: #fff;
        }
        /* 
        .filters .sep {
            background: <?php echo $context_label_colour; ?> !important;
            color: #fff;
        }
        .filters .sep.sep-context {
            /*border-bottom: solid 2px <?php echo $context_label_colour; ?> !important;
            color: <?php echo $context_label_colour; ?> !important;
        }
        .filters .sep.sep-context:after {
            content: '';
            background: <?php echo $context_label_colour; ?> !important;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            opacity: .1;
        }
        
        .filters .sep.sep-context svg {
            stroke: <?php echo $context_label_colour; ?> !important;
        }
        
        .filters .sep:hover {
            color: #fff;
        }
        */
        .task-project .context-label {
            color: #fff;
            border: none !important;
        }
        <?php if(isset($_GET['print'])) { ?>
            .tabby-project .body-rows .more-details li.context-label,
            .single .context-label,
            .page .context-label,
            .kanban-context-label .context-label {
                background: none !important;
                color: <?php echo $context_label_colour; ?> !important;
                border: solid 2px <?php echo $context_label_colour; ?> !important;
                padding: .035cm .75em;
                text-transform: uppercase;
                border-radius: 100em;
            }
            .tabby-project .body-rows .more-details li.context-label a,
            .single .context-label a,
            .kanban-context-label .context-label {
                color: <?php echo $context_label_colour; ?> !important;
                border: solid 2px <?php echo $context_label_colour; ?> !important;
            }
            .filters .sep {
                background: <?php echo $context_label_colour; ?> !important;
                color: <?php echo $context_label_colour; ?> !important;
            }
            .filters .seps.sep-context {
                border-bottom: solid 2px <?php echo $context_label_colour; ?> !important;
                color: <?php echo $context_label_colour; ?> !important;
            }
            .filters .sep:hover {
                color: <?php echo $context_label_colour; ?> !important;
                border: solid 2px <?php echo $context_label_colour; ?> !important;
            }
            .task-project .context-label {
                color: #fff;
                border: solid 2px <?php echo $context_label_colour; ?> !important;
            }
            .letter-avatar {
                display: none !important;
            }
        <?php } ?>
    </style>
<?php } ?>

</head>

<body id="fouc" <?php if(isset($_GET['print'])) { ?>onload="window.print()"<?php } ?> class="page <?php if(is_search()) { echo ' home'; } if(is_single()) { echo $post->post_name; } if(is_singular('task')) { echo ' single'; } echo ' role-' . $user_role; ?> <?php if(is_tax()) { echo 'project-page'; } ?>">

<?php do_action( 'after_body_start' ); ?>

<!--/ Start Header /-->
<?php if(empty($_GET['print'])) { ?>
<header>
    <?php 
        do_action( 'after_header_start' );

        get_template_part('inc/search');
        get_template_part('inc/header-icons');

        do_action( 'before_header_end' );
    ?>    
</header>
<?php } ?>
<!--/ End Header /-->