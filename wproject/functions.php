<?php
/* Run admin setup functions */
require_once('admin-functions/functions-admin.php');

/* Update checker */
require 'theme-updates/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$wprojectUpdateChecker = PucFactory::buildUpdateChecker(
	'https://rocketapps.com.au/files/wproject/wproject/info.json',
	__FILE__, //Full path to the main plugin file or functions.php.
	'wProject'
);
update_option('wproject_key', '*************');
/* Login redirect */
function ra_login_redirect() {
    return home_url();
}
add_filter('login_redirect', 'ra_login_redirect');

/* Login page presentation */
function wproject_login_css() {
    wp_enqueue_style( 'wproject-login', get_template_directory_uri() . '/css/wproject-login.css' );
}
add_action( 'login_enqueue_scripts', 'wproject_login_css' );

/* Custom CSS on login page */
function wproject_inline_css() {

	$wproject_settings = wProject();
	$branding_logo = $wproject_settings['branding_logo'];

	if($branding_logo) {
		$logo_id	= attachment_url_to_postid($branding_logo);
		$icon 		= wp_get_attachment_image_src( $logo_id, $size = 'large', $icon = false)[0];
	} else {
		$icon 		= get_template_directory_uri() . '/images/admin/wproject-logo-full.svg';
	}

	if(!wp_is_mobile()) {
		echo '<style type="text/css">
		body::after {
			content: "";
			display: block;
			width: 250px;
			height: 250px;
			background: url("' . $icon . '") no-repeat center;
			background-size: 100%;
			position: absolute;
			top: 50%;
			transform: translateY(-50%);
			left: calc(25% - 100px);
		}

		@media only screen and (max-width: 960px) {
			body::after {
				width: 150px;
				height: 150px;
				top: auto;
				bottom: calc(25% - 75px);
				transform: translateY(0);
				left: calc(50% - 75px);
			}
			body::before {
				content: "";
				position: absolute;
				bottom: 0;
				width: 100%;
				height: 50%;
				background: #fff;
			}
		}
		</style>';
	} else if(wp_is_mobile()) {
		echo '<style type="text/css">
		h1 {
			width: calc(100% + 60px) !important;
			position: relative;
			left: -30px;
			background: #fff;
			//box-shadow: inset 0 -10px 30px #868a98;
		}
		h1::after {
			content: "";
			display: block;
			background: url("' . $icon . '") no-repeat center !important;
			background-size: 65% !important;
			width: 200px !important;
			height: 200px !important;
			margin: 0 auto;
		}
		</style>';
	}
}
add_action('login_head', 'wproject_inline_css');

/* Language support */
load_theme_textdomain('wproject', get_template_directory() . '/languages/');

/* Do these things upon theme switch */
// add_action('after_switch_theme', 'wproject_setup');
// function wproject_setup () {
// }

/* Add Chart scripts to front-end */
function reports_pro_front_end_js() {
    echo '<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.min.js"></script>';
    echo '<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.3.0"></script>';
}
add_action( 'wp_head', 'reports_pro_front_end_js' );

function before_head() {
	wp_get_current_user();
	$dark_mode	= isset(user_details()['dark_mode']) ? user_details()['dark_mode'] : '';

	if($dark_mode =='yes') {
		$fouc = 'background: #040508 url("' . get_template_directory_uri() . '/images/spinner.svg") no-repeat center;';
	} else {
		$fouc = 'background: #f1f1f1 url("' . get_template_directory_uri() . '/images/spinner.svg") no-repeat center;';
	}

?>
	<style type="text/css">
		#fouc::before {
			content: "";
			display: block;
			width: 100%;
			height: 100%;
			<?php echo $fouc; ?>;
			position: fixed;
			top: 0;
			left: 0;
			z-index: 9999999999999999;
		}
	</style>
<?php }
add_action('before_wp_head', 'before_head');


/* Custom image sizes */
add_theme_support( 'post-thumbnails' );
if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'medium-square', 960, 960, $crop = true );
}

/* Allow admins and PMs (if option is enabled) into admin, otherwise redirect to home */
function wproject_admin_control() {

	$options 				= get_option( 'wproject_settings' );
	$allow_pm_admin_access	= isset($options['allow_pm_admin_access']) ? $options['allow_pm_admin_access'] : '';
	$user 					= wp_get_current_user();
    $user_role 				= $user->roles[0];

	if(current_user_can( 'observer' )) {
        wp_redirect(home_url());
	}
    
    if($user_role == 'project_manager' && $allow_pm_admin_access != 'on' ) {
		wp_redirect(home_url());
    } else if($user_role == 'project_manager' && $allow_pm_admin_access == 'on' ) {
		
		add_action('wp_dashboard_setup', 'wproject_remove_dash_widgets' );
		function wproject_remove_dash_widgets() {
		
			global $wp_meta_boxes;
			unset($wp_meta_boxes['dashboard']);
		
		}

		remove_menu_page( 'index.php' );
		remove_menu_page( 'about.php' );
		remove_submenu_page( 'index.php', 'update-core.php');
		remove_menu_page( 'jetpack' );
		remove_menu_page( 'edit-comments.php' );
		remove_menu_page( 'plugins.php' );
		remove_menu_page( 'tools.php' );
		remove_menu_page( 'tools.php' );
		remove_menu_page( 'export-personal-data.php' );
		remove_menu_page( 'erase-personal-data.php' );
		remove_menu_page( 'edit.php' );
		remove_menu_page( 'upload.php' );
		remove_menu_page( 'edit.php?post_type=page' );
		remove_menu_page( 'edit.php?post_type=message' );
		remove_menu_page( 'edit.php?post_type=task' );
		remove_menu_page( 'edit.php?post_type=task_group' );
		remove_menu_page( 'themes.php' );
		remove_menu_page( 'options-general.php' );
        remove_menu_page( 'wproject-clients-pro' );
        remove_menu_page( 'wproject-gantt-pro' );
        remove_menu_page( 'contacts-pro' );
        remove_menu_page( 'wproject-reports-pro' );
		remove_menu_page( 'comment-mention-pro' );
        remove_submenu_page( 'wproject-settings', 'wproject-license');
	}

    if($user_role == 'team_member') {
        wp_redirect(home_url());
    }

    if($user_role == 'operator') {

		remove_menu_page( 'index.php' );
		remove_menu_page( 'about.php' );
		remove_submenu_page( 'index.php', 'update-core.php');
		remove_menu_page( 'jetpack' );
		remove_menu_page( 'edit-comments.php' );
		remove_menu_page( 'plugins.php' );
		remove_menu_page( 'tools.php' );
		remove_menu_page( 'tools.php' );
		remove_menu_page( 'export-personal-data.php' );
		remove_menu_page( 'erase-personal-data.php' );
		remove_menu_page( 'edit.php' );
		remove_menu_page( 'upload.php' );
		remove_menu_page( 'edit.php?post_type=page' );
		remove_menu_page( 'edit.php?post_type=message' );
		remove_menu_page( 'edit.php?post_type=task' );
		remove_menu_page( 'edit.php?post_type=task_group' );
		remove_menu_page( 'themes.php' );
		remove_menu_page( 'options-general.php' );
        remove_menu_page( 'wproject-clients-pro' );
        remove_menu_page( 'wproject-gantt-pro' );
        remove_menu_page( 'contacts-pro' );
        remove_menu_page( 'wproject-reports-pro' );
        remove_submenu_page( 'wproject-settings', 'wproject-license');

	}
}
add_action('admin_menu', 'wproject_admin_control');


/* Prompt user to update account details */
function user_prompt() {
	
	$user_info 			= get_userdata(get_current_user_id());
	$first_name			= $user_info->first_name;
	$last_name 			= $user_info->last_name;
	$onboarding			= $user_info->onboarding;
	$title				= user_details()['title'];
    $role				= $user_info->roles[0];

	if(!is_page(100)) { /* If not the Account page */
		if(!$first_name || !$last_name || !$title) { ?>
		<div class="cover">
			<div>
				<p><?php _e('One last thing. Before you continue, please set your personal user preferences.','wproject'); ?></p>
				<a href="<?php echo get_the_permalink(100); ?>" class="button"><?php _e('Do it now','wproject'); ?></a>
			</div>
		</div>
		<link rel='stylesheet' id='onboarding-css'  href='<?php echo get_template_directory_uri();?>/css/onboarding.css' type='text/css' media='all' />
	<?php }
	}

	if($role == 'administrator' && !$onboarding) { ?>
		<div class="cover">

			<div class="slide" id="slide-01">
				<img src="<?php echo get_template_directory_uri();?>/images/system/icon.png" />
				<h1><?php _e('Welcome to wProject','wproject'); ?></h1>
				<p><?php _e("If you haven't already done so, go to the settings interface and set things up the way you want.",'wproject'); ?></p>
				<a href="<?php echo admin_url(); ?>admin.php?page=wproject-settings/" class="button" target="_blank"><?php _e('Go to Settings','wproject'); ?></a>
				<span><?php _e("Skip this",'wproject'); ?></span>
			</div>

			<div class="slide" id="slide-02">
				<img src="<?php echo get_template_directory_uri();?>/images/onboarding/backup.png" />
				<h1><?php _e('Back up everything','wproject'); ?></h1>
				<p><?php printf( __('Is your project data important? It is recommended you install a <a href="%1$s" target="_blank" rel="noopener">backup plugin</a> for scheduling regular file and database backups.', 'wproject'),'https://wordpress.org/plugins/search/backup/'); ?></p>
				<a href="https://wordpress.org/plugins/search/backup/" class="button" target="_blank" rel="noopener"><?php _e('Find a backup plugin','wproject'); ?></a>
				<span><?php _e("Skip this",'wproject'); ?></span>
			</div>

			<div class="slide" id="slide-03">
				<img src="<?php echo get_template_directory_uri();?>/images/onboarding/security.png" />
				<h1><?php _e('Protect your data','wproject'); ?></h1>
				<p><?php printf( __('The data you store in wProject might be considered sensitive. Install a good <a href="%1$s" target="_blank" rel="noopener">security plugin</a> for some extra peace of mind.', 'wproject'),'https://wordpress.org/plugins/search/security/'); ?></p>
				<a href="https://wordpress.org/plugins/search/security/" class="button" target="_blank" rel="noopener"><?php _e('Find a security plugin','wproject'); ?></a>
				<span><?php _e("Skip this",'wproject'); ?></span>
			</div>

			<div class="slide" id="slide-04">
				<img src="<?php echo get_template_directory_uri();?>/images/onboarding/ready.png" />
				<h1><?php _e('Ready?','wproject'); ?></h1>
				<p></p>
				<form method="post" id="onboarding" enctype="multipart/form-data">
					<input type="hidden" name="user_id" value="<?php echo get_current_user_id(); ?>" />
					<button><?php _e("Let's go!", 'wproject'); ?></button>
                    <span><a href="<?php echo home_url(); ?>/"><?php _e("Start over",'wproject'); ?></a></span>
				</form>
			</div>

			<em class="dots">
				<em class="slide-01 active"></em>
				<em class="slide-02"></em>
				<em class="slide-03"></em>
				<em class="slide-04"></em>
			</em>

			<script>
				$('#slide-01 span').click(function() {
					$('#slide-01').fadeOut();
					$('.dots em').removeClass('active');
					$('.slide-02').addClass('active');
				});
				$('#slide-02 span').click(function() {
					$('#slide-02').fadeOut();
					$('.dots em').removeClass('active');
					$('.slide-03').addClass('active');
				});
				$('#slide-03 span').click(function() {
					$('#slide-03').fadeOut();
					$('.dots em').removeClass('active');
					$('.slide-04').addClass('active');
				});
			</script>

		</div>
		<link rel='stylesheet' id='onboarding-css'  href='<?php echo get_template_directory_uri();?>/css/onboarding.css' type='text/css' media='all' />
	<?php } ?>
	
<?php }
add_action('before_body_end', 'user_prompt');


/* Favourites */
function favs() { 
    
    //TODO: Also don't allow on unowned tasks
    if(is_singular('task')) {

        $fav_tasks      = get_user_meta( get_current_user_id(), 'fav_tasks' , TRUE );
        $the_fav_tasks  = explode(',',$fav_tasks);    
    ?>
	
    <li class="follow <?php if (in_array(get_the_ID(), $the_fav_tasks)) { echo 'followed'; } ?>">
        <form method="post" class="task-follow" id="task-follow" enctype="multipart/form-data">
            <a>
                <i data-feather="star"></i> 
                <span>
                    <?php 
                        if (in_array(get_the_ID(), $the_fav_tasks)) {
                            _e('Unfollow', 'wproject');
                        } else {
                            _e('Follow', 'wproject');
                        }
                    ?>
                </span>
            </a>
            <input type="hidden" name="follow-status" class="follow-status" value="" />
            <input type="hidden" name="task-id" class="task-id" value="<?php echo get_the_id(); ?>" />
        </form>
    </li>

    <script>
        $( document ).ready(function() {
            $('body').on('click', '.follow', function() {
                $(this).addClass('followed');
                $('.follow-status').val('followed');
                $('.follow span').text('<?php _e("Unfollow", 'wproject'); ?>');

                /* Submit the form */
                setTimeout(function() { 
                    $('#task-follow').submit();
                }, 500)
            });
        });
        $( document ).ready(function() {
            $('body').on('click', '.followed', function() {
                $(this).removeClass('followed');
                $('.follow-status').val('');
                $('.follow span').text('<?php _e("Follow", 'wproject'); ?>');

                /* Submit the form */
                setTimeout(function() { 
                    $('#task-follow').submit();
                }, 500)
            });
        });
    </script>
	
<?php }
}
add_action('side_nav', 'favs', 10);

/* Footer JS */
function footer_js() { ?>
 
    <script>
        /* Copy URL */
        $(document).ready(function() {
            var $temp = $("<input>");
            var $url = $(location).attr('href');
            $('.copy-link').click(function() {
                $('body').append($temp);
                $temp.val($url).select();
                document.execCommand('copy');
                $temp.remove();
                $('.copy-link').addClass('copied');
                $('.copy-link a span').text('<?php _e('Copied!', 'wproject'); ?>');
                $('.copy-link a span').clone().appendTo('.copy-link a').addClass('move');

                setTimeout(function() { 
                    $('.move').remove();
                    $('.copy-link a span').text('<?php _e('Copy link', 'wproject'); ?>');
                }, 600)
            });
        });
    </script>
	
<?php }
add_action('before_wp_footer', 'footer_js', 50);



function after_body_start() { ?>
<script>
	$(document).ready(function() {

		/* Remove body ID (FOUC) */
		setTimeout(function() {
			$('body').removeAttr('id');
		},1000);

		$('.task-status').click(function() {
			$('.task-status').removeClass('active');
			$(this).toggleClass('active');
		});
		
	});

	/* Close the task status options when clicking outside of it */
	$(document).mouseup(function(e)  {
		var container = $('.task-status');

		// if the target of the click isn't the container nor a descendant of the container
		if (!container.is(e.target) && container.has(e.target).length === 0)  {
			container.removeClass('active');
		}
	});

	/* Close the dropdowns when clicking outside of them */
	$(document).mouseup(function(e)  {
		var container = $('.icons li .comments, .icons li .work-in-progress, .icons li .notifications, .my-follows');

		// if the target of the click isn't the container nor a descendant of the container
		if (!container.is(e.target) && container.has(e.target).length === 0)  {
			container.css('display', 'none');
			$('.icons li').removeClass('active')
		}
	});
</script>
<?php }

add_action('after_body_start', 'after_body_start');

function after_body_end() { ?>
	<script>
		// Team filter
		$('.team-filter').click(function() {
			var role = $(this).attr('data');
			$('.team-grid li').fadeOut();
			$('.team-grid .'+role).fadeIn();
            $('.team-filter').removeClass('selected');
            $(this).addClass('selected');
		});
		$('.team-filter-all').click(function() {
			$('.team-grid li').fadeIn();
		});

		// All projects filter
		$('.projects-filter').click(function() {
			var status = $(this).attr('data');
			$('.body-rows li').fadeOut();
			$('.body-rows .'+status).fadeIn();
            $('.projects-filter').removeClass('selected');
            $(this).addClass('selected');
		});
		$('.projects-filter-all').click(function() {
			$('.body-rows li').fadeIn();
		});
        $('.projects-filter-mine').click(function() {
            $('.body-rows li').fadeOut();
			$('.body-rows .pm-user-id-<?php echo get_current_user_id(); ?>').fadeIn();
		});

		<?php if(wp_is_mobile()) { ?>
            if($(window).width() < 960) {
                $('.toggle-search').click(function() {
                    $('.search').toggleClass('move');
                });
            } else {
                $('.toggle-search').remove();
            }
		<?php } ?>

        /* Sticky Row Headers */
        <?php if(!wp_is_mobile()) { ?>

            //var sticky_header = $('.header-row').offset().top;

            <?php if(is_page('projects')) { ?>
            // $(window).scroll(function() {  
            //     if ($(window).scrollTop() > sticky_header) {
            //         $('.header-row').addClass('sticky');
            //         $('.rows.all-projects').css('margin-top', '73px');
            //         $('header').css('border-bottom-left-radius', '0');
            //         console.log('00');

            //         if ($( 'header' ).hasClass('repos')) {
            //             $('.header-row.sticky').css('width', 'calc(100% - 70px');
            //             console.log('01');
            //         } else if (!$( 'header' ).hasClass('repos') && $( 'header-row' ).hasClass('sticky')) {
            //             $('.header-row.sticky').css('width', 'calc(100% - 70px');
            //             console.log('02');
            //         }
            //     } else {
            //         $('.header-row').removeClass('sticky');
            //         $('.rows.all-projects').css('margin-top', '0');
            //         $('header').css('border-bottom-left-radius', '5');
            //         console.log('03');

            //         if ($( 'header' ).hasClass('repos') && !$( 'header-row' ).hasClass('sticky')) {
            //             $('.header-row').css('width', 'auto');
            //             console.log('04');
            //         }
            //     }  
            // });
            <?php } ?>

            <?php if(is_tax()) { ?>
            // $(window).scroll(function() {  
            //     if ($(window).scrollTop() > sticky_header) {
            //         $('.header-row').addClass('sticky');
            //         $('.body-rows').css('margin-top', '50px');
            //         $('header').css('border-bottom-left-radius', '0');
            //     }
            //     else {
            //         $('.header-row').removeClass('sticky');
            //         $('.body-rows').css('margin-top', '0');
            //         $('header').css('border-bottom-left-radius', '5');
            //     }  
            // });
            <?php } ?>
        <?php } ?>

	</script>
<?php }
add_action('after_body_end', 'after_body_end');


