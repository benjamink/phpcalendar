<html>
<head>
	<title>Calendar</title>
	<style type="text/css">
		a { text-decoration: none; }
		table { font-family: verdana, sans-serif; border: 1px solid #ccc; border-collapse: collapse; font-size: 9px; color: #666; width: 600px; }
		th { border: 1px solid #ccc; font-size: 9px; color: #666; }
		tr { border: 1px solid #ccc; font-size: 9px; color: #666; }
		td { border: 1px solid #ccc; font-size: 9px; color: #666; height: 65px; vertical-align: top; width: 14.28%; }
		.spacerdays { background-color: #ccc; }
		.dayHeading { background-color: #00f; color: #fff; }
		.headerTitle { font-size: 14px; color: #99f; background-color: #fff; }
		.headerString { font-size: 11px; color: #fff; background-color: #333; }
		.dayContent { color: #00f; font-size: 9px; background-color: #ddd; margin: 2px; }
		.currentDay { background-color: #fdd; }
		.weekendDay { background-color: #eee; }
	</style>
</head>
<body>
<?php
require_once('calendar.class.inc.php');

/**
 * Ideally this information would come from a DB, but the $content array below is laid
 * out in a fashion that should be very similar to what a query result would be like.
 */

$content = array(
	array(
		'id' => '1',
		'date' => '2006-02-11',
		'text' => 'This is a nice item on Jan 11, 2006',
		'desc' => 'This is a really long description that gets used to describe the item we\'re showing here blah blah blah blah',
		'cat' => 'Announcement',
		'catcolor' => '#ccf'
	),
	array(
		'id' => '2',
		'date' => '2006-05-15',
		'text' => 'Hurray for birds on Jan 22, 2006!',
		'desc' => 'This is a really long description that gets used to describe the item we\'re showing here blah blah blah blah'
	),
	array(
		'id' => '3',
		'date' => '2006-02-21',
		'text' => 'Another entry to fill up space.',
		'desc' => 'This is a really long description that gets used to describe the item we\'re showing here blah blah blah blah',
		'cat' => 'Event',
		'catcolor' => '#fcc'
	)
);

/**
 * Set year and/or month to display calendar for.  If either are set from the URL
 * use the custom date, otherwise default to the current year and/or month.
 */
$year = (isset($_GET['y']) ? $_GET['y'] : null);
$month = (isset($_GET['m']) ? $_GET['m'] : null);

/**
 * Instantiate the PHP Calendar Class
 */
$cal = new Calendar($year,$month);

/**
 * Set the Calendar title bar contents
 */
$cal->setTitle('This is my Calendar!');

/**
 * Loop through the records contained in the DB results or array contents.  Events
 * can also be added manually by running this method for each event to be added.
 */
foreach($content as $k => $v) {
	$cal->setEvents($v['id'],$v['date'],$v['text'],$v['desc'],$v['cat'],$v['catcolor'],false,'destination.php');
}

/**
 * Toggle showing the weekday numbers on
 */
$cal->setShowWeekDayNum(true);

/**
 * Toggle adding an "Add Event" link to each day
 */
$cal->setAddEvt('addevent.php');

/**
 * Output the HTML to draw the calendar.
 */
$cal->drawHTML();

/**
 * Output the calendar as an array
 */
//echo "<hr />\n<pre>\n";
//print_r($cal->drawArray());
//echo "</pre>\n";
?>

<!-- The following block simply displays the source code for review purposes -->
<hr />
<h1>Source Code</h1>
<?php highlight_file(__FILE__); ?>
<!-- END show source code block -->

</body>
</html>