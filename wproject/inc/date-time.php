<?php $current_date        = date_i18n(get_option('date_format')); ?>

<div id="date-time">
    <span class="the-date">
        <i data-feather="calendar"></i>
        <em><?php echo $current_date; ?></em>
    </span>
    <span class="the-time">
        <i data-feather="clock"></i>
        <em><?php echo $current_date; ?></em>
    </span>
</div>

<script>
    $( document ).ready(function() {
        function updateCurrentTime() {
            var currentTime = moment().format('h:mm:ss');
            $('.the-time em').text(currentTime);
        }
        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);
    });
</script>