/* Update Task Status */
function task_status_js() { 

	/*
		This handles the logic for changing the status of a task.
		The most important things is the condition surrounding deleting
		a task, which will ask for confirmation first.
	*/

	$wproject_settings              = wProject();
    $pep_talk_message               = $wproject_settings['pep_talk_message'];

?>
	<script>

        function in_progress_progress() {
            /* In Progress tasks added to the progress bar */
            $('.in-progress-progress').remove();
            var my_in_progress_count = $('.tab-content-my-tasks li.in-progress').length;
            var other_in_progress_count = $('.tab-content-other-tasks li.in-progress').length;
            var total_task_count = parseInt($('.total-task-count .value').text(), 10);
            var combined_count = my_in_progress_count + other_in_progress_count;
            var percentage = (combined_count / total_task_count) * 100;
            var rounded_percentage = percentage.toFixed(1);

            $('.in-progress-progress .count').text(combined_count);
            $('.in-progress-progress .percent').text(rounded_percentage);

            if(rounded_percentage > 0 && rounded_percentage < 100) {

                setTimeout(function() {
                    $('.in-progress-progress .contain').addClass('show');
                }, 100);

                setTimeout(function() {
                    $('.in-progress-progress .contain').removeClass('show');
                }, 3000);

                $('.main-progress').prepend('<em class="in-progress-progress" style="width:'+rounded_percentage+'%"><i class="contain"><i class="count">'+combined_count+'</i> (<i class="percent">'+rounded_percentage+'</i><sup>%</sup>)<small><?php _e('In progress', 'wproject'); ?></small></i></em>');
            }
            
        }

        /* Initially hide the in progress container */
        $( document ).ready(function() {
            $('.in-progress-progress .contain').removeClass('show');
        });

        in_progress_progress();

		/* Change task status */
		$('#update-task-status-form .task-status small').click(function () {

			var task_id = $(this).attr('data');
			var task_status = $(this).attr('value');

			$('#task_id').val(task_id);
			$('#task_status').val(task_status);

			var taskStatus = $(this).attr('data-status');
			var taskId = $(this).attr('data');
			
			var taskValue = $(this).attr('value');

			var tasks_remaining = $('.total-project-tasks .value').text();
			var complete_project_tasks = $('.complete-project-tasks .value').text();
			var total_tasks = $('.total-task-count .value').text();
			var actual_percentage = $('.main-progress span').data();

			var current_count_projects_list = $('.projects-list .current span i').text();

			$('.status-'+taskId).attr('class', 'status status-'+taskId + ' '+taskValue);
			
			if(taskValue != 'delete') {
				$('.status-'+taskId).text(taskStatus);
			}

			if(taskValue == 'on-hold') {
				$(this).closest('li').addClass('faded on-hold');
			} else {
				$(this).closest('li').removeClass('faded on-hold');
			}

			if(taskValue == 'in-progress') {
				$(this).closest('li').addClass('in-progress');
			} else {
				$(this).closest('li').removeClass('in-progress');
			}


			var filterClass = $(this).attr('data');
			var filter_value = $('.filter-selection li[data="'+taskValue+'"] span').val();
			var new_filter_value = filter_value + 1;
			$('.filter-selection li[data="'+taskValue+'"] span').val(new_filter_value);
            

			// TODO: Update priority filter counts when deleting a task or changing task priority.

			/* Simple function to assist drawing attention to the task count real-time update */
			function drawAttention() {
				$('.stats .total-project-tasks .value, .stats .complete-project-tasks .value').addClass('draw-attention');
				setTimeout(function() {
					$('.stats .total-project-tasks .value, .stats .complete-project-tasks .value').removeClass('draw-attention');
				}, 850);
			}

			/*
				Moving the progress bar.
				
				Formula:

				Complete tasks * 100 / Total tasks
				23 * 100 / 26
			*/
			function moveProgressBarUp() {
				var the_complete_project_tasks = parseFloat(complete_project_tasks)+1;
				var current_progress = (parseFloat(the_complete_project_tasks) * 100) / (parseFloat(total_tasks));
				// console.log('### MOVE UP ###');
				// console.log('current_progress: '+current_progress);
				// console.log('completed tasks: '+the_complete_project_tasks);
				// console.log('total tasks: '+total_tasks);
				// console.log('-----------------------------------');
				

				$('.main-progress div').css('width', current_progress.toFixed(1)+'%');
				$('.main-progress span').html(current_progress.toFixed(1)+'<sup>%</sup>');
				
				/* Pep talk logic */
				$('.pep-talk').fadeOut();
				var project_progress = current_progress.toFixed(1);
				var pep_start = $('.main-progress div').data('pep-start');

				// console.log(project_progress);
				// console.log(pep_start);

				if(project_progress >= pep_start && project_progress < 100) {
					$('.right .owner .pep-talk').fadeIn();
					$('.right .pep-talk').text('<?php echo addslashes($pep_talk_message); ?>');
				} else if(project_progress == 100) {
					$('.right .owner .pep-talk').fadeIn();
					$('.right .pep-talk').text('<?php _e('Good job, all done!', 'wproject'); ?>');
				}
			}

			function moveProgressBarDown() {
				var the_complete_project_tasks = parseFloat(complete_project_tasks)-1;
				var current_progress = (parseFloat(the_complete_project_tasks) * 100) / (parseFloat(total_tasks));
				// console.log('### MOVE DOWN ###');
				// console.log('current_progress: '+current_progress);
				// console.log('completed tasks: '+complete_project_tasks);
				// console.log('total tasks: '+total_tasks);
				// console.log('-----------------------------------');

				$('.main-progress div').css('width', current_progress.toFixed(1)+'%');
				$('.main-progress span').html(current_progress.toFixed(1)+'<sup>%</sup>');

				$('.main-progress div').css('width', current_progress.toFixed(1)+'%');
				$('.main-progress span').html(current_progress.toFixed(1)+'<sup>%</sup>');

				/* Pep talk logic */
				var project_progress = current_progress.toFixed(1);
				var pep_start = $('.main-progress div').data('pep-start');

				// console.log(project_progress);
				// console.log(pep_start);

				$('.right .owner .pep-talk').fadeOut();
			}

			function showCompletedMessage() {
				<?php if(is_tax()) { ?>
				$('.middle h1').after('<div class="project-finished"><p><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-thumbs-up feather-icon" color="#ff9800"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg><?php _e('There are no more tasks to do in this project.', 'wproject'); ?></p></div>');
				<?php } ?>
			}

			/* Disable button when clicked */
			$(this).closest('em').find('small').removeClass('disabled');
			$(this).addClass('disabled');

			/* Grey out the task when complete */
			if(taskValue == 'complete') {

				$('#task-id-'+taskId).addClass('complete');
				$('#task-id-'+taskId).addClass('minimise');
				
				$('.total-project-tasks .value').text(parseFloat(tasks_remaining) -1);
				$('.complete-project-tasks .value').text(parseFloat(complete_project_tasks) +1);
				$('.projects-list .current span i').text(parseFloat(current_count_projects_list) +1);
				drawAttention();
				moveProgressBarUp();

				var tasks_remaining = $('.total-project-tasks .value').text();
				if(tasks_remaining == 0) {
					showCompletedMessage();
				}

				<?php if(is_front_page()) { ?>
					/* Get project ID */
					var dashboard_project_id = $(this).closest('li .task-status-container').attr('data');
					/* Get task count from project ID */
					var dashboard_tasks_remaining = $('#project-'+dashboard_project_id+' span i').text();

					$('#project-'+dashboard_project_id+' span i').text(parseFloat(dashboard_tasks_remaining) +1);
                    $('#project-'+dashboard_project_id).addClass('changed');
				<?php } ?>

                in_progress_progress();

			} else if(taskValue == 'in-progress') {

                in_progress_progress();
                
            } else if(taskValue == 'incomplete' || taskValue == 'delete' || taskValue == 'not-started' || taskValue == 'on-hold') {

                in_progress_progress();

            } else {

                if($(this).closest('li').hasClass('complete')) {
					$('.total-project-tasks .value').text(parseFloat(tasks_remaining) +1);
					$('.complete-project-tasks .value').text(parseFloat(complete_project_tasks) -1);
					$('.projects-list .current span i').text(parseFloat(current_count_projects_list) -1);
					drawAttention();
					moveProgressBarDown();

					var tasks_remaining = $('.total-project-tasks .value').text();
					if(tasks_remaining > 0) {
						$('.project-finished').remove();
					}
				}

				$('#task-id-'+taskId).removeClass('complete');
				$('#task-id-'+taskId).removeClass('minimise');

				<?php if(is_front_page()) { ?>
					/* Get project ID */
					var dashboard_project_id = $(this).closest('li .task-status-container').attr('data');
					/* Get task count from project ID */
					var dashboard_tasks_remaining = $('#project-'+dashboard_project_id+' span i').text();

					if($('#project-'+dashboard_project_id).hasClass('changed')) {
						$('#project-'+dashboard_project_id+' span i').text(parseFloat(dashboard_tasks_remaining) -1);
						$('#project-'+dashboard_project_id).removeClass('changed');
					}
				<?php } ?>

				

			}

			/* If task needs to be deleted... */
			if(taskValue == 'delete') {

				if (confirm('<?php _e('Delete this task and any time recorded on it?', 'wproject'); ?>')) {

					/* Fade out the deleted task */
					$('#task-id-'+taskId).slideUp();

					/* Update the count in the My Tasks tab */
					var current_task_count = $('.my-tasks span').text();
					$('.my-tasks span').text(current_task_count-1);

					/* Update the count in the Total Tasks count */
					// TODO: count is concatenating instead of doing the math
					// var total_task_count = $('.status-box .total-tasks').text();
					// $('.status-box .total-tasks').text(total_task_count-1);

					/* Update the count in the My Latest Tasks tab */
					var current_task_count = $('.my-latest-tasks span').text();
					$('.my-latest-tasks span').text(current_task_count-1);

					/* Update the count in the project nav */
					var your_tasks_count = $('.your-tasks-value').text();
					$('.your-tasks-value').text(your_tasks_count-1);

					/* Hide task from Gantt Pro when deleting a task */
					$('.gantt-task-list #task-'+taskId).css('opacity', '0');
					$('[data-id=' + taskId + ']').css('opacity', '0');

					<?php if(is_tax()) { ?>
					/* Update total task count */
					var total_tasks = $('.total-task-count .value').text();
					$('.total-task-count .value').text(parseFloat(total_tasks)-1);

					/* Update tasks remaining count */
					var total_project_tasks = $('.total-project-tasks .value').text();
					$('.total-project-tasks .value').text(parseFloat(total_project_tasks)-1);

					/* Update the count in the project nav */
					//TODO: This is not working!
					// var total_task_count = $('.total-task-count .value').text();
					// var total_project_tasks = $('.total-project-tasks .value').text();
					// $('.projects-list .current span').append('<i>'+total_task_count+'</i>/'+parseFloat(total_project_tasks)-1);
					// moveProgressBarUp();
					<?php } ?>


					<?php if(is_front_page()) { ?>
					/* Get project ID */
					// TODO: update tasks in main nav when deleting a task from 'my latest tasks' on dashboard.
					// var dashboard_project_id = $(this).closest('li .task-status-container').attr('data');
					// /* Get task count from project ID */
					// var dashboard_tasks_remaining = $('#project-'+dashboard_project_id+' span i').text();

					// if(dashboard_tasks_remaining > 0) {
					// 	$('#project-'+dashboard_project_id+' span').html('<i>'+(parseFloat(dashboard_tasks_remaining) -1) +'</i>/'+dashboard_tasks_remaining);
					// 	$('#project-'+dashboard_project_id).removeClass('changed');
					// }
				<?php } ?>


					/* Submit the form */
					setTimeout(function() { 
						$('#update-task-status-form').submit();
					}, 500)
				
				} 

			/* ...otherwise, just update the task status */
			} else {

				/* Submit the form */
				setTimeout(function() { 
					$('#update-task-status-form').submit();
				}, 500)

			}
		});
        
        $(document).ready(function() {
            $('.project-team .pep-talk').remove();
        });
	</script>
<?php }
add_action('before_body_end', 'task_status_js');


/* All pages list, except pages with meta value wproject_page */
function all_pages() { ?>
	<ul>
	<?php

		if(is_tax()) {
			$term 		= get_term_by( 'slug', get_query_var('project'), 'project');
			$term_id 	= $term->term_id;
		} else {
			$term_id 	= '';
		}

		$args = array(
		'post_type'         => 'page',
		'orderby'           => 'name',
		'order'             => 'asc',
		'post_status'		=> 'publish',
		'post__not_in'		=>  array(80,90,101,102,103,104,105,106,107,108,109),
		'posts_per_page'    => -1,
		'meta_query'      	=> array(
			array(
			  'key'     => 'wproject_page',
			  'compare' => 'NOT EXISTS',
			)
		  ),
		);
		$query = new WP_Query($args);
		while ($query->have_posts()) : $query->the_post();

		$page_project_id = get_post_meta(get_the_id(), 'page_project', TRUE); 

		if($page_project_id == $term_id) { ?>
			<li><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></li>
		<?php } 
		endwhile;
		wp_reset_postdata();
	?>
    </ul>
    <?php 
}

/* All projects list (projects that are active in some way) */
function all_projects_list() {

	$wproject_settings = wProject();
	$project_list_style		= $wproject_settings['project_list_style'];
    $completed_projects_nav = $wproject_settings['completed_projects_nav'];

	$style = '';
	$blank = '';
	if($project_list_style == 'dropdown') { 
		$style =  'dropdown';
		$blank = '<div class="dropdown-start">' . __( "Select a project", "wproject" ) . '<i data-feather="chevron-down"></i></div>';
	}

	echo $blank;

	echo '<ul class="projects-list ' . $style . '">';
	$projects = array(
			'taxonomy'      => 'project',
			'hide_empty'    => 0,
			'orderby'       => 'name',
			'post_status'   => 'publish',
			'order'         => 'ASC',
			'hierarchical'  => 0,
			'meta_query' => array(
                array(
                    'key'       => 'project_status',
                    'value'     => array('in-progress', 'complete', 'planning', 'proposed', 'setting-up')
                )
            ), 
		);
		$cats = get_categories($projects);
		
		$tasks_query = new WP_Query( $projects );
		$project_count = $tasks_query->found_posts;

		foreach($cats as $cat) {
			$term_meta = get_term_meta($cat->term_id); 
			$project_status = $term_meta['project_status'][0];

			/* Get the total number of tasks in each project */
			$completed_tasks_project_args = array(
				'posts_per_page' 	=> -1,
				'post_type' 		=> 'task',
				'post_status'		=> 'publish',
				'tax_query' => array(
					array(
						'taxonomy' => 'project',
						'field'    => 'slug',
						'terms'    => array( $cat->slug ),
						'operator' => 'IN'
					),
				),
				'meta_key' 		=> 'task_status',
				'meta_value'	=> array('complete')
			);
			$completed_tasks_posts_project = new WP_Query($completed_tasks_project_args);
			$completed_tasks_project = $completed_tasks_posts_project->post_count;

			if(isset($project_status) && $project_status != 'cancelled' && $project_status != 'archived' && $project_status != 'inactive') {
		?>
            <li class="<?php if(is_tax( 'project', $cat->slug )) { echo 'current'; } ?> <?php if($project_status == 'complete') { echo 'project-complete'; } ?> <?php if($completed_tasks_project == $cat->count && $project_status == 'complete') { echo 'project-complete'; } ?>" id="project-<?php echo $cat->term_id; ?>" data="<?php echo $cat->term_id; ?>">
                <a href="<?php echo get_category_link( $cat->term_id ) ?>" title="<?php echo $cat->name; ?>">
                    <em><?php echo $cat->name; ?></em>

                    <?php if($project_status == 'complete') { ?>

                        <i data-feather="check"></i>
                        
                    <?php } else if($project_status != 'complete' && $completed_tasks_project == $cat->count) { ?>

                        <i data-feather="thumbs-up"></i>

                    <?php } else { ?>
                        <span data="<?php echo $cat->count; ?>">
                            <i><?php echo $completed_tasks_project; ?></i>/<?php echo $cat->count; ?>
                        </span>
                    <?php } ?>
                </a>
            </li>
		<?php
			}
		}
	?>
	</ul>

    <script>
        $(document).ready(function() {
            $('ul.projects-list li').each(function() {
                var $span = $(this).find('span');
                var $i = $(this).find('i');
                if ($span.attr('data') == $i.text()) {
                    
					$('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check feather-icon" color="#ff9800"><polyline points="20 6 9 17 4 12"></polyline></svg>').insertAfter($(this).find('em'));
					$(this).addClass('project-complete');
					$(this).find('span').remove();
					
                }
            });
        });
    </script>

    <?php if($completed_projects_nav) { ?>
		<script>
			$('.projects-list .project-complete').remove();
		</script>
	<?php }
    
    if($project_list_style == 'dropdown') { ?>
		<script>
			$('.left .dropdown-start').click(function() { 
				$(this).toggleClass('spin');
				$('.left .projects-list.dropdown').slideToggle(120);
			});
		</script>
	<?php }
}


/* Count how many tasks the current user has in the current project */
function user_project_tasks_count() {

    $wproject_settings      = wProject();
	$project_access         = $wproject_settings['project_access'];
    $user                   = wp_get_current_user();
    $user_role              = $user->roles[0];
    
    $term_id                = get_queried_object()->term_id; 
    $term_meta              = get_term_meta($term_id); 
    $term_object            = get_term( $term_id );

    $all_tasks_args = array(
        'post_type'         => 'task',
        'post_status'		=> array('publish', 'private'),
        'author'            => get_current_user_id(),
        'posts_per_page'    => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'project',
                'field'    => 'slug',
                'terms'    => array( $term_object->slug ),
                'operator' => 'IN'
            ),
        ),
    );
    $all_tasks = new WP_Query($all_tasks_args);
    $all_tasks_count = $all_tasks->post_count;

    /* If project access setting is always allowed, then return task count as 1 */
    if($user_role == 'administrator' || $user_role == 'project_manager' || $project_access == 'all' && $user_role == 'team_member') {
        return 1;
    } else { /* ...otherwise return actual task count */
        return $all_tasks_count;
    }
    
}

/* All projects count */
function all_projects_count() {
	$projects_args = array(
		'taxonomy'      => 'project',
		'hide_empty'    => 0,
		'orderby'       => 'name',
		'order'         => 'ASC',
		'post_status'   => 'publish',
		'hierarchical'  => 0,
		'meta_query' 	=> array(
			array(
				'key'       => 'project_status',
				'value'     => 'archived',
				'compare'   => '!='
			),array(
				'key'       => 'project_status',
				'value'     => 'cancelled',
				'compare'   => '!='
			),array(
				'key'       => 'project_status',
				'value'     => 'complete',
				'compare'   => '!='
			)
		),
	);
	$cats = get_categories($projects_args);
	$all_projects_count = count( $cats );

	$the_total_projects_count = array(
		'count'	=> $all_projects_count,
	);
	return $the_total_projects_count;
}



// Users Select - New Task
function all_users_task() {
	$user_args = array(
		'role'         => '',
		'role__not_in' => array(),
		'orderby'      => 'nicename',
		'order'        => 'ASC',
	 ); 
	$all_users = get_users( $user_args );
?>
<select name="task_owner" id="task_owner">
	<option id="0" value="0"><?php _e( "Nobody", "wproject" ); ?></option>
	<?php
	foreach ( $all_users as $user ) { ?>
		<option <?php if ($user->ID == get_current_user_id()) echo 'selected';?> value="<?php echo $user->ID; ?>" id="<?php echo $user->ID; ?>">
		<?php if ($user->first_name) { echo $user->first_name; } ?>
		<?php if ($user->last_name) { echo $user->last_name; } ?> 
		(<?php echo $user->user_email; ?>)</option>
	<?php
		}
	?>
</select>
<?php get_users( $user_args );
}


// Users Select - New Project
function all_users_project() {
	$user_args = array(
		'role'         => '',
		'role__not_in' => array(),
		'orderby'      => 'nicename',
		'order'        => 'ASC',
	 ); 
	$all_users = get_users( $user_args );
?>
<select name="project_owner" id="project_owner">
	<option id="0" value="0"><?php _e( "Nobody", "wproject" ); ?></option>
	<?php
	foreach ( $all_users as $user ) { ?>
		<option <?php if ($user->ID == get_current_user_id()) echo 'selected';?> value="<?php echo $user->ID; ?>" id="<?php echo $user->ID; ?>">
		<?php if ($user->first_name) { echo $user->first_name; } ?>
		<?php if ($user->last_name) { echo $user->last_name; } ?> 
		(<?php echo $user->user_email; ?>)</option>
	<?php
		}
	?>
</select>
<?php get_users( $user_args );
}

