<?php

// gettext rework 

include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./phpagi/phpagi-asmanager.php");
//include ("./locale.php");


session_start();

getpost_ifset (array('action', 'confno', 'data', 'bookid', 'name', 'invite_num','rx','tx'));


/* ACTION  *   *   *  * * * *********************************************************/
$name = iconv("cp1251","UTF-8",preg_replace("/[^\x30-\x39\x41-\x5A\x61-\x7A\x20\x5F\xA8\xB8\xC0-\xFF]/","",iconv("UTF-8","cp1251",$name)));
$name = substr($name,0,80);
$data = preg_replace("/[^\x30-\x39]/","",$data);
$bookid = preg_replace("/[^\x30-\x39]/","",$bookid);
$invite_num = preg_replace("/[^\x30-\x39]/","",$invite_num);
$invite_num = substr($invite_num,0,20);
$rx = preg_replace("/[^\x2D\x30-\x39]/","",$rx);
if (($rx < -20) || (20 < $rx)) { $rx=0; }
$tx = preg_replace("/[^\x2D\x30-\x39]/","",$tx);
if (($tx < -20) || (20 < $tx)) { $tx=0; }


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
			//$channel = CHAN_TYPE . "/" . OUT_PEER . "/" . $invite_num ;
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
		$async = true;
		$priority = 1;
		$context = OUT_CONTEXT;
		$timeout = 60000;
		$callerid = "\"$name\" <$invite_num>";
		//$variable = "CDR(bookId)=$bookid,CDR(CIDnum)=$invite_num,CDR(CIDname)=$name";
		$variable = "CDR(userfield)=$bookid,VOLRX=$rx,VOLTX=$tx,VOLUME(RX)=$rx,VOLUME(TX)=$tx"; //,DYNAMIC_FEATURES=volumerxdec#volumetxinc";
		//echo $callerid;
		//echo $channel;
		//echo $variable;
		$res = $as->Originate ($channel, $exten, $context, $priority, $timeout, $callerid, $variable, $account, $application, $data, $async);
	
		$actiondone=1;
	
		// && DISCONNECTING	
		$as->disconnect();
	}
} else {
	$res['Message'] = "Naughty";
}

/*    *    *   *   *  * * * *********************************************************/




?>

<?php if ($action=="quickcall"){ 
	if ($res['Message']=="Originate successfully queued")
		$error = _("Call Placed");
	else
		$error = _("System error, try again later");  
		
	
	echo $error;
}
?>
