<?php
include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./lib/database.php");
include ("locale.php");

session_start();

getpost_ifset(array('DeleteNow', 'DeletebyId', 'sens', 'order', 'current_page'));

// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME=DB_TABLESCHED;
$CDR_TABLE_NAME=DB_TABLECDR;

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();

$FG_TABLE_COL[]=array (_("Conference #"), "confno", "15%", "left", "", "10");
$FG_TABLE_COL[]=array (_("Conference Name"), "pin", "15%", "middle", "", "10");
$FG_TABLE_COL[]=array (_("Starts"), "starttime", "25%", "middle", "SORT", "30");
$FG_TABLE_COL[]=array (_("Ends"), "endtime", "25%", "middle", "", "30");
$FG_TABLE_COL[]=array (_("Participants"), "maxusers", "15%", "", "", "4");

$FG_TABLE_DEFAULT_ORDER = "starttime";
$FG_TABLE_DEFAULT_SENS = "ASC";

// This Variable store the argument for the SQL query
$FG_QUERY='confno, confDesc, starttime, endtime, maxusers, bookId';


// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=15;
$FG_LIMITE_DISPLAY_BLANK_LINE=0;

// Number of column in the html table
$FG_NB_TABLE_COL=count($FG_TABLE_COL);

//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE=" - "._("Scheduled Conference")." : ";

//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH="95%";




if ($FG_DEBUG == 3) echo "<br>Table : $FG_TABLE_NAME  	- 	Col_query : $FG_QUERY";



if ( !isset ($order) || !isset ($sens) ){
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
}


/* ACTION  *   *   *  * * * *********************************************************/

if (isset($DeleteNow)){
	foreach ($DeletebyId as $i){
        	$FG_EDITION_CLAUSE=" bookId='$i' ";
		// Delete the Conference
		$query = "DELETE FROM $FG_TABLE_NAME WHERE bookId =?";
		$data = array($i);
		$result = $db->query($query, $data);
		//Delete the CDR records for this conference
		//$query = "DELETE FROM $CDR_TABLE_NAME WHERE bookId =?";
		$query = "DELETE FROM $CDR_TABLE_NAME WHERE userfield =?";
		$result = $db->query($query, $data);
		// Delete the email entries
		if (defined('AUTH_TYPE') && (AUTH_TYPE == 'sqldb')){
			$query = "DELETE FROM participants WHERE book_id =?";
			$result = $db->query($query, $data);
		}
	}
}


if (isset($confno)){
	$FG_CLAUSE = "(confno='$confno' OR confDesc LIKE '%$confno%')";
	if (strlen($confno)==0){
		$FG_CLAUSE = "";
	}
}

        //Look only for conferences user is owner of
if (defined('AUTH_TYPE') && ($_SESSION['privilege'] != "Admin")) {
        $FG_USER=$_SESSION['userid'];
        if (strlen($FG_CLAUSE)==0) {
		if (AUTH_TYPE == 'adLDAP')
			$FG_CLAUSE = "confOwner='$FG_USER'";
		else
                	$FG_CLAUSE = "clientId='$FG_USER'";
        } else {
		if (AUTH_TYPE == 'adLDAP')
                	$FG_CLAUSE .= " AND confOwner='$FG_USER'";
		else
                	$FG_CLAUSE .= " AND clientId='$FG_USER'";
        }
}

	if (isset($FG_CLAUSE))
		$FG_CLAUSE = "WHERE ".$FG_CLAUSE;
	else
		$FG_CLAUSE = "";

	if (!isset($current_page))
		$current_page=0;

 	$nb_record = $db->getOne("SELECT COUNT(*) FROM $FG_TABLE_NAME $FG_CLAUSE");

	$record_start = intval($current_page*$FG_LIMITE_DISPLAY);

	$query = "SELECT $FG_QUERY FROM $FG_TABLE_NAME $FG_CLAUSE ORDER BY $order $sens LIMIT $FG_LIMITE_DISPLAY OFFSET $record_start";
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
		<title><?php print GUI_TITLE; ?> <?php print _("control"); ?></title>
		<meta http-equiv="Content-Type" content="text/html">
		
		<link rel="stylesheet" type="text/css">
		<style type="text/css" media="screen">
			@import url("css/content.css");
			@import url("css/docbook.css");
		</style>
	
		<?php include ("lib/functions_js.php"); ?>
		<script language="JavaScript" type="text/JavaScript">
		<!--
		function MM_openBrWindow(theURL,winName,features) { //v2.0
		  window.open(theURL,winName,features);
		}
		
		var formblock;
		var forminputs;

		function prepare() {
			formblock= document.getElementById('deleteConf');
			if (formblock){
			forminputs = formblock.getElementsByTagName('input');
			}
		}

		var checkflag = "false";
		function select_all(name) {
		    if (checkflag == "false") {
			    for (i = 0; i < name.length; i++) {
				name[i].checked = true; 
			    }
			    checkflag = "true";
			    return <?php print "\""._("Uncheck All")."\""; ?>;
			} else {
                            for (i = 0; i < name.length; i++) {
				name[i].checked = false; 
                            }
                                checkflag = "false";
                                return <?php print "\""._("Check All")."\""; ?>;
			}
		}

		if (window.addEventListener) {
			window.addEventListener("load", prepare, false);
		} else if (window.attachEvent) {
			window.attachEvent("onload", prepare)
		} else if (document.getElementById) {
			window.onload = prepare;
		}

		//-->
		</script>
	</head>
	<body>



