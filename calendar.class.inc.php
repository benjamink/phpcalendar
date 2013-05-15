<?php
/**
 * Calendar class
 *
 * @author Benjamin Krein <superbenk@superk.org>
 * @version 0.01a - 02-23-2006
 */
class Calendar
{
	private $curDay;
	private $curDayS;
	private $curDayName;
	private $curYear;
	private $curMonth;
	private $curMonthName;
	private $daysInMonth;
	private $firstDayOfTheMonth;
	private $daysArray = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	private $eventItems = array();
	private $dayHeadings;
	private $dayOfMonth = '1';
	private $weekDayNum;
	private $calWeekDays;
	private $outArray = array();
	private $addWeekDay = false;
	private $addEvtDest;
	private $addEvtImg;
	
	public $headerTitle = '';
	public $headerStr = '';
	
	/**
	 * Constructor method to initialize the class
	 * 
	 * If specific year or month is given, set defaults accordingly.  Otherwise
	 * set defaults to the current date.
	 * 
	 * @param int $y Custom year
	 * @param int $m Custom month
	 */
	public function __construct($y=null,$m=null)
	{
		if(is_null($y)) {
			$this->curYear = date('Y');
		} else {
			if(preg_match("/^[0-9]{4}$/", $y)) {
				$this->curYear = $y;
			} else {
				$this->curYear = date('Y');
			}
		}
		
		if(is_null($m)) {
			$this->curMonth = date('m');
			$this->curMonthName = date('F');
			$this->daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->curMonth, $this->curYear);
			$this->firstDayOfTheMonth = date('w', mktime(0,0,0, $this->curMonth, 1, $this->curYear));
		} else {
			if(preg_match("/[0-9][0-9]?/", $m)) {
				$m = ($m > 12 ? date('m') : ($m < 1 ? date('m') : $m));
				$m = (strlen($m) < 2 ? '0'.$m : ((strlen($m) > 2) ? date('m') : $m));
			}
			
			$this->curMonth = $m;
			$this->curMonthName = date('F', mktime(0,0,0,$m,1,$this->curYear));
			$this->daysInMonth = cal_days_in_month(CAL_GREGORIAN, $m, $this->curYear);
			$this->firstDayOfTheMonth = date('w', mktime(0,0,0,$m,1,$this->curYear));
		}
		