/* Notifications */
function messages() {

    $current_user_id     = get_userdata(get_current_user_id());
    $notifications_count = $current_user_id->notifications_count;

	$wproject_settings = wProject();
    $notify_maximum_messages = $wproject_settings['notify_maximum_messages'];

    if($notifications_count > 1) {
        $notify_maximum_messages = $notifications_count;
    } else {
        if($notify_maximum_messages) {
            $notify_maximum_messages = $notify_maximum_messages;
        } else {
            $notify_maximum_messages = 10;
        }
    }

	$args = array(
		'post_type'         => 'message',
		'orderby'           => 'date',
		'order'             => 'desc',
		'author'			=> get_current_user_id(),
		'post_status'		=> 'publish',
		'posts_per_page'    => $notify_maximum_messages
	);
	$query = new WP_Query($args);
	$mc = 1; ?>
	
	<?php while ($query->have_posts()) : $query->the_post();
    $date_format    = get_option('date_format');
    $time_format    = get_option('time_format');
    $date_and_time  = $date_format . ' ' . $time_format;
    ?>
		<div class="message-<?php echo get_the_ID(); ?> message-0<?php echo $mc++; ?>">
			<form class="mark-message-read" id="" method="post" enctype="multipart/form-data">
				<strong><?php echo get_the_title(); ?></strong>
                <span class="message-date"><?php echo get_the_date($date_and_time); ?></span>
				<?php echo get_the_content(); ?>
				<input type="hidden" name="message_id" id="message_id" value="<?php echo get_the_ID(); ?>" />
				<button title="<?php _e('Mark read', 'wproject'); ?>"><i data-feather="trash-2"></i></button>
			</form>
		</div>
	<?php $message_count = $mc++;
	endwhile;
	wp_reset_postdata(); ?>
	
	<script>
		$('.notifications button').click(function() {
			$(this).closest('form').submit().addClass('updating');
		});
	</script>
<?php }

/* Message count */
function message_count() {

    $user_id = get_current_user_id();
	

	if ($user_id) {

		$notifications_count = get_user_meta( $user_id, 'notifications_count' , true );
 
		$wproject_settings = wProject();
		$notify_maximum_messages = $wproject_settings['notify_maximum_messages'];

		if($notifications_count > 1) {
			$notify_maximum_messages = $notifications_count;
		} else {
			if($notify_maximum_messages) {
				$notify_maximum_messages = $notify_maximum_messages;
			} else {
				$notify_maximum_messages = 10;
			}
		}

		$message_args = array(
			'post_type'         => 'message',
			'orderby'           => 'date',
			'order'             => 'desc',
			'author'			=> get_current_user_id(),
			'post_status'		=> 'publish',
			'posts_per_page'    => $notify_maximum_messages
		);
		$messages = new WP_Query($message_args);
		$my_total_task_count = $messages->post_count;
		
		$my_message_count = array(
			'count'	=> $my_total_task_count
		);
		return $my_message_count;
	}
}

/* Enqueue CSS into footer */
function wproject_presentation() { 
    $theme 				= wp_get_theme();
    $theme_version		= $theme->Version;
	$wproject_settings 	= wProject();
	$dark_mode			= isset(user_details()['dark_mode']) ? user_details()['dark_mode'] : '';
	wp_get_current_user();

	if(empty($_GET['print'])) { ?>
    	<link rel='stylesheet' id='wproject-style-css'  href='<?php echo get_template_directory_uri(); ?>/style.css?ver=<?php echo $theme_version;?>' type='text/css' media='all' />

		<?php if($dark_mode == 'yes') { ?>
			<link rel='stylesheet' id='wproject-dark-style-css'  href='<?php echo get_template_directory_uri(); ?>/css/dark.css?ver=<?php echo $theme_version;?>' type='text/css' media='all' />
		<?php } ?>


	<?php } else { ?>

		<link rel='stylesheet' id='wproject-print-css'  href='<?php echo get_template_directory_uri(); ?>/css/print.css?ver=<?php echo $theme_version;?>' type='text/css' media='all' />

	<?php } ?>
	
	<div class="working" data="<?php echo $wproject_settings['response_message_position']; ?>"><i data-feather="loader"></i></div>
	<div class="image-container">
		<i data-feather="x"></i>
	</div>
	<div class="mask"></div>
<?php }
add_action('wp_footer', 'wproject_presentation');


/* User avatar and info */
function user_avatar() {

	$user				= wp_get_current_user();
	$role               = !empty($user->roles) ? $user->roles[0] : '';

    if($role == 'project_manager' || $role == 'administrator' || $role == 'team_member' || $role == 'observer') {
        get_template_part('inc/avatar');
    }
    
}
add_action('avatar', 'user_avatar');


/* Enqueue jQuery */
if (!is_admin()) { add_action('wp_enqueue_scripts', 'wproject_jquery_enqueue'); }
function wproject_jquery_enqueue() {
    
	$wproject_settings 	= wProject();
	$bypass_google      = $wproject_settings['bypass_google'];

	wp_deregister_script('jquery');
	if ($bypass_google == 'on') {
		/* Enqueue local jQuery file */
		wp_enqueue_script('jquery', get_template_directory_uri() . '/js/jquery_1.9.1.min.js', array(), '1.9.1', true);
	} else {
		/* Enqueue jQuery from Google CDN */
		wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js', false, '1.9.1', false);
		wp_enqueue_script('jquery');
	}
}


/* Enqueue Moment JS */
function enqueue_moment_js() {
    wp_enqueue_script('moment', plugins_url( '/js/moment.min.js' , __FILE__ ), array('jquery'));
}
add_action('wp_enqueue_scripts','enqueue_moment_js');

/* Header */
function header_items() { ?>
	<!--/ Mobile Viewport Scale /-->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
	<meta name="mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-capable" content="yes" />

	<?php 
		$wproject_settings 	= wProject();
		$favicon 			= $wproject_settings['favicon'];
		$favicon_id			= attachment_url_to_postid($favicon);

		if($favicon) {
			$favicon 	= wp_get_attachment_image_src( $favicon_id, array(64, 64) )[0];
			$touch_icon	= wp_get_attachment_image_src( $favicon_id, array(180, 180) )[0];
			$icon 		= wp_get_attachment_image_src( $favicon_id, array(192, 192) )[0];
		} else {
			$favicon 	= get_template_directory_uri() . '/images/system/favicon.png';
			$touch_icon	= get_template_directory_uri() . '/images/system/touch-icon.png';
			$icon 		= get_template_directory_uri() . '/images/system/icon.png';
		}
	?>
	<!--/ Icons /-->
	<link rel="shortcut icon" href="<?php echo $favicon; ?>" />
	<link rel="apple-touch-icon" href="<?php echo $touch_icon; ?>" />
	<link rel="icon" sizes="192x192" href="<?php echo $icon; ?>" />

<?php }
add_action( 'after_wp_head', 'header_items' );


/* Change permalink structure */
add_action('init', 'change_permalink_structure');
function change_permalink_structure() {
	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure( '/%postname%/' );
}

/* Prevent guessing missing URLs */
function no_redirect_guess_404_permalink( $header ) {
    global $wp_query;
    if( is_404() )
        unset( $wp_query->query_vars['name'] );
    return $header;
}
add_filter( 'status_header', 'no_redirect_guess_404_permalink' );

/* Disable the Admin bar. */
show_admin_bar(false);

/* Disable Emoji */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

/* Remove wp_scheduled_delete function */
function remove_scheduled_trash_delete() {
    remove_action( 'wp_scheduled_delete', 'wp_scheduled_delete' );
}
add_action( 'init', 'remove_scheduled_trash_delete' );

/* Allow Chrome extension to work */
remove_action( 'login_init', 'send_frame_options_header' );
remove_action( 'admin_init', 'send_frame_options_header' );

/* Enqueue date picker scripts */
if ( is_user_logged_in() ) {
    function dp_scripts() {
        wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery' ) );
        wp_enqueue_style( 'jquery-ui' );
    }
    add_action( 'wp_enqueue_scripts', 'dp_scripts' );
}

/* JS output */
function misc_output() {
	$wproject_settings = wProject();
	if(isset($wproject_settings['response_message_duration']) && $wproject_settings['response_message_duration'] !='' ) {
		$response_message_duration = ($wproject_settings['response_message_duration'] * 1000);
	} else {
		$response_message_duration = 6000;
	}
	?>
	<div id="status-update" data="<?php echo $wproject_settings['response_message_position']; ?>"></div>
	<script src="<?php echo get_template_directory_uri();?>/js/feather/feather.min.js"></script>
	<script src="<?php echo get_template_directory_uri();?>/js/cookie/js.cookie.min.js"></script>
	<script>
	/* Date picker scripts output */
	$(document).ready(function() {
		/*
			Don't allow typing in date picker field.
			Forces users to use the date picker instead.
		*/
		$('#project_start_date, #project_end_date, #task_start_date, #task_end_date').focus(function(){
			$('#date').blur();
		});
		/* Set date format in date picker. */
		$('#project_start_date, #project_end_date, #task_start_date, #task_end_date').datepicker({
			dateFormat : 'yy-mm-dd'
		});
	});
	/* Feather icons */
	feather.replace({ class: 'feather-icon', 'color': '#ff9800' })
	feather.replace();

	/* Remove Lastpass icons from inputs */
	$('input').attr('data-lpignore', 'true');

	/* Show new project name in realtime as you type */
	$('#project_name').keyup(function () {
		$('.main-nav li ul #project-<?php if(isset($_GET['project-id'])) { echo $_GET['project-id']; } ?> em, h1').text($(this).val());
	});
	/* Show new task name in realtime as you type */
	$('#task_name').keyup(function () {
		$('h1').text($(this).val());
	});

	
	$('form').on('submit', function(e) {
		setTimeout(function() {
			$('#status-update').addClass('move');
		}, <?php echo $response_message_duration; ?>);
	});

	if($(window).width() > 960) {

		/* Fadeout the response message when it's clicked */
		$('#status-update').click(function() {
			$(this).toggleClass('move');
		});
		
	} else if($(window).width() < 960) {

		$('#status-update').click(function() {
			$(this).fadeOut();
		});

	}

	/* Pretty radio buttons */
	$('input[type="radio"]').closest('label').addClass('radio');
    $('.radio').click(function() {

        <?php if(is_singular('task')) { ?>
            $(this).closest('.side-form-box').find('.radio').removeClass('selected');
        <?php } else { ?>
            $(this).closest('.radio-group').find('.radio').removeClass('selected');
        <?php } ?>

        $(this).addClass('selected');
    });


	/* Contextual search (search current page) */
	function contextFilter(element) {
		var value = $(element).val().toLowerCase();

		$('.body-rows > li, .team-grid li, .contacts-body-rows li').each(function() {
			if ($(this).text().toLowerCase().search(value) > -1) {
				$(this).show();
			}
			else {
				$(this).hide();
			}
		});
	}



	/* Allow radio butons to be unchecked */
	$('body').on('dblclick', '.radio-group label', function() {
		$(this).removeClass('selected');
		$(this).closest('label').find('input').prop('checked', false);  
	});

	/* Pretty checkboxes */
	$('input[type="checkbox"]').closest('label').addClass('checkbox');
    $('.checkbox').change(function() {
        $(this).toggleClass('selected');
    });

	/* Print popup */
	function printPopUp(url) {
		newwindow=window.open(url,"Print","height=680,width=834");
		if (window.focus) {newwindow.focus()}
		return false;
	}

	/* Toggle the filters */
	$('li.filters').click(function() {
		$(this).toggleClass('open');
		$('.filter-selection').fadeToggle();
	});

	/* Close the element when clicking outside of it */
	$(document).mouseup(function(e)  {
	var container = $('.filter-selection ul');

	// if the target of the click isn't the container nor a descendant of the container
	// if (!container.is(e.target) && container.has(e.target).length === 0)  {
	// 	container.hide();
    //     $('.filter-selection').removeClass('open');
    //     $('.filter-selection ul li').removeClass('active');
	// }
	});

	$('.filter-selection li').click(function() {
		$('.filter-selection li').removeClass('active');
		$(this).addClass('active');
		var filterClass = $(this).attr('data');
		$('.filter-selection .' + filterClass).addClass('active');
		$('.body-rows li').hide();
		$('.body-rows li.' + filterClass).show().addClass('white');
		$('.body-rows li.' + filterClass + ' .more .more-details li').show();

		/* Filter info bar */
		var filter_text = $(this).children('em').text();
		var all_my_tasks = $('.tab-content-my-tasks ul .priority[style="display: flex;"]').length;
		var all_other_tasks = $('.tab-content-other-tasks ul .priority[style="display: flex;"]').length;

		$('.tab-content-my-tasks .filter-row, .tab-content-my-latest-tasks .filter-row').css('display', 'flex');
		$('.tab-content-my-tasks .filter-row .filter-type, .tab-content-my-latest-tasks .filter-type').text(filter_text+' ('+all_my_tasks+')');

		$('.tab-content-other-tasks .filter-row, .tab-content-my-latest-tasks .filter-row').css('display', 'flex');
		$('.tab-content-other-tasks .filter-row .filter-type, tab-content-my-latest-tasks .filter-type').text(filter_text+' ('+all_other_tasks+')');
	});

	$('.filter-row svg, .filter-selection .all').click(function() {
		$('.filter-row').hide();
		$('.body-rows li').fadeIn();
        var original_url = window.location.href.split('?')[0];
        window.history.pushState({ path: original_url }, '', original_url);
	});

	$('.filter-selection .all').click(function() {
		$('.body-rows li').show().removeClass('white');
	});

    $('.filter-orphan').hide();
    $('.other-tasks').click(function() {
        $('.filter-orphan').show();
        $('.filter-selection').removeClass('open');
    });
    $('.my-tasks').click(function() {
        $('.filter-orphan').hide();
        $('.filter-selection').removeClass('open');
    });

    /* Show tasks that are actually incomplete, not just tasks that are labelled directly as incomplete. */
    $('.filter-incomplete').click(function() {
        $('.sort-my-tasks li').hide();
        $('.body-rows li.incomplete, .body-rows li.not-started, .body-rows li.in-progress, .body-rows li.on-hold').show();
		
		/* Filter info bar */
		var filter_text = $(this).children('em').text();
		var all_my_tasks = $('.tab-content-my-tasks ul .priority[style="display: flex;"]').length;
		var all_other_tasks = $('.tab-content-other-tasks ul .priority[style="display: flex;"]').length;
		
		$('.tab-content-my-tasks .filter-row .filter-type, .tab-content-my-latest-tasks .filter-row .filter-type').text(filter_text+' ('+all_my_tasks+')');
		$('.tab-content-other-tasks .filter-row .filter-type, .tab-content-my-latest-tasks .filter-row .filter-type').text(filter_text+' ('+all_other_tasks+')');
    });
	
	/* Make textarea required before submitting comment */
	$('#commentform textarea').attr('required', 'required');

    /* Toggle comment direction */
    $('.reverse-comments').click(function() {
        $(this).toggleClass('flipped');
        $('#commentlist').toggleClass('reverse');
    });


	/* 
		Count items and show in filter dropdown 
	*/
	function filter_counts() {
		
		var my_blocked_count = $('.tab-content-my-tasks ul .priority.is_blocked_by').length;
		var my_complete_count = $('.tab-content-my-tasks ul .priority.complete').length;
		var my_has_issues_with_count = $('.tab-content-my-tasks ul .priority.has_issues_with').length;
		var my_incomplete_count = $('.tab-content-my-tasks ul .priority.incomplete').length;
		var my_in_progress_count = $('.tab-content-my-tasks ul .priority.in-progress').length;
		var my_milestone_count = $('.tab-content-my-tasks ul .priority.milestone').length;
		var my_not_started_count = $('.tab-content-my-tasks ul .priority.not-started').length;
		var my_on_hold_count = $('.tab-content-my-tasks ul .priority.on-hold').length;
		var my_overdue_count = $('.tab-content-my-tasks ul .priority.overdue').length;
		var my_pinned_count = $('.tab-content-my-tasks ul .priority.pinned').length;
		var my_is_similar_to_count = $('.tab-content-my-tasks ul .priority.is_similar_to').length;
		var my_time_count = $('.tab-content-my-tasks ul .priority.time').length;

		var my_total_incomplete_count = parseInt(my_incomplete_count)+parseInt(my_not_started_count)+parseInt(my_in_progress_count)+parseInt(my_on_hold_count);

		var other_blocked_count = $('.tab-content-other-tasks ul .priority.is_blocked_by').length;
		var other_complete_count = $('.tab-content-other-tasks ul .priority.complete').length;
		var other_has_issues_with_count = $('.tab-content-other-tasks ul .priority.has_issues_with').length;
		var other_incomplete_count = $('.tab-content-other-tasks ul .priority.incomplete').length;
		var other_in_progress_count = $('.tab-content-other-tasks ul .priority.in-progress').length;
		var other_milestone_count = $('.tab-content-other-tasks ul .priority.milestone').length;
		var other_not_started_count = $('.tab-content-other-tasks ul .priority.not-started').length;
		var other_on_hold_count = $('.tab-content-other-tasks ul .priority.on-hold').length;
		var other_overdue_count = $('.tab-content-other-tasks ul .priority.overdue').length;
		var other_pinned_count = $('.tab-content-other-tasks ul .priority.pinned').length;
		var other_is_similar_to_count = $('.tab-content-other-tasks ul .priority.is_similar_to').length;
		var other_time_count = $('.tab-content-other-tasks ul .priority.time').length;

		var other_total_incomplete_count = parseInt(other_incomplete_count)+parseInt(other_not_started_count)+parseInt(other_in_progress_count)+parseInt(other_on_hold_count);

		var the_blocked_count = parseInt(my_blocked_count)+parseInt(other_blocked_count);
		var the_complete_count = parseInt(my_complete_count)+parseInt(other_complete_count);
		var the_has_issues_with_count = parseInt(my_has_issues_with_count)+parseInt(other_has_issues_with_count);
		var the_incomplete_count = parseInt(my_total_incomplete_count)+parseInt(other_total_incomplete_count);
		var the_in_progress_count = parseInt(my_in_progress_count)+parseInt(other_in_progress_count);
		var the_milestone_count = parseInt(my_milestone_count)+parseInt(other_milestone_count);
		var the_not_started_count = parseInt(my_not_started_count)+parseInt(other_not_started_count);
		var the_on_hold_count = parseInt(my_on_hold_count)+parseInt(other_on_hold_count);
		var the_overdue_count = parseInt(my_overdue_count)+parseInt(other_overdue_count);
		var the_pinned_count = parseInt(my_pinned_count)+parseInt(other_pinned_count);
		var the_is_similar_to_count = parseInt(my_is_similar_to_count)+parseInt(other_is_similar_to_count);
		var the_time_count = parseInt(my_time_count)+parseInt(other_time_count);

		var the_orphans_count = $('.sort-other-tasks .priority.orphan').length;

        var my_low_priority = $('.tab-content-my-tasks ul .priority.low').length;
        var my_normal_priority = $('.tab-content-my-tasks ul .priority.normal').length;
        var my_high_priority = $('.tab-content-my-tasks ul .priority.high').length;
        var my_urgent_priority = $('.tab-content-my-tasks ul .priority.urgent').length;

        var others_low_priority = $('.tab-content-other-tasks ul .priority.low').length;
        var others_normal_priority = $('.tab-content-other-tasks ul .priority.normal').length;
        var others_high_priority = $('.tab-content-other-tasks ul .priority.high').length;
        var others_urgent_priority = $('.tab-content-other-tasks ul .priority.urgent').length;

        var total_low_priority = parseInt(my_low_priority)+parseInt(others_low_priority);
        var total_normal_priority = parseInt(my_normal_priority)+parseInt(others_normal_priority);
        var total_high_priority = parseInt(my_high_priority)+parseInt(others_high_priority);
        var total_urgent_priority = parseInt(my_urgent_priority)+parseInt(others_urgent_priority);

		$('.filter-selection li span').remove();
		$('.filter-selection li[data="is_blocked_by"]').append('<span>'+the_blocked_count+'</span>');
		$('.filter-selection li[data="complete"]').append('<span>'+the_complete_count+'</span>');
		$('.filter-selection li[data="has_issues_with"]').append('<span>'+the_has_issues_with_count+'</span>');
		$('.filter-selection li[data="incomplete"]').append('<span>'+the_incomplete_count+'</span>');
		$('.filter-selection li[data="in-progress"]').append('<span>'+the_in_progress_count+'</span>');
		$('.filter-selection li[data="milestone"]').append('<span>'+the_milestone_count+'</span>');
		$('.filter-selection li[data="not-started"]').append('<span>'+the_not_started_count+'</span>');
		$('.filter-selection li[data="on-hold"]').append('<span>'+the_on_hold_count+'</span>');
		$('.filter-selection li[data="overdue"]').append('<span>'+the_overdue_count+'</span>');
		$('.filter-selection li[data="pinned"]').append('<span>'+the_pinned_count+'</span>');
		$('.filter-selection li[data="is_similar_to"]').append('<span>'+the_is_similar_to_count+'</span>');
		$('.filter-selection li[data="time"]').append('<span>'+the_time_count+'</span>');
		$('.filter-selection li[data="orphan"]').append('<span>'+the_orphans_count+'</span>');

        $('.filter-selection li[data="low"]').append('<span>'+total_low_priority+'</span>');
        $('.filter-selection li[data="normal"]').append('<span>'+total_normal_priority+'</span>');
        $('.filter-selection li[data="high"]').append('<span>'+total_high_priority+'</span>');
        $('.filter-selection li[data="urgent"]').append('<span>'+total_urgent_priority+'</span>');
        
	}

	$('.filters').click(function() {
		filter_counts();
	});

    /* View logic: Append URL with query string of the clicked filter */
    var original_url = window.location.href.split('?')[0];
    var current_url = window.location.href;

    $('.filter-selection li').click(function() {

        var view = $(this).attr('data');
        
        if (view === "all") {
            window.history.pushState({ path: original_url }, '', original_url);
        } else {
            var new_url = original_url + (original_url.indexOf('?') !== -1 ? '&' : '?') + 'view='+view;
            window.history.pushState({ path: new_url }, '', new_url);
        }
	});
	
	<?php if(!wp_is_mobile() && is_tax()) { ?>
	var distance = $('.tabby-project .rows .header-row').offset().top-50;
	$(window).scroll(function() {
		if ( $(this).scrollTop() >= distance ) {
			// at the top
			$('.tabby-project .rows .header-row').addClass('sticky');
            $('.middle h1').addClass('spacer');
		} else {
			// not at the top
			$('.tabby-project .rows .header-row').removeClass('sticky');
            $('.middle h1').removeClass('spacer');
		}
	});
    <?php } ?>
    

</script>
<?php }
add_action( 'before_body_end', 'misc_output' );

