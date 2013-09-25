<?php
$gui_title = GUI_TITLE;
$gui_icon = GUI_ICON;
$array = array (
	_("Information"), 
	"Адресная книга", 
	_("Scheduling") => array(
		_("Add Conference"),
		_("Delete Conferences"), 
		_("Past Conferences"), 
		_("Current Conferences"), 
		_("Future Conferences")
		)
	);

$user_section = count($array);

if(AUTH_TYPE=="sqldb")
{
if($_SESSION['privilege']== 'Admin')
    {
	    $array[_("User Management")] =  array(_("Add User"), _("Update User"));
	    $user_add_section = "section" . $user_section . "0";
	    $user_update_section = "section" . $user_section . "1";
    }
    else
    {
	    $array[_("User Management")] =  array(_("Update User"));
	    $user_update_section = "section" . $user_section . "0";
    }
}

$report_sel = count($array);
$report_section = "section" . $report_sel;
array_push($array,_("Reports"));

$about_sel = count($array);
$about_section = "section" . $about_sel;
array_push($array,_("About"));

if( (count($_SESSION) > 1) && $_SESSION['auth'])
{
	$logoff_sel = count($array);
	$logoff_section = "section" . $logoff_sel;
	array_push($array,_("Log-off"));
}
$s = $s ? $s : 0;
if(!isset($section))
	$section = "";

if($section != "section99" || ((count($_SESSION) > 1) && $s == $logoff_sel) || $s == $about_sel 
	|| $s == 0 )
{
	$section="section$s$t";
}
//print_r ($array);
$racine="/meet1/meetme_control.php";
$update = "21 March 2005";
print <<<HEADER
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
        <head>
                <title>$gui_title control</title>
                <!--<meta http-equiv="Content-Type" content="text/html">//-->
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <style type="text/css" media="screen">
                        @import url("css/layout.css");
                        @import url("css/content.css");
                        @import url("css/docbook.css");
                </style>
                <meta name="MSSmartTagsPreventParsing" content="TRUE">
        </head>
        <body BGCOLOR="#d9d9d9">





                <!-- header BEGIN -->
                <div id="fedora-header">

                        <div id="fedora-header-logo">
                                 <table border="0" cellpadding="0" cellspacing="0"><tr><td><img src="images/$gui_icon"  alt="$gui_title"></td><td>
                                 <H1><font color=#990000>&nbsp;&nbsp;&nbsp; $gui_title Control </font></H1></td></tr></table>
                        </div>

                </div>
                <div id="fedora-nav"></div>
                <!-- header END -->
HEADER;
?>
