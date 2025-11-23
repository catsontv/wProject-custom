<?php 
    /*
        Required Variables:

        $avatar             Image of the person sending the email.
        $subject            The subject of the email.
        $message_body       The email body message.
        $link               The button URL or link.
        $sender_name        The name of the person sending the email.
        $sender             The email address of the person sending the email.
        $recipient          The email of the address to send the email to.

    */
    $options        = get_option( 'wproject_settings' );
    $sender_name    = isset($options['sender_name']) ? $options['sender_name'] : '';

    if($sender_name) {
        $sender_name = $sender_name;
    } else {
        $sender_name = 'wProject';
    }

    $h1     = 'style="font-family: Arial, Helvetica, sans-serif; font-size: 22px; color: #5b606c;"';
    $p      = 'style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; color: #5b606c; line-height: 22px;"';
    $small  = 'style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #a6afc5; line-height: 12px;"';

    $button = '<div><!--[if mso]> <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="' . $link . '" style="height:50px;v-text-anchor:middle;width:200px;" arcsize="200%" stroke="f" fillcolor="#00bcd4"> <w:anchorlock/> <center> <![endif]--> <a href="' . $link . '" style="background-color:#00bcd4;border-radius:100px;color:#ffffff !important;display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">' . $button_label . '</a> <!--[if mso]> </center> </v:roundrect> <![endif]--></div>';

    if($project_name && $project_url) {
        $the_project = '<p ' . $p . '><strong>' . __('Project', 'wproject') . ': </strong><a href="' . $project_url . '" style="color:#00bcd4">' . $project_name . '</a>.</p>';
    } else {
        $the_project = '';
    }
    
    ob_start();

    $body[] = 
    '<style>body{background:#ffffff;padding:50px;}a{color:#00bcd4!important;}</style>' . 
    '<div style="text-align:center;padding:50px;margin:25px auto; box-shadow:10px 10px 50px rgba(0, 0, 0, 0.05);max-width:600px;">' . 
    '<img src="' . $avatar . '" style="width: 50px; height: 50px; display: block; border-radius: 7px; margin: 0 auto;" />' . 
    '<h1 ' . $h1 . '>' . $subject . '</h1>' . 
    $the_project . 
    '<p ' . $p . '>' . $message_body . '</p>' . 
    $button . 
    '<p ' . $small . '>' . __('Message sent from ' . $sender_name . '.', 'wproject') . 
    '</div>';

    $the_subject    = $subject .  ' (' . __('wProject', 'wproject') . ')';
    $email_body     = join("\r\n",$body);

    $headers[]	    = "Content-type:text/html;charset=UTF-8";
    $headers[]	    = 'From:' . $sender;
    $headers[]	    = 'Reply-To: ' . $sender;
    $headers[]	    = "MIME-Version: 1.0";
    wp_mail($recipient, str_replace("&#8217;", "'", $the_subject), $email_body, $headers);
    ob_end_clean();