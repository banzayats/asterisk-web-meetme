<?php
include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./lib/database.php");

session_start();


getpost_ifset(array('confno', 'bookId'));

/* The db for Version 4 uses bookid, while the app uses bookId */

$query = "SELECT confOwner from " . DB_TABLESCHED . " WHERE bookId='$bookId'";

$result = $db->query($query);
$row = $result->fetchRow();

if ($_SESSION['auth'] && ($_SESSION['privilege'] == "Admin" || 
    $row[0] == $_SESSION['userid'])) {
	if (is_numeric($confno) && is_numeric($bookId)) {
		$file = $confno . "-" . $bookId . ".wav";
		//$playfile = "/var/spool/asterisk/meetme/meetme-conf-rec-" . $file;
		$playfile = RECORDING_PATH . "meetme-conf-rec-" . $file;
		if (!(file_exists($playfile))) {
			//$playfile = "/var/lib/asterisk/sounds/conf-recordings/meetme-conf-rec-" . $file; 
			$playfile = RECORDING_PATH . "meetme-conf-rec-" . $file; 
			$file = "meetme-conf-rec-". $file;
		} else {
			$file = "meetme-conf-rec-". $file;
		}
		
		if (file_exists($playfile)) {
			$mimetype = "audio/x-wav";
			$content_len = filesize($playfile);
			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"".$file."\";" );
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".$content_len);
			//header("Content-type: $mimetype");
			//header('Content-Disposition: inline; filename=$file');
			readfile($playfile);
		}
	}
}
?>

