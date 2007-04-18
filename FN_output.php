<?
/************************************************************************/
/* Leonardo: Gliding XC Server					                        */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2004-5 by Andreadakis Manolis                          */
/* http://leonardo.thenet.gr                                            */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

function sec2Time($secs,$no_seconds=false) {
  if ($no_seconds)
    return sprintf("%d:%02d",$secs/3600,($secs%3600)/60);
  else 
    return '<span class="time_style">'.sprintf("%d:%02d:%02d",$secs/3600,($secs%3600)/60,$secs%60).'</span>';
}

function days2YearsMonths($days) {
   $years=floor($days/365);
   $months=ceil( ($days%365) / 30 );
   return array($years,$months);
}

function formatURL($linkURL,$numChars=0) {
	if ($numChars>0) {
		 if (strlen($linkURL) > $numChars - 3 )
		 $linkURL=substr($linkURL,0,$numChars)."...";
	}
	if ( substr($linkURL,0,7) == "http://" ) return $linkURL;
	else return "http://".$linkURL;
}

function formatDate($date,$html_output=true) {
  // from 2002-07-14 -> 14/07/2004
  $dt_str=sprintf("%02d/%02d/%4d",substr($date,8,2),substr($date,5,2),substr($date,0,4));
  return $dt_str;
  //if ($html_output)
  //  return '<font color=#224488>'.$dt_str.'</font>';
  //else return $dt_str;
}

function formatOLCScoreType($type,$html_output=true) {
	$ret="#".$type."#";
	if ($type=="FREE_FLIGHT") $ret=_FREE_FLIGHT;
	else if ($type=="FREE_TRIANGLE") $ret=_FREE_TRIANGLE;
	else if ($type=="FAI_TRIANGLE") $ret=_FAI_TRIANGLE;
	if ($html_output)
		return '<span class="score_type_style">'.$ret.'</span>';
	else return $ret;
}

function formatDistance($distance,$showUnits=false) { // in meters
	global $PREFS;
	// 1 kilometer = 0.62 mil
	// 1 meter  =  3.28 feet
	if ($PREFS->metricSystem==2) { 
		$dis=($distance*0.62)/1000; 
		$units=_MI;
	} else { // km
		$dis=$distance/1000;
		$units=_KM;
	}
	// return sprintf("%.2f km",$distance/1000);
	return sprintf("%.1f %s",$dis,($showUnits)?$units:"");
}

function formatDistanceOpen($distance,$showKm=true) { // in meters
	return  formatDistance($distance,$showKm);
	// return '<font color=#4400aa>'.formatDistance($distance,$showKm).'</font>';
}

function formatDistanceOLC($distance,$showKm=true) { // in meters
	return '<span class="distance_style">'.formatDistance($distance,$showKm).'</span>';
}



function formatOLCScore($score,$html_output=true) { 
	return sprintf("%.2f",$score);
	//if ($html_output) return '<font color=#0000ff>'.sprintf("%.1f",$score).'</font>';
	//else return sprintf("%.1f",$score);
}

function formatAltitude($alt) { 
	global $PREFS;
	// 1 kilometer = 0.62 mil
	// 1 meter  =  3.28 feet
	if ($PREFS->metricSystem==2) { 
		$alt=$alt*3.28; //feet
		$units=_FT;
	} else { 
		$units=_M;
	}
	return '<span class="altitude_style">'.sprintf("%d %s",$alt,$units).'</span>';
}

function formatSpeed($speed) { // in km/h
	global $PREFS;
	// 1 kilometer = 0.62 mil
	// 1 meter  =  3.28 feet
	if ($PREFS->metricSystem==2) { 
		$speed=$speed*0.62; // ml/h
		$units=_MPH;
	} else { 
		$units=_KM_PER_HR;
	}
	return '<span class="speed_style">'.sprintf("%.1f %s",$speed,$units).'</span>';
}

function formatVario($vario) { // in m/sec
	global $PREFS;
	// 1 kilometer = 0.62 mil
	// 1 meter  =  3.28 feet
	if ($PREFS->metricSystem==2) { 
		$vario=$vario*3.28*60; // feet /min
		$units=_FPM;
		return '<span class="vario_style">'.sprintf("%.0f %s",$vario,$units).'</span>';
	} else { 
		$units=_M_PER_SEC;
		return '<span class="vario_style">'.sprintf("%.1f %s",$vario,$units).'</span>';
	}

}

