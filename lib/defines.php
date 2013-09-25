<?php

include(dirname(__FILE__)."/../locale.php");

//To debug uncomment the following
//ini_set('display_errors', TRUE);
//error_reporting(E_ALL ^ E_NOTICE);

//The AJAX features require a consistent URL
//Make sure to set WEBROOT to the exact URL
//Users will use to access WMM
define ("WEBROOT", "/meet1/");
define ("FSROOT", "/var/www/meet1/");
define ("LIBDIR", FSROOT."lib/");

//GUI title
define("GUI_TITLE", "Web-MeetMe");
define("GUI_ICON", "asterisk.gif");
define("GUI_VER", "4.0.4");
define("GUI_SRC", "http://sourceforge.net/projects/web-meetme/");

//Email Variables - Support contacts, Call in numbers and other spit and polish for the about page.
define("LOCAL_SUPPORT", "Support Department");
define("LOCAL_PHONE", "8(495)--");
define("PHONENUM", "8(495)--");
define("PBX_ICON", "asterisk.gif");

//Maximum concurrent caller limit
//define("MAX_CALLER_LIMT", "9");

// Conference monitor options
define("MON_REFRESH", "5");

// Recording path
define("RECORDING_PATH", "/var/lib/asterisk/sounds/conf-recordings/meet1/");

// THIS VARIABLE DEFINE THE COLOR OF THE HEAD TABLE
$FG_TABLE_HEAD_COLOR = "#D1D9E7";
$FG_TABLE_EXTERN_COLOR = "#7F99CC"; //#CC0033 (Rouge)
$FG_TABLE_INTERN_COLOR = "#EDF3FF"; //#FFEAFF (Rose)

$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#1FFFFF";
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#F2F8FF";

// THIS VARIABLE DEFINE THE COLOR OF THE ADMIN ROW
$FG_TABLE_ROW_COLOR_ADMIN = "#FCCDCA";


// Comment out the following lines to disable authentication
define ("AUTH_TYPE", "sqldb"); // adLDAP or sqldb 
define ("ADMIN_GROUP", "Domain Admins");
define ("AUTH_TIMEOUT", "3");	//Hours
include (LIBDIR.AUTH_TYPE.".php");


//Database tables
define ("DB_TABLECDR", "cdr");
define ("DB_TABLESCHED", "booking");
define ("DB_TABLEUSERS","user");

define ("SERVER_TZ", "PST/PDT");
define ("USE_24H", "YES");

//Outcall defaults
//define ("CHAN_TYPE", "Local"); //Use Local to let dialplan decide which chan
define ("CHAN_TYPE", "SIP"); //Use Local to let dialplan decide which chan
define ("OUT_PEER", "100"); // Use this if not using CHAN_TYPE Local TRUNK
define ("LOCAL_CONTEXT", "meetme_out"); //Select a context to place the call from
define ("OUT_CONTEXT", "meetme"); //Select a context to place the call from
define ("OUT_CALL_CID", "Meet Admin <2012>"); // Caller ID for Invites ;; Ignore

//Standard flags for Users and Admins
define ("SAFLAGS", "aAsM");
define ("SUFLAGS", "sM");
$Mod_Options = array(array(_("Announce"), "I"), array(_("Record"), "r"));
$User_Options = array(array(_("Announce"), "I"), array(_("Listen Only"), "m"), array(_("Wait for Leader"), "w"));

//Require conference PIN (passwords)
define ("PASSWORD_OPTION", "NO");

//Change conference End Time on a 'End Now' click
//define ("FORCE_END", "YES");

//Mailer type: 
// CLIENT to use mailto: and default user mail client
// SERVER to use the server's mailer
define ("MAILER", "CLIENT");
include ("email_body.php");

//Avatar definitions
$icons_list['0'] = "./images/icons/Darth Vader.GIF"; 
$icons_list['1'] = "./images/icons/Anakin.GIF";
$icons_list['2'] = "./images/icons/Scout Trooper.GIF";
$icons_list['3'] = "./images/icons/Hoth Soldier.GIF";
$icons_list['4'] = "./images/icons/Major Matt Mason.GIF";
$icons_list['5'] = "./images/icons/Landspeeder Ben.GIF";
$icons_list['6'] = "./images/icons/Obi Wan With Hood.GIF";
$icons_list['7'] = "./images/icons/Wedge.GIF";
$icons_list['8'] = "./images/icons/Rebel Tech.GIF";
$icons_list['9'] = "./images/icons/Larry the Lobster.GIF";



$months = array(_("January"),_("February"),_("March"),_("April"),_("May"),_("June"),_("July"),_("August"),_("September"),_("October"),_("November"),_("December"));

$days = array("31","28","31","30","31","30","31","31","30","31","30","31");

$recurLabel = array(_("Daily"), _("Weekly"), _("Bi-weekly"));
$recurInterval = array("86400", "604800", "1209600");
$recurPeriod = array("14", "26", "13");

function contact(){
	?>      
	<table width="90%">
	<tr><td couluns=2 ><br><h1><?php print _("Support Team"); ?> ...</h1></td></tr>
	<tr>
	<td><em><strong>Contact: </strong></em><?php print _("Contact"); ?></td>
	<td><em><strong>Phone: </strong></em><?php _("Phone"); ?></td>
	</tr>

	<tr><td span=2 ><br><h2><?php print _("Developer Team"); ?> ...</h2></td></tr>
	<tr>
    	<td><a href="mailto:<?=$myemail?>">Arezqui Bela&iuml;d
      	</a> : areski (no@spam) gmail (dot) com</td>
    	<td><a href="mailto:dan_austin@phoenix.com">Dan Austin
      	</a></td>
  	</tr>
	<tr>
	<td><em><strong>Last update: </strong></em><?php  _("Last update"); ?></td>
	<td><em><strong>Developer Website: </strong></em><a href="<?php _("Developer Website"); ?>">Web-MeetMe</a></td>
	</tr>
	<tr>
	<td><br><img src="images/<?=PBX_ICON?>"></td>
	<tr>
	<td><h2>User details ...</h2></td>
	</tr>
	<tr>
	<td> <?php print _("Currently logged on as"); ?> <?php echo $_SESSION['userid']?> a <?php echo $_SESSION['privilege']?> <?php if (isset ($_SESSION['groups'])) print _("and a member of"); ?> <?php if (isset ($_SESSION['groups'])) echo $_SESSION['groups']?></td>
	</tr>
	</table>
<br><br>
	<?
}

?>
