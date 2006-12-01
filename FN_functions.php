<? 
/************************************************************************/
/* Leonardo: Gliding XC Server					                        */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2004-5 by Andreadakis Manolis                          */
/* http://sourceforge.net/projects/leonardoserver                       */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/
 
function getHTTPpage($url,$timeout=5) {
	return fetchURL($url,$timeout);

	preg_match("/^(http:\/\/)?([^\/]+)/i", $url, $matches);
	$ServerHostName= $matches[2]; 
	$pos = strpos( $ServerHostName,":"); 
	if ($pos === false) $ServerPort=80;
	else  { 
		$ServerPort= substr($ServerHostName,$pos+1);
		$ServerHostName=substr($ServerHostName,0,$pos);
	}

	// echo "#".$ScoringServerHostName."#". $ScoringServerPort ."#";
	$fp = @fsockopen ($ServerHostName, $ServerPort, $errno, $errstr, $timeout); 
	if (!$fp) { return 0; }
	else fclose ($fp); 
	
	$contents = file($url); 
	//	set_time_limit(180);
	return $contents ;
}

function fetchURL( $url, $timeout=5) {
   $url_parsed = parse_url($url);
   $host = $url_parsed["host"];
   $port = $url_parsed["port"];
   if ($port==0)
       $port = 80;
   $path = $url_parsed["path"];
   if ($url_parsed["query"] != "")
       $path .= "?".$url_parsed["query"];

   $out = "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n";

   $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
   if (!$fp) { return 0; }

   stream_set_timeout($fp,$timeout);

   if (fwrite($fp, $out)) {
	   $body = false;
	   while (!feof($fp)) {
		   if ( ! $s = fgets($fp, 1024) ) { 
				//echo "#"; 
				break; 
			}
		   if ( $body )
			   $in .= $s;
		   if ( $s == "\r\n" )
			   $body = true;
	   }
   }  else {
	//echo "$";
   }

   fclose($fp);
  
   return $in;
}

function splitLines($line) {
 $max_line_len=35;
 $words=split(" ",$line);
 $sline="";
 $i=0;
 foreach($words as $word) {
    if ($i==0) $sep=""; else $sep=" ";
	$i++;
 	$tmp_sline = $sline.$sep.$word;
	if ($nl_pos=strrchr($tmp_sline, 10)) $tmp_sline = substr($tmp_sline ,$nl_pos ); 

	if (strlen($tmp_sline) > $max_line_len ) {
	   $sline=$sline."\n".$word;	
	   $i=0;  
	} else {
	   $sline=$sline.$sep.$word;
	}

 }
 
 return $sline;
}

function delDir($dir){
 if ( !is_dir($dir) ) return;
 $current_dir = opendir($dir);
 while($entryname = readdir($current_dir)){
    if(is_dir("$dir/$entryname") and ($entryname != "." and $entryname!="..")){
       deldir("${dir}/${entryname}");
    }elseif($entryname != "." and $entryname!=".."){
       unlink("${dir}/${entryname}");
    }
 }
 closedir($current_dir);
 @rmdir(${dir});
}


function  checkPath($path){
  if (!is_dir($path))  mkdir($path,0755);
  if (!is_dir($path."/flights"))	mkdir($path."/flights",0755);
  if (!is_dir($path."/maps") )		mkdir($path."/maps",0755);
  if (!is_dir($path."/charts")) 	mkdir($path."/charts",0755);
  if (!is_dir($path."/photos")) 	mkdir($path."/photos",0755);
 
}

function resizeJPG($forcedwidth, $forcedheight, $sourcefile, $destfile, $imgcomp)
{
	$g_imgcomp=100-$imgcomp;
	$g_srcfile=$sourcefile;
	$g_dstfile=$destfile;
	$g_fw=$forcedwidth;
	$g_fh=$forcedheight;
	if(file_exists($g_srcfile))
	{
	  $image_details = getimagesize($g_srcfile);
	  $source_width  = $image_details[0];
      $source_height = $image_details[1];

	  $dest_width_max   = $forcedwidth;
	  $dest_height_max  = $forcedheight;
	  // The two lines beginning with (int) are the super important magic formula part.
	  (int)$dest_width  = ($source_width <= $source_height) ? round(($source_width  * $dest_height_max)/$source_height) : $dest_width_max;
	  (int)$dest_height = ($source_width >  $source_height) ? round(($source_height * $dest_width_max) /$source_width)  : $dest_height_max;
  
   	   if ($dest_width > $source_width ) {
			$dest_width = $source_width;
			$dest_height = $source_height;
	   }

	   $img_src=imagecreatefromjpeg($g_srcfile);

	   if (function_exists("gd_info")) {
		   $gdinfo=gd_info();
		   if ( strpos($gdinfo["GD Version"],"2.") ) $gd2=true;
		   else $gd2=false;
	   } else $gd2=false;

	   if ( $gd2 ) { 
		   $img_dst=imagecreatetruecolor($dest_width,$dest_height);
		   imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $dest_width, $dest_height,  $source_width, $source_height);
		} else {
		   $img_dst=imagecreate($dest_width,$dest_height);
		   imagecopyresized($img_dst, $img_src, 0, 0, 0, 0, $dest_width, $dest_height,  $source_width, $source_height);
		}

		imagejpeg($img_dst, $g_dstfile, $g_imgcomp);
		imagedestroy($img_dst);
		return true;
	}
	else
		return false;
}

