<?php

// gettext reworked

include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./lib/database.php");
include ("locale.php");

session_start();
if (AUTH_TYPE == "sqldb")
	$clientId = $_SESSION['clientid'];
$dowork = false;

if ( !isset($mode_list))
	$mode_list = "";
$recur = 0;

getpost_ifset(array('confno','pin','adminpin','confOwner','confDesc','Hour','Min','month','day','year','AMPM','ConfHour','ConfMin','confdate','maxUser','add','bookId','update','recur','recurLbl','recurPrd','adminopts','opts','updateSeries','Extend'));
getpost_ifset(array('fname', 'lname', 'email', 'phone', 'nopass'));
// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME=DB_TABLESCHED;

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();


$FG_TABLE_COL[]=array (_("ID"), "bookId", "8%", "center", "", "30");
$FG_TABLE_COL[]=array (_("ConfId"), "confno", "12%", "center", "", "19");
$FG_TABLE_COL[]=array (_("Password"), "pin", "12%", "center", "", "30");
$FG_TABLE_COL[]=array (_("starttime"), "starttime", "15%", "center", "", "30");
$FG_TABLE_COL[]=array (_("endtime"), "endtime", "15%", "center", "", "30");
$FG_TABLE_COL[]=array (_("Callers"), "maxUser", "12%", "center", "", "30","list", $mode_list);



$FG_TABLE_DEFAULT_ORDER = "bookId";
$FG_TABLE_DEFAULT_SENS = "DESC";

// This Variable store the argument for the SQL query
$FG_COL_QUERY='bookId, clientId, confno, pin, adminpin, starttime, endtime, dateReq, maxUser, confOwner, confDesc, adminopts, opts, sequenceNo, recurInterval';


// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=30;
$FG_LIMITE_DISPLAY_BLANK_LINE=5;

// Number of column in the html table

//This variable will store the total number of column
$FG_NB_TABLE_COL=count($FG_TABLE_COL);
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE=" - "._("Scheduled Conferences")." : - ";

//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH="100%";

if ($FG_DEBUG == 3) echo "<br>Table : ".$FG_TABLE_NAME."  	- 	Col_query : ".$FG_COL_QUERY;

//if ( is_null ($order) || is_null($sens) ){
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
//}


