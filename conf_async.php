<?php
include (dirname(__FILE__)."/phpagi/phpagi-asmanager.php");
include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./lib/database.php");
include ("./locale.php");

session_start(); 
getpost_ifset(array('confno','action','user_id'));


// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();


$FG_TABLE_COL[]=array (_("ID"), "user_id", "5%", "center", "", "10");
$FG_TABLE_COL[]=array (_("CallerId"), "callerid", "50%", "center", "", "80");
$FG_TABLE_COL[]=array (_("Duration"), "duration", "12%", "center", "", "30");
$FG_TABLE_COL[]=array (_("Mode"), "mode", "10%", "center", "", "30");



// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=30;
$FG_LIMITE_DISPLAY_BLANK_LINE=5;

// Number of column in the html table
$FG_NB_TABLE_COL=count($FG_TABLE_COL);

// The variable $FG_EDITION define if you want process to the edition of the database record
$FG_VOICE_RIGHT=true;
$FG_KICKOUT=true;

//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;
if ($FG_VOICE_RIGHT || $FG_KICKOUT) $FG_TOTAL_TABLE_COL++;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE=" - Conference Users : [ROOM : $confno] - ";

//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH="100%";


/* ACTION  *   *   *  * * * *********************************************************/

if ( isset ($sens) && $sens != "ASC" && $sens != "DESC")
	$sens = "ASC";

$temp = $confno;

if (!is_numeric(urlencode($temp)))
	$confno = 0;

if ( !(isset($order)) || $order != $FG_TABLE_COL[0][1])
	$order = $FG_TABLE_COL[0][1];


if (isset($confno)){

	$as = new AGI_AsteriskManager();
	// && CONNECTING
	$res = $as->connect();
	if (!$res){ echo _("Error connection to the manager")."!"; exit();}
	
//First check if user is owner of conference
        $showConference = 1;
        if (defined('AUTH_TYPE') && ($_SESSION['privilege'] != "Admin")) {
		$FG_USER=$_SESSION['userid'];
                $FG_TABLE_CLAUSE=" confOwner='$FG_USER'";
		
		if (isset($confno) && intval($confno))
			$FG_TABLE_CLAUSE=  $FG_TABLE_CLAUSE . " AND confno='$confno'";

                $FG_TABLE_NAME = DB_TABLESCHED;
                $FG_COL_QUERY='confno';
		if (!($db->getOne("SELECT COUNT(*) FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE"))) {
                        $FG_ERROR = _("You are not the owner of this conference or the conference does not exist")."!";
                        $showConference = 0;
                }
        }	
        if ($showConference==1) {
                // Conference exists and user is owner -> get Data	
		$res = $as->Command('meetme list '.$confno.' concise');
		$line= split("\n", $res['data']);
	
		$nbuser=0;
		foreach ($line as $myline){
			$linevalue = explode("!", $myline);
			if (is_numeric($linevalue[0])){
			    $meetmechannel [$nbuser][0] = $linevalue[0];
			    if ( $linevalue[1] == $linevalue[2] ){
				     $meetmechannel [$nbuser][1] = "Без имени &lt;".$linevalue[1]."&gt;";
			    } else {
				      $meetmechannel [$nbuser][1] = $linevalue[2]." &lt;".$linevalue[1]."&gt;";
			    }

			    $meetmechannel [$nbuser][2] = $linevalue[9];
			    $meetmechannel [$nbuser][5] = $linevalue[3];

			    if ($linevalue[6]=="") 
				if ($linevalue[8]=="1")// || $linevalue[8]=="-1")
					$meetmechannel [$nbuser][3] = "Talking";
				else
					$meetmechannel [$nbuser][3] = "UnMuted";
			    else 
				if ($linevalue[7]=="")
					$meetmechannel [$nbuser][3] = "Muted";
				else
					$meetmechannel [$nbuser][3] = "Requests Floor";
			
			    if ($linevalue[4]=="") $meetmechannel [$nbuser][4] = "User";
			    else $meetmechannel [$nbuser][4] = "Admin";			
			    $nbuser++;
		    }
		}
		//sleep (0.5);
	}	
		
    /*  Concise MeetMe List output
    [0] => Caller #
    [1] => Callerid Number
    [2] => Callerid Name
    [3] => Channel:
    [4] => 1 for Admin, NULL for User
    [5] => 1 for Monitor, Null otherwise
    [6] => 1 for Muted, NULL for UnMuted
    [7] => 1 for Resquests Floor, 0 otherwise
    [8] => 1 for 'Is Talking', 0 otherwise, -1 for ?
    [9] => Call duration
    */	
	$as->disconnect();
			
}