/* Additional user profile fields */
function extra_profile_details( $methods ) {
    $methods['phone'] 					    = __( "Phone", "wproject" );
    $methods['skype'] 					    = __( "Skype", "wproject" );
    $methods['flock'] 					    = __( "Flock", "wproject" );
    $methods['slack'] 					    = __( "Slack", "wproject" );
    $methods['hangouts']                    = __( "Google Meet", "wproject" );
    $methods['teams'] 					    = __( "Microsoft Teams", "wproject" );
    $methods['show_tips'] 				    = __( "Show tips", "wproject" );
    $methods['title'] 					    = __( "Title", "wproject" );
    $methods['the_status'] 				    = __( "Status", "wproject" );
    $methods['user_photo'] 				    = __( "Photo", "wproject" );
    $methods['default_task_order']		    = __( "Default task order", "wproject" );
    $methods['default_task_ownership']	    = __( "Default task ownership", "wproject" );
    $methods['recent_tasks']                = __( "Recent tasks are", "wproject" );
    $methods['latest_activity']			    = __( "Latest activity", "wproject" );
    $methods['hide_gantt']				    = __( "Hide Gantt by default", "wproject" );
    $methods['minimise_complete_tasks']	    = __( "Minimise completed tasks in project view", "wproject" );
    $methods['pm_only_show_my_projects']    = __( "Only show my projects on the Project page", "wproject" );
    $methods['show_latest_activity']        = __( "Show the Latest Activity on the dashboard", "wproject" );
    $methods['dark_mode']				    = __( "Dark mode", "wproject" );
    $methods['dashboard_bar_chart']		    = __( "Dashboard bar chart", "wproject" );
    $methods['task_wip']                    = __( "Current task timer ID", "wproject" );
    $methods['fav_tasks']                   = __( "Favourite tasks", "wproject" );
    $methods['pm_auto_kanban_view']         = __( "Automatically open the Kanban board on project view", "wproject" );
    $methods['notifications_count']         = __( "Notifications count", "wproject" );
    return $methods;
}
add_filter('user_contactmethods','extra_profile_details',10,1);

/* User details */
function user_details() {

	$user_id = get_current_user_id();

	if ($user_id) {
		$user_info = get_userdata($user_id);
		$user_details = array(
			'user_email'				=> $user_info->user_email,
			'first_name'  				=> $user_info->first_name,
			'last_name'  				=> $user_info->last_name,
			'nickname'  				=> $user_info->nickname,
			'description'  				=> $user_info->description,
			'phone'  					=> $user_info->phone,
			'flock'  					=> $user_info->flock,
			'slack'  					=> $user_info->slack,
			'teams'  					=> $user_info->teams,
			'skype'  					=> $user_info->skype,
			'hangouts'					=> $user_info->hangouts,
			'title'						=> $user_info->title,
			'the_status'				=> $user_info->the_status,
			'show_tips'					=> $user_info->show_tips,
			'user_photo'				=> $user_info->user_photo,
			'default_task_order'		=> $user_info->default_task_order,
			'default_task_ownership'	=> $user_info->default_task_ownership,
			'recent_tasks'				=> $user_info->recent_tasks,
			'latest_activity'			=> $user_info->latest_activity,
			'hide_gantt'				=> $user_info->hide_gantt,
			'minimise_complete_tasks'	=> $user_info->minimise_complete_tasks,
			'pm_only_show_my_projects'	=> $user_info->pm_only_show_my_projects,
			'show_latest_activity'	    => $user_info->show_latest_activity,
			'dark_mode'					=> $user_info->dark_mode,
			'dashboard_bar_chart'		=> $user_info->dashboard_bar_chart,
			'task_wip'		            => $user_info->task_wip,
			'notifications_count'       => $user_info->notifications_count,
			'fav_tasks'		            => $user_info->fav_tasks,
			'pm_auto_kanban_view'       => $user_info->pm_auto_kanban_view
		);
		return $user_details;
	}
}


/* Log when the user last logged in */
function user_last_login( $user_login, $user ) {
    update_user_meta( $user->ID, 'last_login', time() );
}
add_action( 'wp_login', 'user_last_login', 10, 2 );
 
/* Task status */
function task_status() {
	$post_id = get_the_ID();
	$task_status = get_post_meta($post_id, 'task_status', TRUE);

	if($task_status == 'complete') {
		$task_status_name = '<i data-feather="check-circle-2"></i>' . __('Complete', 'wproject');
	} else if($task_status == 'incomplete') {
		$task_status_name = __('Incomplete', 'wproject');
	} else if($task_status == 'on-hold') {
		$task_status_name = __('On hold', 'wproject');
	} else if($task_status == 'in-progress') {
		$task_status_name = __('In progress', 'wproject');
	} else {
		$task_status_name = __('Not started', 'wproject');
	}

	$the_task_status = array(
        'name'	=> $task_status_name,
        'slug'  => $task_status
    );
	return $the_task_status;
	
}

/* Task priority */
function task_priority() {
	$post_id = get_the_ID();
	$task_priority = get_post_meta($post_id, 'task_priority', TRUE);

	if($task_priority == 'low') {
		$task_priority_name = /* translators: One of 4 possible task priorities */ __('Low', 'wproject');
	} else if($task_priority == 'normal' || $task_priority == '') {
		$task_priority_name = /* translators: One of 4 possible task priorities */ __('Normal', 'wproject');
	} else if($task_priority == 'high') {
		$task_priority_name = /* translators: One of 4 possible task priorities */ __('High', 'wproject');
	} else if($task_priority == 'urgent') {
		$task_priority_name = /* translators: One of 4 possible task priorities */ __('Urgent', 'wproject');
	}

	$the_task_priority = array(
        'name'	=> $task_priority_name,
        'slug'  => $task_priority
    );
	return $the_task_priority;
	
	/* 
	Template Usage:

		$task_priority = task_priority();
		echo $task_priority['name'];
		echo $task_priority['slug'];
	*/
}

/* Number of tasks completed in the project */
function project_tasks_complete() {
	$term_id		= get_queried_object()->term_id; 
	$term_object 	= get_term( $term_id );
	$tasks_complete_args = array(
		'post_type'     => 'task',
		'post_status'   => 'publish',
		'numberposts'   => -1,
		'tax_query' => array(
			array(
				'taxonomy' => 'project',
				'field'    => 'slug',
				'terms'    => array( $term_object->slug ),
				'operator' => 'IN'
			),
		),
		'meta_key' 		=> 'task_status',
		'meta_value'	=> array('complete')
	);
	$tasks_complete_query = new WP_Query( $tasks_complete_args );
	$tasks_complete_count = $tasks_complete_query->found_posts;

	if($tasks_complete_count > 1) {
		$the_phrase = $tasks_complete_count . ' ' . __('tasks have been completed in this project.', 'wproject');
	} else {
		$the_phrase = $tasks_complete_count . ' ' . __('task has been completed in this project.', 'wproject');
	}
	
	$task_info = array(
        'phrase'	=> $the_phrase,
        'count'  	=> $tasks_complete_count
    );
	return $task_info;
}

/* Project team member icons and count */
function project_team() {
	$category           = get_queried_object();
	$taxonomy_name      = 'project';
	$current_category   = $category->slug;
	$author_array = array();
	$args = array(
		'posts_per_page'    => -1,
		'post_type'         => 'task',
		'orderby'           => 'author',
		'order'             => 'ASC',
		'tax_query'         => array(
		array(
			'taxonomy'  => 'project',
			'field'     => 'slug',
			'terms'     => $current_category
			),
		),
	);
	$cat_posts = get_posts($args);
	foreach ($cat_posts as $cat_post) :
		if (!in_array($cat_post->post_author,$author_array)) {
			$author_array[] = $cat_post->post_author;
		}
	endforeach;
	

	$term_id                = get_queried_object()->term_id; 
	$term_meta              = get_term_meta($term_id); 
	$project_manager     	= get_user_by('ID', $term_meta['project_manager'][0]);
	$project_manager_id		= $project_manager->ID;

	$team_count = 0;
	$count = 1;

    foreach ($author_array as $author) :
        $user_data = get_userdata($author);

        if ($user_data) {
            $author_id         = $user_data->ID;
            $first_name        = $user_data->first_name;
            $last_name         = $user_data->last_name;
            $user_photo        = $user_data->user_photo;
            $user_id           = get_userdata($author_id);
            $user_role         = $user_id->roles[0];

            $the_first_name    = isset($first_name) ? $first_name[0] : '';
            $the_last_name     = isset($last_name) ? $last_name[0] : '';

            if (preg_match('/[a-e]/i', $the_first_name)) {
                $colour = 'a-e';
            } else if (preg_match('/[f-j]/i', $the_first_name)) {
                $colour = 'f-j';
            } else if (preg_match('/[k-o]/i', $the_first_name)) {
                $colour = 'k-o';
            } else if (preg_match('/[p-t]/i', $the_first_name)) {
                $colour = 'p-t';
            } else if (preg_match('/[u-z]/i', $the_first_name)) {
                $colour = 'u-z';
            } else {
                $colour = '';
            }

            if ($user_photo) {
                $avatar        = $user_photo;
                $avatar_id     = attachment_url_to_postid($avatar);
                $small_avatar  = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                $avatar        = $small_avatar[0];
                $the_avatar    = '<img src="' . $small_avatar[0] . '" class="avatar" />';
            } else {
                $the_avatar    = '<em class="letter-avatar avatar ' . $colour . '">' . $the_first_name . $the_last_name . '</em>';
            }

            if ($author_id && $project_manager_id != $author_id) { /* Only include users who are not project managers */ ?>
                <li title="<?php if ($user_role == 'client') {
                    _e('Client', 'wproject');
                } ?>">
                    <a href="<?php echo get_the_permalink(109); ?>?id=<?php echo $author_id; ?>">
                        <?php echo $the_avatar; ?>
                        <?php if ($user_role == 'client') {
                            echo '<em class="client-icon"></em>';
                        } ?>
                    </a>
                    <span class="pop"><?php echo $first_name; ?> <?php echo $last_name; ?></span>
                </li>
            <?php
            }
        }
    endforeach;
	?>
    <style>
        .client-icon {
            display: block;
            width: 16px;
            height: 16px;
            border: solid 1px #fff;
            border-radius: 100%;
            background: #fff url('<?php echo plugins_url() . '/clients-pro/images/client.svg'; ?>') no-repeat center;
            background-size: cover;
            position: absolute;
            bottom: 0;
            right: 0;
        }
    </style>
<?php }


/* Project Status */
function project_status() { 
	$the_task_info 				= project_tasks_complete();
	$wproject_settings          = wProject(); 
	$print_hide_user_photos  = $wproject_settings['print_hide_user_photos'];
?>

	<!--/ Start Project Status /-->
	<div class="status-box">

		<!--/ Start Stats /-->
		<div class="stats">
			<div class="total-task-count">
				<strong><?php _e('Total tasks', 'wproject'); ?></strong><span class="task-count value"></span>
			</div>

			<div class="complete-project-tasks">
				<strong><?php _e('Complete tasks', 'wproject'); ?></strong><span class="total-tasks value"></span>
			</div>

			<div class="total-project-tasks">
				<strong><?php _e('Tasks remaining', 'wproject'); ?></strong><span class="total-project value"></span>
			</div>

			<div class="total-team-count">
				<strong><?php _e('Project team', 'wproject'); ?></strong><span class="team-count value"></span>
			</div>
		</div>
		<!--/ End Stats /-->

		<!--/ Start Team /-->
		<div class="project-team <?php if($print_hide_user_photos) { echo 'hide'; } ?>">
			<?php project_team(); ?>
		</div>
		<!--/ End Team /-->

	</div>
	<!--/ End Project Status /-->

<?php }

/* My total task count in all projects */
function my_total_task_count() {

	global $wpdb;
    $tablename = $wpdb->prefix;
    $my_total_task_count = $wpdb->get_var("
    SELECT COUNT(p.ID)

    FROM " . $tablename . "posts p
    
    INNER JOIN " . $tablename . "postmeta pm 
        ON pm.post_id = p.ID 
        AND pm.meta_key = 'task_status'
        AND pm.meta_value NOT IN ('complete')
    
    INNER JOIN " . $tablename . "term_relationships tr ON tr.object_id = p.ID 
    
    INNER JOIN " . $tablename . "term_taxonomy tt 
        ON tt.term_id = tr.term_taxonomy_id 
        AND tt.taxonomy = 'project' 
    
    INNER JOIN " . $tablename . "termmeta tm 
        ON tm.term_id = tt.term_id 
        AND tm.meta_key = 'project_status'
        AND tm.meta_value NOT IN ('archived', 'cancelled', 'complete')
    
    WHERE  p.post_type = 'task'
        AND p.post_status IN ('publish', 'private')
        AND p.post_author = " . get_current_user_id()
    );
    return $my_total_task_count;
}

