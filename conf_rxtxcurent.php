<?php

include (dirname(__FILE__)."/phpagi/phpagi-asmanager.php");
include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./lib/database.php");

session_start(); 
getpost_ifset(array('channel','command'));
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

<body bgColor=#FFFFFF>
<center>
<?php
/* ACTION  *   *   *  * * * *********************************************************/

	$as = new AGI_AsteriskManager();
	// && CONNECTING
	$res = $as->connect();
	if (!$res){ echo _("Error connection to the manager")."!"; exit();}

	if ($command=="rxcurrent"){
		$res = $as->Getvar($channel,"VOLRX"); //print_r ($res);
		if ($res['Response']=="Success") {
			echo "RX = ".$res['Value']."<br>\n";
		}
		sleep(1);
	}
	if ($command=="txcurrent"){
		$res = $as->Getvar($channel,"VOLTX"); //print_r ($res);
		if ($res['Response']=="Success") {
			echo "TX = ".$res['Value']."<br>\n";
		}
		sleep(1);
	}
?>
<br>
<input type="submit" value="Закрыть окно" onClick="window.close()" /></center>
