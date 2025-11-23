<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!--/ Start Team /-->
<ul class="team-grid">
<?php 
    $users = get_users( array( ) );
    foreach ( $users as $user ) { 
        $user_photo = $user->user_photo;

        $user           = get_userdata($user->ID);
        $user_role      = $user->roles[0];
        $first_name     = $user->first_name;
        $last_name      = $user->last_name;

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

        $title      = $user->title;
        $the_status = $user->the_status;
        $title_lc   = strtolower($title);
        $title_slug = str_replace(' ', '-', $title_lc);

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
        } else if($the_status == 'need-something-to-do') {
            $the_status = __('Need something to do', 'wproject');
        } else if($the_status == 'on-vacation') {
            $the_status = __('On vacation', 'wproject');
        } else if($the_status == 'out-to-lunch') {
            $the_status = __('Out to lunch', 'wproject');
        } else if($the_status == 'ready-to-assist') {
            $the_status = __('Ready to assist', 'wproject');
        } else if($the_status == 'working-remotely') {
            $the_status = __('Working remotely', 'wproject');
        } 

        if($user_role != 'operator') {
        ?>
        <li class="<?php echo esc_html( $user_role ); ?>">
            <div>

                <div class="avatar-box">
                    <?php echo $the_avatar; ?>
                    <?php if($the_status) { ?>
                    <span class="status-text"><?php echo esc_html( $the_status ); ?></span>
                    <?php } ?>
                </div>

                <strong><?php echo esc_html( $user->first_name ); ?> <?php echo esc_html( $user->last_name ); ?></strong>
                
                <span class="title"><?php echo esc_html( $user->title ); ?></span>

                <span><i data-feather="mail"></i><a href="mailto:<?php echo esc_html( $user->user_email ); ?>"><?php echo esc_html( $user->user_email ); ?></a></span>
                
                <?php if($user->last_login) { ?>
                    <span>
                        <i data-feather="log-in"></i>
                        <?php echo human_time_diff( $user->last_login, time() ) ; ?> <?php /* translators: Example: The user logged in 10 days ago */ _e('ago', 'wproject'); ?>
                    </span>
                <?php }
                ?>

                <?php if($user_role == 'client') { echo '<em class="client-icon"></em>'; } ?>

            </div>
            
            <a href="<?php echo get_the_permalink(109);?>?id=<?php echo esc_html( $user->ID ); ?>" class="btn-light"><?php _e('View profile', 'wproject'); ?></a>
        </li>
    <?php }
    }
?>
</ul>

<!--/ Start Team Widget /-->
<?php if ( is_active_sidebar( 'wproject-team-widget' ) ) { 
    dynamic_sidebar( 'wproject-team-widget' );
} ?>
<!--/ End Team Widget /-->

<?php /* Help topics */
function team_help() {
    
    $user 					    = wp_get_current_user();
    $user_role 				    = $user->roles[0];

    ?>
    <h4><?php _e('All team members', 'wproject'); ?></h4>
    <?php if($user_role == 'client') { ?>
        <p><?php _e('All members of the organisation who are involved in projects are displayed here.', 'wproject'); ?></p>
    <?php } else { ?>
        <p><?php printf( __('A list of every user involved in any project is displayed here. If you have the <a href="%1$s" target="_blank" rel="noopener">Clients Pro plugin</a>, clients will also be displayed.', 'wproject'),'https://rocketapps.com.au/product/clients-pro/');?></p>
    <?php } ?>
<?php }
add_action('help_start', 'team_help');

/* Side nav items */
function team_role_nav() { ?>
    <li class="team-filter team-filter-all selected"><a><i data-feather="users"></i><?php _e('All users', 'wproject'); ?></a></li>
    <li class="team-filter" data="administrator"><a><i data-feather="user"></i><?php _e('Administrators', 'wproject'); ?></a></li>
    <?php if(function_exists('add_clients_pro_settings_page')) { ?>
        <li class="team-filter" data="client"><a><i data-feather="user"></i><?php _e('Clients', 'wproject'); ?></a></li>
    <?php } ?>
    <li class="team-filter" data="observer"><a><i data-feather="user"></i><?php _e('Observers', 'wproject'); ?></a></li>
    <li class="team-filter" data="project_manager"><a><i data-feather="user"></i><?php _e('Project managers', 'wproject'); ?></a></li>
    <li class="team-filter" data="team_member"><a><i data-feather="user"></i><?php _e('Team members', 'wproject'); ?></a></li>
<?php }
add_action('side_nav', 'team_role_nav'); ?>
<!--/ End Team /-->