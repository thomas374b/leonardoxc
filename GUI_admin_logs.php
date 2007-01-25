<? 
/************************************************************************/
/* Leonardo: Gliding XC Server					                                */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2004-5 by Andreadakis Manolis                          */
/* http://sourceforge.net/projects/leonardoserver                       */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
//************************************************************************/

 
  $sortOrder=$_REQUEST["sortOrder"];
  if ( $sortOrder=="")  $sortOrder="actionTime";

  //$page_num=$_REQUEST["page_num"]+0;
  //if ($page_num==0)  $page_num=1;

//-----------------------------------------------------------------------------------------------------------

  	$query="SELECT * FROM ".$logTable." ORDER BY $sortOrder DESC ";	
   // echo $query;
	$res= $db->sql_query($query);		
    if($res <= 0){
		echo "no log entries found<br>";
		return ;
    }

	$legend="Log entries";
	$legendRight="";
   echo  "<div class='tableTitle shadowBox'>
   <div class='titleDiv'>$legend</div>
   <div class='pagesDiv'>$legendRight</div>
   </div>" ;

function printHeaderTakeoffs($width,$sortOrder,$fieldName,$fieldDesc,$query_str) {
  global $moduleRelPath;
  global $Theme,$module_name;

  if ($width==0) $widthStr="";
  else  $widthStr="width='".$width."'";

  if ($fieldName=="intName") $alignClass="alLeft";
  else $alignClass="";

  if ($sortOrder==$fieldName) { 
   echo "<td $widthStr  class='SortHeader activeSortHeader $alignClass'>
			<a href='?name=$module_name&op=admin_logs&sortOrder=$fieldName$query_str'>$fieldDesc<img src='$moduleRelPath/img/icon_arrow_down.png' border=0  width=10 height=10></div>
		</td>";
  } else {  
	   echo "<td $widthStr  class='SortHeader $alignClass'><a href='?name=$module_name&op=admin_logs&sortOrder=$fieldName$query_str'>$fieldDesc</td>";
   } 
}

  
   $headerSelectedBgColor="#F2BC66";

  ?>
  <table class='simpleTable' width="100%" border=0 cellpadding="2" cellspacing="0">
  <tr>
  	<td width="25" class='SortHeader'>#</td>
 	<?
		printHeaderTakeoffs(100,$sortOrder,"actionTime","DATE",$query_str) ;
		printHeaderTakeoffs(0,$sortOrder,"ServerItemID","Server",$query_str) ;
		printHeaderTakeoffs(80,$sortOrder,"userID","userID",$query_str) ;

		printHeaderTakeoffs(100,$sortOrder,"ItemType","Type",$query_str) ;
		printHeaderTakeoffs(100,$sortOrder,"ItemID","ID",$query_str) ;
		printHeaderTakeoffs(100,$sortOrder,"ActionID","Action",$query_str) ;
		echo '<td width="100" class="SortHeader">Details</td>';
		printHeaderTakeoffs(100,$sortOrder,"Result","Result",$query_str) ;
		echo '<td width="100" class="SortHeader">ACTIONS</td>';
		
	?>
	
	</tr>
<?
   	$currCountry="";
   	$i=1;
	while ($row = $db->sql_fetchrow($res)) {  
		if ( is_leo_admin($row['userID'])  ) $admStr="*ADMIN*";
		else $admStr="";

		if ($row['ServerItemID']==0) $serverStr="Local";
		else $serverStr=$row['ServerItemID'];
		
		$i++;
		echo "<TR class='$sortRowClass'>";	
	   	echo "<TD>".($i-1+$startNum)."</TD>";
		
		echo "<td>".date("d/m/y H:i:s",$row['actionTime'])."</td>\n";
		echo "<td>".$serverStr."</td>\n";
		echo "<td>".$row['userID']."$admStr<br>(".$row['effectiveUserID'].")</td>\n";
		echo "<td>".Logger::getItemDescription($row['ItemType'])."</td>\n";
		echo "<td>".$row['ItemID']."</td>\n";
		echo "<td>".Logger::getActionDescription($row['ActionID'])."</td>\n";
		echo "<td>";

		echo "<div id='sh_details$i'><STRONG><a href='javascript:toggleVisibility(\"details$i\");'>Show details</a></STRONG></div>";
			echo "<div id='details$i' style='display:none'><pre>".$row['ActionXML']."</pre></div>";
		echo "</td>\n";
		echo "<td>".$row['Result']."</td>\n";
		
		echo "<td>";
		if ($row['ItemType']==4) { // waypoint
				echo "<a href='?name=$module_name&op=show_waypoint&waypointIDview=".$row['ItemID']."'>Display</a>";
		}
		
		echo "</td>\n";

		
		echo "</TR>";
   }     
   echo "</table>";
   $db->sql_freeresult($res);

?>