/* ACTION  *   *   *  * * * *********************************************************/
if (isset($add)){
	$dowork = true;
	
	if (!use24h()){
		if ($AMPM == "PM" && $Hour <= 11){
			$Hour=$Hour+12;
		} elseif ($AMPM == "AM" && $Hour == 12){
			$Hour=0;
		}
	}
	
	$permamentYears=10; //a 10 years conference can be considered permanent
        $start=mktime($Hour,$Min,0,$month,$day,$year);
	$starttime = date("Y-m-d H:i:s", $start);
	$tmpTime = (strtotime($starttime)+(3600*$ConfHour)+(60*$ConfMin));
	$tmpTime = $tmpTime - ($tmpTime%60);
	$endtime = date("Y-m-d H:i:s", $tmpTime);
	$dateReq = date("Y-m-d H:i:s");
	$permanent=mktime($Hour,$Min,0,$month,$day,$year); 
	$permanentTime=date("Y-m-d H:i:s", $permanent); 
	if($ConfHour==0&$ConfMin==0){ 
		$tmpTime=(strtotime($permanentTime)+(31556926*$permamentYears)); 
	}
	
	if($tmpTime < time()){
		$error = _("Sorry, you are not allowed to schedule a conference in the past endtime").": $endtime";
	} else {
	if ( intval($confno)== 0){
		$error = _("A conference number must be numeric").".";
	}

// Set MeetMe flags based on checkboxs.  d flag is mandatory
	if (isset($adminopts)){
		if(is_array($adminopts))
	    		$adminopts = arraytostring($adminopts);
	} else {
		$adminopts = "";
	}
	if (isset($opts)) {
		if (is_array($opts))
		    $opts = arraytostring($opts);
	} else {
		$opts = "";
	}

	$adminopts = SAFLAGS . $adminopts;
	$opts = SUFLAGS . $opts;

	if (strchr($adminopts, "r"))
		$opts = $opts . "r";

	if(PASSWORD_OPTION == "YES")
	{
		if($nopass)
		{
			$adminpin = "";
			$pin = "";
		}
		else
		{
			if (strlen($adminpin) == 0)
			{
				$adminpin=randNum(1000, 9999);
			}
			if (strlen($pin) == 0)
			{
				$pin = $adminpin;
				while($pin == $adminpin)
				{
					$pin=randNum(1000, 9999);
				}
			}
        	}
	}
	else
	{
		if ( (strlen($adminpin) != 0) && (strlen($pin) == 0)){
			if(intval($adminpin)){
			$pin=randNum(1000, 9999);
			} else {
			$error = _("Conference PINs must be numeric only");
			}
		}
		if (strlen($pin) && !(intval($pin)))
			$error = _("Conference PINs must be numeric only");
		if (strlen($adminpin) && !(intval($adminpin)))
			$error = _("Conference PINs must be numeric only");
		if (intval($adminpin) && (intval($pin) == intval($adminpin)))
			$error = _("Moderator and user PINs must not be equal");
		if (!(strlen($adminpin)) && (strchr($opts, "w")))
			$error = _("Moderator PIN required if  'Wait for Leader is set'");

	}
	if((intval($maxUser) < 2))
		$error = _("You must reserve at least 2 seats in this conference");

	if(defined('MAX_CALLER_LIMT')) {
		$FG_TABLE_CLAUSE="((starttime<='$starttime' AND endtime>='$starttime') OR (starttime<='$starttime' AND endtime>='$endtime') OR (starttime>='$starttime' AND endtime<='$endtime') OR (starttime<='$endtime' AND endtime>='$endtime'))";

		$prev_seats = $db->getOne("SELECT maxUser FROM $FG_TABLE_NAME WHERE confno='$confno' AND bookid='$bookid'");
		$used_seats = $db->getOne("SELECT SUM(maxUser) FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE");

		if( (intval($maxUser) + intval($used_seats) - intval($prev_seats)) > MAX_CALLER_LIMT)
			$error = "The requested number of participents, ". $maxUser .", would exceed the system limit of: " . MAX_CALLER_LIMT . " , for this time period. <br>" . (intval(MAX_CALLER_LIMT) - $used_seats) . " seats are available.";
	}

	$FG_TABLE_CLAUSE="confno='$confno' AND ((starttime<='$starttime' AND endtime>='$starttime') OR (starttime<='$starttime' AND endtime>='$endtime') OR (starttime>='$starttime' AND endtime<='$endtime') OR (starttime<='$endtime' AND endtime>='$endtime'))";

//placeholder for original Start and End times
   $st = $starttime;
   $et = $endtime;


//Look for another conference with the same id at the same time
	If (intval($recur)) {
	   for ($i=0; $i < count($recurLabel); $i++){
		if ($recurLbl == $recurPeriod[$i]){
			$recurInt = intval($recurInterval[$i]);
		}
	}
	   $stemp = (strtotime($starttime));
	   $etemp = (strtotime($endtime));
	   for ($i=0; $i < intval($recurPrd); $i++){
		$ctemp = $db->getOne("SELECT COUNT(*) FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE");
		$conflict += (intval($ctemp));
		$stemp += $recurInt;
		$etemp += $recurInt;
                $starttime= date("Y-m-d H:i:s", $stemp);
                $endtime= date("Y-m-d H:i:s", $etemp);

	   }
	} else {
	    $ctemp = $db->getOne("SELECT COUNT(*) FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE");
	    $conflict = (intval($ctemp));
	    $recurPrd = 1;
	}

//If the conference is unique, we can insert it
	if (!isset($error)){
        if ($conflict != 0){
	   $error = _("Your conference is not unique.  Please use a different start time or conference ID");
	   } else {
	   $stemp = (strtotime($st));
	   $etemp = (strtotime($et));
   	   $starttime = $st;
   	   $endtime = $et;
	   $status = "A";
	   $dateMod=getConfDate($today);
	   $startHour = substr($starttime, 10, 18); 
	   $endHour = substr($endtime, 10, 18); 

	   if (!$recurInt)
		$recurInt = 0;
	
	   for ($i=0; $i < intval($recurPrd); $i++){
		if ($clientId){
        		$param_columns ="clientId,confno,pin,adminpin,starttime,endtime,dateReq,dateMod,maxUser,status,confOwner,confDesc,adminopts,opts,sequenceNo,recurInterval"; 
        		$param_update ="'$clientId','$confno','$pin','$adminpin','$starttime','$endtime','$dateReq','$dateMod','$maxUser','$status','$confOwner','$confDesc','$adminopts','$opts','$i','$recurInt'"; 
		} else {
        		$param_columns ="confno,pin,adminpin,starttime,endtime,dateReq,dateMod,maxUser,status,confOwner,confDesc,adminopts,opts,sequenceNo,recurInterval"; 
        		$param_update ="'$confno','$pin','$adminpin','$starttime','$endtime','$dateReq','$dateMod','$maxUser','$status','$confOwner','$confDesc','$adminopts','$opts','$i','$recurInt'"; 

		}
		$query = "INSERT INTO $FG_TABLE_NAME($param_columns) VALUES ($param_update)";
		$result = $db->query($query);
		$stemp += $recurInt;
		$etemp += $recurInt;
		$starttime= date("Y-m-d ", $stemp) . $startHour;
		$endtime= date("Y-m-d ", $etemp) . $endHour;
		$query = "SELECT max(bookId) AS mbid FROM booking";
		$result = $db->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$bookId = $row['mbid'];

		if (strchr($opts, "r") || strchr($adminopts, "r")) {
			$recordingfilename = RECORDING_PATH . "meetme-conf-rec-". $confno . "-" . $bookId;
			$query = "UPDATE $FG_TABLE_NAME SET recordingfilename='$recordingfilename' where bookId='$bookId'";
			$result = $db->query($query);
		}

		if($i == 0)
		{
			$em_bookId = $bookId;
		}
		$invitees = count($email);
		for($j=0;$j < $invitees; $j++)
		{
			if(strlen(trim($email[$j])))
			{
				$query = "SELECT id FROM user WHERE email =? ";
				$data = array($email[$j]);
				$result = $db->query($query, $data);
				if($result->numRows())
				{
					$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
					$puid = $row['id'];
				}
				else
				{
					$phone[$j] = addslashes($phone[$j]);
					$query = "INSERT INTO user (first_name, last_name, email, telephone) VALUES ('$fname[$j]','$lname[$j]','$email[$j]','$phone[$j]')";
					$result = $db->query($query);
					$query = "SELECT id FROM user WHERE email =? ";
					$data = array($email[$j]);
					$result = $db->query($query);
					$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
					$puid = $row['id'];
				}
				$query = "SELECT user_id FROM participants WHERE user_id =? AND book_id =?";
				$data = array($puid, $bookId);
				$result = $db->query($query, $data);
				if(!$result->numRows())
				{
					$query = "INSERT INTO participants (user_id, book_id) VALUES ('$puid', '$bookId')";
					$result = $db->query($query);
				}
			}
		}
		
	   } 
	//Restore Start and End time for display purposes
	}
   $starttime = $st;
   $endtime = $et;
	}
    }
}