/*    *    *   *   *  * * * *********************************************************/

if (isset($meetmechannel)) {
	$list=$meetmechannel;
	$nb_record=count($meetmechannel);
} else {
	$list = "";
	$nb_record  = 0;
}

if ($FG_DEBUG == 3) echo "<br>Nb_record : $nb_record";
if ($FG_DEBUG == 3) echo "<br>Nb_record_max : $nb_record_max";




?>

<body bgColor=#FFFFFF>


<br><br>
<!-- ** ** ** ** ** Part to display the conference user ** ** ** ** ** -->
<?php if (isset($confno)) { ?>
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
				
				  <TD width="<?php echo $FG_ACTION_SIZE_COLUMN; ?>" align=center class="tableBodyRight" style="PADDING-BOTTOM: 2px; PADDING-LEFT: 2px; PADDING-RIGHT: 2px; PADDING-TOP: 2px"></TD>					
				  
                  <?php 
				  	if (is_array($list) && count($list)>0){
					
				  	for($i=0;$i<$FG_NB_TABLE_COL;$i++){ 
						//$FG_TABLE_COL[$i][1];			
						//$FG_TABLE_COL[]=array ("Name", "name", "20%");
					?>				
				  
					
                  <TD width="<?php echo $FG_TABLE_COL[$i][2]; ?>" align=middle class="tableBody" style="PADDING-BOTTOM: 2px; PADDING-LEFT: 2px; PADDING-RIGHT: 2px; PADDING-TOP: 2px"> 
                    <center><strong> 
                    <?php if (strtoupper($FG_TABLE_COL[$i][4])=="SORT"){?>
                    <a href="<?php echo $_SERVER[PHP_SELF]."?s=1&t=0&order=".$FG_TABLE_COL[$i][1]."&sens="; if ($sens=="ASC"){echo"DESC";}else{echo"ASC";} 
					echo "&confno=$confno";?>"> 
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
				   
				   <?php if ($FG_VOICE_RIGHT || $FG_KICKOUT){ ?>
                  		<TD></TD>                  
					<?php } ?>	
                <TR> 
                  <TD bgColor=#e1e1e1 colSpan=<?php echo $FG_TOTAL_TABLE_COL+1; ?> height=1><IMG height=1 src="images/clear.gif" width=1></TD>
                </TR>
				<?php
				
				
				  
				  	 $ligne_number=-1;					 
					 //print_r($list);
				  	 foreach ($list as $recordset){ 
						 $ligne_number++;
				?>
					<?php if ($recordset[4]=="Admin"){ ?>
						<TR bgcolor="<?php echo $FG_TABLE_ROW_COLOR_ADMIN; ?>"  onMouseOver="bgColor='#FFA5A5'" onMouseOut="bgColor='<?php echo $FG_TABLE_ROW_COLOR_ADMIN; ?>'"> 
					<?php }else{ ?>
               		 	<TR bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]; ?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]; ?>'"> 
					<?php } ?>
						
						<TD vAlign=middle align="<?php echo $FG_TABLE_COL[$i][3]; ?>" class=tableBody>							
							<?php if ($recordset[4]=="Admin"){
									$icon=$icons_list['0'];
								}else{
									$icons_indice= ($recordset[0] % (count($icons_list)-1))+1;									
									$icon=$icons_list[$icons_indice];
								}
							?>
							<?php if ($recordset[4]=="Admin") echo "Admin"; ?>
						</TD>
							 
				  		<?php for($i=0;$i<$FG_NB_TABLE_COL;$i++){ ?>
						
						  
						<?php	//$FG_TABLE_COL[$i][1];			
							//$FG_TABLE_COL[]=array ("Name", "name", "20%");
							
							
							if ($FG_TABLE_COL[$i][6]=="lie"){


									$instance_sub_table = new Table($FG_TABLE_COL[$i][7], $FG_TABLE_COL[$i][8]);
									$sub_clause = str_replace("%id", $recordset[$i], $FG_TABLE_COL[$i][9]);																																	
									$select_list = $instance_sub_table -> Get_list ($sub_clause, null, null, null, null, null, null);
									
									
									$field_list_sun = split(',',$FG_TABLE_COL[$i][8]);
									$record_display = $FG_TABLE_COL[$i][10];
									//echo $record_display;
									
									for ($l=1;$l<=count($field_list_sun);$l++){										
										$record_display = str_replace("%$l", $select_list[0][$l-1], $record_display);	
									}
								
							}elseif ($FG_TABLE_COL[$i][6]=="list"){
									$select_list = $FG_TABLE_COL[$i][7];
									$record_display = $select_list[$recordset[$i]][0];
							
							}else{
									$record_display = $recordset[$i];
							}
							
							
							if ( is_numeric($FG_TABLE_COL[$i][5]) && (strlen($record_display) > $FG_TABLE_COL[$i][5])  ){
								$record_display = substr($record_display, 0, $FG_TABLE_COL[$i][5]-3)."";  
															
							}
							
							
				 		 ?>
                 		 <TD vAlign=middle align="<?php echo $FG_TABLE_COL[$i][3]; ?>" class=tableBody><?php echo stripslashes($record_display); ?></TD>
				 		 <?php } ?>
						 
						 
	    	             <TD>
					<?php if (($FG_VOICE_RIGHT || $FG_KICKOUT )){ ?>
						 <?php if ($FG_VOICE_RIGHT){ ?>
							
							<a href="#" onClick="conf_action('1','1','1','<?PHP echo $recordset[5]; ?>','rxdec')">[RX-]</a>
							-
							<a href="#" onClick="conf_action('1','1','1','<?PHP echo $recordset[5]; ?>','rxinc')">[RX+]</a>
							-
							<a href="#" onClick="conf_rxtxcurent('<?PHP echo $recordset[5]; ?>','rxcurrent')">[RX?]</a>
							<br>
							<a href="#" onClick="conf_action('1','1','1','<?PHP echo $recordset[5]; ?>','txdec')">[TX-]</a>
							-
							<a href="#" onClick="conf_action('1','1','1','<?PHP echo $recordset[5]; ?>','txinc')">[TX+]</a>
							-
							<a href="#" onClick="conf_rxtxcurent('<?PHP echo $recordset[5]; ?>','txcurrent')">[TX?]</a>
							<br>
							<?php if ($recordset[3]=='Muted' || $recordset[3]=='Requests Floor'){ ?>
							 <a href="#" onClick="conf_action('unmute','<?PHP echo $confno; ?>','<?PHP echo intval($recordset[0]); ?>')">[UNMUTE]</a>
							<?php }else{ ?>
							 <a href="#" onClick="conf_action('mute','<?PHP echo $confno; ?>','<?PHP echo intval($recordset[0]); ?>'); ">[MUTE]</a>
							<?php } ?>
							
						<?php } ?>
						-
						<?php if ($FG_KICKOUT){ ?>
							 <a href="#"  onClick="javascript:conf_action('kick','<?PHP echo $confno; ?>','<?PHP echo intval($recordset[0]); ?>')">[KICK]</a>

						<?php } ?>
				   		
				   	<?php } ?>	
				   		 </TD>  
                
						 
                  
					</TR>
				<?php
					 }//foreach ($list as $recordset)
					 while ($ligne_number < $FG_LIMITE_DISPLAY_BLANK_LINE){
					 	$ligne_number++;
				?>
					<TR bgcolor="<?php $FG_TABLE_ALTERNATE_ROW_COLOR[$ligne_number%2]; ?>"> 
				  		<?php for($i=0;$i<$FG_NB_TABLE_COL+1;$i++){ 
							//$FG_TABLE_COL[$i][1];			
							//$FG_TABLE_COL[]=array ("Name", "name", "20%");
				 		 ?>
                 		 <TD vAlign=top class=tableBody>&nbsp;</TD>
				 		 <?php } ?>
                 		 <TD align="center" vAlign=top class=tableBodyRight>&nbsp;</TD>				
					</TR>
									
				<?php					 
					 } //END_WHILE
					 
				  }else{
				  		echo "No data found !!!";				  
				  }//end_if
				 ?>
                
                <TR> 
                  <TD class=tableDivider colSpan=<?php echo $FG_TOTAL_TABLE_COL+1; ?>><IMG height=1 
                              src="images/clear.gif" 
                              width=1></TD>
							 
                </TR>
				
              </TBODY>
            </TABLE></td>
        </tr>
      </table>
<?php }else{ ?>
<?php echo _("No user in this conference room")."."; ?>
<?php } ?>
</center>
<?php } // if (isset($confno))  ?>

</td></tr></tbody></table>
</center>
<br></br>&nbsp;&nbsp;
<br></br>&nbsp;&nbsp;