function formatLocation($name,$vinicity,$radious) {
	global $PREFS;
	if ($PREFS->metricSystem==2) $dis=($vinicity*0.62)/1000; 
	else $dis=$vinicity/1000;

 if ($vinicity > 300000 ) $res_name="UNKNOWN";
 else if ($vinicity > $radious ) 
		$res_name=$name."&nbsp;[~".sprintf("%.1f",$dis)."]"; 
//		$res_name=$name."&nbsp;[~".sprintf("%.1f",$vinicity/1000)."&nbsp;km]"; 
 else $res_name=$name;

 $res_name=str_replace("&#039;","'",$res_name);
 return $res_name;
}


function datetime2UnixTimestamp($datestamp) {
    if ($datestamp!=0) {
        list($date, $time)=split(" ", $datestamp);
        list($year, $month, $day)=split("-", $date);
        list($hour, $minute, $second)=split(":", $time);
        $stampeddate=mktime($hour,$minute,$second,$month,$day,$year);
     
        return   $stampeddate ;
    }
}



function processIGC($filePath) {
	global $takeoffRadious;
	//echo $filePath."<br>";
	//echo filesize( $filePath)."<br>";
	$lines = file ($filePath); 
	$points=0;
	foreach($lines as $line) {
		$line=trim($line);
		if  (strlen($line)==0) continue;
		
		if ($line{0}=='B') {
			if  ($points==0)  { // first point 
				$firstPoint=new gpsPoint($line);
				echo _TAKEOFF_COORDS." ";
				echo $firstPoint->lat." ";
				echo $firstPoint->lon."<br>";


			$zone= getUTMzoneLocal( $firstPoint->lon,$firstPoint->lat);
			$timezone= ceil(-$firstPoint->lon / (180/12) );
			echo "<b>UTM zone:</b> ".$zone." ";
			echo "<b>Timezone:</b> ".$timezone."<br>";
			} else  {
				$lastPoint=new gpsPoint($line);
			}
			$points++;		   
		}
	}
	// echo $points;
}

function getUTMzoneLocal($lon, $lat)  {
	if ($lon < 0.0)  $lonTmp = 180 - $lon ;
	else $lonTmp = 180 - $lon ;

	$UTMzone=ceil($lonTmp/6);
	return $UTMzone;
}

function getUTMtimeOffset($lat,$lon, $theDate ) {
// $lon is the X (negative is EAST positive is WEST
// for now we return a very rough calculation

	$timezone= ceil( -$lon / (180/12) );
	return $timezone;
}


function getTZoffset($TZone,$tm) {
	$oldTZ=getenv("TZ");
	// echo "old:$oldTZ";
	
	putenv("TZ=$TZone");
	$offset=date('O',$tm);
	putenv("TZ=$oldTZ");
	
	if ( preg_match("/([-+])(\d\d)(\d\d)/",$offset,$matches) ) {
		$secs=$matches[2]*3600+$matches[3]*60;
		if ($matches[1]=='-') $secs=-$secs;
	} else  {
		echo "FATAL error in flight offset";
		exit;
	}
	
	
	return $secs;
}

