#!/usr/bin/php -q
<?php
ob_implicit_flush(true);
set_time_limit(0);

require (dirname(__FILE__)."/../phpagi/phpagi-asmanager.php");
require (dirname(__FILE__)."/defines.php");
require (dirname(__FILE__)."/functions.php");
require (dirname(__FILE__)."/database.php");

$FG_TABLE_NAME=DB_TABLESCHED;
$FG_QUERY='endTime,bookId';


function getNow() {
   $date = getDate();
   foreach($date as $item=>$value) {
       if ($value < 10)
           $date[$item] = "0".$value;
   }
   return $date['year']."-".$date['mon']."-".$date['mday']." ".$date['hours'].":".$date['minutes'].":".$date['seconds'];
}


function setTime($eT,$offset){
    $tmp = (strtotime($eT) - $offset);
    $tmp = date("Y-m-d H:i:s", $tmp);

    return $tmp;
}

function ignore($ecode, $data, $server, $port) {
	/* Empty eventhandler to keep the logs clean
	   and hopefully prevent the Manager interface
	   from hanging */
}

function wmm_cdr($ecode, $data, $server, $port) {
	
	global $db;
	global $dsn;

	$CHECK_TABLE=DB_TABLESCHED;
	$CT_QUERY='bookId';
	$CDR_TABLE_NAME=DB_TABLECDR;
	$CDR_COL_QUERY='bookId';

	$now = getNow();
	$now =  substr($now, 0, 16);
	$now .= ":00";

	//Reconnect to DB
	$db->disconnect();
	$db = DB::connect($dsn);

	$TEMP_CLAUSE="starttime<='$now' AND endtime>='$now' AND confno='$data[Meetme]'";
	$bookId = $db->getOne("SELECT $CT_QUERY FROM $CHECK_TABLE WHERE $TEMP_CLAUSE");
	$CIDname = $data[CallerIDName];
	$CIDnum = $data[CallerIDNum];
	$dur = intval($data[Duration]);


       	//$CDR_CLAUSE=" bookId, duration, CIDname, CIDnum";
       	$CDR_CLAUSE=" userfield, duration, clid, src";
       	$param_update = "'$bookId','$dur','$CIDname','$CIDnum'";
	$query = "INSERT INTO $CDR_TABLE_NAME VALUES ($param_update)";
	$result = $db->query($query);
}


while ( true ){
    $as = new AGI_AsteriskManager();
    $res = $as->connect();
    if ($res) {
       $as->add_event_handler('meetmeleave', 'wmm_cdr');
       $as->add_event_handler('*', 'ignore');

	while ( true ){
		$res = $as->Command('Ping');
    		if(!$res)
			break;
		sleep(5);
	}
    }

    print "Asterisk unavailable.  Waiting for it to return!\r\n";
    $res = $as->disconnect();
    sleep(5);
}

?>