<br><br>

<!-- ** ** ** ** ** Part to display the conference  ** ** ** ** ** -->
<center>
		<?php if (is_array($list) && (!defined('AUTH_TYPE') || $_SESSION['auth'])){ ?>
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
			<FORM METHOD="POST" id="deleteConf" ACTION="<?php echo "$_SERVER[PHP_SELF]"; ?>">
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
					//	$FG_TABLE_COL[$i][1];			
					//	$FG_TABLE_COL[]=array ("Name", "name", "20%");
					?>				
				  
					
                  <TD width="<?php echo $FG_TABLE_COL[$i][2]; ?>" align=middle class="tableBody" style="PADDING-BOTTOM: 2px; PADDING-LEFT: 2px; PADDING-RIGHT: 2px; PADDING-TOP: 2px"> 
                    <center><strong> 
                    <?php if (strtoupper($FG_TABLE_COL[$i][4])=="SORT"){?>
                    <a href="<?php echo $_SERVER[PHP_SELF]."?s=1&t=0&current_page=$current_page&order=".$FG_TABLE_COL[$i][1]."&sens="; if ($sens=="ASC"){echo"DESC";}else{echo"ASC";} echo "&confno=$confno";
					echo "";?>"> 
                    <span class="liens"><?php } ?>
                    <?php echo $FG_TABLE_COL[$i][0]; ?> 
                    <?php if ($order==$FG_TABLE_COL[$i][1] && $sens=="ASC"){?>
                    &nbsp;<img src="images/icon_up_12x12.gif" width="12" height="12" border="0"> 
                    <?php }elseif ($order==$FG_TABLE_COL[$i][1] && $sens=="DESC"){?>
                    &nbsp;<img src="images/icon_down_12x12.gif" width="12" height="12" border="0"> 
                    <?php }?>
                    <?php  if (strtoupper($FG_TABLE_COL[$i][4])=="SORT"){?>
                    </span></a> 
                    <?php }?>
                    </strong></center></TD>
				   <?php } ?>
				   
				<?php
				
				
				  
				  	 $ligne_number=-1;					 
					 //print_r($list);
				  	 foreach ($list as $recordset){ 
						 $ligne_number++;
				?>
					<?php  if ($recordset[0]=="2"){ ?>
						<TR bgcolor="<?php echo $FG_TABLE_ROW_COLOR_ADMIN; ?>"  
						onMouseOver="bgColor='#FFA5A5'" onMouseOut="bgColor='<?php echo $FG_TABLE_ROW_COLOR_ADMIN; ?>'"> 
					<?php }else{ ?>
               		 	<TR bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]; ?>"  
						onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]; ?>'"> 
					<?php } ?>

						
							 
				  		<?php for($i=0;$i<$FG_NB_TABLE_COL;$i++){ ?>
						
						<?php  $record_display = $recordset[$i];
		if ($i == 0) { ?> 
			
                 		 <TD width="<?php echo $FG_TABLE_COL[$i][2]; ?>" vAlign=middle 
						 align="<?php echo $FG_TABLE_COL[$i][3]; ?>" class=tableBody>
							<INPUT TYPE=CHECKBOX NAME="DeletebyId[]" 
								VALUE="<?php print $recordset[5] ?>"><?php echo stripslashes($record_display); ?></TD>
		<?php } else { ?>
                 		 <TD width="<?php echo $FG_TABLE_COL[$i][2]; ?>" vAlign=middle align="<?php echo $FG_TABLE_COL[$i][3]; ?>" class=tableBody><?php echo stripslashes($record_display); ?></TD>
		<?php }
				   	 } ?>	
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
                    <?php if ($current_page>0){?>
                    <img src="images/fleche-g.gif" width="5" height="10"> 
						<a href="<?php echo "$_SERVER[PHP_SELF]?s=1&t=0&order=$order&sens=$sens&current_page=" . ($current_page-1); ?><?php if(isset($confno)) echo "&confno=$confno";?>"> 
                    <?php print _("Previous"); ?> </a> - 
                    <?php }?>
                    <?php echo ($current_page+1) . " / " . $nb_record_max; ?> 
                    <?php if ($current_page<$nb_record_max-1){?>
                    - <a href="<?php echo "$_SERVER[PHP_SELF]?>?s=1&t=0&order=" . $order?>&sens=<?php echo $sens?>&current_page=<?php echo ($current_page+1)?><?php if (isset($confno)) echo "&confno=$confno";?>"> 
                    <?php print _("Next"); ?> </a> <img src="images/fleche-d.gif" width="5" height="10"> 
                    </B></SPAN> 
                    <?php }?>
                  </TD>
              </TBODY>
            </TABLE></TD>
        </TR>
      </table>
		<INPUT TYPE="Button" VALUE=<?php print "\""._("Check All")."\""; ?> onclick="this.value=select_all(this.form['DeletebyId[]'])">
		<INPUT TYPE="Submit" NAME="DeleteNow" VALUE=<?php print "\""._("Delete Selected")."\""; ?> onClick="if(!confirm('<?php print _("Are you sure")."?"; ?>')) return false">
		</FORM>
                  
<?php }else{ ?>
<?php echo _("No conference with that conference number") ; ?>
<?php } ?>
</center>

</td></tr></tbody></table>
</center>
<br></br>&nbsp;&nbsp;
<br></br>&nbsp;&nbsp;

		<!-- END -->
	</body>
</html>