function generate_flights_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE)
{


/*
	By default, $begin_end is 3, and $from_middle is 1, so on page 6 in a 12 page view, it will look like this:

	a, d = $begin_end = 3
	b, c = $from_middle = 1

 "begin"        "middle"           "end"
    |              |                 |
    |     a     b  |  c     d        |
    |     |     |  |  |     |        |
    v     v     v  v  v     v        v
    1, 2, 3 ... 5, 6, 7 ... 10, 11, 12

	Change $begin_end and $from_middle to suit your needs appropriately
*/
	$begin_end = 4;
	$from_middle = 3;
	
	$total_pages = ceil($num_items/$per_page);

	if ( $total_pages == 1 )
	{
		return '';
	}

	$on_page = floor($start_item / $per_page) + 1;

	$page_string = '';
	if ( $total_pages > ((2*($begin_end + $from_middle)) + 2) )
	{
	//	$init_page_max = ( $total_pages > 3 ) ? 3 : $total_pages;
		$init_page_max = ( $total_pages > $begin_end ) ? $begin_end : $total_pages;


		for($i = 1; $i < $init_page_max + 1; $i++)
		{
			// $page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . append_sid($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
			$page_string .= ( $i == $on_page ) ? '<div class="activePageNum">' . $i . '</div>' : '<a href="' . append_sid($base_url . "&amp;page_num=".$i ) . '">' . $i . '</a>';
			if ( $i <  $init_page_max )
			{
				$page_string .= ", ";
			}
		}

		//if ( $total_pages > 3 )
		if ( $total_pages > $begin_end )
		{
			if ( $on_page > 1  && $on_page < $total_pages )	{
				//$page_string .= ( $on_page > 5 ) ? ' ... ' : ', ';
				$page_string .= ( $on_page > ($begin_end + $from_middle + 1) ) ? ' ... ' : ', ';


				//$init_page_min = ( $on_page > 4 ) ? $on_page : 5;
				$init_page_min = ( $on_page > ($begin_end + $from_middle) ) ? $on_page : ($begin_end+ $from_middle + 1);
				//$init_page_max = ( $on_page < $total_pages - 4 ) ? $on_page : $total_pages - 4;
				$init_page_max = ( $on_page < $total_pages - ($begin_end + $from_middle) ) ? $on_page : $total_pages - ($begin_end + $from_middle);

//echo $init_page_min."#".$init_page_max."#".$on_page;
				//for($i = $init_page_min - 1; $i < $init_page_max + 2; $i++)
				for($i = $init_page_min - $from_middle; $i < $init_page_max + ($from_middle + 1); $i++)
				{
					$page_string .= ($i == $on_page) ? '<div class="activePageNum">' . $i . '</div>' : '<a href="' . append_sid($base_url . "&amp;page_num=$i").'">'.$i. '</a>';
					//if ( $i <  $init_page_max + 1 )
					if ( $i <  $init_page_max + $from_middle )
					{
						$page_string .= ', ';
					}
				}

				//$page_string .= ( $on_page < $total_pages - 4 ) ? ' ... ' : ', ';
				$page_string .= ( $on_page < $total_pages - ($begin_end + $from_middle) ) ? ' ... ' : ', ';


			} else {
				$page_string .= ' ... ';
			}

			//for($i = $total_pages - 2; $i < $total_pages + 1; $i++)
			for($i = $total_pages - ($begin_end - 1); $i < $total_pages + 1; $i++)
			{
				$page_string .= ( $i == $on_page ) ? '<div class="activePageNum">' . $i . '</div>'  : '<a href="' . append_sid($base_url . "&amp;page_num=$i").'">' . $i . '</a>';
				if( $i <  $total_pages )
					$page_string .= ", ";
			}
		}
	} else {
		for($i = 1; $i < $total_pages + 1; $i++)
		{
			$page_string .= ( $i == $on_page ) ? '<div class="activePageNum">' . $i . '</div>': '<a href="' . append_sid($base_url . "&amp;page_num=$i").'">'.$i.'</a>';
			if ( $i <  $total_pages )
				$page_string .= ', ';
		}
	}

	if ( $add_prevnext_text )
	{
		if ( $on_page > 1 )
			$page_string = ' <a href="' . append_sid($base_url . "&amp;page_num=" . ( $on_page - 1 )  ) . '">�</a>&nbsp;&nbsp;'.$page_string;

		if ( $on_page < $total_pages )
			$page_string .= '&nbsp;&nbsp;<a href="' . append_sid($base_url . "&amp;page_num=" . ($on_page+1)   ) . '">�</a>';
	}
	$page_string = '<div class="numeration">'.$page_string.'</div>';
	return $page_string;
}

function makePilotPopup() {
	global $moduleRelPath,$module_name,$opMode;
	ob_start();

?>
<script language="javascript">
var pilotTip = new TipObj('pilotTip');
with (pilotTip)
{
  template = '<table bgcolor="#000000" cellpadding="0" cellspacing="0" width="%3%" border="0">' +
  '<tr><td class="infoBoxHeader">%5%</td></tr>'+
  '<tr><td class="infoBox">'+
  "<img src='<?=$moduleRelPath?>/img/icon_pilot.gif' border=0 align='absmiddle'> <a href='?name=<?=$module_name?>&op=pilot_profile&pilotIDview=%4%'><? echo _Pilot_Profile ?></a>"+
	'</td></tr>'+
    '<tr><td class="infoBox">'+

	"<img src='<?=$moduleRelPath?>/img/icon_magnify_small.gif' border=0 align='absmiddle'> <a href='?name=<?=$module_name?>&op=list_flights&year=0&month=0&pilotID=%4%&takeoffID=0&country=0&cat=0'><? echo _PILOT_FLIGHTS ?></a>"+
	'</td></tr>'+
    '<tr><td class="infoBox">'+

	"<img src='<?=$moduleRelPath?>/img/icon_stats.gif' border=0 align='absmiddle'> <a href='?name=<?=$module_name?>&op=pilot_profile_stats&pilotIDview=%4%'><? echo _flights_stats ?></a>"+

	<?  if ($opMode==2)  { ?>// phpbb only 
	'</td></tr>'+
    '<tr><td class="infoBox">'+
	"<img src='<?=$moduleRelPath?>/img/icon_user.gif' alt='PM this user' width=16 height=16 border=0 align='absmiddle'> <a href='/privmsg.php?mode=post&u=%4%'><? echo "PM" ?></a>"+
    <? } ?>

	'</td></tr></table>';

 tipStick = 0;
 showDelay = 0;
 hideDelay = 0;
 doFades = false;
}
</script>
<div id="pilotTipLayer" class="shadowBox" style="position: absolute; z-index: 10000; 
visibility: hidden; left: 0px; top: 0px; width: 10px">&nbsp;</div>
<?
	$c=ob_get_contents();
	ob_end_clean();
	return  $c;
}

