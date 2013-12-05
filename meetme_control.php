<?php
include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./lib/database.php");
include ("locale.php");

if ($locale != 'en_US') {
    $button_search = $locale."_button-search.gif";
} else {
    $button_search = "button-search.gif";
}

$s = '';
$t = '';
$logoff_section='';

getpost_ifset(array('s', 't'));

if (!is_numeric(substr($s, 0, 1))) {
	$s = "";
	$t = "";
	$section = $logoff_section;
} else {
	$s = substr($s, 0, 1);
}
			
if (!is_numeric(substr($t, 0, 1))) {
	$t = "";
	$section = $logoff_section;
} else {
	$t = substr($t, 0, 1);
}

if (!isset($confno))
	$confno = "";


if (defined('AUTH_TYPE')){
	getpost_ifset(array('AUTH_USER', 'AUTH_PW'));
	session_set_cookie_params(0, '/' );
	session_start();

	if ($_SESSION['auth']) {
        	if (($_SESSION['lifetime']) <= time()){
                	$_SESSION['auth'] = 0;
                	$_SESSION['privilege'] = "";
                	$_SESSION['userid'] = "";
                	unset($AUTH_USER);
                	unset($AUTH_PW);
        	}
	} 

	if ( !($_SESSION['auth']) && $AUTH_USER != NULL && $AUTH_PW != NULL ){
		$user = new userSec();
		$user -> authenticate($AUTH_USER, $AUTH_PW);
		$user -> isAdmin($AUTH_USER);
	}

	if (!($_SESSION['auth']))
		$section="section99";

}
include ("./lib/header.php");
include ("./lib/leftnav.php");
?>

<?php include ("lib/functions_js.php"); ?>
<script type = "text/javascript" src = "./lib/prototype.js"></script> 

		<!-- content BEGIN -->
		<div id="fedora-middle-two">
			<div class="fedora-corner-tr">&nbsp;</div>
			<div class="fedora-corner-tl">&nbsp;</div>
			<div id="fedora-content">