function getBrowser() {
	//
	// Determine the Browser the User is using, because of some nasty incompatibilities.
	// Most of the methods used in this function are from phpMyAdmin. :)
	//
	if (!empty($_SERVER['HTTP_USER_AGENT'])) 
	{
		$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
	} 
	else if (!empty($HTTP_SERVER_VARS['HTTP_USER_AGENT'])) 
	{
		$HTTP_USER_AGENT = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
	}
	else if (!isset($HTTP_USER_AGENT))
	{
		$HTTP_USER_AGENT = '';
	}

	if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
	{
		$browser_version = $log_version[2];
		$browser_agent = 'opera';
	} 
	else if (ereg('MSIE ([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
	{
		$browser_version = $log_version[1];
		$browser_agent = 'ie';
	} 
	else if (ereg('OmniWeb/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
	{
		$browser_version = $log_version[1];
		$browser_agent = 'omniweb';
	} 
	else if (ereg('Netscape([0-9]{1})', $HTTP_USER_AGENT, $log_version)) 
	{
		$browser_version = $log_version[1];
		$browser_agent = 'netscape';
	} 
	else if (ereg('Mozilla/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
	{
		$browser_version = $log_version[1];
		$browser_agent = 'mozilla';
	} 
	else if (ereg('Konqueror/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) 
	{
		$browser_version = $log_version[1];
		$browser_agent = 'konqueror';
	} 
	else 
	{
		$browser_version = 0;
		$browser_agent = 'other';
	}
	return array($browser_agent,$browser_version);
}

	function prep_for_DB($str) {
		return		str_replace("'","&#039;",$str);
	}
	function prep_from_DB($str) {
		return		str_replace("&#039;","'",$str);
	}


	function fill_year_month_array($first_month ,$last_month){
		$year_month_array=array();

		$start_year=substr($first_month,0,4);
		$start_month=substr($first_month,5,2);
		$end_year=substr($last_month,0,4);
		$end_month=substr($last_month,5,2);


		for($y=$start_year;$y<=$end_year;$y++) {
			for($m=1;$m<=12;$m++) {
				array_push($year_month_array,sprintf("%04d-%02d",$y,$m));
			}
		}
		return 		$year_month_array;
	}

	function DEBUG($debugCat,$debugLevel,$msg ) {
		global $DBGcat,$DBGlvl;
		if ( ($DBGcat == $debugCat || $DBGcat=='' ) && $DBGlvl & $debugLevel  ) echo $msg;
	}

	function setDEBUGfromGET(){
		global $DBGcat,$DBGlvl;
		if ( $_GET['DBGcat'] ) $DBGcat=$_GET['DBGcat'] ;
		if ( $_GET['DBGlvl'] ) $DBGlvl=$_GET['DBGlvl'] ;		
	}

	function getAvailableThemes() {
		global $moduleAbsPath;
		 $res=array();
		 $dir=$moduleAbsPath."/templates";
		 $current_dir = opendir($dir);
		 while($entryname = readdir($current_dir)){
			if( is_dir($dir ."/".$entryname) && ($entryname != "." and $entryname!="..")){
			   array_push($res,$entryname);
			}
		 }
		 closedir($current_dir);
		 return $res;
	}

	function is_club_admin($userID,$clubID) {
		global $clubsList;
		if ($clubsList[$clubID]['adminID']==$userID) return 1;
		else return 0;
	}

	function is_leo_admin($userID) {
		global $admin_users;
		if (in_array($userID,$admin_users)) return 1;
		else return 0;
	}
	
	# Compares versions of software
	# versions must must use the format ' x.y.z... ' 
	# where (x, y, z) are numbers in [0-9]
	function check_version($currentversion, $requiredversion)
	{
		list($majorC, $minorC, $editC) = split('[/.-]', $currentversion);
		list($majorR, $minorR, $editR) = split('[/.-]', $requiredversion);
		
		if ($majorC > $majorR) return true;
		if ($majorC < $majorR) return false;
		// same major - check ninor
		if ($minorC > $minorR) return true;
		if ($minorC < $minorR) return false;
		// and same minor
		if ($editC  >= $editR)  return true;
		return false;
	}

?>