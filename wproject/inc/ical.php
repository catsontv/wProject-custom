<?php if(isset($_GET["title"]) && isset($_GET["datestart"]) && isset($_GET["datestart"]) && isset($_GET["description"]) && isset($_GET["uniqid"]) && isset($_GET["uri"])) {
$title          = $_GET["title"] . "\r\n";
$file_name      = $_GET["filename"] . ".ics\r\n";
$date_start     = $_GET["datestart"] . "\r\n";
$date_end       = $_GET["dateend"] . "\r\n";
$description    = $_GET["description"] . "\r\n";
$uniqid         = $_GET["uniqid"] . "\r\n";
$uri            = $_GET["uri"] . "\r\n";
} else {
    exit;
}

header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $file_name);

function escapeString($string) {
  return preg_replace('/([\,;])/','\\\$1', $string);
}
?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTEND:<?php echo $date_end; ?>
UID:<?php echo $uniqid; ?>
DTSTAMP:<?php echo time(); ?>
LOCATION:<?php echo escapeString($uri); ?>
DESCRIPTION:<?php echo escapeString($description); ?>
URL:<?php echo escapeString($uri); ?>
SUMMARY:<?php echo escapeString($title); ?>
DTSTART:<?php echo $date_start; ?>
END:VEVENT
END:VCALENDAR