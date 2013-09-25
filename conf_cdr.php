<?php
include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./lib/database.php");
//include ("locale.php");

session_start();

getpost_ifset(array('bookId','confno','view','s','t'));

// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME=DB_TABLECDR;

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();

$FG_TABLE_COL[]=array (_("Caller Name"), "CIDname", "50%", "middle", "", "30");
$FG_TABLE_COL[]=array (_("Telephone"), "CIDnum", "25%", "middle", "", "30");
$FG_TABLE_COL[]=array (_("Duration"), "duration", "25%", "middle", "", "30");

//$FG_TABLE_DEFAULT_ORDER = "CIDname";
$FG_TABLE_DEFAULT_ORDER = "clid";
$FG_TABLE_DEFAULT_SENS = "ASC";

// This Variable store the argument for the SQL query
//$FG_COL_QUERY='CIDname, CIDnum, duration';
$FG_COL_QUERY='clid, src, duration';


// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=15;
$FG_LIMITE_DISPLAY_BLANK_LINE=5;

// Number of column in the html table
$FG_NB_TABLE_COL=count($FG_TABLE_COL);

//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE=" - "._("Conference Participants").": $confno";

//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH="100%";

if ($FG_DEBUG == 3) echo "<br>Table : $FG_TABLE_NAME  	- 	Col_query : $FG_COL_QUERY";

if ( !isset ($order) || !isset ($sens) ){
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
}


$now=getConfDate();
if (isset($bookId)){
	//$FG_TABLE_CLAUSE = "bookId='$bookId'";
	$FG_TABLE_CLAUSE = "userfield='$bookId'";
}	
	//$result = $db->query("SET NAMES utf8");
	$i = 0;
	$query = "SELECT $FG_COL_QUERY FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE";
	$result = $db->query($query);
	
	if ($FG_DEBUG == 3)
		print_r ($result);
	while($row = $result->fetchRow())
		$list[$i++] = $row;
	$nb_record = $i-1;

	if ($i){
	   for ($x=0; $x < $i; $x++){
		$dur = intval($list[$x][2]);
		$hr = intval(($dur / 3600));
		$min = intval(($dur % 3600) / 60);
		$sec = intval(($dur % 60));
		$list[$x][2] = $hr.":".$min.":".$sec;
	   }
	}

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
		<title><?php echo GUI_TITLE; ?> control</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		
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
<?php if ( (isset($list) && is_array($list)) ){ ?>

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
				  
					
                  <TD width="<?php $FG_TABLE_COL[$i][2]; ?>" align=middle class="tableBody" style="PADDING-BOTTOM: 2px; PADDING-LEFT: 2px; PADDING-RIGHT: 2px; PADDING-TOP: 2px"> 
                    <center><strong> 
                    <?php if (strtoupper($FG_TABLE_COL[$i][4])=="SORT"){?>
                    <a href="<?php echo $_SERVER[PHP_SELF]."?s=$s&t=$t&view=$view&current_page=$current_page&order=".$FG_TABLE_COL[$i][1]."&sens="; 
						if ($sens=="ASC"){echo"DESC";}else{echo"ASC";} 
					echo "";?>"> 
                    <span class="liens"><?php } ?>
                    <?php echo $FG_TABLE_COL[$i][0]; ?> 
                    <?php if (strtoupper($FG_TABLE_COL[$i][4])=="SORT"){?>
                    </span></a> 
                    <?php } ?>
                    </strong></center></TD>
				   <?php } ?>
				   
				<?php
				
				
				  
				  	 $ligne_number=1;
				  	 foreach ($list as $recordset){ 
				  	    preg_match('/\"(.+)\"/',$recordset[0],$matches);
					    $recordset[0] = $matches[1];
				 	    $ligne_number++;
					    if ($recordset[0] || $recordset[1]){ ?>
               		 		    <TR bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2];?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]; ?>'"> 
							 
				  		<?php for($i=0;$i<$FG_NB_TABLE_COL;$i++){ ?>
						
						<?php  $record_display = $recordset[$i];
						       
				 	 ?>
                 		 <TD width="<?php echo $FG_TABLE_COL[$i][2]; ?>" vAlign=middle align="<?php echo $FG_TABLE_COL[$i][3];?>" class=tableBody>
		<?php //echo htmlentities( $record_display);
		echo $record_display;
		 ?></TD>
								 


				   	<?php } ?>	
				   	<?php } ?>	
				   		 </TD>  
					</TR>

				<?php	 
					}
				  }else{
				  		echo _("No data found")."!!!";				  
				  }//end_if
				 ?>
                
                <TR> 
                  <TD class=tableDivider colSpan=<?php echo $FG_TOTAL_TABLE_COL+1; ?>><IMG height=1 
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
                    <?php if (isset ($current_page) && $current_page>0){?>
                    <img src="images/fleche-g.gif" width="5" height="10"> 
					<a href="<?php echo "$_SERVER[PHP_SELF]?order=$order&sens=$sens&current_page=" . ($current_page-1); echo "&bookId=$bookId&confno=$confno";?>"> 
                    Previous </a> - 
                    <?php }?>
                    <?php if (isset($current_page)) echo ($current_page+1) ." / " . $nb_record_max; ?> 
                    <?php if (isset ($current_page) && $current_page<$nb_record_max-1){?>
                    - <a href="<?php echo "$_SERVER[PHP_SELF]?order=$order&view=$view&sens=$sens&current_page=" . ($current_page+1); echo "&bookId=$bookId&confno=$confno";?>"> 
                    Next </a> <img src="images/fleche-d.gif" width="5" height="10"> 
                    </B></SPAN> 
                    <?php }?>
                  </TD>
              </TBODY>
            </TABLE></TD>
        </TR>
      </table>

	<FORM>
	<INPUT TYPE="Submit" onClick="window.close()" NAME="Close" VALUE="Close" align="top" border="0"/>
	</FORM>
                 
<?php 
}
else
{
	echo _("No participants found for that conference number");
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
