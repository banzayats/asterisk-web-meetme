<?php

include (dirname(__FILE__)."/../locale.php");

//Email text
function email_body($confDesc, $confOwner, $confno, $pin, $starttime, $endtime, $maxUser, $recurPrd, $encode)
{
$starttime = strtotime($starttime);
$endtime = strtotime($endtime);
 if (use24h()) {
         $starttime = date("l d.m.Y H:i:s", $starttime);
         $endtime = date("l d.m.Y H:i:s", $endtime);
 }
 else {
         $starttime = date("l M d, Y h:i:s A", $starttime);
         $endtime = date("l M d, Y h:i:s A", $endtime);
 }
$local_phone = LOCAL_PHONE;
$local_support = LOCAL_SUPPORT;
$srv_phone = PHONENUM;

if ($pin == "")
	$pin = "No password.";

if ($encode){
	print rawurlencode(_("Conference Name").":          $confDesc \n");
	print rawurlencode(_("Conference Owner").":         $confOwner \n");
	print rawurlencode(_("Conference ID").":            $confno \n");
	print rawurlencode(_("Conference Password").":      $pin \n");
	print rawurlencode(_("Start Date and Time").":      $starttime \n");
	print rawurlencode(_("End Date and Time").":        $endtime \n");
	print rawurlencode(_("Participants").":             $maxUser \n");
	print rawurlencode(_("Recurrence Information").":   $recurPrd \n");
	print rawurlencode("-------------------------------------------------- \n");
	print rawurlencode(_("Dial In Info")." : \n");
	print rawurlencode(_("The conference call can be accessed by calling")." $srv_phone.  \n");
	print rawurlencode(_("Please contact")." $local_support "._("at")." $local_phone "._("for assistance").". \n");
}
else
{
	print "\n";
	print _("Conference Name").":          $confDesc \n";
	print _("Conference Owner").":         $confOwner \n";
	print _("Conference ID").":            $confno \n";
	print _("Conference Password").":      $pin \n";
	print _("Start Date and Time").":      $starttime \n";
	print _("End Date and Time").":        $endtime \n";
	print _("Participants").":             $maxUser \n";
	print _("Recurrence Information").":   $recurPrd \n";
	print "-------------------------------------------------- \n";
	print _("Dial In Info")." : \n";
	print _("The conference call can be accessed by calling")." $srv_phone.  \n";
	print _("Please contact")." $local_support "._("at")." $local_phone "._("for assistance").". \n";
}
}

?>