if (isset($update)){
	$dowork = true;
	$loopCount = 1;
	$em_bookId[0] = $bookId;

        $query = "SELECT confno,starttime,dateReq FROM booking WHERE bookId =?";
	$data = array($bookId);
        $result = $db->query($query, $data);
	$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
	if ( $confno != $row['confno'] )
	{
	   $searchconfno = $row['confno'];
	}
	else
	{
	    $searchconfno = $confno;
	}

	//Only update future conferences
	$searchTime = $row['starttime'];
	$dateReq = $row['dateReq'];

	$query = "SELECT bookId,starttime,sequenceNo,recurInterval FROM booking WHERE confno =? AND dateReq =? AND starttime >=? ORDER BY sequenceNo";
	$data = array($searchconfno, $dateReq, $searchTime);
	$result = $db->query($query, $data);
	$i=0;
	while ( $row = $result->fetchRow(DB_FETCHMODE_ASSOC))
	{
		$em_bookId[$i] = $row['bookId'];
		$em_sT[$i] = $row['starttime'];
		$em_sqNo[$i] = intval($row['sequenceNo']);
		$em_rIntv[$i++] = intval($row['recurInterval']);
	}
	$recurInt = $em_rIntv[0];
	$recurPrd = intval($result->numRows());
	$loopCount = $recurPrd;

	if (!use24h()){
		if ($AMPM == "PM" && $Hour <= 11){
			$Hour=$Hour+12;
		} elseif ($AMPM == "AM" && $Hour == 12){
			$Hour=0;
		}
	}

        $start=mktime($Hour,$Min,0,$month,$day,$year);
        $starttime= date("Y-m-d H:i:s", $start);
        $tmpTime = (strtotime($starttime)+(3600*$ConfHour)+(60*$ConfMin));
        $tmpTime = $tmpTime - ($tmpTime%60);
        $endtime= date("Y-m-d H:i:s", $tmpTime);

	if($tmpTime < time()){
		$error = _("Sorry, you are not allowed to  scheduled a conference in the past");
	} else {
	if ( intval($confno)== 0){
		$confno=randNum(10000, 99999);
	}


// Set MeetMe flags based on checkboxs.  d flag is mandatory
        if (is_array($adminopts))
            $adminopts = arraytostring($adminopts);
        if (is_array($opts))
            $opts = arraytostring($opts);

	$adminopts = SAFLAGS . $adminopts;
	$opts = SUFLAGS . $opts;

	if(PASSWORD_OPTION == "YES")
	{
		if($nopass)
		{
			$adminpin = "";
			$pin = "";
		}
		else
		{
			if (strlen($adminpin) == 0)
			{
				$adminpin=randNum(1000, 9999);
			}
			if (strlen($pin) == 0)
			{
				$pin = $adminpin;
				while($pin == $adminpin)
				{
					$pin=randNum(1000, 9999);
				}
			}
        	}
	}
	else
	{
		if ( (strlen($adminpin) != 0) && (strlen($pin) == 0)){
			if(intval($adminpin)){
			$pin=randNum(1000, 9999);
			} else {
			$error = _("Conference PINs must be numeric only");
			}
		}
		if (strlen($pin) && !(intval($pin)))
			$error = _("Conference PINs must be numeric only");
		if (strlen($adminpin) && !(intval($adminpin)))
			$error = _("Conference PINs must be numeric only");
		if (intval($adminpin) && (intval($pin) == intval($adminpin)))
			$error = _("Moderator and user PINs must not be equal");
		if (!(strlen($adminpin)) && (strchr($opts, "w")))
			$error = _("Moderator PIN required if  'Wait for Leader is set'");
	}

	if((intval($maxUser) < 2))
		$error = _("You must reserve at least 2 seats in this conference");


	$FG_TABLE_CLAUSE="confno='$confno' AND bookId<>'$bookId' AND ((starttime<='$starttime' AND endtime>='$starttime') OR (starttime<='$starttime' AND endtime>='$endtime') OR (starttime>='$starttime' AND endtime<='$endtime') OR (starttime<='$endtime' AND endtime>='$endtime'))";

	//placeholder for original Start and End times
	$st = $starttime;
	$et = $endtime;

        if (intval($updateSeries))
        {
           $stemp = (strtotime($starttime));
           $etemp = (strtotime($endtime));
           for ($i=0; $i < $loopCount; $i++){
		$ctemp = $db->getOne("SELECT COUNT(*) FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE");
                $conflict += (intval($ctemp));
                $stemp = strtotime($starttime)+($em_sqNo[$i]*$recurInt);
                $etemp = strtotime($endtime)+($em_sqNo[$i]*$recurInt);
                $starttime= date("Y-m-d H:i:s", $stemp);
                $endtime= date("Y-m-d H:i:s", $etemp);

           }
	   $starttime = $st;
	   $endtime = $et;

	}
	else
	{
           $conflict = $db->getOne("SELECT COUNT(*) FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE");
	   $loopCount = 1;
	}

	if (!($error)){
        if (intval($conflict) != 0){ 
	   $error = _("Your conference is not unique.  Please a different start time or conference ID");
	   } else {

	        $startHour = substr($st, 10, 18); 
	        $endHour = substr($et, 10, 18); 

		for ($i = 0; $i < $loopCount; $i++)
		{
                	$stemp = strtotime($st)+(($em_sqNo[$i]-$em_sqNo[0])*$recurInt);
                	$etemp = strtotime($et)+(($em_sqNo[$i]-$em_sqNo[0])*$recurInt);
			$starttime= date("Y-m-d ", $stemp) . $startHour;
			$endtime= date("Y-m-d ", $etemp) . $endHour;
			$FG_EDITION_CLAUSE=" bookId='$em_bookId[$i]' ";
			
			if (strchr($opts, "r") || strchr($adminopts, "r")) {
				$recordingfilename = RECORDING_PATH . "meetme-conf-rec-". $confno . "-" . $bookId;
        			$param_update ="confno='$confno',pin='$pin',adminpin='$adminpin', starttime='$starttime', endtime='$endtime', maxUser='$maxUser', confOwner='$confOwner', confDesc='$confDesc', adminopts='$adminopts', opts='$opts', recordingfilename='$recordingfilename'"; 
			} else {
        			$param_update ="confno='$confno',pin='$pin',adminpin='$adminpin', starttime='$starttime', endtime='$endtime', maxUser='$maxUser', confOwner='$confOwner', confDesc='$confDesc', adminopts='$adminopts', opts='$opts'"; 
			}
			$query = "UPDATE $FG_TABLE_NAME SET $param_update WHERE $FG_EDITION_CLAUSE";
			$result = $db->query($query);

			$invitees = count($email);
			if($invitees > 0)
			{
				$query = "DELETE FROM participants WHERE book_id =?";
				$data = array($bookId);
				$result = $db->query($query, $data);
			}
			for($j=0;$j < $invitees; $j++)
			{
				if(strlen(trim($email[$j])))
				{
					$query = "SELECT id FROM user WHERE email =?";
					$data = array($email[$j]);
					$result = $db->query($query, $data);
					if($result->numRows())
					{
						$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
						$puid = $row['id'];
					}
					else
					{
						$phone[$j] = addslashes($phone[$j]);
						$query = "INSERT INTO user (first_name, last_name, email, telephone) VALUES ('$fname[$j]','$lname[$j]','$email[$j]','$phone[$j]')";
						$result = $db->query($query);
						$query = "SELECT id FROM user WHERE email =?";
						$data = array($email[$j]);
						$result = $db->query($query, $data);
						$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
						$puid = $row['id'];
					}
                               		$query = "SELECT user_id FROM participants WHERE user_id =? AND book_id =?";
					$data = array($puid, $bookId);
                               		$result = $db->query($query, $data);
                               		if(!$result->numRows())
                               		{
                                       		$query = "INSERT INTO participants (user_id, book_id) VALUES ('$puid', '$bookId')";
                                       		$result = $db->query($query);
                               		}
				}
			}
		} 
	}
	}
}
}

