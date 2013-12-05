<?php
include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./lib/database.php");
include ("locale.php");

session_start();

getpost_ifset(array(confno,pin));
$view = "Past";


// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME=DB_TABLESCHED;

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();

$FG_TABLE_COL[]=array (_("Conference #"), "confno", "15%", "left", "", "10");
$FG_TABLE_COL[]=array (_("Conference Name"), "pin", "25%", "left", "", "10");
$FG_TABLE_COL[]=array (_("Start"), "starttime", "20%", "left", "SORT", "30");
$FG_TABLE_COL[]=array (_("End"), "endtime", "20%", "left", "", "30");

$FG_TABLE_DEFAULT_ORDER = "starttime";
//$FG_TABLE_DEFAULT_SENS = "ASC";
$FG_TABLE_DEFAULT_SENS = "DESC";

// This Variable store the argument for the SQL query
$FG_COL_QUERY='confno, confDesc, starttime, endtime, maxusers, bookId, pin, confOwner, adminpin, adminopts, opts';


// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=15;
$FG_LIMITE_DISPLAY_BLANK_LINE=5;

// Number of column in the html table
$FG_NB_TABLE_COL=count($FG_TABLE_COL);

//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE=" - "._("Scheduled Conference")." : ";

//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH="100%";




if ($FG_DEBUG == 3) echo "<br>Table : $FG_TABLE_NAME  	- 	Col_query : $FG_COL_QUERY";




if ( !isset ($order) || !isset ($sens) ){
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
}


$now=getConfDate($today);

	$FG_CLAUSE = "endtime<='$now' AND confno = '$confno' AND (pin = '$pin' or adminpin = '$pin') AND adminopts LIKE '%r%'";

	$nb_record = $db->getOne("SELECT COUNT(*) FROM $FG_TABLE_NAME WHERE $FG_CLAUSE");

	$record_start = intval($current_page*$FG_LIMITE_DISPLAY);

	$query = "SELECT $FG_COL_QUERY FROM $FG_TABLE_NAME WHERE $FG_CLAUSE ORDER BY $order $sens LIMIT
$record_start, $FG_LIMITE_DISPLAY";
        $result = $db->query($query);

        $i = 0;
        while($row = $result->fetchRow())
                $list[$i++] = $row;

if ($FG_DEBUG >= 1) var_dump ($list);


if ($nb_record<=$FG_LIMITE_DISPLAY){ 
	$nb_record_max=1;
}else{ 
	$nb_record_max=(intval($nb_record/$FG_LIMITE_DISPLAY)+1);
}

if ($FG_DEBUG == 3) echo "<br>Nb_record : $nb_record";
if ($FG_DEBUG == 3) echo "<br>Nb_record_max : $nb_record_max";




?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?php print GUI_TITLE; ?> control</title>
		<meta http-equiv="Content-Type" content="text/html">
		
		<link rel="stylesheet" type="text/css">
		<style type="text/css" media="screen">
			@import url("css/content.css");
			@import url("css/docbook.css");
		</style>
		
		<script language="JavaScript" type="text/JavaScript">
		<!--
		function MM_openBrWindow(theURL,winName,features) { //v2.0
		  window.open(theURL,winName,features);
		}
		
		//-->
		</script>
	</head>
	<body>



<br><br>

