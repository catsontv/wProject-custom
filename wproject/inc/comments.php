<?php if (!defined('ABSPATH')) exit;
if(!is_page(107)) { /* If not the Reports page */ ?>

<?php if ( have_comments() ) : 

    $wproject_settings  = wProject();
    $comment_order      = $wproject_settings['comment_order'];
    $show_comment_dates = $wproject_settings['show_comment_dates'];

    if($comment_order == 'oldest') {
        $comment_order = 0;
        $sort_icon = '<i data-feather="sort-desc"></i>';
    } else if($comment_order == 'latest') {
        $comment_order = 1;
        $sort_icon = '<i data-feather="sort-asc"></i>';
    } else {
        $comment_order = 1;
        $sort_icon = '<i data-feather="sort-desc"></i>';
    }
?>

<div class="navigation">
    <div class="alignleft"><?php previous_comments_link() ?></div>
    <div class="alignright"><?php next_comments_link() ?></div>
</div>

<div class="all-comments" id="comments" data-comment-count="<?php echo get_comments_number($post->ID); ?>">


    <div class="conversation-list">
        <span class="all selected"><?php _e( 'All', 'wproject' ); ?></span>
        <span class="participants"></span>
    </div>
    
    <em class="reverse-comments"><?php echo $sort_icon; ?></em>

    <form class="comment-threads" id="comment-threads-form" method="post" enctype="multipart/form-data">
        <ol id="commentlist">
        <?php wp_list_comments('type=comment&callback=wproject_comments&reverse_top_level='. $comment_order);?>
        </ol>
        <input type="hidden" name="comment_id" id="comment_id" value="" />
    </form>

    <script>

        $( document ).ready(function() {
            $('.tab-comments span').text(<?php echo get_comments_number($post->ID); ?>);
            $('.tab-comments span').attr('data-comment-count', <?php echo get_comments_number($post->ID); ?>);
        });

        <?php if($show_comment_dates) { ?>
            $( document ).ready(function() {
                $('.the-comment').parent().find('.comment-date').show();
            });
        <?php } else { ?>
            $('.the-comment').click(function() {
                $(this).parent().find('.comment-date').fadeToggle(120);
            });
        <?php } ?>

        $('.comment-actions .comment-delete').click(function() {

            if (confirm('<?php _e('Really delete this comment?', 'wproject'); ?>')) {
                
                var comment_id = $(this).attr('data-id');
                var comment_count = parseInt($('.tab-comments span').attr('data-comment-count'));
                var new_comment_count = comment_count - 1;

                $('#comment_id').val(comment_id);

                setTimeout(function() { 
                    $('.tab-comments span').text(new_comment_count);
                    $('.tab-comments span').attr('data-comment-count', new_comment_count);
                    $('#comment-threads-form').submit();
                }, 100);

            } else {
                $('#comment_id').val('');
            }

        });
        $('code').wrap('<pre></pre>');
    </script>
</div>

<!-- <div class="navigation">
    <div class="alignleft"><?php previous_comments_link() ?></div>
    <div class="alignright"><?php next_comments_link() ?></div>
</div> -->

<?php else : // this is displayed if there are no comments so far ?>

<?php if ( comments_open() ) : ?>
    <!-- If comments are open, but there are no comments. -->

 <?php else : // comments are closed ?>
    <!-- If comments are closed. -->
    <p class="nocomments"><?php _e( "Comments are closed", "wproject" ); ?></p>

<?php endif; ?>
<?php endif; ?>

<?php 
$user           = wp_get_current_user();
$user_role      = $user->roles[0];

if($user_role != 'observer') {
if ( comments_open() ) { 

    $author_id      = get_current_user_id();
    $author_email   = get_the_author_meta( 'email', $author_id );
    $first_name     = get_the_author_meta( 'first_name', $author_id );
    $last_name      = get_the_author_meta( 'last_name', $author_id );
    $author_name    = $last_name . ' ' . $last_name;
?>

    <div id="respond">
        <div id="cancel-comment-reply">
            <small><?php cancel_comment_reply_link() ?></small>
        </div>

        <form action="<?php echo site_url(); ?>/wp-comments-post.php" method="post" id="commentform">   

            <input type="hidden" name="author" value="<?php echo esc_attr($author_name); ?>" />
            <input type="hidden" name="email"  value="<?php echo esc_attr($author_email); ?>" />

            <ul class="comment-tags">
                <li class="a"><i data-feather="link"></i></li>
                <li class="strong"><i data-feather="bold"></i></li>
                <li class="i"><i data-feather="italic"></i></li>
                <li class="s"><i data-feather="strike"></i></li>
                <li class="ul"><i data-feather="list"></i></li>
                <li class="ol"><i data-feather="list-ordered"></i></li>
                <li class="pre"><i data-feather="code"></i></li>
                <li class="blockquote"><i data-feather="quote"></i></li>
            </ul>
            <textarea name="comment" id="comment-box" cols="58" rows="10" class="mention"></textarea>
            <script>
                $(document).ready(function() {
                    $('.comment-tags li').click(function() {
                        var tagName = $(this).attr('class'); // Get the class name of the clicked list item
                        var commentBox = $('#comment-box');
                        var selectedText = commentBox.val().substring(commentBox[0].selectionStart, commentBox[0].selectionEnd);

                        // Wrap the selected text with the corresponding HTML tag
                        var wrappedText;
                        if (tagName === 'ul' || tagName === 'ol') {
                        wrappedText = '<' + tagName + '>\n<li>' + selectedText + '</li>\n</' + tagName + '>';
                        } else if (tagName === 'a') {
                        wrappedText = '<' + tagName + ' href="">' + selectedText + '</' + tagName + '>';
                        } else {
                        wrappedText = '<' + tagName + '>' + selectedText + '</' + tagName + '>';
                        }

                        // Replace the selected text with the wrapped text in the comment-box
                        var existingText = commentBox.val();
                        var newText = existingText.substring(0, commentBox[0].selectionStart) + wrappedText + existingText.substring(commentBox[0].selectionEnd);
                        commentBox.val(newText);
                    });
                });
            </script>
            
            <div class="comment-box-footer">
                
                <?php if(!wp_is_mobile()) { ?>
                    <span class="comment-help">
                        <i data-feather="info"></i>

                        <span class="comment-tips">
                            <?php _e( 'Allowed Tags', 'wproject' ); ?><br /><span>a</span><span>strong</span><span>i</span><span>s</span><span>ul</span><span>ol</span><span>li</span><span>code</span><span>blockquote</span>
                        </span>
                    </span>
                <?php } ?>

                <input name="submit" type="submit" id="submit" tabindex="5" value="<?php esc_attr_e('Post Reply', 'wproject'); ?>" />
            </div>
        
            
            <?php comment_id_fields(); ?>
            <?php do_action( 'comment_form', $post->ID ); ?>

            <script>
                /* Insert reply name into comment field */
                $( document ).ready(function() {
                    $('.comment-reply').click(function() {
                        var reply_name = $(this).closest('.comment').attr('data-reply-name');
                        var comment_content = $('#comment-box').val();
                        $('#comment-box').val(reply_name + ' ' + ' ' + comment_content);
                    });

                    /* Create an empty array to store unique names */
                    var uniqueNames = [];
                    
                    /* Loop through each list item and extract the data-reply-name attribute */
                    $('#commentlist li').each(function() {
                        var name = $(this).data('reply-name');

                        // Check if the 'reply-name' attribute exists
                        if (name !== undefined) {
                            var at_name = name; // You can remove this line if it's not needed

                            name = name.replace('@', '');

                            /* If the name is not already in the array, add it */
                            if ($.inArray(name, uniqueNames) === -1) {
                                uniqueNames.push(name);
                            }
                        }
                    });
                    
                    /* Loop through the unique names and append them to the conversation-list element */
                    $.each(uniqueNames, function(index, value) {
                        $('<span>', { text: value, 'data-filter': '@'+value }).appendTo('.conversation-list .participants');
                    });

                    /* Show / hide comments for clicked comment author */
                    $('.conversation-list span[data-filter]').on('click', function() {
                        var name = $(this).data('filter');
                        $('#commentlist li').fadeOut();
                        $('#commentlist li[data-reply-name="' + name + '"]').fadeIn();
                        $('.conversation-list span[data-filter], .all').removeClass('selected');
                        $(this).addClass('selected');
                    });

                    $('.conversation-list .all').click(function() {
                        $('.conversation-list span[data-filter]').removeClass('selected');
                        $(this).addClass('selected');
                        $('#commentlist li').fadeIn();
                    });

                });
            </script>

        </form>
    </div>
<?php }
    }
}