if (isset($Extend)){
	$now=getConfDate($today);
        if (isset($confno)){
                $FG_TABLE_CLAUSE = "confno='$confno'AND starttime<='$now' AND endtime>='$now'";
		$FG_COL_QUERY='bookId, endtime';
        }


	$query = "SELECT $FG_COL_QUERY FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE";
	$result = $db->query($query);

	$recordset = $result->fetchRow(DB_FETCHMODE_ASSOC);
	$bookId=$recordset[bookId];
	$endtime=$recordset[endtime];

        $FG_EDITION_CLAUSE=" bookId='$bookId' ";

        $tmpTime = (strtotime($endtime)+600);
        $endtime= date("Y-m-d H:i:s", $tmpTime);
        $param_update ="endtime='$endtime'";
	$query = "UPDATE $FG_TABLE_NAME SET $param_update WHERE $FG_EDITION_CLAUSE";
	$result = $db->query($query);


}

/*    *    *   *   *  * * * *********************************************************/
	
if ($FG_DEBUG >= 1) var_dump ($list);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title><?php echo GUI_TITLE; ?> control</title>
		<meta http-equiv="Content-Type" content="text/html">
		<link rel="stylesheet" type="text/css">
		<style type="text/css" media="screen">
			@import url("css/content.css");
			@import url("css/layout.css");
			@import url("css/docbook.css");
		</style>
		
		<script language="JavaScript" type="text/JavaScript">
		<!--
		function MM_openBrWindow(theURL,winName,features) { //v2.0
		  window.open(theURL,winName,features);
		}
		
		var Submitted = false;
		var addContinue = "<?php print _("Add Conference"); ?>";

		//-->
		</script>
	</head>
	<body style="background-color:#FFFFFF">