<?php
if ($section=="section0" || $section=="section2"){?>


<h1><center><?php  echo GUI_TITLE; ?></center></h1>

<h2><?php print _("Communicate and control your audience."); ?></h2>



<?php }elseif ($section=="section10"){

getpost_ifset(array('confno','book')); ?>
<!-- ** ** ** ** ** Part to select the conference ** ** ** ** ** -->
&nbsp;
        <script>
        function out_call(cN, bI) {
            window.open ('out_call.php?confno='+cN+'&book='+bI, 'newWin', 'toolbar=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=420,height=150')
        }
        </script>
        <script>
        function out_call_book(cN, bI, uI, pr) {
            window.open ('out_call_book.php?confno='+cN+'&book='+bI+'&user='+uI+'&privilege='+pr, 'newWinAdd', 'toolbar=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=820,height=600')
        }
        </script>

<br/>
<center><?php print _("Select the room number that you want to handle"); ?>
<FORM METHOD=POST NAME="WMMon" ACTION="conf_control.php?s=1&t=0&order=<?php echo "$order&sens=$sens&current_page=$current_page&PHPSESSID=$PHPSESSID"; ?>" target="superframe">
<INPUT TYPE="hidden" NAME="current_page" value=0>
	<table class="bar-status" width="70%" border="0" cellspacing="1" cellpadding="2" align="center">
		<tbody>
		<tr>
		<td align="left" bgcolor="#000033">
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Conference ROOM"); ?> :</b></font>
			</td>
			<td class="bar-search" align="center" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="confno" value="<?php echo $confno; ?>"></td>
			</tr></table></td>
		</tr>


		<tr>
		<td class="bar-search" align="left" bgcolor="#555577"> </td>

			<td class="bar-search" align="center" bgcolor="#cddeff">
				<input type="image"  name="image16" align="top" border="0" src="images/<?php print $button_search ?>" />

			</td>
	</tr>
	</tbody></table>
</FORM>

<iframe name="superframe" src="conf_control.php" BGCOLOR="#FFFFFF"      width=750 height=500 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=auto>

</iframe>

<table border="0" width="50%"> 
<?php if ($confno != ""){ ?>
    <tr>
        <td>
            <form METHOD=POST ACTION="conf_add.php?s=1&t=0&order=<?php echo "$order&sens=$sens&current_page=$current_page&PHPSESSID=$PHPSESSID&Extend&confno=$confno"; ?>" target="superframe">
                <input type="Submit" name="Extend" align="top" border="0" value="<?php echo _("Extend by 10 minutes");?>" />
            </form>
        </td>
        <td>
            <form METHOD=POST ACTION="meetme_control.php?s=2&t=3" onclick="return conf_action('end','<?PHP echo $confno; ?>', '')">
                <input type="Submit" name="EndConf" align="top" border="0" value="<?php echo _("Finish conference");?>" />
            </form>
        </td>
        <td>
            <input type="Submit" name="Invite" onClick="out_call(<?php echo $confno.", ".$book;?>)" align="top" border="0" value="<?php echo _("Invite participants");?>" />
        </td>
        <td>
            <input type="Submit" name="Invite_book" onClick="out_call_book(<?php echo $confno.", ".$book.", '".$_SESSION['userid']."'".", '".$_SESSION['privilege']."'";?>)" align="top" border="0" value="<?php echo _("Invite participants from list");?>" />
        </td>
    </tr>
</table>
</center>
 

<script language="javascript">
<!--
document.WMMon.submit()
//-->
</script>

<?php } else { ?>
<script language="javascript">
<!--
document.WMMon.confno.focus()
//-->
</script>
<?php } ?>


<?php }elseif ($section=="section11"){?>
<!-- ** ** ** ** ** Part to Place a Outgoing Call** ** ** ** ** -->


<center>
<iframe name="superframe" src="<?php echo "call_operator.php?atmenu=operator&stitle=Make+Outbound+Call";?>" BGCOLOR=white      width=750 height=450 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=auto>

</iframe>
</center>


<?php }elseif ($section=="section20"){?>
	<script>
	<!-- Begin
	function monthPop(objForm,selectIndex) {
	timeA = new Date(objForm.year.options[objForm.year.selectedIndex].text, objForm.month.options[objForm.month.selectedIndex].value,1);
	timeDifference = timeA - 86400000;
	timeB = new Date(timeDifference);
	var daysInMonth = timeB.getDate();

	for (var i = 0; i < objForm.day.length; i++) {
		objForm.day.options[0] = null;
	}
	for (var i = 0; i < daysInMonth; i++) {
		objForm.day.options[i] = new Option(i+1);
	}
	document.WMAdd.day.options[0].selected = true;
	}
	
	function recurPop(objForm,selectIndex){
	var recurPrd = (objForm.recurLbl.options[objForm.recurLbl.selectedIndex].value);
	for (var i = 0; i <= 26 ; i++){
		objForm.recurPrd.options[i] = null;
	}
	for (var i = 0; i <= (recurPrd-2); i++) {
		if (recurPrd == 14){
			objForm.recurPrd.options[i] = new Option((i+2)+ <?php print "\" "._("days")."\""; ?>, (i+2));
		} else {
		if (recurPrd == 26){
			objForm.recurPrd.options[i] = new Option((i+2)+ <?php print "\" "._("weeks")."\""; ?>, (i+2));
		} else {
			objForm.recurPrd.options[i] = new Option(((i+1)*2)+ <?php print "\" "._("weeks")."\""; ?>, (i+2));
		}		
		}
	}
		document.WMAdd.recurPrd.options[0].selected = true;	
	}

	//  End -->
	</script>

<!-- ** ** ** ** ** Part to add the conference ** ** ** ** ** -->
&nbsp;
<br/>
<?php
getpost_ifset('bookId');

if (!isset($bookId)) {
	$bookId = 0;
}
	

if ($bookId){
	$FG_COL_QUERY='confno, confDesc, starttime, endtime, dateReq, maxusers, bookId, pin, confOwner, adminpin, adminopts, opts';
	$result = $db->query("SELECT $FG_COL_QUERY FROM booking WHERE bookId='$bookId'");
	$recordset = $result->fetchRow();
	$confno = $recordset[0];
	$confDesc = $recordset[1];
	$starttime = $recordset[2];
	$endtime = $recordset[3];
	$dateReq = $recordset[4];
	$maxusers = $recordset[5];
	$bookId = $recordset[6];
	$pin = $recordset[7];
	$confOwner = $recordset[8];
	$adminpin = $recordset[9];
	$adminopts = $recordset[10];
	$opts = $recordset[11];
}

echo "<center>";
if($bookId)
{
	echo _("Change details about this conference");
	if(PASSWORD_OPTION=="YES")
	{
		if(strlen($adminpin) == 0 && strlen($pin) == 0)
		{
			$np_checked = "CHECKED";
			echo "<script>togglePass();</script>";
		}
		else
		{
			$np_checked = "";
		}
	}
}
else
{
	echo _("Enter the details about the conference to add");
	$confno=randNum(10000, 99999);
}
?>

<FORM METHOD=POST NAME="WMAdd" ACTION="conf_add.php?&s=1&t=2&order=<?php echo "$order&sens=$sens&current_page=$current_page";?>" target="superframe">
<INPUT TYPE="hidden" NAME="current_page" value=0>
	<table class="bar-status" width="750" border="0" cellspacing="1" cellpadding="2" align="center">
		<tbody>
		<tr>
		<td align="left" bgcolor="#000033" width="20%">                     
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;
				<SPAN title=" Conference Description " class="popup"><?php print _("Conference Name")." :"; ?></SPAN></b></font>
			</td>
			<td class="bar-search" align="left" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="confDesc" value="<?php if (isset ($confDesc)) echo $confDesc; ?>" size=35></td>
			</tr></table></td>
		</tr>
		<td align="left" bgcolor="#000033">                     
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Conference Owner")." :"; ?></b></font>
			</td>
			<td class="bar-search" align="left" bgcolor="#acbdee">
	<?php	if (isset($confOwner)){ ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="confOwner" value="<?php echo $confOwner; ?>"></td>
	<?php	} else { ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="confOwner" value="<?php echo $_SESSION['userid']; ?>"></td>
	<?php	} ?>
			</tr></table></td>
		</tr>
		<td align="left" bgcolor="#000033">                     
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<SPAN title=" Conference Room  Number " class="popup"><?php print _("Conference Number"); ?> :</SPAN></b></font>
			</td>
			<td class="bar-search" align="left" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="confno" value="<?php echo $confno; ?>"></td>
			</tr></table></td>
		</tr>
		<td align="left" bgcolor="#000033">
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<SPAN title=" Numeric password for the Moderator " class="popup"><?php print _("Moderator PIN"); ?> :</SPAN></b></font>
			</td>
			<td class="bar-search" align="left" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="adminpin" value="<?php if (isset($adminpin)) echo $adminpin; ?>"></td>
			</tr></table></td>
		</tr>

		<td align="left" bgcolor="#000033">
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<SPAN title=" Options that apply only to Moderators " class="popup"><?php print _("Moderator Options"); ?> :</SPAN></b></font>
			</td>
			<td class="bar-search" align="left" bgcolor="#acbdee">
			<table width="65%" border="0" cellspacing="0" cellpadding="0"><tr>
                        <?php 
			for ($i=0; $i < count($Mod_Options); $i++){
				print "<INPUT TYPE=CHECKBOX NAME=adminopts[] ";
				if(isset($adminopts)){
					if (strchr($adminopts, $Mod_Options[$i][1]))
					{ 
						print "VALUE=\"";
						print $Mod_Options[$i][1];
						print "\" CHECKED>";
					}
					else
					{
						print "VALUE=\"";
						print $Mod_Options[$i][1];
						print "\">";
					}
                        }
			else
			{
				print "VALUE=\"";
				print $Mod_Options[$i][1];
				print "\">";
			}
  				print "<font face=\"arial\" size=\"1\" ><b>";
				print $Mod_Options[$i][0];
				print "&nbsp;&nbsp;&nbsp</b></font>\n";
			}
			if(PASSWORD_OPTION=="YES")
			{ 
			?>
                        <INPUT TYPE=CHECKBOX NAME="nopass" value = "1" onclick="togglePass();" <?php echo $np_checked; ?>>
                        <font face="arial" size="1" ><b><?php print _("No Passwords"); ?>&nbsp;&nbsp;&nbsp;</b></font>

			<?php
			}
			?>
			</tr></table></td>
		</tr>

		<td align="left" bgcolor="#000033">                     
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<SPAN title=" Numeric password for normal callers " class="popup"><?php print _("User PIN"); ?> :</SPAN></b></font>
			</td>
			<td class="bar-search" align="left" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="pin" value="<?php if (isset($pin)) echo $pin; ?>"></td>
			</tr></table></td>
		</tr>

		<td align="left" bgcolor="#000033">                     
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<SPAN title=" Options that apply only to normal callers " class="popup">User Options :</SPAN></b></font>
			</td>
			<td class="bar-search" align="left" bgcolor="#acbdee">
			<table width="65%" border="0" cellspacing="0" cellpadding="0"><tr></td>
                        <?php
			for ($i=0; $i < count($User_Options); $i++){
				print "<INPUT TYPE=CHECKBOX NAME=opts[] ";
				if(isset($opts)){
					if (strchr($opts, $User_Options[$i][1]))
					{ 
						print "VALUE=\"";
						print $User_Options[$i][1];
						print "\" CHECKED>";
					}
					else
					{
						print "VALUE=\"";
						print $User_Options[$i][1];
						print "\">";
					}
                        }
			else
			{
				print "VALUE=\"";
				print $User_Options[$i][1];
				print "\">";
			}
  				print "<font face=\"arial\" size=\"1\" ><b>";
				print $User_Options[$i][0];
				print "&nbsp;&nbsp;&nbsp</b></font>\n";
			}
                        ?>

			</tr></table></td>
		</tr>

		<td align="left" bgcolor="#000033">                     
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Start Time"); ?> (<?php echo SERVER_TZ; ?>) :</b></font>
			</td>
			<td class="bar-search" align="left" bgcolor="#acbdee">
<?php if (isset($starttime)){
	$starttime=strtotime($starttime);
   } else {
	$starttime=strtotime(getConfDate());
} ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;

<SELECT NAME="month" onChange="monthPop(this.form,this.form.day.selectedIndex)">
<?php for ($i=0; $i<12; $i++){
if ( $i < 9 ){
	if ( $i+1 == intval(date("n", $starttime)) ){ ?>
	<OPTION SELECTED VALUE="0<?php echo $i+1; ?>"><?php echo $months[$i]; ?>
	<?php } else { ?>
	<OPTION VALUE="0<?php echo $i+1; ?>"><?php echo $months[$i]; ?>
	<?php } ?>
<?php } else {
	if ( $i+1 == intval(date("n", $starttime)) ){ ?>
	<OPTION SELECTED VALUE="<?php echo $i+1; ?>"><?php echo $months[$i]; ?>
	<?php } else { ?>
	<OPTION VALUE="<?php echo $i+1;?>"><?php echo $months[$i]; ?>
	<?php } ?>
<?php } ?>
<?php } ?>

</SELECT>
<SELECT NAME="day">
<?php $tmp=intval(date("n", $starttime));
for ($i=1; $i<= $days[$tmp-1]; $i++) {
if ( $i < 9 ){
	if ( $i == intval(date("j", $starttime)) ){ ?>
	<OPTION SELECTED VALUE="0<?php echo $i; ?>"><?php echo $i; ?>
	<?php } else { ?>
	<OPTION VALUE="0<?php echo $i; ?>"><?php echo $i; ?>
	<?php } ?>
<?php } else {
	if ( $i == intval(date("j", $starttime)) ){ ?>
	<OPTION SELECTED VALUE="<?php echo $i; ?>"><?php echo $i; ?>
	<?php } else { ?>
	<OPTION VALUE="<?php echo $i; ?>"><?php echo $i; ?>
	<?php  } ?>
<?php  } ?>
<?php  } ?>
</SELECT>

<SELECT NAME="year" onChange="monthPop(this.form,this.form.month.selectedIndex)">
<?php  for ($i=0; $i<=3; $i++){
$tmp=intval(date("Y", $starttime));
   if ( $tmp+$i == intval(date("Y", $starttime)) ){ ?>
	<OPTION SELECTED VALUE="<?php echo $tmp; ?>"><?php echo $tmp; ?>
<?php  } else { ?>
	<OPTION VALUE="<?php echo $tmp+$i; ?>"><?php echo $tmp+$i; ?>
<?php } ?>
<?php } ?>
</SELECT>
&nbsp;&nbsp; &nbsp;&nbsp;
<INPUT TYPE="text" SIZE="2" MAXLENGTH="2" NAME="Hour" VALUE="<?php if (use24h()) $format = "G"; else $format = "g";echo date($format, $starttime); ?>">
<INPUT TYPE="text" SIZE="2" MAXLENGTH="2" NAME="Min" VALUE="<?php echo date("i", $starttime); ?>">

<?php if (!use24h()) { ?>
<SELECT NAME="AMPM">
<?php if ( date("A", $starttime) == "AM") { ?>
<OPTION SELECTED VALUE="AM">AM
<OPTION VALUE="PM">PM
<?php } else { ?>
<OPTION VALUE="AM">AM
<OPTION SELECTED VALUE="PM">PM
<?php } ?>
</SELECT>
<?php }?>
</td>
		</tr></table></td>
		</tr>
<?php if (isset($endtime)){
	$tmp=strtotime($endtime)-$starttime;
	$Min=(($tmp/60)%60);
	$Hour=((($tmp/60)-(($tmp/60)%60))/60);
	if ($Min < 10){
		$Min="0$Min";
	}
   } else {
	$Hour="1";
	$Min="00";
} ?>
		<td align="left" bgcolor="#000033">

		<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Duration"); ?> (HH:MM):</b></font>
		</td>
		<td class="bar-search" align="left" bgcolor="#acbdee">
		<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="ConfHour" size="2" value=<?php echo $Hour; ?>><INPUT TYPE="text" NAME="ConfMin" size="2" value=<?php echo $Min; ?>></td>
			</tr></table></td>
		</tr>
<?php if(!isset($endtime)){ ?>
		<td align="left" bgcolor="#000033">                     
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;
				<SPAN title=" Single or repeating scheduled conferences " class="popup"><?php print _("Recurs"); ?> :</SPAN></b></font>
			</td>
			<td class="bar-search" align="left" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;
			<INPUT TYPE=CHECKBOX NAME="recur" VALUE="1">
			<font face="arial" size="1"><b>&nbsp;&nbsp;<?php print _("Reoccurs"); ?>: </b></font>

			<SELECT NAME="recurLbl" onChange="recurPop(this.form,this.form.recur.selectedIndex)">
			    <?php for ($i=0; $i < count($recurLabel); $i++){ ?>
				<OPTION VALUE="<?php echo $recurPeriod[$i]; ?>"><?php echo $recurLabel[$i]; ?>
			    <?php } ?>
			</SELECT>	
			<font face="arial" size="1"><b>&nbsp;&nbsp;<?php print _("for"); ?> </b></font>
			<SELECT NAME="recurPrd">
			    <?php for ($i=2; $i<=$recurPeriod[0]; $i++){ ?>
        			<OPTION VALUE="<?php echo $i; ?>"><?php echo "$i "._("days"); ?>
			   <?php } ?>
			</SELECT>
			</tr></table></td>
		</tr>
<?php } ?>

		<td align="left" bgcolor="#000033">                     
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<SPAN title=" How many callers may join " class="popup"><?php print _("Max Participants"); ?> :</SPAN></b></font>
			</td>
			<td class="bar-search" align="left" bgcolor="#acbdee">
	<?php if (isset($maxusers)) { ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="maxusers" value=<?php echo $maxusers; ?> size=5></td>
	<?php } else { ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="maxusers" value="10" size=5></td>
	<?php } ?>
			</tr></table></td>
		</tr>
<?php if (AUTH_TYPE == "sqldb"){ ?>
		<tr>
                <td align="left" bgcolor="#000033">
			<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Invite"); ?> :</b></font>
		</td>
		<td class="bar-search" align="left" bgcolor="#acbdee">
        <input type="hidden" value="0" id="theValue" />
        <button class="bstandard" onclick="addRowToTable('invite');"><?php print _("Invite to conference"); ?></button>
	<table id="invite">
<?php 
	if ($bookId)
	{
		$query = "SELECT u.first_name, u.last_name, u.email, u.telephone FROM user u, participants p
				WHERE u.id = p.user_id AND p.book_id = '$bookId'";
		$result=$db->query($query);
		while($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
		        extract($row);
			echo "<tr>";
			echo "<td><input type=text name=fname[] size=12 value=\"$first_name\"</td>";
			echo "<td><input type=text name=lname[] size=12 value=\"$last_name\"</td>";
			echo "<td><input type=text name=email[] size=22 value=\"$email\"</td>";
			echo "<td><input type=text name=phone[] size=15 value=\"$telephone\"</td>";
			echo "<td><button class=warn onclick=\"deleteCurrentRow(this)\">Delete</button></td>";
			echo "</tr>";
		}
	}
?>
	</table>
		</td>
		</tr>
<?php } ?>

		<tr>
		<td class="bar-search" align="left" bgcolor="#555577"> </td>
		<INPUT TYPE="hidden" NAME="bookId" value=<?php echo $bookId; ?>>
		<INPUT TYPE="hidden" NAME="dateReq" value="<?php echo $dateReq; ?>">

			<td class="bar-search" align="center" bgcolor="#cddeff">
	<?php if ($bookId) { ?>
				<input type="Submit"  id="_update" name="update" align="top" border="0" value="Update Conference"/>
				<input type="CHECKBOX"  name="updateSeries" align="top" border="0" value="1"/>
			<font face="arial" size="1" ><b>&nbsp;&nbsp;<?php print _("Update the Series"); ?>: </b></font>

	<?php } else { ?>
				<input type="Submit" id="_add" name="add" align="top" border="0" value="<?php print _("Add Conference"); ?>" onClick="if(confirm('Are you sure?')) show('add_summary'); else return false" >
	<?php } ?>
			</td>
	</tr>
	</tbody></table>
</FORM>
<script language="javascript">
<!--
document.WMAdd.confDesc.focus()
//-->
</script>

<br>
<iframe name="superframe" src="conf_add.php" BGCOLOR=white  width="750" height=500 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=auto >
</iframe>


<?php }elseif ($section=="section21"){?>

<!-- ** ** ** ** ** Part to select the conference(s) to delete ** ** ** ** -->
&nbsp;
<br/>
<center><?php print _("Select the Conference that you want to Delete"); ?>
<FORM METHOD=POST NAME="WMDel" ACTION="conf_delete.php?s=1&t=2&order=starttime&sens=ASC&current_page=1" target="superframe">
<INPUT TYPE="hidden" NAME="current_page" value=0>
	<table class="bar-status" width="70%" border="0" cellspacing="1" cellpadding="2" align="center">
		<tbody>
		<tr>
		<td align="left" bgcolor="#000033">
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<SPAN title=" Select a specific conference number " class="popup"> <?php print _("Search for Conference"); ?> :</SPAN></b></font>
			</td>
			<td class="bar-search" align="center" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="confno" value="<?php echo $confno; ?>"></td>
			</tr></table></td>
		</tr>


		<tr>
		<td class="bar-search" align="left" bgcolor="#555577"> </td>

			<td class="bar-search" align="center" bgcolor="#cddeff">
				<input type="image"  name="image16" align="top" border="0" src="images/<?php print $button_search ?>" />

			</td>
	</tr>
	</tbody></table>
</FORM>
<script language="javascript">
<!--
document.WMDel.confno.focus()
//-->
</script>

</center>

<center>
<iframe name="superframe" src="conf_delete.php" BGCOLOR=white      width=750 height=500 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=auto>

</iframe>
</center>


<?php }elseif ($section=="section21"){?>
<!-- ** ** ** ** ** Part to select the conference ** ** ** ** ** -->
&nbsp;
<br/>
<center><?php print _("Select the room number that you want to handle"); ?>
<FORM METHOD=POST ACTION="conf_control.php?s=1&t=0&order=<?php echo "$order&sens=$sens&current_page=$current_page"; ?>" target="superframe">
<INPUT TYPE="hidden" NAME="current_page" value=0>
	<table class="bar-status" width="70%" border="0" cellspacing="1" cellpadding="2" align="center">
		<tbody>
		<tr>
		<td align="left" bgcolor="#000033">                     
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Conference ROOM"); ?> :</b></font>
			</td>
			<td class="bar-search" align="center" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="confno" value="<?php echo $confno; ?>"></td>
			</tr></table></td>
		</tr>


		<tr>
		<td class="bar-search" align="left" bgcolor="#555577"> </td>

			<td class="bar-search" align="center" bgcolor="#cddeff">
				<input type="image"  name="image16" align="top" border="0" src="images/<?php print $button_search ?>" />

			</td>
	</tr>
	</tbody></table>
</FORM>
</center>

<center>
<iframe name="superframe" src="conf_control.php" BGCOLOR=white      width=750 height=500 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=auto>

</iframe>
</center>

<?php }elseif ($section=="section22" || $section=="section23" || $section=="section24"){?>
<!-- ** ** ** ** ** Part to Update the conference ** ** ** ** ** -->
&nbsp;
<br/>

<?php  if ($section=="section22"){
	$view="Past"; ?>
	<center><?php print _("Select the Conference that you want to Review"); ?>
<?php   } elseif ($section=="section23"){
	$view="Current"; ?>
	<center><?php print _("Select the Conference that you want to Monitor"); ?>
<?php  } else {
	$view="Future"; ?>
	<center><?php print _("Select the Conference that you want to Modify"); ?>
<?php  } ?>

<FORM METHOD=POST NAME="WMCPF" ACTION="conf_update.php?s=<?php echo "$s&t=$t&order=starttime&sens=ASC&current_page=$current_page&view=$view"; ?>" target="superframe">
<INPUT TYPE="hidden" NAME="current_page" value=0>
	<table class="bar-status" width="70%" border="0" cellspacing="1" cellpadding="2" align="center">
		<tbody>
		<tr>
		<td align="left" bgcolor="#000033">
				<font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp; <SPAN title=" Select a specific conference number " class="popup"><?php print _("Search for Conference"); ?> :</SPAN></b></font>
			</td>
			<td class="bar-search" align="center" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="confno" value="<?php echo "$confno"; ?>"></td>
			</tr></table></td>
		</tr>


		<tr>
		<td class="bar-search" align="left" bgcolor="#555577"> </td>

			<td class="bar-search" align="center" bgcolor="#cddeff">
				<input type="image"  name="image16" align="top" border="0" src="images/<?php print $button_search ?>" />

			</td>
	</tr>
	</tbody></table>
</FORM>
</center>

<center>
<iframe name="superframe" src="conf_update.php?s=<?php echo "$s&t=$t&view=$view"; ?>" BGCOLOR=white      width=750 height=500 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=auto>

</iframe>
<script language="javascript">
<!--
document.WMCPF.confno.focus()
//-->
</script>

</center>


<?php }elseif ( isset($user_add_section) && $section==$user_add_section){?>

<!-- ** ** ** ** ** Part to add the user ** ** ** ** ** -->
&nbsp;
<br/>
<center><?php print _("Enter the details about the user to add"); ?>
<FORM METHOD=POST NAME="WMAdd" ACTION="user_add_<?php echo AUTH_TYPE; ?>.php?&s=3&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php echo $current_page?>" target="superframe">
<INPUT TYPE="hidden" NAME="current_page" value=0>
	<table class="bar-status" width="60%" border="0" cellspacing="1" cellpadding="2" align="center">
		<tbody>
                <tr>
                <td align="left" bgcolor="#000033">
                                <font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("First Name"); ?> :</b></font>
                        </td>
                        <td class="bar-search" align="center" bgcolor="#acbdee">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="fname" value="<?php print $first_name ?>"></td>
                        </tr></table></td>
                </tr>
                <tr>
                <td align="left" bgcolor="#000033">
                                <font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Last Name"); ?> :</b></font>
                        </td>
                        <td class="bar-search" align="center" bgcolor="#acbdee">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="lname" value="<?php print $last_name ?>"></td>
                        </tr></table></td>
                </tr>
                <tr>
                <tr>
                <td align="left" bgcolor="#000033">
                                <font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("User Email"); ?> :</b></font>
                        </td>
                        <td class="bar-search" align="center" bgcolor="#acbdee">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="userEmail" value="<?php print $email ?>"></td>
                        </tr></table></td>
                </tr>
                <tr>
                <td align="left" bgcolor="#000033">
                                <font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Telephone"); ?> :</b></font>
                        </td>
                        <td class="bar-search" align="center" bgcolor="#acbdee">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="phone" value="<?php print $telephone ?>"></td>
                        </tr></table></td>
                </tr>

                <tr>
 		<td align="left" bgcolor="#000033">
				<font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Password"); ?> :</b></font>
			</td>
			<td class="bar-search" align="center" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="password" NAME="userPass" value="<?php print $userPass ?>"></td>
			</tr></table></td>
		</tr>
<?php
if($_SESSION['privilege']== 'Admin')
{
?>
		<tr>
		<td align="left" bgcolor="#000033">
				<font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Is Admin")."?"; ?> :</b></font>
			</td>
			<td class="bar-search" align="center" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="radio" NAME="userAdmin" value="0" <?php if(!$userAdmin) echo 'checked'; ?>> <?php print _("No"); ?><br />&nbsp;&nbsp;<INPUT TYPE="radio" NAME="userAdmin" value="1" <?php if($userAdmin) echo 'checked'; ?>> <?php print _("Yes"); ?></td>
			</tr></table></td>
		</tr>
<?php
}
?>


		<tr>
		<td class="bar-search" align="left" bgcolor="#555577"> </td>

			<td class="bar-search" align="center" bgcolor="#cddeff">
	<?php if ($userEmail) { ?>
				<input type="Submit"  name="update" align="top" border="0" value="Update User"/>
                                <input type="Submit"  name="remove" align="top" border="0" value="Remove User"/>

	<?php } else { ?>
				<input type="Submit"  name="add" align="top" border="0" value="<?php print _("Add User"); ?>"/>
	<?php } ?>
			</td>
	        </tr>
	</tbody></table>
</FORM>
<script language="javascript">
<!--
document.WMAdd.confDesc.focus()
//-->
</script>

</center>
<iframe name="superframe" src="user_add_<?php echo AUTH_TYPE; ?>.php" BGCOLOR=white  width=750 height=500 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=auto>

</iframe>
<center>


<?php }elseif ( isset ($user_update_section) && $section==$user_update_section){?>

<!-- ** ** ** ** ** Part to select the conference(s) to delete ** ** ** ** -->
&nbsp;
<br/>
<center><?php print _("Select the user that you want to edit"); ?>
</center>
<center>
<iframe name="superframe" src="user_edit_<?php echo AUTH_TYPE; ?>.php?&s=3&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php echo $current_page?>" BGCOLOR=white      width=750 height=500 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=auto>

</iframe>
</center>

<?php }elseif ($section==$report_section){ ?>
<!-- ** ** ** ** ** Part to select the date for reports ** ** ** ** -->
&nbsp;
<br/>
<center><?php print _("Select the date to view a Report"); ?>
</center>
<center>
<iframe name="superframe" src="daily.php?" BGCOLOR=white      width=750 height=600 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=auto>

</iframe>
</center>

<?php }elseif ($section==$about_section){?>
<?php  contact(); ?>

<?php }elseif ($section==$logoff_section)
{
	$_SESSION['auth'] = 0;
	$_SESSION['userid'] = "";
	$_SESSION['privilege'] = "";
	unset($AUTH_USER);
	unset($AUTH_PW);
	echo _("You have successfully logged off. ");
}
elseif ($section=="section99"){

if (AUTH_TYPE == "sqldb"){
	$Logon_Lable = _("User Email");
}
else
{
	$Logon_Lable = _("Logon Name");
} ?>


<h1><center><?php  echo GUI_TITLE; ?> <?php print _("Login"); ?></center></h1>

<h2><?php print _("Please enter your username and password"); ?>:</h2>
<FORM METHOD=POST  NAME="WMLogon" ACTION=<?php echo "$_SERVER[PHP_SELF]?s=$s&t=$t"; ?>>
<INPUT TYPE="hidden" NAME="current_page" value=0>
        <table class="bar-status" width="70%" border="0" cellspacing="1" cellpadding="2" align="center">
                <tbody>
                <tr>
                <td align="left" bgcolor="#000033">
                                <font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php echo $Logon_Lable; ?>:</b></font>
                        </td>
                        <td class="bar-search" align="left" bgcolor="#acbdee">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT tabindex="100" TYPE="text" NAME="AUTH_USER" value="" size=25></td>
                        </tr></table></td>
                </tr>
                <td align="left" bgcolor="#000033">
                                <font face="arial" size="1" color="#ffffff"><b>&nbsp;&nbsp; <SPAN title=" <?php print _("Your password goes here"); ?> " class="popup"> <?php print _("Password"); ?> :</SPAN></b></font>
                        </td>
                        <td class="bar-search" align="left" bgcolor="#acbdee">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT tabindex="101" TYPE="password" NAME="AUTH_PW" value="" size=25></td>
                        </tr></table></td>
                </tr>
		<tr>
		<td class="bar-search" align="left" bgcolor="#555577"> </td>
		<INPUT TYPE="hidden" NAME="bookId" value=<?php if(isset($bookId)) echo $bookId; ?>>
		<td class="bar-search" align="center" bgcolor="#cddeff">
		<input tabindex="102" type="Submit"  name="Login" align="top" border="0" value="Login "/> </td></tr>
        </tbody></table>
</FORM>
<script language="javascript">
<!--
document.WMLogon.AUTH_USER.focus()
//-->
</script>

</center>
<?php }elseif($section=="section1"){
    //echo "Адресная";
    include("./lib/addressbook.php");
?>

<?php }else{?>

<?php
echo $section;   
?>
<?php }
if(PASSWORD_OPTION=="YES")
{
	if(strlen($np_checked))
	{
		echo "<script>togglePass();</script>\n";
	}
}
?>
	</div>	
		<!-- content END -->
		
		<!-- footer BEGIN -->
		<div id="fedora-footer">
		</div>
		<!-- footer END -->
<pre><?php //print_r($array); ?></pre>
	</body>
</html>
