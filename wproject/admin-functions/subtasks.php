<?php
$wproject_settings              = wProject();
$enable_subtask_descriptions    = $wproject_settings['enable_subtask_descriptions'];
?>
<div class="section sub-tasks">
    <strong><?php _e( "Subtasks", "wproject" ); ?></strong>
    <p class="add-item button"><?php _e( "Add subtask", "wproject" ); ?></p>
    <ul class="subtask-items materials">            
    </ul>

    <?php if(isset($_GET['post'])) {
        echo '<ul class="subtask-items-existing">';
        $all_rows = get_post_meta( (int)$_GET['post'], 'subtask_list', true);
        if($all_rows) {
            if( count($all_rows ) > 0  ) {
                sort($all_rows); /* Sort alphabetically */
                foreach( $all_rows as $s_row ) {
                ?>
                    <li class="item <?php if($s_row['subtask_status'] == '1') { echo 'done'; } ?>">
                        <span>
                            <input type="text" name="subtask_name[]" placeholder="<?php _e('Subtask', 'wproject'); ?>" data-lpignore="true" value="<?php echo $s_row['subtask_name'] ?>" required />
                            <?php if($enable_subtask_descriptions) { ?>
<textarea name="subtask_description[]" placeholder="<?php _e('Additional information', 'wproject'); ?>">
<?php echo $s_row['subtask_description'];?></textarea>
                            <?php } ?>
                            <input type="hidden" name="subtask_status[]" value="<?php echo $s_row['subtask_status'] ?>" />
                        </span>
                        <span class="remove"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff9800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span>
                    </li>
                <?php 
                }
            }
        }
        echo '</ul>';
    }
    ?>

    <script>
        /* Subtask handling */
        var trash_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff9800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

        jQuery(window).load(function() {
            jQuery(function() {
                var subtaskItems = jQuery('.subtask-items');
                var i = jQuery('.subtask-items li').size() + 1;
                
                jQuery('.add-item').click(function() {
                    jQuery('<li class="item"><span><input type="text" name="subtask_name[]" data-lpignore="true" placeholder="<?php _e('Subtask', 'wproject'); ?>" required /><?php if($enable_subtask_descriptions) { ?><textarea name="subtask_description[]" placeholder="<?php _e('Additional information', 'wproject'); ?>"></textarea><?php } ?><input type="hidden" name="subtask_status[]" value="0" /></span><span class="remove" title="<?php _e('Remove (double click)', 'wproject'); ?>">'+trash_icon+'</span></li>').prependTo(subtaskItems);
                    i++;
                });
                
                jQuery('.subtask-items, .subtask-items-existing').on('dblclick', '.remove', function() {
                    jQuery('.subtask-items, .subtask-items-existing').find(this).parent().remove();
                    return false;
                });
            });
        });
    </script>

</div>