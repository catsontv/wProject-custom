<?php if ( ! defined( 'ABSPATH' ) ) { exit; } 

    $wproject_settings          = wProject();
    $project_access             = $wproject_settings['project_access'];

    $user 					    = wp_get_current_user();
    $user_role                  = !empty($user->roles) ? $user->roles[0] : '';

    $search_query               = isset($_GET['s']) ? $_GET['s'] : '';

    if($user_role == 'administrator' || $user_role == 'project_manager' || $user_role == 'team_member' || $user_role == 'observer') {

?>
<form role="search" method="get" action="<?php echo home_url();?>/" class="search">

    <?php if(!wp_is_mobile()) { ?>
        <span class="toggle-sidebars" title="<?php _e('Toggle sidebars', 'wproject'); ?>"><i data-feather="menu"></i></span>
    <?php } ?>

    <i data-feather="search"></i>
    <input type="search" name="s" onkeyup="contextFilter(this)" autocomplete="off" placeholder="<?php _e('Search', 'wproject'); ?>" <?php if(!empty($search_query)) { echo 'value="' . $search_query . '"'; } ?> required />

    <span class="clear-search">
        <i data-feather="x"></i>
    </span>

</form>
<script>
    $('.search input').focus(function() {
        $('.search').addClass('rotate');
    });
    $('.search input').blur(function() {
        $('.search').removeClass('rotate');
    });

    <?php if(empty($search_query)) { ?>
    $('.clear-search').hide();
    <?php } ?>
    
    $('.clear-search').click(function() {
        $('input[type="search"]').val('');
        $('.body-rows li, .contacts-body-rows li').show();
        $(this).fadeOut();
    });
    
    $('input[type="search"]').keyup(function () {
		if($(this).val() == '') {
            $('.clear-search').fadeOut();
        } else {
            $('.clear-search').fadeIn();
        }
	});
</script>
<?php } ?>