		if($this->curMonth != date('m') || $this->curYear != date('Y')) {
			$this->curDay = null;
			$this->curDayS = null;
			$this->curDayName = null;
		} else {
			$this->curDay = date('d');
			$this->curDayS = date('dS');
			$this->curDayName = date('l');
		}
	}
	
	/**
	 * Set the calendar title
	 * 
	 * Add the title string to the calendar (if any)
	 */
	private function makeCalendarTitle()
	{
		if(strlen($this->headerTitle) > 0) {
			$this->calWeekDays .= "\t<tr>\n\t\t<th class=\"headerTitle\" colspan=\"7\">".$this->headerTitle."</th>\n\t</tr>";
			$this->outArray['title'] = $this->headerTitle;
		}
	}
	
	/**
	 * Set the calendar heading
	 * 
	 * Add the heading string to the calendar.
	 * 
	 */
	private function makeCalendarHead()
	{
		if(strlen($this->headerStr) < 1) {
			$head = "\t<tr>\n\t\t<th colspan=\"7\" class=\"headerString\">";
			if(!is_null($this->curDay)) {
				$head .= $this->curDayName.' the '.$this->curDayS.' of '.$this->curMonthName.', '.$this->curYear;
			} else {
				$head .= $this->curMonthName.', '.$this->curYear;
			}
			$head .= "</th>\n\t</tr>\n";
		} else {
			$head = $this->headerStr;
		}
		
		$this->calWeekDays .= $head;
		$this->outArray['head'] = $head;
	}
	
	/**
	 * Make Day Column Headings
	 * 
	 * Build the row of day headings using the $daysArray array.
	 *
	 */
	private function makeDayHeadings()
	{
		$this->outArray['dayheadings'] = array();
		$this->dayHeadings .= "\t<tr>\n";
		foreach($this->daysArray as $day) {
			$this->dayHeadings .= "\t\t<th class=\"dayHeading\">$day</th>\n";
			array_push($this->outArray['dayheadings'], $day);
		}
		$this->dayHeadings .= "\t</tr>\n";
		
		$this->calWeekDays .= $this->dayHeadings;
	}
	
	/**
	 * Set start of month spacers
	 * 
	 * Create a spacer cell that adds blank space for the start of the month
	 * before the first day of the month.
	 *
	 */
	private function startMonthSpacers()
	{
		if($this->firstDayOfTheMonth != '0') {
			$this->calWeekDays .= "\t\t<td colspan=\"".$this->firstDayOfTheMonth."\" class=\"spacerDays\">&nbsp;</td>\n";
			$this->outArray['firstday'] = $this->firstDayOfTheMonth;
		}
	}
	
	/**
	 * Set end of month spacers
	 * 
	 * Create a spacer cell that adds blank space for the end of the month
	 * after the last day of the month.
	 *
	 */
	private function endMonthSpacers()
	{
		if((8 - $this->weekDayNum) >= '1') {
			$this->calWeekDays .= "\t\t<td colspan=\"".(8 - $this->weekDayNum)."\" class=\"spacerDays\">&nbsp;</td>\n";
			$this->outArray['lastday'] = (8 - $this->weekDayNum);			
		}
	}
	
	/**
	 * Create a listing of events for HTML display
	 * 
	 * Creates a listing of events occuring on the given day as <div> items.  These
	 * items are added into the cell of the calendar for the given day.
	 *
	 * @return string HTML output of events listing
	 */
	private function makeDayEventListHTML()
	{
		$showtext = "";
		foreach($this->eventItems as $item) {
			if($item['date'] == $this->curYear.'-'.$this->curMonth.'-'.$this->dayOfMonth) {
				$category = (strlen($item['cat']) > 0 ? 'Category: '.$item['cat'].' - ' : '');
				$outevents[$n]['category'] = $item['cat'];
				
				if($item['stdurl']) {
					$href = '<a href="'.$item['url'].'?id='.$item['id'].'" title="'.$category.$item['desc'].'">'.$item['text'].'</a>';
					$outevents[$n]['url'] = $item['url'].'?id='.$item['id'];
				} else {
					$href = (strlen($item['url']) > 0 ? '<a href="'.$item['url'].'" title="'.$category.$item['desc'].'">'.$item['text'].'</a>' : $item['text']);
					$outevents[$n]['url'] = $item['url'];
				}
				
				$style = (strlen($item['catcolor']) > 0 ? ' style="background-color:#'.str_replace('#','',$item['catcolor']).';" ' : ' style="background-color:#eeeeee;" ');
				
				$showtext .= "\n\t\t\t<div class=\"dayContent\"".$style.">".$href."</div>\n\t\t";
				
				$outevents[$n]['summary'] = $item['text'];
				$outevents[$n]['description'] = $item['desc'];
				$outevents[$n]['categorycolor'] = (strlen($item['catcolor']) > 0 ? str_replace('#','',$item['catcolor']) : '#eeeeee');
			}
			$n++;
		}
		return $showtext;
	}

	/**
	 * Create a listing of events as an array
	 * 
	 * Creates a listing of events occuring on the given day as array items.  These
	 * items are added into the cell of the calendar for the given day.
	 *
	 * @return array Array output of events listing
	 */
	private function makeDayEventListArray()
	{
		$outevents = array();
		$n = 0;
		foreach($this->eventItems as $item) {
			if($item['date'] == $this->curYear.'-'.$this->curMonth.'-'.$this->dayOfMonth) {		
				$outevents[$n]['category'] = $item['cat'];
				
				if($item['stdurl']) {
					$outevents[$n]['url'] = $item['url'].'?id='.$item['id'];
				} else {
					$outevents[$n]['url'] = $item['url'];
				}
				
				$outevents[$n]['summary'] = $item['text'];
				$outevents[$n]['description'] = $item['desc'];
				$outevents[$n]['categorycolor'] = (strlen($item['catcolor']) > 0 ? str_replace('#','',$item['catcolor']) : '#eeeeee');
			}
			$n++;
		}
		
		if(count($outevents)) {
			return $outevents;
		}
		return false;
	}
	
	/**
	 * Iterate calendar days (HTML version)
	 * 
	 * Loop through the calendar days for the given month and create cells for each
	 * day.  Populate each cell with it's given events (if any) and output as a complete
	 * table contents.
	 * 
	 * @return string HTML table contents
	 */
	private function makeHTMLIterator()
	{
		$this->weekDayNum = $this->firstDayOfTheMonth+1;
		for($this->dayOfMonth; $this->dayOfMonth <= $this->daysInMonth; $this->dayOfMonth++) {
			// Set the default style
			$style = 'class="normalDay"';
			
			// Set the style to a weekend day style
			if(($this->weekDayNum == 8)||($this->weekDayNum == 7)) {
				$style = 'class="weekendDay"';
			}
			
			// Set the style to a current day style
			if($this->curDay == $this->dayOfMonth) {
				$style = 'class="currentDay"';
			}
			
			// If the current day is Sunday, add a new row
			if($this->weekDayNum == 8) {
				$this->calWeekDays .= "\t</tr>\n\t<tr>\n";
				$this->weekDayNum = 1;
			}

			// Draw days
			$this->calWeekDays .= "\t\t<td valign=\"top\" ".$style.'>'.$this->dayOfMonth;
			
			if($this->addWeekDay) {
				$this->calWeekDays .= "|".$this->weekDayNum;
			}
			
			if(strlen($this->addEvtDest) > 0) {
				$addevtimg = (strlen($this->addEvtImg) > 0 ? '<img src="'.$this->addEvtImg.'" class="addevtimg" />' : '+');
				$this->calWeekDays .= '[<a href="'.$this->addEvtDest.'?date='.$this->curYear.'-'.$this->curMonth.'-'.$this->dayOfMonth.'" title="Add Event" class="addevt">'.$addevtimg.'</a>]';
			}
			
			$this->calWeekDays .= " ".$this->makeDayEventListHTML()."</td>\n";
			
			$this->weekDayNum++;
		}
	}
	
	/**
	 * Iterate calendar days (Array version)
	 * 
	 * Loop through calendar days for the given month and create a large multi-dimensional array
	 * of all the elements required to draw the calendar.
	 * 
	 * @return array Array of calendar elements
	 */
	private function makeArrayIterator()
	{
		$this->weekDayNum = $this->firstDayOfTheMonth+1;		
		for($this->dayOfMonth; $this->dayOfMonth <= $this->daysInMonth; $this->dayOfMonth++) {
			// Set the default style
			$this->outArray['days'][$this->dayOfMonth]['style'] = 'normalDay';
			
			// Set the style to a weekend day style
			if(($this->weekDayNum == 8)||($this->weekDayNum == 7)) {
				$this->outArray['days'][$this->dayOfMonth]['style'] = 'weekendDay';
			}
			
			// Set the style to a current day style
			if($this->curDay == $this->dayOfMonth) {
				$this->outArray['days'][$this->dayOfMonth]['style'] = 'currentDay';
			}
			
			// If the current day is Sunday, add a new row
			if($this->weekDayNum == 8) {
				$this->weekDayNum = 1;
			} 
			
			$this->outArray['days'][$this->dayOfMonth]['dayname'] = $this->daysArray[$this->weekDayNum - 1];
			$this->outArray['days'][$this->dayOfMonth]['weekdaynumber'] = $this->weekDayNum;
									
			// Draw days
			if($this->makeDayEventListArray()) {
				$this->outArray['days'][$this->dayOfMonth]['events'] = $this->makeDayEventListArray();
			}
			
			$this->outArray['days'][$this->dayOfMonth]['datestamp'] = $this->curYear.'-'.$this->curMonth.'-'.$this->dayOfMonth;
			
			$this->weekDayNum++;
		}
	}
	
	/**
	 * Set the calendar title string
	 * 
	 * If a custom calendar title string is desired, this method is used to set
	 * it.  Otherwise, no calendar title is used.
	 * 
	 * @param string $str Custom title string
	 */
	public function setTitle($str)
	{
		$this->headerTitle = $str;
	}
	
	/**
	 * Set the calendar header string
	 * 
	 * If a custom header string is desired, this method is used to set it.
	 * Otherwise, a generated string is used.
	 *
	 * @param string $str Custom header string
	 */
	public function setHeader($str)
	{
		$this->headerStr = $str;
	}

	/**
	 * Toggle add event
	 * 
	 * Set whether or not to show an "Add Event" link.  Requires a destination for
	 * processing the "Add Event" logic.
	 * 
	 * @param string $dest Destination URL for processing the "Add Event" 
	 * @param string $img Optional image file to use for "Add Event" icon
	 */
	public function setAddEvt($dest,$img=null)
	{
		$this->addEvtDest = $dest;
		$this->addEvtImg = (!is_null($img) ? $img : '');
	}
	
	/**
	 * Toggle showing weekday numbers
	 * 
	 * Set whether or not to add the weekday number in addition to the month number
	 * for each calendar day cell.
	 * 
	 * @param boolean $sw Toggle switch - true/false
	 */
	public function setShowWeekDayNum($sw=false)
	{
		$this->addWeekDay = $sw;
	}
	
	/**
	 * Add events to the calendar
	 * 
	 * Handler method to add events to an array of events to be fed to the
	 * calendar.
	 *
	 * @param int $id Record ID
	 * @param string $date Date in YYYY-MM-DD format
	 * @param string $text Short summary text
	 * @param string $desc Long description text
	 * @param string $cat Category name
	 * @param string $catcolor RGB custom category color
	 * @param boolean $stdurl Use the standard URL - destination.php?id=## - or depend on the custom supplied URL
	 * @param string $url Custom supplied URL or destination file for the standard URL
	 */
	public function setEvents($id,$date,$text,$desc,$cat=null,$catcolor=null,$stdurl=false,$url=null)
	{
		$events = array(
			'id' => $id,
			'date' => $date,
			'text' => $text,
			'desc' => $desc,
			'cat' => $cat,
			'catcolor' => $catcolor,
			'stdurl' => $stdurl,
			'url' => $url
		);
		array_push($this->eventItems, $events);
	}
	
	/**
	 * Generic method to output the calendar
	 * 
	 * Outputs the calendar as an array
	 *
	 * @return array Array output of calendar
	 */
	public function drawArray()
	{
		$this->outArray['days'] = array();
		$this->makeCalendarTitle();
		$this->makeCalendarHead();
		$this->makeDayHeadings();
		$this->startMonthSpacers();
		$this->makeArrayIterator();
		$this->endMonthSpacers();		
		
		return $this->outArray;
	}
	
	/**
	 * Generic method to draw the calendar
	 * 
	 * Outputs the HTML for drawing the calendar table.
	 * 
	 * @return string HTML of the calendar table
	 *
	 */
	public function drawHTML()
	{
		$this->calWeekDays .= "<table border=\"1\">\n";
		$this->makeCalendarTitle();
		$this->makeCalendarHead();
		$this->makeDayHeadings();
		$this->calWeekDays .= "\t<tr>\n";
		$this->startMonthSpacers();
		$this->makeHTMLIterator();
		$this->endMonthSpacers();
		$this->calWeekDays .= "\t</tr>\n";
		$this->calWeekDays .= "</table>\n";
		
		echo $this->calWeekDays;
	}
}
?>