<?php if (isset($Extend)){ ?>
<FORM METHOD=POST NAME="WMMon" ACTION="conf_control.php?s=1&t=0&order=<?php echo "$order&sens=$sens&current_page=$current_page&PHPSESSID=$PHPSESSID"; ?> target="superframe">

<INPUT TYPE="hidden" NAME="confno" VALUE=<?php echo $confno; ?>>
<script language="javascript">
<!--
document.WMMon.submit()
//-->
</script>
<?php } ?>

<script language="javascript">
<!--
function ClientMailer()
{
        var _mailto = "mailto:"+cMailer._ToAddress.value;
	if(cMailer._Subject.value == "")
	   cMailer._Subject.value = "<?php print _("Your Conference Details"); ?>";

	_mailto += "?Subject="+cMailer._Subject.value;
	_mailto += "&body="+cMailer._Body.value;
        document.cMailer.action  = _mailto;
        document.cMailer.submit();
}
//-->
</script>

<?php if ( isset($confno) && !isset($error) && !isset($Extend) && $dowork) { 
	if (MAILER == "SERVER") {
	?>
		<form action="email_participants.php" method="post">
		<input type=hidden value="<?php 
			if (is_array($em_bookId))
				print $em_bookId[0];
			else
				print $em_bookId; ?>" name=bookId>
	<?php
	}
	else
	{
	?>
		<form name="cMailer" method="post"> 
		<input type=hidden value="<?php echo $confOwner; ?>" id="_ToAddress">
		<input type=hidden value="<?php echo $confDesc; ?>" id="_Subject">
	<?php
	}
	?>
	<script language="JavaScript" type="text/JavaScript">
		Submitted = true;
		<?php if ($add)
		{ ?>
			parent.WMAdd._add.onclick = "";
			parent.WMAdd._add.value = "Continue";
			parent.WMAdd.action = "./meetme_control.php?&s=2&t=3";
		<?php 
		}
		else
		{ ?>
			parent.WMAdd._add.onclick = "";
			parent.WMAdd._update.value = "Continue";
			parent.WMAdd.action = "./meetme_control.php?&s=2&t=4";
		<?php
		}
		?>
		parent.WMAdd.target = "_top";
	</script>

        <table class="bar-status" width="750" border="0" cellspacing="1" cellpadding="2">
                <tbody>
                <tr>
                <td align="left" bgcolor="#000033">
                                <font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;</b></font>
		</td>
		<td class="bar-search" align="center" bgcolor="#acbdee">
<h1>
<?php print _("Conference"); ?> 
<?php 
	if($add)
	{
		echo _("Scheduled");
	}
	else
	{
		echo _("Updated");
	}
?>
</h1>
<b><?php print _("Send conference call details and the message you enter below"); ?></b>
		</td>
                </tr>
                <tr>
                <td align="left" bgcolor="#000033" width="20%">
                                <font face="arial" size="1" color="#ffffff">&nbsp;&nbsp;<b><?php print _("Email Message").":"; ?></b></font>
		</td>
		<td class="bar-search" align="center" bgcolor="#acbdee">
		<?php
		if (MAILER == "SERVER") {
		?>
			<textarea rows=6 cols=50 name="body">
		<?php
		}
		else
		{
		?>
			<input type=hidden id="_Body" value="<?php email_body($confDesc, $confOwner, $confno, $pin, $starttime, $endtime, $maxUser, $recurPrd, TRUE); ?>">
			<textarea rows=18 cols=52 readonly>
		<?php
		}
		email_body($confDesc, $confOwner, $confno, $pin, $starttime, $endtime, $maxUser, $recurPrd, FALSE);?></textarea>
		</td>
                </tr>
                <tr>
                <td align="left" bgcolor="#000033">
                                <font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;</b></font>
		</td>
		<td class="bar-search" align="center" bgcolor="#acbdee">
                <?php
                if (MAILER == "SERVER") {
                ?>
			<input type=submit value=<?php print "\""._("email participants")."\"";?> class=bstandard>
                <?php
                }
                else
                {
                ?>
			<input type=button value=<?php print "\""._("email participants")."\"";?> onClick="ClientMailer()" class=bstandard>
                <?php
                }
                ?>

		</td>
                </tr>
	</tbody>
	</table>

</form>

<?php } ?>

<?php if ( isset($confno) && isset($error)  && !isset($Extend)) { ?>

<center><strong> <?php print _("Conference Not Scheduled").":"; ?> </strong></center><br>
<center><strong><?php print $error; ?></strong></center>


<?php } ?>
<br><br>
	</body>
</html>

