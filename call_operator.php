<?php

// gettext rework 

include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./phpagi/phpagi-asmanager.php");
//include ("./locale.php");


session_start();

getpost_ifset (array('action', 'confno', 'data', 'bookid', 'name', 'invite_num'));

// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// THIS VARIABLE DEFINE THE COLOR OF THE HEAD TABLE
$FG_TABLE_HEAD_COLOR = "#D1D9E7";


$FG_TABLE_EXTERN_COLOR = "#7F99CC"; //#CC0033 (Rouge)
$FG_TABLE_INTERN_COLOR = "#EDF3FF"; //#FFEAFF (Rose)


// THIS VARIABLE DEFINE THE COLOR OF THE HEAD TABLE
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#FFFFFF";
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#F2F8FF";

// THIS VARIABLE DEFINE THE COLOR OF THE ADMIN ROW
$FG_TABLE_ROW_COLOR_ADMIN = "#FCCDCA";


// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();


$FG_TABLE_COL[]=array (_("ID"), "user_id", "12%", "center", "", "19");
$FG_TABLE_COL[]=array (_("Channel"), "chan_name", "20%", "center", "", "30");
$FG_TABLE_COL[]=array (_("ConfNo"), "confno", "12%", "center", "", "30");
$FG_TABLE_COL[]=array (_("Mode"), "mode", "12%", "center", "", "30");



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



//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH="100%";



/* ACTION  *   *   *  * * * *********************************************************/
$name = iconv("cp1251","UTF-8",preg_replace("/[^\x30-\x39\x41-\x5A\x61-\x7A\x20\x5F\xA8\xB8\xC0-\xFF]/","",iconv("UTF-8","cp1251",$name)));
$name = substr($name,0,80);
$data = preg_replace("/[^\x30-\x39]/","",$data);
$bookid = preg_replace("/[^\x30-\x39]/","",$bookid);
$invite_num = preg_replace("/[^\x30-\x39]/","",$invite_num);
$invite_num = substr($invite_num,0,20);

if ($name == "") {
	$name = $invite_num;
}


if ($_SESSION['auth'] && $invite_num !="" && $data != "" && $bookid != "") {
	if ($action=='outboundcall' || $action=='quickcall'){

	
		$as = new AGI_AsteriskManager();
		// && CONNECTING
		$res = $as->connect();
		if (!$res){ echo _("Error connection to the manager")."!"; exit();}
	
		if ( CHAN_TYPE == "Local") {
			$channel = CHAN_TYPE . "/" . $invite_num . "@" . LOCAL_CONTEXT . "/n" ;
		} else {
			if ($invite_num==100) {
				$channel = "SIP/" . $invite_num ;
			} elseif (preg_match("/^83532(\d{6})/",$invite_num,$tmp)) {
				$channel = "SIP/oren-kamailio/" . $tmp[1] ;
			} else {
				//$channel = "SIP/buz-cisco/#2" . $invite_num ;
				$channel = "SIP/oren-kamailio/" . $invite_num ;
			}
		}
		$application = "MeetMe";
		$data = $data;
		$priority = 1;
		$context = OUT_CONTEXT;
		$timeout = 60000;
		$callerid = "\"$name\" <$invite_num>";
		//$variable = "CDR(bookId)=$bookid,CDR(CIDnum)=$invite_num,CDR(CIDname)=$name";
		$variable = "CDR(userfield)=$bookid,VOLRX=0,VOLTX=0"; //,DYNAMIC_FEATURES=volumerxdec#volumetxinc";
		//echo $callerid;
		$res = $as->Originate ($channel, $exten, $context, $priority, $timeout, $callerid, $variable, $account, $application, $data);
	
		$actiondone=1;
	
		// && DISCONNECTING	
		$as->disconnect();
	}
} else {
	$res['Message'] = "Naughty";
}

/*    *    *   *   *  * * * *********************************************************/




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

<body bgColor=#acbdee>


<br><br>

<!-- ** ** ** ** ** Part to display the conference user ** ** ** ** ** -->