function makeTakeoffPopup($ext=0,$userID=0) {
	global $moduleRelPath,$module_name,$opMode;
	ob_start();

?>
<script language="javascript">
var takeoffTip = new TipObj('takeoffTip');
with (takeoffTip)
{
 template = '<table bgcolor="#000000" cellpadding="0" cellspacing="0" width="%3%" border="0">' +
  '<tr><td class="infoBoxHeader">%5%</td></tr>'+
  '<tr><td class="infoBox">'+
	"<img src='<?=$moduleRelPath?>/img/icon_magnify_small.gif' align='absmiddle' border=0> <a href='?name=<?=$module_name?>&op=list_flights&takeoffID=%4%&year=0&month=0&pilotID=0&country=0&cat=0'><? echo  _See_flights_near_this_point ?></a>"+
	'</td></tr>'+
    '<tr><td  class="infoBox">'+
	"<img src='<?=$moduleRelPath?>/img/icon_pin.png' align='absmiddle' border=0> <a href='?name=<?=$module_name?>&op=show_waypoint&waypointIDview=%4%'><? echo _SITE_INFO  ?></a>"+
	'</td></tr>'+
    '<tr><td  class="infoBox">'+
	"<img src='<?=$moduleRelPath?>/img/gearth_icon.png' align='absmiddle' border=0> <a href='<?=$moduleRelPath?>/download.php?type=kml_wpt&wptID=%4%'><? echo _Navigate_with_Google_Earth ?></a>"+
	<? if ( $ext && is_leo_admin($userID) ) { ?>
    '</td></tr><tr><td class="infoBox adminBox">'+
	 "<img src='<?=$moduleRelPath?>/img/icon_add.png' align='absmiddle' border=0> <a href='javascript:add_takeoff(%6%,%7%,%4%)'><?=_ADD_WAYPOINT?></a>"+

     '</td></tr><tr><td class="infoBox adminBox">'+
	 "<img src='<?=$moduleRelPath?>/img/change_icon.png' align='absmiddle' border=0> <a href='javascript:edit_takeoff(%4%)'><?=_EDIT_WAYPOINT?></a>"+

     '</td></tr><tr><td class="infoBox adminBox">'+
	 "<img src='<?=$moduleRelPath?>/img/x_icon.gif' align='absmiddle' border=0> <a href='javascript:delete_takeoff(%4%)'><?=_DELETE_WAYPOINT?></a>"+

	<? } ?>
	'</td></tr></table>';

 tipStick = 0;
 showDelay = 0;
 hideDelay = 0;
 doFades = false;
}
</script>
<div id="takeoffTipLayer" class="shadowBox" style="position: absolute; z-index: 10000; visibility: hidden; left: 0px; top: 0px; width: 10px">&nbsp;</div>
<?
	$c=ob_get_contents();
	ob_end_clean();
	return  $c;
}

function prepare_for_js($name) {
	$name=str_replace("&#039;","'",$name); 
	$name=str_replace("\"","'",$name); 
	// $name=str_replace("'","\'",$name);
	return $name;
}


function flush2Browser() {

// first version 
/*
  echo str_pad(" ",4096," ");
  flush();
  ob_flush();
  while (@ ob_end_flush());
*/

// second version 
	// ob_implicit_flush(1) ;

	/*for($i = 0; $i < 200; $i++){
		print "<!-- bufferme -->\n";
	}*/	

// third version
	?>
	<script language="javascript">		
		for(i=0;i<100;i++) {
			document.writeln("<!-- NULL -->");
		}		
	</script>
	<?
    flush();
 	while (@ob_end_flush()); 
	//ob_end_flush();
	//	ob_start();	

}
?>