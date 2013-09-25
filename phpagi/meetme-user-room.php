<?php
require "phpagi-asmanager.php";

$as = new AGI_AsteriskManager();
// && CONNECTING
$res = $as->connect();

$room = 1234;
$res = $as->Command('meetme list '.$room);
	
//echo  $res['data'].'</br>';


$line= split("\n", $res['data']);
	
//print_r ($line);
echo $line[0];

$element= split(' ', $line[0]);

print_r ($element);
	
/*

</br>Array
(
    [0] => User #: 1  Channel: IAX2/areskiax@areskiax-2   (Admn Muted) (unmonitored)
    [1] => User #: 2  Channel: SIP/kphone-b15c    (unmonitored)
    [2] => 2 users in that conference.
    [3] => 
)


*/

flush(); 
ob_flush();

// && DISCONNECTING
$as->disconnect();

?>