<!-- ** ** ** ** ** Part to display the conference  ** ** ** ** ** -->
<center>
<?php if (is_array($list) && $_SESSION['auth']){ ?>
      <table width="<?php echo $FG_HTML_TABLE_WIDTH; ?>" border="0" align="center" cellpadding="0" cellspacing="0">
		<TR bgcolor="#ffffff"> 
          <TD bgColor=#7f99cc height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px"> 
            <TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
              <TBODY>
                <TR> 
                  <TD><SPAN style="COLOR: #ffffff; FONT-SIZE: 11px"><B><?php echo $FG_HTML_TABLE_TITLE; ?></B></SPAN></TD>
                  <TD align=right> <IMG alt="Back to Top" border=0 height=12 src="images/btn_top_12x12.gif" width=12> 
                  </TD>
                </TR>
              </TBODY>
            </TABLE></TD>
        </TR>
        <TR> 
          <TD> 
		  	<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
			<TBODY>
			
                <TR bgColor=#F0F0F0> 
				
				  
                  <?php 
				  	if (is_array($list) && count($list)>0){
					
				  	for($i=0;$i<$FG_NB_TABLE_COL;$i++){ 
					?>				
				  
					
                  <TD width="<?php echo $FG_TABLE_COL[$i][2]; ?>" align=left class="tableBody" style="PADDING-BOTTOM: 2px; PADDING-LEFT: 2px; PADDING-RIGHT: 2px; PADDING-TOP: 2px"> 
                    <strong> 
                    <?php if (strtoupper($FG_TABLE_COL[$i][4])=="SORT"){?>
                    <a href="<?php echo $_SERVER[PHP_SELF]."?s=$s&t=$t&view=$view&current_page=$current_page&order=".$FG_TABLE_COL[$i][1]."&sens="; if ($sens=="ASC"){echo"DESC";}else{echo"ASC";} 
					echo "\"";?>"> 
                    <span class="liens">
		<?php 
		}
		echo $FG_TABLE_COL[$i][0];
		if ($order==$FG_TABLE_COL[$i][1] && $sens=="ASC")
		{
		?>
                    &nbsp;<img src="images/icon_up_12x12.gif" width="12" height="12" border="0"> 
		<?php
		}
		elseif ($order==$FG_TABLE_COL[$i][1] && $sens=="DESC")
		{
		?>
                    &nbsp;<img src="images/icon_down_12x12.gif" width="12" height="12" border="0"> 
		<?php 
		}
		if (strtoupper($FG_TABLE_COL[$i][4])=="SORT")
		{
			echo "</span></a>"; 
		}
		?>
                    </strong></TD>
		<?php 
		}
		echo "</TR>";
		$ligne_number=-1;					 
		foreach ($list as $recordset)
		{ 
			$adminopts = $recordset[9];
			$ligne_number++;
			if ($recordset[0]=="2")
			{ 
		?>
		<TR bgcolor="<?php echo $FG_TABLE_ROW_COLOR_ADMIN; ?>"  onMouseOver="bgColor='#FFA5A5'" onMouseOut="bgColor='<?php echo $FG_TABLE_ROW_COLOR_ADMIN; ?>'"> 
			<?php 
			}
			else
			{ 
			?>
               		 	<TR bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]; ?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]; ?>'"> 
			<?php 
			}
			for($i=0;$i<$FG_NB_TABLE_COL;$i++)
			{
				$record_display = $recordset[$i];
				if($i == 2 || $i ==3)
				{
					$tmpTime=strtotime($recordset[$i]);
					$record_display = date("m/d/y g:ia", $tmpTime);
				}
				if ($i == 0)
				{ 
			?>
                 		 <TD width="<?php echo $FG_TABLE_COL[$i][2]; ?>" vAlign=middle align="<?php $FG_TABLE_COL[$i][3]; ?>" class=tableBody>
		<?php 
			if ($view == "Current")
			{ 
		?>
                <a href="./meetme_control.php?s=1&t=0&confno=<?php
		echo $recordset[0]; ?>"target="_top">
                <?php echo stripslashes($record_display); ?></a></TD>
                                        <?php } elseif ($view == "Past"){ 
			if(strchr($adminopts, 'r') && file_exists(RECORDING_PATH . "meetme-conf-rec-".$recordset[0]."-".$recordset[5].".wav"))
			{
				echo "<a href=\"javascript:;\" onClick=\"window.open('play.php?confno=$recordset[0]&bookId=$recordset[5]', 'newWin', 'toolbar=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=350,height=100')\"><img src=\"images/speaker.gif\" alt=\"Play\" border=0></a>";
			}
			else
			{
				echo "<img src=\"images/spacer.gif\" width=\"20\">&nbsp;";
			}
		?>
                <?php echo stripslashes($record_display); 
		?>
		</TD>
					<?php } else { ?>
		<a href="./meetme_control.php?s=2&t=0&bookId=
		<?php 
		echo "$recordset[5]\" target=\"_top\">" . stripslashes($record_display); ?></a>
		</TD>
								 
				<?php }} else { ?>

                                 <TD width="<?php echo $FG_TABLE_COL[$i][2]; ?>" vAlign=middle align="<?php echo $FG_TABLE_COL[$i][3]; ?>" class=tableBody><?php echo stripslashes($record_display); ?></TD>

				<?php } ?>		 
				   	<?php } ?>	
				   		 </TD>  
					</TR>
				<?php
					 }//foreach ($list as $recordset)
					 while ($ligne_number < $FG_LIMITE_DISPLAY_BLANK_LINE){
					 	$ligne_number++;
				?>
					<TR bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]; ?>"> 
					</TR>
									
				<?php					 
					 } //END_WHILE
					 
				  }else{
				  		echo "No data found !!!";				  
				  }//end_if
				 ?>
                
                <TR> 
                  <TD class=tableDivider colSpan=<?php $FG_TOTAL_TABLE_COL+1; ?>><IMG height=1 
                              src="images/clear.gif" 
                              width=1></TD>
							 
                </TR>
				<TR> 
                  <TD class=tableDivider align=right colSpan=<?php echo $FG_TOTAL_TABLE_COL+1; ?>>
				  </TD>
				  
                </TR>
              </TBODY>
            </TABLE></td>
        </tr>
        <TR bgcolor="#ffffff"> 
          <TD bgColor=#ADBEDE height=16 style="PADDING-LEFT: 5px; PADDING-RIGHT: 3px"> 
			<TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
              <TBODY>
                <TR> 
                  <TD align="right"><SPAN style="COLOR: #ffffff; FONT-SIZE: 11px"><B> 				  
                    <?php if ($current_page>0) { ?>
                    <img src="images/fleche-g.gif" width="5" height="10"> <a href="<?php echo "$_SERVER[PHP_SELF]?s=1&t=0&order=$order&view=$view&sens=$sens&current_page=" . $current_page-1 ."\">"; 
			?> 
                    <?php print _("Previous"); ?> </a> - 
                    <?php 
			}
			echo ($current_page+1) ." / " . $nb_record_max; 
			if ($current_page<$nb_record_max-1)
			{
				echo "- <a href=\"$_SERVER[PHP_SELF]?s=2&t=2&order=$order&view=$view&sens=$sens&current_page=" . $current_page+1;
				">";
		    ?> 
                    <?php print _("Next"); ?> </a> <img src="images/fleche-d.gif" width="5" height="10"> 
                    </B></SPAN> 
                    <?php } ?>
                  </TD>
              </TBODY>
            </TABLE></TD>
        </TR>
      </table>

                  
<?php 
} else {
	echo _("No recorded conference found with that conference number and pin") ;
}
?>
</center>

</td></tr></tbody></table>
</center>
<br></br>&nbsp;&nbsp;
<br></br>&nbsp;&nbsp;

		<!-- END -->
	</body>
</html>