<center>
<?php if ($action=="quickcall"){ 
	if ($res['Message']=="Originate successfully queued")
		$error = _("Call Placed");
	else
		$error = _("System error, try again later");  ?>
	<font color=red><b><?php echo $error?></b></font><br><br>
	<input type="submit" value="Вернуться для приглашения следующего" onclick="javascript:window.history.back();">
	<br>
	<input type="submit" value="Закрыть окно" onClick="window.close()" />
<?php } else { ?>

		  
		<FORM action=<?php echo $_SERVER[PHP_SELF]?> id=form1 method=post name=form1>
			<center>
			<font color=red><b><?php echo $res['Message']?></b></font>
			<TABLE width="626" border="1" cellpadding="2" cellspacing="2" bordercolor="#E2E2D3" style="padding: 5px 5px 7px;">			
			
			
	
				  <INPUT type="hidden" name="action" value="outboundcall">
				  <TBODY>
				    <TR>
					   <td width="165" valign="top" bgcolor="#D3D3F2">
                        <table width="100%"  border="0" cellpadding="2" cellspacing="0" ><tr><td><b><?php print _("CHANNEL"); ?> </b></td></tr></table>
					   </td>
                       <TD width="405" valign="top"> 					
                      		<INPUT name=channel  size=60 maxlength=100 value="">                         	
					   </TD>
					</TR>
					
					 <TR>
					   <td width="165" valign="top" bgcolor="#E2E2D3">
                        <table width="100%"  border="0" cellpadding="2" cellspacing="0" ><tr><td><b><?php print _("EXTEN"); ?> </b></td></tr></table>
					   </td>
                       <TD width="405" valign="top"> 					
                      		<INPUT name=exten  size=60 maxlength=100 value="">                         	
					   </TD>
					</TR>
					
					 <TR>
					   <td width="165" valign="top" bgcolor="#D3D3F2">
                        <table width="100%"  border="0" cellpadding="2" cellspacing="0" ><tr><td><b><?php print _("CONTEXT"); ?></b> </td></tr></table>
					   </td>
                       <TD width="405" valign="top"> 					
                      		<INPUT name=context  size=60 maxlength=100 value="">                         	
					   </TD>
					</TR>
					
					 <TR>
					   <td width="165" valign="top" bgcolor="#E2E2D3">
                        <table width="100%"  border="0" cellpadding="2" cellspacing="0" ><tr><td><b><?php print _("PRIORITY"); ?> <b></td></tr></table>
					   </td>
                       <TD width="405" valign="top"> 					
                      		<INPUT name=priority  size=60 maxlength=100 value="">                         	
					   </TD>
					</TR>
	
					 <TR>
					   <td width="165" valign="top" bgcolor="#D3D3F2">
                        <table width="100%"  border="0" cellpadding="2" cellspacing="0" ><tr><td><b><?php print _("TIMEOUT"); ?></b> </td></tr></table>
					   </td>
                       <TD width="405" valign="top"> 					
                      		<INPUT name=timeout  size=60 maxlength=100 value="">                         	
					   </TD>
					</TR>
					 <TR>
					   <td width="165" valign="top" bgcolor="#E2E2D3">
                        <table width="100%"  border="0" cellpadding="2" cellspacing="0" ><tr><td><b><?php print _("CALLERID"); ?></b> </td></tr></table>
					   </td>
                       <TD width="405" valign="top"> 					
                      		<INPUT name=callerid  size=60 maxlength=100 value="">                         	
					   </TD>
					</TR>
					 <TR>
					   <td width="165" valign="top" bgcolor="#D3D3F2">
                        <table width="100%"  border="0" cellpadding="2" cellspacing="0" ><tr><td><b><?php print _("VARIABLE"); ?></b> </td></tr></table>
					   </td>
                       <TD width="405" valign="top"> 					
                      		<INPUT name=variable  size=60 maxlength=100 value="">                         	
					   </TD>
					</TR>
					 <TR>
					   <td width="165" valign="top" bgcolor="#E2E2D3">
                        <table width="100%"  border="0" cellpadding="2" cellspacing="0" ><tr><td><b><?php print _("ACCOUNT"); ?></b> </td></tr></table>
					   </td>
                       <TD width="405" valign="top"> 					
                      		<INPUT name=account  size=60 maxlength=100 value="">                         	
					   </TD>
					</TR>
					 <TR>
					   <td width="165" valign="top" bgcolor="#D3D3F2">
                        <table width="100%"  border="0" cellpadding="2" cellspacing="0" ><tr><td><b><?php print _("APPLICATION"); ?></b> </td></tr></table>
					   </td>
                       <TD width="405" valign="top"> 					
                      		<INPUT name=application  size=60 maxlength=100 value="">                         	
					   </TD>
					</TR>
					 <TR>
					   <td width="165" valign="top" bgcolor="#E2E2D3">
                        <table width="100%"  border="0" cellpadding="2" cellspacing="0" ><tr><td><b><?php print _("DATA"); ?></b> </td></tr></table>
					   </td>
                       <TD width="405" valign="top"> 					
                      		<INPUT name=data  size=60 maxlength=100 value="">                         	
					   </TD>
					</TR>
					
					 <TR>
					   <td width="165" valign="top" bgcolor="#F2D3D3" colspan=2 align=right>
                        	<input type="submit" value="MAKE THE OUTBOUND CALL" />
					   </td>
                       
					</TR>
                    		
				</TBODY>
			</TABLE>
			</center>
		</FORM>
		

</td></tr></tbody></table>
</center>

<?php } ?>
<!-- END -->
</body>
</html>
