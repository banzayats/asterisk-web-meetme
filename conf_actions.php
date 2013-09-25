<?php

include (dirname(__FILE__)."/phpagi/phpagi-asmanager.php");
include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./lib/database.php");

session_start(); 
getpost_ifset(array('confno','action','user_id','channel','command'));



/* ACTION  *   *   *  * * * *********************************************************/

$temp = $confno;

if (!is_numeric(urlencode($temp)))
	$confno = 0;


if (isset($confno)){

	$as = new AGI_AsteriskManager();
	// && CONNECTING
	$res = $as->connect();
	if (!$res){ echo _("Error connection to the manager")."!"; exit();}
	
	if (($action=="mute") || ($action=="unmute") || ($action=="kick") ){
		$res = $as->Command("meetme $action $confno $user_id");
		sleep(1);		
	}
	
	if ($command=="rxinc"){
		$res = $as->Getvar($channel,"VOLRX"); //print_r ($res);
		if ( ($res['Response']=="Success") && (-20 <= $res['Value']) && ($res['Value'] <= 20) ) {
			$vol=$res['Value'];
			$res = $as->Setvar($channel,"VOLUME(RX)",$vol+1);
			$res = $as->Setvar($channel,"VOLRX",$vol+1);
		}
		sleep(0.2);
	}
	if ($command=="rxdec"){
		$res = $as->Getvar($channel,"VOLRX"); //print_r ($res);
		if ( ($res['Response']=="Success") && (-20 <= $res['Value']) && ($res['Value'] <= 20) ) {
			$vol=$res['Value'];
			$res = $as->Setvar($channel,"VOLUME(RX)",$vol-1);
			$res = $as->Setvar($channel,"VOLRX",$vol-1);
		}
		sleep(0.2);
	}
	if ($command=="txinc"){
		$res = $as->Getvar($channel,"VOLTX"); //print_r ($res);
		if ( ($res['Response']=="Success") && (-20 <= $res['Value']) && ($res['Value'] <= 20) ) {
			$vol=$res['Value'];
			$res = $as->Setvar($channel,"VOLUME(TX)",$vol+1);
			$res = $as->Setvar($channel,"VOLTX",$vol+1);
		}
		sleep(0.2);
	}
	if ($command=="txdec"){
		$res = $as->Getvar($channel,"VOLTX"); //print_r ($res);
		if ( ($res['Response']=="Success") && (-20 <= $res['Value']) && ($res['Value'] <= 20) ) {
			$vol=$res['Value'];
			$res = $as->Setvar($channel,"VOLUME(TX)",$vol-1);
			$res = $as->Setvar($channel,"VOLTX",$vol-1);
		}
		sleep(0.2);
	}
	if ($command=="rxcurrent"){
		$res = $as->Getvar($channel,"VOLRX"); print_r ($res);
		if ($res['Response']=="Success") {
			echo $res['Value']."<br>\n";
		}
		sleep(1);
	}
	if ($command=="txcurrent"){
		$res = $as->Getvar($channel,"VOLTX"); print_r ($res);
		if ($res['Response']=="Success") {
			echo $res['Value']."<br>\n";
		}
		sleep(1);
	}
	
	if (($action=="end") ){
		$res = $as->Command("meetme kick $confno all");
		if (FORCE_END == "YES"){
			$now_datetime = getConfDate();
                	$FG_TABLE_NAME = DB_TABLESCHED;
			$query = "UPDATE $FG_TABLE_NAME SET endtime=? WHERE confno=?";
			$data = array($now_datetime, $confno);
			$db->query($query, $data); 
		}
		sleep(1);		
	}
}
?>