/* All tasks across all projects */
function all_tasks_count() {
		
    global $wpdb;
    $tablename = $wpdb->prefix;
    $active_tasks_count = $wpdb->get_var("
    SELECT COUNT(p.ID)

    FROM " . $tablename . "posts p
    
    INNER JOIN " . $tablename . "postmeta pm 
        ON pm.post_id = p.ID 
        AND pm.meta_key = 'task_status'
        AND pm.meta_value NOT IN ('complete')
    
    INNER JOIN " . $tablename . "term_relationships tr ON tr.object_id = p.ID 
    
    INNER JOIN " . $tablename . "term_taxonomy tt 
        ON tt.term_id = tr.term_taxonomy_id 
        AND tt.taxonomy = 'project' 
    
    INNER JOIN " . $tablename . "termmeta tm 
        ON tm.term_id = tt.term_id 
        AND tm.meta_key = 'project_status'
        AND tm.meta_value NOT IN ('archived', 'cancelled', 'complete')
    
    WHERE  p.post_type = 'task'
        AND p.post_status IN ('publish', 'private')
    ");
    return $active_tasks_count;

}

/* Count all other tasks */
function all_other_tasks_count() {
	if(all_tasks_count() == 0 ||  my_total_task_count() == 0) {
		$all_other_tasks_count = 0;
	} else {
		$all_other_tasks_count = all_tasks_count() - my_total_task_count();
	}
	return $all_other_tasks_count;
	
}


/* Project Progress */
function project_progress() {

    $wproject_settings              = wProject();

	$term_id		                = get_queried_object()->term_id; 
    $term_meta		                = get_term_meta($term_id); 
	$term_object	                = get_term( $term_id );
	$project_status	                = $term_meta['project_status'][0];
    
    $project_pep_talk_percentage    = isset($term_meta['project_pep_talk_percentage'][0]) ? $term_meta['project_pep_talk_percentage'][0] : '';
	$project_pep_talk_message   	= isset($term_meta['project_pep_talk_message'][0]) ? $term_meta['project_pep_talk_message'][0] : '';

    $pep_talk_percentage            = $wproject_settings['pep_talk_percentage'];
    $pep_talk_message               = $wproject_settings['pep_talk_message'];
   
    $print                          = isset($_GET['print']) ? $_GET['print'] : '';

    if($print) {
        $delay  = 0;
    } else {
        $delay  = 1500;
    }

    if($project_pep_talk_percentage && $project_pep_talk_percentage) {
        $the_pep_talk_percentage = $project_pep_talk_percentage;
        $the_pep_talk_message = $project_pep_talk_message;
		$use_pep_talk = true;
    }  else if($pep_talk_percentage && $pep_talk_message) {
		$the_pep_talk_percentage = $pep_talk_percentage;
        $the_pep_talk_message = $pep_talk_message;
		$use_pep_talk = true;
	} else {
        $the_pep_talk_percentage = 0;
        $the_pep_talk_message = '';
		$use_pep_talk = false;
    }

	$all_tasks_args = array(
		'posts_per_page' 	=> -1,
		'post_type' 		=> 'task',
		'post_status'		=> array('publish', 'private'),
		'numberposts'       => -1,
		// 'meta_key'          => 'task_status',
		// 'meta_value'        => array('incomplete', 'in-progress', 'on-hold', 'complete', 'not-started'),
		'tax_query' => array(
			array(
				'taxonomy' => 'project',
				'field'    => 'slug',
				'terms'    => array( $term_object->slug ),
				'operator' => 'IN'
			),
		)
	);
	$all_tasks = new WP_Query($all_tasks_args);
	$all_project_tasks = $all_tasks->post_count;

	$project_tasks_complete 	= project_tasks_complete();
	$all_project_tasks_complete = $project_tasks_complete['count'];

	if($all_project_tasks > 0) {
		$progress = $all_project_tasks_complete / $all_project_tasks * 100;
	} else {
		$progress = 0;
	}
	?><?php if($project_status == 'complete') { ?>

    <div class="project-finished">
        <p><i data-feather="check"></i><?php _e('This project is complete.', 'wproject'); ?></p>
    </div>
    <?php } else if($progress == 100) { ?>
    <div class="project-finished">
        <p><i data-feather="thumbs-up"></i><?php _e('There are no more tasks to do in this project.', 'wproject'); ?></p>
    </div>
    
    <?php } ?>
	<!--/ Start Project Progress Bar /-->
	<div class="main-progress">
		<span data="<?php echo round($progress, 10); ?>"><?php echo round($progress, 1); ?></span>
		<div style="width:0" data-progress="<?php echo round($progress, 1); ?>" data-pep-start="<?php echo $the_pep_talk_percentage; ?>"></div>

        <script>
            setTimeout(function(){
                $('.main-progress div').attr('style', 'width:<?php echo round($progress, 1); ?>%;');
				var project_progress = $('.main-progress div').data('progress');
				
                var count = parseFloat($('.main-progress span').text());
                $({ Counter: 0 }).animate({ Counter: count }, {
                    duration: <?php echo $delay; ?>,
                    easing: 'swing',
                    step: function() {
                        $('.main-progress span').html(this.Counter.toFixed(1) + '<sup>%</sup>');
                    }
                });

                <?php if($print) { ?>
                    $('.main-progress span').html(<?php echo round($progress, 1); ?> + '<sup>%</sup>');
                <?php } ?>

				// Pep talk logic
				<?php if($use_pep_talk == true) { ?>
					$('.right .owner').append('<span class="pep-talk"><?php echo addslashes($the_pep_talk_message); ?></span>');
				<?php } ?>

				if(project_progress >= <?php echo $the_pep_talk_percentage; ?> && project_progress < 100) {
					
					setTimeout(function() {
						$('.pep-talk').fadeIn();
					}, <?php echo $delay; ?>);

					$('.pep-talk').click(function() {
						$('.pep-talk').fadeOut();
					});

				} else if(project_progress == 100) {

					$('.pep-talk').fadeIn();
					$('.pep-talk').text('<?php _e('Good job, all done!', 'wproject'); ?>');

				}
				//console.log(project_progress);

            }, <?php echo $delay; ?>);

			$('body').on('click', '.pep-talk', function() {
				$(this).fadeOut();
			});

        </script>
	</div>
	<!--/ End Project Progress Bar /-->

<?php 		
}

/* Projects that I manage */
function projects_managed() {

	$wproject_settings  = wProject(); 
	$task_spacing       = $wproject_settings['task_spacing'];

	echo '<ul class="body-rows">';

	$projects = array(
			'taxonomy'      => 'project',
			'hide_empty'    => 0,
			'orderby'       => 'name',
			'post_status'   => 'publish',
			'order'         => 'ASC',
			'meta_query' => array(
				array(
				   'key'       => 'project_status',
				   'value'     => 'complete',
				   'compare'   => '!='
				)
			),
			'meta_query' => array(
				array(
				   'key'       => 'project_manager',
				   'value'     => get_current_user_id(),
				   'compare'   => '=='
				)
			),
		);
		$cats = get_categories($projects);
		$project_count = 0;
		$i = 1;
		foreach($cats as $cat) { 

			$date_format        		= get_option('date_format'); 
			$term_id                    = $cat->term_id; 
			$term_meta                  = get_term_meta($term_id); 
			$term_object                = get_term( $term_id );
			$description                = term_description($term_id);
			$project_status             = $term_meta['project_status'][0];
			$project_start_date         = $term_meta['project_start_date'][0];
			$project_end_date           = $term_meta['project_end_date'][0];
			$project_time_allocated     = $term_meta['project_time_allocated'][0];
			$project_hourly_rate        = $term_meta['project_hourly_rate'][0];

            $project_created_date       = $term_meta['project_created_date'][0] ?? '';
			if ($project_created_date) {
				$new_project_created_date   = new DateTime($project_created_date);
				$the_project_created_date   = $new_project_created_date->format($date_format);
			} else {
				$new_project_created_date   = '';
				$the_project_created_date   = '';
			}
			
			$budget = '';
			if($project_time_allocated && $project_hourly_rate) {
				$budget = $project_time_allocated * $project_hourly_rate;
			}

			if($project_start_date || $project_end_date) {
				$new_project_start_date     = new DateTime($project_start_date);
				$the_project_start_date     = $new_project_start_date->format($date_format);
		
				$new_project_end_date       = new DateTime($project_end_date);
				$the_project_end_date       = $new_project_end_date->format($date_format);
			} else {
				$the_project_start_date = '';
				$the_project_end_date   = '';
			}

			/* Project status */
			if($project_status == 'in-progress') {
				$the_project_status = __('In progress', 'wproject');
			} else if($project_status == 'planning') {
				$the_project_status = __('Planning', 'wproject');
			} else if($project_status == 'proposed') {
				$the_project_status = __('Proposed', 'wproject');
			} else if($project_status == 'setting-up') {
				$the_project_status = __('Setting up', 'wproject');
			} else if($project_status == 'archived') {
				$the_project_status = __('Archived', 'wproject');
			} else if($project_status == 'cancelled') {
				$the_project_status = __('Cancelled', 'wproject');
			} else if($project_status == 'complete') {
				$the_project_status = __('Complete', 'wproject');
			}

			$wproject_settings = wProject();

		?>

		<li class="<?php if($task_spacing) { echo 'spacing'; } ?>">
			<span>
				<strong <?php if($project_status == 'cancelled' || $project_status == 'archived') { echo 'style="text-decoration:line-through;"'; }?>><a href="<?php echo get_category_link( $term_id ) ?>"><?php echo $cat->name; ?></a></strong>
				<?php if($project_status) { ?>
				    <em>
                        <?php if($project_status) { echo $the_project_status; } ?>
                        <?php if($project_created_date) { ?>
                            | <?php _e('Created', 'wproject'); ?> <?php echo $the_project_created_date; ?>
                        <?php } ?>
                    </em>
				<?php } ?>
			</span>

			<?php if($project_start_date) { ?>
				<span><?php if(wp_is_mobile()) { echo '<em class="space">'; _e( 'Start date', 'wproject' ); echo ': </em>'; } ?><?php echo $the_project_start_date; ?></span>
			<?php } else { ?>
				<?php if(!wp_is_mobile()) { ?>
                    <span></span>
                <?php } ?>
			<?php } ?>

			<?php if($project_end_date) { ?>
				<span><?php if(wp_is_mobile()) { echo '<em class="space">'; _e( 'Due', 'wproject' ); echo ': </em>'; } ?><?php echo $the_project_end_date; ?></span>
			<?php } else { ?>
				<?php if(!wp_is_mobile()) { ?>
                    <span></span>
                <?php } ?>
			<?php } ?>

			<?php if($project_time_allocated) { ?>
				<span><?php if(wp_is_mobile()) { echo '<em class="space">'; _e( 'Time allocated', 'wproject' ); echo ': </em>'; } ?><?php echo $project_time_allocated; ?><?php /* Abbreviation of 'hours': */ _e( 'hrs', 'wproject' ); ?></span>
			<?php } else { ?>
				<?php if(!wp_is_mobile()) { ?>
                    <span></span>
                <?php } ?>
			<?php } ?>

			<?php if(!empty($budget)) { ?>
				<span><?php if(wp_is_mobile()) { echo '<em class="space">'; _e( 'Budget', 'wproject' ); echo ': </em>'; } ?><?php if($wproject_settings['currency_symbol_position'] == 'l') { echo $wproject_settings['currency_symbol']; } ?><?php if($project_time_allocated && $project_hourly_rate) { echo number_format($budget); } ?><?php if($wproject_settings['currency_symbol_position'] == 'r') { echo $wproject_settings['currency_symbol']; } ?></span>
			<?php } else { ?>
				<?php if(!wp_is_mobile()) { ?>
					<span></span>
				<?php } ?>
			<?php } ?>
		</li>
		<?php
		$project_count = $i++;
		}
	?>
	</ul>
	<script>
		/* Inject: into the Project I manage tab on dashboard */
		$('.tab-nav .my-projects span').text( '<?php echo $project_count; ?>' );
		<?php if($project_count < 1) { ?>
			$('.tab-content-my-projects, .tab-nav .my-projects').remove();
		<?php } ?>
	</script>
<?php }


/* Task filters */
function task_filter() {

	$wproject_settings          = wProject();
	$cl                         = $wproject_settings['context_labels'];
    $context_labels             = rtrim($cl, ', ');
    $the_context_labels         = explode(',', $context_labels);

?>
<ul class="filter-selection">
	<li class="sep sep-filter"><?php _e('Filter', 'wproject'); ?><i data-feather="filter"></i></li>
	<li data="all" class="all active"><em><?php _e('All', 'wproject'); ?></em></li>    
	<li data="is_blocked_by"><em><?php _e('Blocked', 'wproject'); ?></em></li>       
	<li data="complete"><em><?php _e('Complete', 'wproject'); ?></em></li>       
	<li data="has_issues_with"><em><?php _e('Has issues', 'wproject'); ?></em></li>       
	<li data="incomplete" class="filter-incomplete"><em><?php _e('Incomplete', 'wproject'); ?></em></li>       
	<li data="in-progress"><em><?php _e('In progress', 'wproject'); ?></em></li>       
	<li data="milestone"><em><?php _e('Milestones', 'wproject'); ?></em></li>       
	<li data="not-started"><em><?php _e('Not started', 'wproject'); ?></em></li>       
	<li data="on-hold"><em><?php _e('On hold', 'wproject'); ?></em></li>       
	<li data="orphan" class="filter-orphan"><em><?php _e('Orphans', 'wproject'); ?></em></li>       
	<li data="overdue"><em><?php _e('Overdue', 'wproject'); ?></em></li>       
	<li data="pinned"><em><?php _e('Pinned', 'wproject'); ?></em></li>       
    <li data="time"><em><?php _e('Recording time', 'wproject'); ?></em></li>
	<li data="is_similar_to"><em><?php _e('Similar tasks', 'wproject'); ?></em></li>              

	<li class="sep sep-priority"><?php _e('Priority', 'wproject'); ?><i data-feather="alert-circle"></i></li>
	<li data="low"><em><?php _e('Low', 'wproject'); ?></em></li>       
	<li data="normal"><em><?php _e('Normal', 'wproject'); ?></em></li>       
	<li data="high"><em><?php _e('High', 'wproject'); ?></em></li>       
	<li data="urgent"><em><?php _e('Urgent', 'wproject'); ?></em></li>       
	
	<li class="sep sep-context"><?php _e('Context Labels', 'wproject'); ?><i data-feather="tag"></i></li>       
	<?php
	foreach($the_context_labels as $value) { ?>
		<li data="<?php echo strtolower(trim($value)); ?>" class="<?php echo strtolower(trim($value)); ?>"><em><?php echo str_replace('-', ' ', trim($value)); ?></em></li>
	<?php }
	?>
</ul>
<script>
    /* Close the task status options when clicking outside of it */
	$(document).mouseup(function(e)  {
		var container = $('.filter-selection');

		// if the target of the click isn't the container nor a descendant of the container
		if (!container.is(e.target) && container.has(e.target).length === 0)  {
			container.fadeOut();
            jQuery('.filters').removeClass('open');
		}
	});
</script>
<?php }

/* Add widget support */
function wproject_dashboard_widget() {
	register_sidebar( array(
		'name'          => 'wProject Dashboard',
		'id'            => 'wproject-dashboard-widget',
        'description'   => __( 'Appears on the dashboard below existing elements.', 'wproject' ),
		'before_widget' => '<div class="wproject-dashboard-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'wproject_dashboard_widget' );

function wproject_sidebar_widget() {
	register_sidebar( array(
		'name'          => 'wProject Sidebar',
		'id'            => 'wproject-sidebar-widget',
        'description'   => __( 'Appears in the right pane on all pages.', 'wproject' ),
		'before_widget' => '<div class="wproject-sidebar-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'wproject_sidebar_widget' );

function wproject_team_widget() {
	register_sidebar( array(
		'name'          => 'wProject Team',
		'id'            => 'wproject-team-widget',
        'description'   => __( 'Appears on the team page.', 'wproject' ),
		'before_widget' => '<div class="wproject-team-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'wproject_team_widget' );

function wproject_account_widget() {
	register_sidebar( array(
		'name'          => 'wProject Account',
		'id'            => 'wproject-account-widget',
        'description'   => __( 'Appears on the account page.', 'wproject' ),
		'before_widget' => '<div class="wproject-account-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'wproject_account_widget' );

function wproject_project_widget() {
	register_sidebar( array(
		'name'          => 'wProject Project',
		'id'            => 'wproject-project-widget',
        'description'   => __( 'Appears on projects pages.', 'wproject' ),
		'before_widget' => '<div class="wproject-project-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'wproject_project_widget' );

function wproject_task_widget() {
	register_sidebar( array(
		'name'          => 'wProject Task',
		'id'            => 'wproject-task-widget',
        'description'   => __( 'Appears on task pages.', 'wproject' ),
		'before_widget' => '<div class="wproject-task-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'wproject_task_widget' );

function wproject_page_widget() {
	register_sidebar( array(
		'name'          => 'wProject Page',
		'id'            => 'wproject-page-widget',
        'description'   => __( 'Appears on custom page content.', 'wproject' ),
		'before_widget' => '<div class="wproject-page-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2>',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'wproject_page_widget' );


/* Ajax functions */
require_once('admin-functions/functions-ajax.php');
require_once('admin-functions/functions-task.php');


/* Comments */
function wproject_comments($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment;

	$comment_author_id 	= get_comment(get_comment_ID())->user_id;
    $comment_id 	    = get_comment_ID();
	$userID 			= get_current_user_id();
	$user_info			= get_userdata($comment_author_id);
    $first_name         = @$user_info->user_firstname;
    $last_name          = @$user_info->user_lastname;
    $user               = wp_get_current_user();
    $user_role          = $user->roles[0];

	if($first_name == '') {
		$first_name = get_comment_author($comment_id);
		$initials = substr(get_comment_author($comment_id), 0, 1);
	} else {
		$initials = substr($first_name,0,1) . ' ' . substr($last_name,0,1);
	}

	if($last_name == '') {
		$last_name = '';
	} else {
		$last_name = $last_name;
	}

    if(preg_match("/[a-e]/i", $first_name[0])) {
        $colour = 'a-e';
    } else if(preg_match("/[f-j]/i", $first_name[0])) {
        $colour = 'f-j';
    } else if(preg_match("/[k-o]/i", $first_name[0])) {
        $colour = 'k-o';
    } else if(preg_match("/[p-t]/i", $first_name[0])) {
        $colour = 'p-t';
    } else if(preg_match("/[u-z]/i", $first_name[0])) {
        $colour = 'u-z';
    } else {
        $colour = '';
    }

	$user_photo         = get_the_author_meta( 'user_photo', $comment_author_id );   
	if($user_photo) {
        $avatar         = $user_photo;
        $avatar_id      = attachment_url_to_postid($avatar);
        $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
        $avatar         = $small_avatar[0];
        $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
    } else {
        $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $initials . '</div>';
    }

	?>

	<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>" data-reply-name="@<?php echo $first_name . " " . $last_name; ?>">
	    
		<div class="comment-wrap">

			<div class="comment-img">
                <?php echo $the_avatar; ?>
			</div>

			<div class="comment-body">
                
				<?php if ($comment->comment_approved == '0') { ?><em><i aria-hidden="true"></i> <?php _e('Comment awaiting approval', 'wproject'); ?></em><br /><?php } ?>

                <h4 class="comment-author-and-date">
                    <i><?php echo $first_name . " " . $last_name; ?></i>
                    <a href="<?php echo get_comment_link(); ?>" class="comment-date"> <i data-feather="calendar"></i> <?php printf(__('%1$s at %2$s', 'wproject'), get_comment_date(),  get_comment_time()) ?></a>
                </h4>

				<div class="the-comment">
                    
                    <?php comment_text(); ?>

                    <?php if($user_role != 'observer') { ?>
                        <div class="comment-actions">
                            <span class="comment-reply"><?php echo comment_reply_link(array_merge( $args, array('reply_text' => __('Reply', 'wproject'), 'depth' => $depth, 'max_depth' => $args['max_depth'])), $comment->comment_ID); ?></span>
                            <?php if($comment_author_id == $userID) { ?>
                            <span class="comment-delete" data-id="<?php echo $comment_id; ?>"><a><?php _e('Delete', 'wproject'); ?></a></span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
			</div>
			
		</div>

	</li>
 <?php
}

/* Get wProject Options */
function wProject() {

	$options 							= get_option( 'wproject_settings' );

	/* Presentation options */
	$branding_logo 						= isset($options['branding_logo']) ? $options['branding_logo'] : '';
	$favicon 							= isset($options['favicon']) ? $options['favicon'] : '';
	$remove_pages_nav					= isset($options['remove_pages_nav']) ? $options['remove_pages_nav'] : '';
	$pages_label 						= isset($options['pages_label']) ? $options['pages_label'] : '';
	$avatar_style						= isset($options['avatar_style']) ? $options['avatar_style'] : '';
    $system_busy_blur                   = isset($options['system_busy_blur']) ? $options['system_busy_blur'] : '';
    $system_busy_disable_ui             = isset($options['system_busy_disable_ui']) ? $options['system_busy_disable_ui'] : '';
	$force_avatar					    = isset($options['force_avatar']) ? $options['force_avatar'] : '';
	$task_spacing					    = isset($options['task_spacing']) ? $options['task_spacing'] : '';
    $project_list_style					= isset($options['project_list_style']) ? $options['project_list_style'] : '';
    $pep_talk_percentage                = isset($options['pep_talk_percentage']) ? $options['pep_talk_percentage'] : '';
    $pep_talk_message                   = isset($options['pep_talk_message']) ? $options['pep_talk_message'] : '';

	/* Team privs options */
	$users_can_create_tasks				= isset($options['users_can_create_tasks']) ? $options['users_can_create_tasks'] : '';
	$users_can_assign_tasks				= isset($options['users_can_assign_tasks']) ? $options['users_can_assign_tasks'] : '';
	$users_can_task_takeover			= isset($options['users_can_task_takeover']) ? $options['users_can_task_takeover'] : '';
	$users_can_claim_task_ownership		= isset($options['users_can_claim_task_ownership']) ? $options['users_can_claim_task_ownership'] : '';
	$allow_pm_admin_access				= isset($options['allow_pm_admin_access']) ? $options['allow_pm_admin_access'] : '';
    $project_access				        = isset($options['project_access']) ? $options['project_access'] : '';

	/* Gantt options */
	$gantt_show_dashboard				= isset($options['gantt_show_dashboard']) ? $options['gantt_show_dashboard'] : '';
	$gantt_show_project					= isset($options['gantt_show_project']) ? $options['gantt_show_project'] : '';
	$gantt_show_all_project_page		= isset($options['gantt_show_all_project_page']) ? $options['gantt_show_all_project_page'] : '';
	$gantt_scale_tasks					= isset($options['gantt_scale_tasks']) ? $options['gantt_scale_tasks'] : '';
	$gantt_scale_projects				= isset($options['gantt_scale_projects']) ? $options['gantt_scale_projects'] : '';
	$gantt_pagination					= isset($options['gantt_pagination']) ? $options['gantt_pagination'] : '';
	$gantt_hide_completed				= isset($options['gantt_hide_completed']) ? $options['gantt_hide_completed'] : '';
	$gantt_jump_to_today				= isset($options['gantt_jump_to_today']) ? $options['gantt_jump_to_today'] : '';
	$gantt_show_popup					= isset($options['gantt_show_popup']) ? $options['gantt_show_popup'] : '';
	$gantt_show_subtasks				= isset($options['gantt_show_subtasks']) ? $options['gantt_show_subtasks'] : '';
	$gantt_popup_position				= isset($options['gantt_popup_position']) ? $options['gantt_popup_position'] : '';
	
	/* Kanban options */
	$enable_kanban						= isset($options['enable_kanban']) ? $options['enable_kanban'] : '';
	$kanban_density						= isset($options['kanban_density']) ? $options['kanban_density'] : '';
	$kanban_card_colours				= isset($options['kanban_card_colours']) ? $options['kanban_card_colours'] : '';
	$kanban_card_descriptions           = isset($options['kanban_card_descriptions']) ? $options['kanban_card_descriptions'] : '';
	$kanban_unfocused_cards           	= isset($options['kanban_unfocused_cards']) ? $options['kanban_unfocused_cards'] : '';

    /* Context Labels options */
    $context_labels						= isset($options['context_labels']) ? $options['context_labels'] : '';
	$context_label_colour				= isset($options['context_label_colour']) ? $options['context_label_colour'] : '';
	$context_label_display				= isset($options['context_label_display']) ? $options['context_label_display'] : '';

	/* Time & Cost options */
	$enable_time						= isset($options['enable_time']) ? $options['enable_time'] : '';
    $overtime				            = isset($options['overtime']) ? $options['overtime'] : '';
	$currency_symbol					= isset($options['currency_symbol']) ? $options['currency_symbol'] : '';
	$currency_symbol_position			= isset($options['currency_symbol_position']) ? $options['currency_symbol_position'] : '';
	$default_project_rate				= isset($options['default_project_rate']) ? $options['default_project_rate'] : '';
	$logged_time_increments				= isset($options['logged_time_increments']) ? $options['logged_time_increments'] : '';

	/* Notification options */
	$notify_when_task_takeover			= isset($options['notify_when_task_takeover']) ? $options['notify_when_task_takeover'] : '';
	$notify_when_task_takeover_decided	= isset($options['notify_when_task_takeover_decided']) ? $options['notify_when_task_takeover_decided'] : '';
	$response_message_duration			= isset($options['response_message_duration']) ? $options['response_message_duration'] : '';
	$response_message_position			= isset($options['response_message_position']) ? $options['response_message_position'] : '';
	$notify_maximum_messages			= isset($options['notify_maximum_messages']) ? $options['notify_maximum_messages'] : '';
	$notify_when_task_created			= isset($options['notify_when_task_created']) ? $options['notify_when_task_created'] : '';
	$notify_when_comment_reply			= isset($options['notify_when_comment_reply']) ? $options['notify_when_comment_reply'] : '';
	$notify_all_when_project_created    = isset($options['notify_all_when_project_created']) ? $options['notify_all_when_project_created'] : '';
    $notify_pm_when_task_complete		= isset($options['notify_pm_when_task_complete']) ? $options['notify_pm_when_task_complete'] : '';
    $notify_pm_when_subtasks_complete   = isset($options['notify_pm_when_subtasks_complete']) ? $options['notify_pm_when_subtasks_complete'] : '';
	//$notify_when_project_complete		= isset($options['notify_when_project_complete']) ? $options['notify_when_project_complete'] : '';
	//$notify_when_project_deleted		= isset($options['notify_when_project_deleted']) ? $options['notify_when_project_deleted'] : '';
	//$notify_when_comment				= isset($options['notify_when_comment']) ? $options['notify_when_comment'] : '';
    $sender_name		                = isset($options['sender_name']) ? $options['sender_name'] : '';
    $sender_email		                = isset($options['sender_email']) ? $options['sender_email'] : '';
	$dashboard_message					= isset($options['dashboard_message']) ? $options['dashboard_message'] : '';
    $team_page					        = isset($options['team_page']) ? $options['team_page'] : '';
    $default_pm					        = isset($options['default_pm']) ? $options['default_pm'] : '';
    $relation_tasks                     = isset($options['relation_tasks']) ? $options['relation_tasks'] : '';

	/* Comment options */
	$task_comments_enabled				= isset($options['task_comments_enabled']) ? $options['task_comments_enabled'] : '';
	$recent_comments_number				= isset($options['recent_comments_number']) ? $options['recent_comments_number'] : '';
    $comment_order				        = isset($options['comment_order']) ? $options['comment_order'] : '';
    $show_comment_dates                 = isset($options['show_comment_dates']) ? $options['show_comment_dates'] : '';

	/* Printing options */
	$print_hide_complete_tasks			= isset($options['print_hide_complete_tasks']) ? $options['print_hide_complete_tasks'] : '';

	/* Other options */
    $bypass_google				        = isset($options['bypass_google']) ? $options['bypass_google'] : '';
	$enable_subtask_descriptions		= isset($options['enable_subtask_descriptions']) ? $options['enable_subtask_descriptions'] : '';
    $enable_leave_warning				= isset($options['enable_leave_warning']) ? $options['enable_leave_warning'] : '';
	$job_number_prefix					= isset($options['job_number_prefix']) ? $options['job_number_prefix'] : '';
	$preferred_calendar					= isset($options['preferred_calendar']) ? $options['preferred_calendar'] : '';
	$show_task_id						= isset($options['show_task_id']) ? $options['show_task_id'] : '';
	$wproject_license					= isset($options['wproject_license']) ? $options['wproject_license'] : '';	
	$print_hide_user_photos			    = isset($options['print_hide_user_photos']) ? $options['print_hide_user_photos'] : '';
    $print_hide_task_descriptions       = isset($options['print_hide_task_descriptions']) ? $options['print_hide_task_descriptions'] : '';
	$completed_projects_nav				= isset($options['completed_projects_nav']) ? $options['completed_projects_nav'] : '';
    $fade_on_hold				        = isset($options['fade_on_hold']) ? $options['fade_on_hold'] : '';
	$contacts_link_to_project			= isset($options['contacts_link_to_project']) ? $options['contacts_link_to_project'] : '';
    $client_use_kanban           	    = isset($options['client_use_kanban']) ? $options['client_use_kanban'] : '';
    $delete_projects_from_backend       = isset($options['delete_projects_from_backend']) ? $options['delete_projects_from_backend'] : '';
	
    
	$wprojectSettings = array(
		
		'branding_logo'    					=> $branding_logo,
		'favicon'    						=> $favicon,
		'remove_pages_nav'					=> $remove_pages_nav,
		'pages_label'    					=> $pages_label,
		'avatar_style'    					=> $avatar_style,
        'system_busy_blur'                  => $system_busy_blur,
        'system_busy_disable_ui'            => $system_busy_disable_ui,
        'force_avatar'                      => $force_avatar,
		'task_spacing'                      => $task_spacing,
		'project_list_style'				=> $project_list_style,
        'pep_talk_percentage'				=> $pep_talk_percentage,
        'pep_talk_message'				    => $pep_talk_message,

		'users_can_assign_tasks'			=> $users_can_assign_tasks,
		'users_can_task_takeover'			=> $users_can_task_takeover,
		'users_can_claim_task_ownership'	=> $users_can_claim_task_ownership,
		'users_can_create_tasks'			=> $users_can_create_tasks,
		'allow_pm_admin_access'				=> $allow_pm_admin_access,
        'project_access'				    => $project_access,

		'gantt_show_dashboard'				=> $gantt_show_dashboard,
		'gantt_show_project'				=> $gantt_show_project,
		'gantt_show_all_project_page'		=> $gantt_show_all_project_page,
		'gantt_scale_tasks'					=> $gantt_scale_tasks,
		'gantt_scale_projects'				=> $gantt_scale_projects,
		'gantt_pagination'					=> $gantt_pagination,
		'gantt_hide_completed'				=> $gantt_hide_completed,
		'gantt_jump_to_today'				=> $gantt_jump_to_today,
		'gantt_show_popup'					=> $gantt_show_popup,
		'gantt_show_subtasks'				=> $gantt_show_subtasks,		
		'gantt_popup_position'				=> $gantt_popup_position,

		'enable_kanban'						=> $enable_kanban,
		'kanban_density'					=> $kanban_density,
		'kanban_card_colours'				=> $kanban_card_colours,
        'kanban_card_descriptions'          => $kanban_card_descriptions,
		'kanban_unfocused_cards'          	=> $kanban_unfocused_cards,

        'context_labels'					=> $context_labels,
		'context_label_colour'				=> $context_label_colour,
		'context_label_display'				=> $context_label_display,
		
		'enable_time'						=> $enable_time,
        'overtime'                          => $overtime,
		'currency_symbol'					=> $currency_symbol,
		'currency_symbol_position'			=> $currency_symbol_position,
		'default_project_rate'				=> $default_project_rate,
		'logged_time_increments'			=> $logged_time_increments,

		'response_message_duration'			=> $response_message_duration,
		'response_message_position'			=> $response_message_position,
		'notify_maximum_messages'			=> $notify_maximum_messages,
		'notify_when_task_created'			=> $notify_when_task_created,
		'notify_when_comment_reply'			=> $notify_when_comment_reply,
		'notify_when_task_takeover'			=> $notify_when_task_takeover,
		'notify_when_task_takeover_decided' => $notify_when_task_takeover_decided,
		'notify_all_when_project_created'   => $notify_all_when_project_created,
		//'notify_when_project_complete' 		=> $notify_when_project_complete,
		//'notify_when_project_deleted' 		=> $notify_when_project_deleted,
		//'notify_when_comment' 				=> $notify_when_comment,
		'notify_pm_when_task_complete'		=> $notify_pm_when_task_complete,
        'notify_pm_when_subtasks_complete'  => $notify_pm_when_subtasks_complete,
        'sender_name'		                => $sender_name,
        'sender_email'		                => $sender_email,
		'dashboard_message'					=> $dashboard_message,
        'team_page'					        => $team_page,
        'default_pm'                        => $default_pm,
        'relation_tasks'                    => $relation_tasks,
			
		'task_comments_enabled'				=> $task_comments_enabled,
		'recent_comments_number'			=> $recent_comments_number,
        'comment_order'			            => $comment_order,
        'show_comment_dates'                => $show_comment_dates,

		'print_hide_complete_tasks'			=> $print_hide_complete_tasks,

        'bypass_google'				        => $bypass_google,
		'enable_subtask_descriptions'		=> $enable_subtask_descriptions,
		'enable_leave_warning'				=> $enable_leave_warning,
		'job_number_prefix'					=> $job_number_prefix,
		'show_task_id'  					=> $show_task_id,
		'preferred_calendar'				=> $preferred_calendar,
		'print_hide_user_photos'			=> $print_hide_user_photos,
        'print_hide_task_descriptions'      => $print_hide_task_descriptions,
		'completed_projects_nav'			=> $completed_projects_nav,

		'wproject_license'					=> $wproject_license,
        'fade_on_hold'					    => $fade_on_hold,
		'contacts_link_to_project'			=> $contacts_link_to_project,
        'client_use_kanban'			        => $client_use_kanban,
        'delete_projects_from_backend'      => $delete_projects_from_backend,

		'current_url'						=> (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"

		
    );
	return $wprojectSettings;
	
	/*
		Template Usage:

		$wproject_settings = wProject();
        echo $wproject_settings['currency_symbol'];
	*/
}


/* Copy button */
function copy_link() { ?>
	<li class="copy-link"><a><i data-feather="copy"></i><span><?php _e('Copy link', 'wproject'); ?></span></a></li>
<?php }

/* Add to your calendar */
function add_to_calendar() {
	$wproject_settings  = wProject();
	$preferred_calendar = $wproject_settings['preferred_calendar'];
	$date_format 		= get_option('date_format'); 
	$start_time			= "000000";
    $end_time			= "000000";
    global $wp;

	if($preferred_calendar != '0') {

	/* If on a project page */
	if(is_tax()) {
		$term_id			= get_queried_object()->term_id; 
		$term_meta			= get_term_meta($term_id); 
		$term_object		= get_term( $term_id );
		$start_date			= str_replace('-', '', $term_meta['project_start_date'][0]);
		$end_date			= str_replace('-', '', $term_meta['project_end_date'][0]);
		$description		= urlencode(preg_replace('/<p[^>]*>(.*)<\/p[^>]*>/i', '$1', strip_tags(get_the_archive_description())));
		$title				= urlencode(preg_replace('/<p[^>]*>(.*)<\/p[^>]*>/i', '$1', strip_tags(get_queried_object()->name)));

	/* otherwise if on a task page */
	} else if(is_singular('task')) {
		$task_id			= get_the_ID();
		$start_date			= str_replace('-', '', get_post_meta($task_id, 'task_start_date', TRUE));
        $end_date			= str_replace('-', '', get_post_meta($task_id, 'task_end_date', TRUE));
		$description		= str_replace('-', '', get_post_meta($task_id, 'task_description', TRUE));
		$title				= urlencode(preg_replace('/<p[^>]*>(.*)<\/p[^>]*>/i', '$1', strip_tags(get_the_title())));
	}

	$location 				= home_url( $wp->request );

	if($preferred_calendar && $start_date && $end_date) {

		if($preferred_calendar == 'google') {
			
			$cal_link = 'https://calendar.google.com/calendar/u/0/r/eventedit?text=' . $title . '&details=' . $description . '&dates=' . $start_date .'T' . $end_time . '/' . $end_date . 'T' . $end_time . '&location=' . $location;

		} else if($preferred_calendar == 'yahoo') {

			$cal_link = 'https://calendar.yahoo.com/?v=60&TITLE=' . $title . '&ST=' . $start_date .'&ET=' . $end_time . '&DESC=' . $description . '&URL=' . $location . '&in_loc=' . $location;

		} else if($preferred_calendar == 'outlook') {

			$cal_link = 'https://outlook.live.com/owa/?path=/calendar/view/month&rru=addevent&startdt=' . $start_date .'Z&enddt=' . $end_date .'Z&subject=' . $title . '&location' . $location;

		} else if($preferred_calendar == 'ical') {

			$cal_link = get_template_directory_uri() . '/inc/ical.php?title=' . $title . '&datestart=' . $start_date .'&dateend=' . $end_date .'&description=' . $description . '&filename=' . $title . '&uniqid=project-' . $title . ' - Project' . '&uri=' . $location;
		}

	?>
		<li><a href="<?php echo $cal_link; ?>" target="_blank" rel="noopener"><i data-feather="calendar"></i><?php printf(__('Add to %1$s calendar', 'wproject'), ucfirst($preferred_calendar)); ?></a></li>        
	<?php }
	}
}


/* Archive the project */
function archive_project_button() { ?>
    <form name="archive-project" id="archive-project" method="post">
        <li class="archive-this-project">
            <a><i data-feather="archive"></i><?php _e('Archive project', 'wproject') ?></a>
        </li>  
        <input type="hidden" name="project_id" value="<?php $term_id = get_queried_object()->term_id; echo $term_id; ?>" />
    </form>
    <script>
        $('.archive-this-project').click(function() {

            if (confirm('<?php _e('Really archive this project?', 'wproject'); ?>')) {
                setTimeout(function() { 
                    $('#archive-project').submit();
                }, 100);
                setTimeout(function() { 
                    var project_name = $('.middle h1').text();
                    $('.middle h1').text(project_name + ' (<?php _e('Archived', 'wproject') ?>)');
                }, 2000);
            }

        });
    </script>
<?php }


/* Delete completed tasks within the project */
function delete_completed_project_tasks_button() { ?>
    <form name="delete-completed-project-tasks" id="delete-completed-project-tasks" method="post">
        <li class="delete-completed-project-tasks">
            <a><i data-feather="x-square"></i><?php _e('Delete completed', 'wproject') ?></a>
        </li>  
        <input type="hidden" name="project_id" value="<?php $term_id = get_queried_object()->term_id; echo $term_id; ?>" />
    </form>
    <script>
        $('.delete-completed-project-tasks').click(function() {

            if (confirm('<?php _e('Really delete all completed tasks in this project?', 'wproject'); ?>')) {
                if (confirm('<?php _e('Are you sure? This cannot be undone.', 'wproject'); ?>')) {
                    setTimeout(function() { 
                        $('#delete-completed-project-tasks').submit();
                    }, 100);
                }
            }
        });
    </script>
<?php }

/* Overdue notice for projects and tasks */
function overdue() { 

	/* Only use this on project or task pages */
	if(is_tax('project') || is_singular('task')) {

		$wproject_settings      = wProject(); 
		$now                    = strtotime('today');
		$date_format            = get_option('date_format'); 

		/* If on a project page */
		if(is_tax('project')) {

			$term_id		= get_queried_object()->term_id; 
			$term_meta		= get_term_meta($term_id); 

			$new_date		= new DateTime($term_meta['project_end_date'][0]);
			$end_date       = $term_meta['project_end_date'][0];
			$due_date		= strtotime($end_date);
			$the_status	    = $term_meta['project_status'][0]; /* Leave empty so $task_status condition can be honoured on project page */

			$text_end_date	= $new_date->format($date_format);
			$type			= __('project', 'wproject');

		/* otherwise if on a task page */
		} else if(is_singular('task')) {

			$task_id		= get_the_ID();
			$the_status 	= get_post_meta($task_id, 'task_status', TRUE);
			$end_date		= get_post_meta($task_id, 'task_end_date', TRUE);
			$due_date		= strtotime($end_date);
			$new_date		= new DateTime($end_date);

			$text_end_date	= $new_date->format($date_format);
			$type			= __('Task', 'wproject');

		}
		
		if($due_date && $now > $due_date && $the_status !='complete' && $the_status != 'archived') { ?>
			<div class="side-notice warn">
				<span><i data-feather="x"></i></span>
				<p>
                    <?php 
                        //printf( __('Overdue %1$s: %2$s', 'wproject' ), $type, $text_end_date);
                        printf( __('Due: %1$s', 'wproject' ),$text_end_date);
                    ?>
                </p>
			</div>

			<?php if($the_status !='complete') { ?>
			<script>
				/* Inject overdue class for overdue dates */
				$('.task-specs .dates, .project-details .due-date').addClass('overdue');
			</script>
			<?php } ?>
			
			<script>
				if($(window).width() < 960) {
					$('.warn').click(function() {
						$('.side-notice').fadeOut();
					});
				}
			</script>
			
		<?php 
		}
	}
}
add_action('before_side_nav', 'overdue', 20);


/* Extend project dialogue */
function extend_the_project() {

	if(is_tax('project')) {

	$now				= strtotime('today');
	$date_format		= get_option('date_format'); 
	$term_id			= get_queried_object()->term_id; 
	$term_meta			= get_term_meta($term_id); 
	$end_date			= $term_meta['project_end_date'][0];
	$due_date			= strtotime($end_date);
	$new_date			= new DateTime($term_meta['project_end_date'][0]);
    $project_status     = $term_meta['project_status'][0];
	$text_end_date		= $new_date->format($date_format);
	$current_user_id	= get_current_user_id();
	$project_manager_id	= get_user_by('ID', $term_meta['project_manager'][0]);
	global $wp;

	if($end_date && $project_status != 'archived') { 
		if($now > $due_date && $project_status !='complete' && $current_user_id == $project_manager_id->ID) { ?>
			<form class="general-form extend-project-deadline" id="extend-project-deadline" method="post">

				<p><strong><?php _e('Extend deadline?', 'wproject'); ?></strong></p>

				<input type="date" name="project_end_date" id="project_end_date" min="<?php echo date("Y-m-d"); ?>" />

				<button disabled><i data-feather="arrow-right"></i></button>

				<input type="hidden" name="project_id" value="<?php echo $term_id; ?>" />
				<input type="hidden" name="project_url" value="<?php echo home_url( $wp->request); ?>" />

				<script>
					/* Submit form */
					$('.extend-project-deadline button').click(function() {
						$('#extend-project-deadline').submit();
					});
					$('#project_end_date').change(function() {
						var project_extend_value = $(this).val();

						if(project_extend_value) {
							$('.extend-project-deadline  button').removeAttr('disabled');
						}
					});
				</script>
			</form>
		<?php }
		}
	}
}
add_action('side_nav', 'extend_the_project', 1);


/* Leave Warning */
function leave_warning() {

	$wproject_settings 		= wProject();
    $enable_leave_warning 	=  $wproject_settings['enable_leave_warning'];
	if($enable_leave_warning == 'on') {
?>
	<script>
		document.querySelector('.submit button').addEventListener("click", function(){
			window.btn_clicked = true;
		});
		window.onbeforeunload = function(){
			if(!window.btn_clicked) {
				return '';
			}
		};
	</script>
<?php
	}
}

/* Timer UI in the task sidebar */
function timer_ui() { 

    $wproject_settings  = wProject();
    $enable_time        = $wproject_settings['enable_time'];
    $overtime           = $wproject_settings['overtime'];

    if($overtime) {
        $overtime = $overtime;
    } else {
        $overtime = '24';
    }

    $user_id 			= get_current_user_id();
    $task_wip		    = get_user_meta( $user_id, 'task_wip' , true );

    $task_id			= get_the_ID();
    $user_id 			= get_current_user_id();
    $task_owner_id		= get_post_field( 'post_author', $task_id );

    $task_start_time	= get_post_meta($task_id, 'task_start_time', TRUE);
    $task_timer	        = get_post_meta($task_id, 'task_timer', TRUE);

	if(is_singular('task') && $enable_time) {

		$categories 		= get_the_terms( $task_id, 'project' );
		foreach( $categories as $category ) { 
			$project_id = $category->term_id;
		}

		$wproject_settings = wProject();
        $enable_time = $wproject_settings['enable_time'];

		
		/* Prevents non-numeric value error */
		$task_total_time = null;
		
		$now = time();

		if($task_timer == 'on') {
			/* Timing has already started */
			@$resumed_time = $now - $task_start_time;
		}

		/* Only the task owner can work on the task */
		if($user_id == $task_owner_id) {
	?>
    <!--/ Start Timer UI /-->
	<div class="timer-ui">

		<div class="task-time <?php if(!$task_wip) { echo 'fade'; } ?>"><em>00:00:00</em></div>

		<form class="timer-start" id="timer-start" method="post" enctype="multipart/form-data">

			<?php if(!$task_wip) { ?>
			<p class="timer">
				<button><?php _e('Start Working', 'wproject'); ?></button>
			</p>
			<?php } ?>
            <p class="add-time">
				<span><i data-feather="plus"></i></span>
			</p>
			<input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
			<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
			<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
		</form>

		<form class="timer-stop" id="timer-stop" method="post" enctype="multipart/form-data">
			<p class="timer">
				<button><span></span><?php _e('Stop Working', 'wproject'); ?></button>
			</p>
			<input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
			<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
			<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
		</form>

        <form class="add-missed-time" id="add-missed-time" method="post" enctype="multipart/form-data">
            <input type="number" name="missed_hrs" min="0" max="<?php echo $overtime; ?>" placeholder="Hrs" />
            <input type="number" name="missed_mins" min="0" max="60" placeholder="Mins" />
            <input type="date" name="missed_date" max="<?php echo date('Y-m-d'); ?>" required />
            <input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
			<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
			<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
            <button><?php _e('Add time', 'wproject'); ?></button>
        </form>

        <?php
            $first_name			= get_the_author_meta( 'first_name', $user_id );
			$last_name			= get_the_author_meta( 'last_name', $user_id );
			$user_photo			= get_the_author_meta( 'user_photo', $user_id );

            if(preg_match("/[a-e]/i", $first_name[0])) {
                $colour = 'a-e';
            } else if(preg_match("/[f-j]/i", $first_name[0])) {
                $colour = 'f-j';
            } else if(preg_match("/[k-o]/i", $first_name[0])) {
                $colour = 'k-o';
            } else if(preg_match("/[p-t]/i", $first_name[0])) {
                $colour = 'p-t';
            } else if(preg_match("/[u-z]/i", $first_name[0])) {
                $colour = 'u-z';
            } else {
                $colour = '';
            }

            if($user_photo) {
                $avatar         = $user_photo;
                $avatar_id      = attachment_url_to_postid($avatar);
                $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                $avatar         = $small_avatar[0];
                $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
            } else {
                $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '</div>';
            }
        ?>
        <div class="current-user-data" data-name="<?php echo $first_name; ?> <?php echo $last_name; ?>" data-avatar="" data-date="<?php echo get_the_date(); ?>"></div>

	</div>
    <!--/ End Timer UI /-->
	
	<script src="<?php echo get_template_directory_uri();?>/js/easytimer/easytimer.min.js"></script>
	<script>
		var timer = new Timer();
		/* 
			Swap the forms depending on the timer status.
		*/
		<?php if($task_timer && $task_timer == 'on') { ?>
			$('.timer-start').hide();
			$('.timer-stop .timer span').addClass('running');

			/*
				If the page is loaded while the task is being timed, we need to
				resume the timer to make it look like it's actual progressed time.
				See the $resumed_time variable.
			*/
			$( document ).ready(function() {
				timer.start({precision: 'seconds', startValues: {seconds: <?php echo $resumed_time; ?>}});
				$('.task-time em').html(timer.getTimeValues().toString());

				timer.addEventListener('secondsUpdated', function (e) {
					$('.task-time em').html(timer.getTimeValues().toString());
				});
			});
			
		<?php } else { ?>
			$('.timer-stop').hide();
			$('.timer-stop .timer span').removeClass('running');
		<?php } ?>

		
		/* When Start Work is clicked, resume the timer from where it is */
		$('.timer-start .timer button').click(function() {
			$(this).css('pointer-events', 'none').css('opacity', '.5').css('cursor', 'wait');
			timer.start({precision: 'seconds', startValues: {seconds: 0}});
			$('.task-time em').html(timer.getTimeValues().toString());
            $('.tab-content-task-time .inserted .time').html(timer.getTimeValues().toString());
            $('.right .add-time').fadeOut(120);

			timer.addEventListener('secondsUpdated', function (e) {
				$('.task-time em').html(timer.getTimeValues().toString());
                $('.tab-content-task-time .inserted .time').html(timer.getTimeValues().toString());
			});
		});

		/* When timing is stopped on click */
		$('.timer-stop button').click(function() {
			$(this).css('pointer-events', 'none').css('opacity', '.5').css('cursor', 'wait');
		});

        /* Add Time UI Toggle */
        $('.right .add-time').click(function() {
            $(this).toggleClass('active');
            $('.right .add-missed-time').slideToggle(120);
            $('.timer-start button').toggleClass('disabled');
            //$('.task-time').toggleClass('fade');
        });
        
	</script>
<?php 	}
    }
}
add_action('before_side_nav', 'timer_ui', 30);


/* Recent Tasks */
function recent_tasks() { 

	$current_user_id        = get_current_user_id();
	$user                   = get_userdata($current_user_id);
    $show_latest_activity   = get_user_meta( $current_user_id, 'show_latest_activity' , true );
	$user_role			    = $user->roles[0];

    if($show_latest_activity == 'yes') {
    

        if($user_role == 'project_manager' || $user_role == 'administrator' || $user_role == 'team_member') {

            if(is_front_page()) {
                $author_id			= get_post_field ('post_author', get_the_id());
                $latest_activity	= get_the_author_meta( 'latest_activity', get_current_user_id() );

                if($latest_activity > 0) {
        ?>
        <h2 class="even-heading"><?php _e('Latest Activity', 'wproject'); ?></h2>
       
        <div class="latest-activity">
            <ul>
            <?php
                
                $args = array(
                    'post_type'         => 'task',
                    'orderby'           => 'date',
                    'order'             => 'desc',
                    'posts_per_page'    => $latest_activity,
                    'author__not_in'    => array(0)
                );

                $query = new WP_Query($args);
                $la = 1;
                while ($query->have_posts()) : $query->the_post();
                $author_id			= get_post_field ('post_author', get_the_id());
                $first_name			= get_the_author_meta( 'first_name', $author_id );
                $last_name			= get_the_author_meta( 'last_name', $author_id );
                $user_photo			= get_the_author_meta( 'user_photo', $author_id );
                $task_private		= get_post_meta(get_the_id(), 'task_private', TRUE);
                $task_status		= get_post_meta(get_the_id(), 'task_status', TRUE);
                $initials           = substr($first_name,0,1) . substr($last_name,0,1);

                if($task_status == 'complete') {
                    $the_task_status = __('Complete', 'wproject');
                } else if($task_status == 'incomplete') {
                    $the_task_status = __('Incomplete', 'wproject');
                } else if($task_status == 'on-hold') {
                    $the_task_status = __('On hold', 'wproject');
                } else if($task_status == 'in-progress') {
                    $the_task_status = __('In progress', 'wproject');
                } else if($task_status == 'not-started') {
                    $the_task_status = __('Not started', 'wproject');
                } else {
                    $the_task_status = __('Not started', 'wproject');
                }

                $author             = get_userdata($author_id);
                $author_role        = @$author->roles;

                if($author_id != '0') {
                    if(preg_match("/[a-e]/i", $first_name)) {
                        $colour = 'a-e';
                    } else if(preg_match("/[f-j]/i", $first_name)) {
                        $colour = 'f-j';
                    } else if(preg_match("/[k-o]/i", $first_name)) {
                        $colour = 'k-o';
                    } else if(preg_match("/[p-t]/i", $first_name)) {
                        $colour = 'p-t';
                    } else if(preg_match("/[u-z]/i", $first_name)) {
                        $colour = 'u-z';
                    } else {
                        $colour = '';
                    }
                }

                if($author_id != '0') {
                    if($user_photo) {
                        $avatar         = $user_photo;
                        $avatar_id      = attachment_url_to_postid($avatar);
                        $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                        $avatar         = $small_avatar[0];
                        $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
                    } else {
                        $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $initials . '</div>';
                    }
                } else {
                    $the_avatar = '<img src="' . get_template_directory_uri() . '/images/unknown-user.svg' . '" class="avatar" />';
                }
                
                $categories = get_the_terms( get_the_id(), 'project' );
                if($task_private != 'yes') { ?>
                    <li class="latest <?php echo $task_status; ?> latest-0<?php echo $la++; ?>" title="<?php echo $the_task_status; ?>">
                        <?php echo $the_avatar; ?>
                        <div class="activity-content">
                            <?php if($author_id) { ?>
                                <strong><?php echo $first_name; ?> <?php echo $last_name; ?></strong>
                            <?php } else { ?>
                                <strong><?php _e('Nobody', 'wproject'); ?></strong>
                            <?php } ?>

                            <a href="<?php echo get_the_permalink(); ?>" class="task-name">
                                <?php echo the_title(); ?>
                            </a>

                            <small class="project-name">
                                <?php if($categories !='') {
                                    foreach( $categories as $category ) { 
                                        echo '<a href="' . home_url() . '/project/' . $category->slug . '">' . $category->name . '</a>';
                                    }
                                    } else {
                                        _e('No project', 'wproject');
                                    }
                                ?>
                            </small>
                            
                            <span class="status <?php echo $task_status; ?>"><?php echo $the_task_status; ?></span>
                        </div>
                        <?php if($task_status == 'complete') { ?><i data-feather="check-circle-2"></i><?php } ?>
                        <?php if($task_status == 'incomplete') { ?><i data-feather="minus-circle"></i><?php } ?>
                        <?php if($task_status == 'in-progress') { ?><i data-feather="arrow-right-circle"></i><?php } ?>
                        <?php if($task_status == 'not-started') { ?><i data-feather="stop-circle"></i><?php } ?>
                        <?php if($task_status == 'on-hold') { ?><i data-feather="pause-circle"></i><?php } ?>
                    </li>
                <?php }
                endwhile;
                wp_reset_postdata();
            ?>
            </ul>
        </div>
<?php 		}
}
        }
    }
}
add_action('before_tips', 'recent_tasks');


/* Task in progress */
function task_in_progress() {
    $user_id 			= get_current_user_id();
    $task_wip		    = get_user_meta($user_id, 'task_wip' , TRUE );
    $task_description   = get_post_meta($task_wip, 'task_description' , TRUE );
    $task_end_date      = get_post_meta($task_wip, 'task_end_date' , TRUE );
	$task_stop_time		= get_post_meta($task_wip, 'task_stop_time', TRUE);

    $date_format    = get_option('date_format'); 
    $timestamp      = strtotime($task_end_date);
    $due_date	    = date($date_format, $timestamp);
?>

	<li class="task-in-progress <?php if($task_wip) { echo 'glow';  } ?>" data="<?php _e('You are recording time on this task.', 'wproject'); ?>" title="<?php if($task_wip) { _e('Recording time in progress', 'wproject'); } ?>">
		
        <span class="timer <?php if(!$task_wip) { echo 'fade'; } ?> <?php if($task_wip) { echo 'spin';  } ?>">
		    <i data-feather="clock-12"></i>
        </span>

		<?php if($task_wip) { ?>

			<div class="work-in-progress dropdown">
				<h3><?php _e('Recording time in progress', 'wproject'); ?><?php if(wp_is_mobile()) { echo '<i data-feather="x"></i>'; } ?></h3>
				<div>
                    <a href="<?php echo get_the_permalink($task_wip); ?>"><?php echo get_the_title($task_wip); ?></a>
                    <?php if($task_end_date) { ?>
                        (<?php _e('Due', 'wproject'); ?> <?php echo $due_date; ?>).
                    <?php } ?>
                    <?php if($task_description) { ?>
                        <small><?php echo mb_strimwidth($task_description, 0, 100, '...'); ?></small>
                    <?php } ?>
                    <a href="<?php echo get_the_permalink($task_wip); ?>" class="btn-light"><?php _e('Go to task', 'wproject'); ?></a>
				</div>
			</div>
		<?php } else { ?>
			<div class="work-in-progress dropdown">
				<h3><?php _e('Time', 'wproject'); ?><?php if(wp_is_mobile()) { echo '<i data-feather="x"></i>'; } ?></h3>
				<div>
					<?php _e('You are currently not recording time.', 'wproject'); ?>
				</div>
			</div>
		<?php } ?>

	</li>
	<script>				
		$('.task-in-progress').click(function() {
			//$('.dropdown').hide();
			$('.work-in-progress').toggle();
			$(this).toggleClass('active');
		});
	</script>

<?php }

function project_time() {
	global $wpdb;
	$tablename = $wpdb->prefix.'time';
	$query = "
		SELECT * 
		FROM $tablename
	";
	$result = $wpdb->get_results($query);
	$sum = 0;
	foreach ($result as $data) {
		if($data->task_id == get_the_ID() ) {
			$sum+= $data->time_log;
		}
	}
	$sum_hours = floor($sum / 3600);
	$sum_minutes = floor(($sum / 60) % 60);
	$sum_seconds = $sum % 60;
	return printf("%02d:%02d:%02d", $sum_hours, $sum_minutes, $sum_seconds);
}

/* Limited projects list (only projects the person has tasks in) */
function limited_projects_list() {

    $wproject_settings      = wProject();
	$completed_projects_nav = $wproject_settings['completed_projects_nav'];
	$project_list_style		= $wproject_settings['project_list_style'];
    $user_id                = get_current_user_id();
	
	if(is_tax()) {
		$project_id = get_queried_object()->term_id;
	} else {
		$project_id = '';
	}

	$style = '';
	$blank = '';
	if($project_list_style == 'dropdown') { 
		$style =  'dropdown';
		$blank = '<div class="dropdown-start">' . __( "Select a project", "wproject" ) . '<i data-feather="chevron-down"></i></div>';
	}

	echo $blank;
    global $wpdb;
    $categories = $wpdb->get_results("
		SELECT DISTINCT(terms.term_id) as ID, terms.name, terms.slug, tax.description
		FROM $wpdb->posts as posts
		LEFT JOIN $wpdb->term_relationships as relationships ON posts.ID = relationships.object_ID
		LEFT JOIN $wpdb->term_taxonomy as tax ON relationships.term_taxonomy_id = tax.term_taxonomy_id
		LEFT JOIN $wpdb->terms as terms ON tax.term_id = terms.term_id
		LEFT JOIN $wpdb->termmeta as termmeta ON terms.term_id = termmeta.term_id AND termmeta.meta_key = 'project_status'
		WHERE 
		posts.post_status = 'publish' AND 
		posts.post_type = 'task' AND 
		tax.taxonomy = 'project' AND 
		termmeta.meta_value != 'archived' AND 
		termmeta.meta_value != 'cancelled' AND 
		termmeta.meta_value != 'inactive' AND 
		posts.post_author = '$user_id'
		ORDER BY terms.name ASC
	");
    
    echo '<ul class="projects-list ' . $style . '">';
    foreach($categories as $category) : ?>
        <li id="project-<?php echo $category->ID; ?>" data="<?php echo $category->ID; ?>" <?php if($project_id == $category->ID) { ?>class="current"<?php } ?>>
            <a href="<?php echo get_category_link( $category->ID ); ?>" title="<?php echo $category->name ?>">
                <?php echo $category->name; ?>
            </a>
        </li>
        <?php endforeach;
    echo '</ul>'; ?>
    
    <script>
        <?php if($project_list_style == 'dropdown') { ?>
            $('.left .dropdown-start').click(function() { 
                $(this).toggleClass('spin');
                $('.left .projects-list.dropdown').slideToggle(120);
            }); 
        <?php } ?>

		// /* Dynamically get and calc values and copy them into element */
        // var limited_projects_list_count = $('.projects-list li').length;
        // $('.main-nav .project-count').text(limited_projects_list_count);
    </script>
<?php }

/* Exclude page IDs and include post types in search results */
if ( ! function_exists ( 'wproject_search_filter' ) ) {
    function wproject_search_filter( $query ) {

        $options                    = get_option( 'wproject_settings' );
        $contacts_search_visibility = isset($options['contacts_search_visibility']) ? $options['contacts_search_visibility'] : '';

        if($contacts_search_visibility) {
            $contacts_search_visibility = 'contacts_pro';
        } else {
            $contacts_search_visibility = '';
        }

        if ( ! $query->is_admin && $query->is_search ) {
            $query->set( 'post__not_in', array( 101,102,107,109 ) );            /* Exclude these page IDs */
            $query->set( 'post_type', array( 'task', 'page', 'attachment', $contacts_search_visibility) );   /* Include these post types */
            $query->set( 'post_status', array( 'publish', 'inherit' ) );
        }
    }
    add_action( 'pre_get_posts', 'wproject_search_filter' );
}


/* 
    Project selection for creating and editing a task.
    To avoid generating a separate query, we simply
    Grab the current projects list from the navigation
    and manipulate the dom to turn into a select element.
*/
function close_projects_selection() { ?>
    <select class="projects-selection" name="task_project" id="task_project" required>
    </select>
    <?php 
		$task_id 	= isset($_GET['task-id']) ? $_GET['task-id'] : '';
		$project_id	= isset($_GET['project-id']) ? $_GET['project-id'] : '';
		if(is_page(array(102))) {
			$categories = get_the_terms( $task_id, 'project' );
			foreach( $categories as $category ) { 
			}
			$project_id = $category->term_id; 
		} else if(is_page(array(105))) {
			if($project_id) {
				$project_id = $project_id; 
			} else {
				$project_id = ''; 
			}
		}
    ?>
    <script>
        $( document ).ready(function() {
            /* Add in an empty <option></option> at the start */
            $('.projects-selection').prepend('<option></option>');
            
            /* For each element in the main nav project list, do a bunch of things */
            $('.main-nav .projects-list li a').each(function() {
                var project_name    = $(this).attr('title');                /* Get the project title */
                var data_id         = $(this).parent().attr('data');          /* Get the project data ID */
                //var project_id      = data_id.replace('project-','');    /* Replace text to leave just the project ID */

                /* Add <option> items into the select with the project values and names */
                $('.projects-selection' ).append('<option value="'+data_id+'">' + project_name + '</option>');
            });

            <?php if(is_page(102)) { ?>
            /* Add 'selected' to project. */
            $(".projects-selection option[value='<?php echo $project_id; ?>']").attr('selected', 'selected');
            <?php } ?>

			<?php if(is_page(105)) { ?>
            /* Add 'selected' to project. */
            $(".projects-selection option[value='<?php echo $project_id; ?>']").attr('selected', 'selected');
            <?php } ?>
            
        });
    </script>
    
<?php }
add_action('projects_selection', 'close_projects_selection');

/* Task assignee for all users */
function task_assignee() { 

    $wproject_settings          = wProject();
    $users_can_assign_tasks     = $wproject_settings['users_can_assign_tasks'];

	if(function_exists('add_client_settings')) {
		$wproject_client_settings   = wProject_client();
		$client_can_assign_tasks    = $wproject_client_settings['client_can_assign_tasks'];
	} else {
		$client_can_assign_tasks    = '';
	}

    $user                       = wp_get_current_user();
    $user_role                  = $user->roles[0];

    $user_info                  = get_userdata(get_current_user_id());
    $default_task_ownership     = $user_info->default_task_ownership;

    if($user_role == 'project_manager' || $user_role == 'administrator' || $user_role == 'team_member' && $users_can_assign_tasks || $user_role == 'client' && $client_can_assign_tasks) { ?>
        <select name="task_owner" required>
        <option value="0"><?php _e('Nobody (unowned)', 'wproject'); ?></option>
        <?php
			$args = array(
				'role__in'	=> array('team_member', 'project_manager', 'client', 'administrator'),
				'orderby'	=> 'first_name',
				'order'   	=> 'ASC'
			);
            $users = get_users($args);
            foreach ( $users as $user ) { ?>
                <option value="<?php echo esc_html( $user->ID ); ?>" <?php if($default_task_ownership == 'yes' && $user->ID == get_current_user_id()) { echo 'selected'; } ?>><?php echo esc_html( $user->first_name ); ?> <?php echo esc_html( $user->last_name ); ?> - <?php echo esc_html( $user->title ); ?><?php if($user->roles[0] == 'client') { echo ' (' . esc_html( $user->roles[0] ) . ')'; } ?></option>
            <?php }
        ?>
    </select>
    <?php } else { ?>
        <input type="text" class="text" value="<?php echo esc_html( $user->first_name ); ?> <?php echo esc_html( $user->last_name ); ?>" disabled />
        <input type="hidden" name="task_owner" class="text" value="<?php echo esc_html( $user->ID ); ?>" required />
    <?php }
    }
add_action('task_assignment', 'task_assignee');


/* Due Date Sorting JS */
function due_date_sorting() { ?>

	<script>
		/* My tasks sorting */
		function my_forward_order() {
			var $wrapper = $('.sort-my-tasks');
			$wrapper.find('.priority').sort(function (a, b) {
				return +a.getAttribute('data-date') - +b.getAttribute('data-date');
			}).appendTo( $wrapper );
		}
		function my_reverse_order() {
			var $wrapper = $('.sort-my-tasks');
			$wrapper.find('.priority').sort(function (b, a) {
				return +a.getAttribute('data-date') - +b.getAttribute('data-date');
			}).appendTo( $wrapper );
		}
		$('.my-due-date-toggle').click(function() { 
			$(this).toggleClass('active');
			return (this.tog = !this.tog) ? my_reverse_order() : my_forward_order();
		});

		<?php if(is_tax()) { /* Only use this on a project page */ ?>
		/* Other tasks sorting */
		function other_forward_order() {
			var $wrapper = $('.sort-other-tasks');
			$wrapper.find('.priority').sort(function (a, b) {
				return +a.getAttribute('data-date') - +b.getAttribute('data-date');
			}).appendTo( $wrapper );
		}
		function other_reverse_order() {
			var $wrapper = $('.sort-other-tasks');
			$wrapper.find('.priority').sort(function (b, a) {
				return +a.getAttribute('data-date') - +b.getAttribute('data-date');
			}).appendTo( $wrapper );
		}
		$('.other-due-date-toggle').click(function() { 
			$(this).toggleClass('active');
			return (this.tog = !this.tog) ? other_reverse_order() : other_forward_order();
		});
		<?php } ?>
        /* End Toggle due date order */
	</script>

<?php }


/* Change sender name */
function wpb_sender_name( $original_email_from ) {
    $wproject_settings = wProject();
    if($wproject_settings['sender_name']) {
        $sender_name = $wproject_settings['sender_name'];
    } else {
        $sender_name = get_bloginfo( 'name' );
    }
    return $sender_name;
}
add_filter( 'wp_mail_from_name', 'wpb_sender_name' );

/* Change sender email address */
function wpb_sender_email( $original_email_address ) {
    $wproject_settings = wProject();
    if($wproject_settings['sender_email']) {
        $sender_email = $wproject_settings['sender_email'];
    } else {
        $sender_email = get_bloginfo( 'admin_email' );
    }
    return $sender_email;
}
add_filter( 'wp_mail_from', 'wpb_sender_email' );


/* Avatar */
function avatar() {

    $wproject_settings  = wProject(); 
    $avatar_style       = $wproject_settings['avatar_style'];

    if(is_tax()) {
        $term_id            = get_queried_object()->term_id; 
        $term_meta          = get_term_meta($term_id); 
        $pm_user            = get_user_by('ID', $term_meta['project_manager'][0]);
        $owner_name         = $pm_user->first_name . ' ' . $pm_user->last_name;
        $first_name         = $pm_user->first_name;
        $last_name          = $pm_user->last_name;
        $user_photo         = $pm_user->user_photo;
        $the_status         = $pm_user->the_status;
        $author_id          = $pm_user->user_id;
        $profile_link       = get_the_permalink(109) . '?id=' . $term_meta['project_manager'][0];
        $author             = get_userdata($author_id);
        $role               = @$author->roles[0];
    }
    if(is_singular('task')) {
        $task_id            = get_the_ID();
        $author_id          = get_post_field ('post_author', $task_id);
        $user_ID            = get_the_author_meta( 'ID', $author_id );
        $first_name         = get_the_author_meta( 'first_name', $author_id );
        $last_name          = get_the_author_meta( 'last_name', $author_id );
        $owner_name         = $first_name . ' ' . $last_name; 
        $user_photo         = get_the_author_meta( 'user_photo', $author_id );
        $the_status         = get_the_author_meta( 'the_status', $author_id );
        $profile_link       = get_the_permalink(109) . '?id=' . $user_ID;
        $author             = get_userdata($author_id);
        $role               = @$author->roles[0];
    }

    if($author_id != '0') {
        if(preg_match("/[a-e]/i", $first_name[0])) {
            $colour = 'a-e';
        } else if(preg_match("/[f-j]/i", $first_name[0])) {
            $colour = 'f-j';
        } else if(preg_match("/[k-o]/i", $first_name[0])) {
            $colour = 'k-o';
        } else if(preg_match("/[p-t]/i", $first_name[0])) {
            $colour = 'p-t';
        } else if(preg_match("/[u-z]/i", $first_name[0])) {
            $colour = 'u-z';
        } else {
            $colour = '';
        }
    }

    if($the_status == 'available') {
        $the_status = __('Available', 'wproject');
    } else if($the_status == 'away') {
        $the_status = __('Away', 'wproject');
    } else if($the_status == 'bored') {
        $the_status = __('Bored', 'wproject');
    } else if($the_status == 'busy') {
        $the_status = __('Busy', 'wproject');
    } else if($the_status == 'commuting') {
        $the_status = __('Commuting', 'wproject');
    } else if($the_status == 'do-not-disturb') {
        $the_status = __('Do not disturb', 'wproject');
    } else if($the_status == 'in-a-meeting') {
        $the_status = __('In a meeting', 'wproject');
    } else if($the_status == 'on-vacation') {
        $the_status = __('On vacation', 'wproject');
    } else if($the_status == 'out-to-lunch') {
        $the_status = __('Out to lunch', 'wproject');
    } else if($the_status == 'ready-to-assist') {
        $the_status = __('Ready to assist', 'wproject');
    } else if($the_status == 'working-remotely') {
        $the_status = __('Working remotely', 'wproject');
    } 

    if($author_id != '0') {
        if($user_photo) {
            $avatar         = $user_photo;
            $avatar_id      = attachment_url_to_postid($avatar);
            $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
            $avatar         = $small_avatar[0];
            $the_avatar     = '<img src="' . $small_avatar[0] . '" class="avatar" />';
        } else {
            $the_avatar     = '<div class="letter-avatar avatar ' . $colour . '">' . $first_name[0] . $last_name[0] . '</div>';
        }
    } else {
        $the_avatar = '<img src="' . get_template_directory_uri() . '/images/unknown-user.svg' . '" class="avatar" />';
    }
    ?>

    <div class="owner <?php if(is_tax()) { echo 'pm'; } ?>">
        <a href="<?php if($owner_name == '') { echo $profile_link; } else { echo '#'; } ?>">
            <?php echo $the_avatar; ?>
            <div class="owner-details">
                <strong>
                    <?php 
                        if($owner_name == '') {
                            echo $owner_name;
                        } else {
                         echo $owner_name;
                        }
                    ?>
                </strong>
                <span>
                    <?php if(is_tax()) {
                        _e('Project manager', 'wproject');
                    } else if(is_singular('task')) {
                        _e('Task owner', 'wproject');
                    }
                    ?>
                </span>
                <?php if($the_status) {
                    echo '<em>' . $the_status . '</em>';
                } ?>
            </div>
        </a>
    </div>

    <?php $task_status = get_post_meta( get_the_ID(), 'task_status', true );
    if($task_status == 'complete') {
        $task_status_name = '<i data-feather="check-circle-2"></i>' . __('Complete', 'wproject');
    } else if($task_status == 'incomplete') {
        $task_status_name = '<i data-feather="minus-circle"></i>' . __('Incomplete', 'wproject');
    } else if($task_status == 'on-hold') {
        $task_status_name = '<i data-feather="pause-circle"></i>' . __('On hold', 'wproject');
    } else if($task_status == 'in-progress') {
        $task_status_name = '<i data-feather="play-circle"></i>' . __('In progress', 'wproject');
    }  else if($task_status == 'not-started') {
        $task_status_name = '<i data-feather="stop-circle"></i>' . __('Not started', 'wproject');
    }

    if($task_status && is_singular('task') && $task_status == 'complete') {?>
        <div class="single-task-status <?php echo $task_status; ?>">
            <?php echo $task_status_name; ?>
        </div>
    <?php } ?> 

    <?php if(is_tax()) { ?>
    <script>
        /* 
            Clone the project manager details into the project team section.
            Make required adjustments to match team markup.
        */
        $( document ).ready(function() {
            $('.right .owner').clone().prependTo('.middle .project-team');
            $('.middle .project-team .owner').removeClass('owner');
            <?php if($user_photo) { ?>
                $('.middle .project-team div').replaceWith('<li class="pm">' + $('.middle .project-team div').html() +'</li>');
                $('.middle .project-team a div').replaceWith('<span class="pop">' + $('.middle .project-team a div').html() +'<small>(<?php _e('Project Manager', 'wproject'); ?>)</small></span>');
                $('.middle .project-team a .pop span, .middle .project-team a .pop svg, .middle .project-team a .pop em').remove();
                $('.middle .project-team a img').addClass('avatar  <?php echo $avatar_style; ?>');
            <?php } else { ?>
                $('.middle .project-team .pm').replaceWith('<li class="pm">' + $('.middle .project-team .pm').html() +'</li>');
                $('.middle .project-team .pm a').after('<span class="pop">' + $('.middle .project-team .owner-details strong').html() +'</span>');
                $('.middle .project-team .owner-details').remove();
            <?php } ?>
            

            /* Inject project team count */
            var project_team_count = $('.project-team li').length;
            $('.team-count.value').text( project_team_count );
        });
    </script>
    <?php } ?>

<?php }

/* Project PM */
if ( ! function_exists ( 'project_pm' ) ) {
    function project_pm() { 
        if(is_tax()) {
            avatar();
        }
    }
    add_action('before_side_nav', 'project_pm', 10);
}

/* Task Owner */
if ( ! function_exists ( 'task_owner' ) ) {
    function task_owner() { 
        if(is_singular('task')) {
            avatar();
        }
    }
    add_action('before_side_nav', 'task_owner', 10);
}


/* 
	Comment Reply Email Notification.
	https://gist.github.com/thesnippetdev/d0ad79959fabcabc72f9fb2c0270136c
*/
add_action('wp_insert_comment', 'wp_email_notify_comment_reply', 99, 2);
function wp_email_notify_comment_reply($comment_id, $comment_object) {

	$wproject_settings = wProject();

	$notify_when_comment_reply	= $wproject_settings['notify_when_comment_reply'];
	$sender_name 				= $wproject_settings['sender_name'];
	$sender_email				= $wproject_settings['sender_email'];

	/* If option is enabled */
	if($notify_when_comment_reply) {

		if($wproject_settings['sender_name']) {
			$sender_name = $sender_name;
		} else {
			$sender_name = get_bloginfo( 'name' );
		}

		if($wproject_settings['sender_email']) {
			$sender_email = $sender_email;
		} else {
			$sender_email = get_bloginfo( 'admin_email' );
		}

		if ( ( $comment_object->comment_approved == 1 ) && ($comment_object->comment_parent > 0 ) ) {

			$comment_parent = get_comment($comment_object->comment_parent);

			/* The person commenting */
			$commenter_user_id			= $comment_object->user_id;
			$commenter_first_name		= get_user_meta( $commenter_user_id, 'first_name', true );
			$commenter_last_name		= get_user_meta( $commenter_user_id, 'last_name', true );
            $comment_count              = get_comments_number();
            $comment_task_id            = $comment_parent->comment_post_ID;
            $task_owner_id              = get_post_field ('post_author', $comment_task_id);

            // $headers[] = 'From: you@wproject.com <hello@wproject.com>';
            // $headers[] = 'Content-Type: text/html; charset=UTF-8';
            // wp_mail( 'mike <none@wproject.com>', 'debugging', 'Task Owner id: ' . $task_owner_id . '<br />Current User ID: ' . get_current_user_id(), $headers );

            if(get_current_user_id() != $task_owner_id) {

                $user_photo = get_userdata($commenter_user_id)->user_photo;
                if($user_photo) {
                    $avatar         = $user_photo;
                    $avatar_id      = attachment_url_to_postid($avatar);
                    $small_avatar   = wp_get_attachment_image_src($avatar_id, 'thumbnail');
                    $avatar         = $small_avatar[0];
                    $the_avatar     = '<img src="' . $small_avatar[0] . '" style="width: 50px; height: 50px; border-radius: 12%; margin: 0 auto; display: block;" width="50" height="50" /><br />';
                }

                $comment_post_id			= $comment_parent->comment_post_ID;

                $comment_link               = get_comment_link($comment_object->comment_ID);
                $comment_content            = $comment_object->comment_content;

                $comment_author_email 		= $comment_parent->comment_author_email;
                $comment_author_name		= $comment_parent->comment_author;
                
                $original_comment_author	= $comment_parent->comment_author;

                $task_link					= get_the_permalink($comment_post_id);
                $task_title					= get_the_title($comment_post_id);

                $the_user					= get_user_by('email', $comment_author_email);
                $the_user_first_name		= $the_user->first_name;
                $the_user_id				= $the_user->ID;

                $button_label				= __('Go to Comment', 'wproject');

                $h1         = ' style="font-family: Arial, Helvetica, sans-serif; font-size: 22px; color: #5b606c;"';
                $a			= ' style="color:#00bcd4;text-decoration:underline;"';
                $p          = ' style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: #5b606c; line-height: 22px; margin-top: 0; margin-bottom: 0;"';
                $p_comment  = ' style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: #5b606c; line-height: 22px; display: block; padding: 15px; margin-top: 0; margin-bottom: 0; width: 100%; border: solid 1px #e3e3e3; background-color: #f9f9f9;background: #f9f9f9; box-sizing: border-box;"';
                $small      = ' style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #a6afc5; line-height: 12px;"';
                $button     = ' <table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td align="center"><table cellpadding="0" cellspacing="0" width="200" border="0" style="border-collapse: separate !important;"><tr><td style="background-color:#00bcd4;color:#FFFFFF;font-size:15px;padding:15px 15px;border-radius:2px;font-weight:bold; font-family:Arial, Helvetica, sans-serif;text-align:center;border:solid 1px #FFFFFF"><a href="' . $comment_link . '" style="color:#FFFFFF;text-decoration:none;display:block;border-radius:2px;">' . $button_label . '</a></td></tr></table></td></tr></table>';

                $subject = __('A reply to your comment', 'wproject') . ' ' .  '[' . $sender_name . '] ';

                $body = '<style>body{background:#f6f6f6;padding:50px;}table td { padding: 15px; margin: 0;}</style>';
                $body .= '<table style="text-align:center;background:#fff;padding:50px;margin:25px auto; box-shadow:10px 10px 50px rgba(0, 0, 0, 0.05);max-width:600px;" width="600" bgcolor="#ffffff; border-collapse: collapse !important;" border="0" cellpadding="0" cellspacing="0" align="center">';
                $body .= '<tr><td><h1' . $h1 . '>' . __('Comment Response', 'wproject') . '</h1></td></tr>';
                if($user_photo) {
                    $body .= '<tr><td>' . $the_avatar . '</td></tr>';
                }
                $body .= '<tr><td><p' . $p . '><strong>' . $commenter_first_name . ' ' . $commenter_last_name . ' </strong>' . __('has responded to your comment on this task:', 'wproject') . '</p></td></tr>';
                $body .= '<tr><td><p' . $p . '><a' . $a . ' href="' . $task_link . '">' . $task_title . '</a></p></td></tr>';
                $body .= '<tr><td style="margin-right:30px;margin-left:30px;"><p' . $p_comment . '><strong style="font-size: 20px">&ldquo;</strong>' . nl2br($comment_content) . '<strong style="font-size: 20px">&rdquo;</strong></p></td></tr>';
                $body .= '<tr><td>' . $button . '</td></tr>';
                $body .= '</table>';

                $headers[] = 'From: '. $sender_name .' <' . $sender_email . '>';
                $headers[] = 'Content-Type: text/html; charset=UTF-8';

                /* Create message / notification. */
                $message_title		= sprintf( __('Comment Response', 'wproject'));
                $message_body		= sprintf( __('%1$s has responded to your comment: <a href="%2$s">%3$s</a>.', 'wproject'),$commenter_first_name . ' ' . $commenter_last_name, $task_link, $task_title);
                
                wp_insert_post(array (
                    'post_type' 		=> 'message',
                    'post_author'		=> $the_user_id,
                    'post_title' 		=> $message_title,
                    'post_content' 		=> $message_body,
                    'post_status' 		=> 'publish',
                    'comment_status'	=> 'closed',
                    'ping_status' 		=> 'closed'
                ));

                wp_mail( $comment_author_name.' <'.$comment_author_email.'>', $subject, $body, $headers );

                /* DEBUG
                    echo $body;
                    echo '<br />';
                    echo 'comment_author_email: ' . $comment_author_email;
                    echo '<br />';
                    echo 'the user id: ' . $the_user_id;
                    exit;
                */
            }
		}
	}

}

/* Use CSS to align the currency symbols right (if enabled) on specific elements */
function currency_position() { 
    $wproject_settings          = wProject(); 
    $currency_symbol_position   = $wproject_settings['currency_symbol_position'];
    if($currency_symbol_position == 'r') { ?>
    <style>
        .project-material-cost::placeholder {
            text-align: right;
        }
    </style>
    <?php }
?>
<?php }
add_action('after_wp_footer', 'currency_position');

/* Extend login session */
add_filter( 'auth_cookie_expiration', 'extend_login_session' );
function extend_login_session( $expire ) {
    $options        = get_option( 'wproject_settings' );
	$session_time   = isset($options['session_time']) ? $options['session_time'] : '';

    if($session_time) {
        $session_time = $session_time * 86400; /* 86400 seconds = 1 day */
    } else {
        $session_time = 86400;
    }

    return $session_time;
}


/* Assign projects to web administrator when user is deleted */
function reassign_projects_to_admin($user_id) {
    global $wpdb;

    $wproject_settings  = wProject();
    $default_pm_id      = $wproject_settings['default_pm'];

    if($default_pm_id) {
        $default_pm_id = $default_pm_id;
    } else {
        $default_pm_id = 1;
    }

    /* Get the ID of the user that was deleted */
    $deleted_user_id = $user_id;

    /* Search the termmeta table for all meta_key that have a meta_value of the user ID that as deleted. */
    $meta_key_results = $wpdb->get_results(
        $wpdb->prepare("
            SELECT meta_key
            FROM $wpdb->termmeta
            WHERE meta_value = %d
        ", $deleted_user_id)
    );

    /* Update that meta_value with the number 1 */
    foreach ($meta_key_results as $result) {
        $meta_key = $result->meta_key;
        $wpdb->update(
            $wpdb->termmeta,
            array('meta_value' => $default_pm_id),
            array('meta_key' => $meta_key, 'meta_value' => $deleted_user_id)
        );
    }
}
add_action('delete_user', 'reassign_projects_to_admin');


/* Prevent possible xss injection. */
function wproject_search_control() {

    function sanitize_search_query_before_parse_request() {
        if (isset($_GET['s'])) {
        
            $search_query = urldecode($_GET['s']);
            $sanitized_query = strip_tags($search_query);
            $sanitized_query = urlencode($sanitized_query);
            $_GET['s'] = $sanitized_query;
        }
    }
    add_action('parse_request', 'sanitize_search_query_before_parse_request', 1);
}
add_action('init', 'wproject_search_control');