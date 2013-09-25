<?php
include ("./lib/defines.php");
include ("./lib/functions.php");
include ("locale.php");

session_start();
getpost_ifset(array('confno', 'pin'));

if (!is_numeric(substr($confno, 0, 1)))
	$confno = 0;

if (!is_numeric(substr($pin, 0, 1)))
	$pin = 0;

if (isset($confno)){
      	unset($_SESSION['confno']);
      	unset($_SESSION['pin']);
	session_set_cookie_params(0, '/' );
	$_SESSION['confno'] = $confno;
	$_SESSION['pin'] = $pin;
}
else
{
        if (($_SESSION['lifetime']) <= time()){
        	unset($_SESSION['confno']);
               	unset($_SESSION['pin']);
	}
}
include ("./lib/header.php");
?>

		<!-- content BEGIN -->
		<div id="fedora-middle-one">
			<div class="fedora-corner-tr">&nbsp;</div>
			<div class="fedora-corner-tl">&nbsp;</div>
			<div id="fedora-content">
<center>
<h1><?php  echo "Listen to previously recorded conferences"; ?></h1>

<h2><?php print _("Please enter the conference number and pin").":"; ?></h2>
<FORM METHOD=POST  NAME="WMLogon" ACTION=<?php echo $_SERVER[PHP_SELF]; ?>>
<INPUT TYPE="hidden" NAME="current_page" value=0>
        <table class="bar-status" width="35%" border="0" cellspacing="1" cellpadding="2" align="center">
                <tbody>
                <tr>
                <td align="left" bgcolor="#000033">
                                <font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Conference #"); ?> :</b></font>
                        </td>
                        <td class="bar-search" align="center" bgcolor="#acbdee">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT tabindex="100" TYPE="text" NAME="confno" value="<?php echo $_SESSION['confno']; ?>"></td>
                        </tr></table></td>
                </tr>
                <td align="left" bgcolor="#000033">
                                <font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Pin"); ?> :</b></font>
                        </td>
                        <td class="bar-search" align="center" bgcolor="#acbdee">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT tabindex="101" TYPE="password" NAME="pin" value="<?php echo $_SESSION['pin']; ?>"></td>
                        </tr></table></td>
                </tr>
		<tr>
		<td class="bar-search" align="left" bgcolor="#555577"> </td>
		<INPUT TYPE="hidden" NAME="bookId" value=<?php echo $bookId; ?>>
		<td class="bar-search" align="center" bgcolor="#cddeff">
		<input tabindex="102" type="Submit"  name="Find" align="top" border="0" value="Find "/> </td></tr>
        </tbody></table>
</FORM>
<script language="javascript">
<!--
document.WMLogon.confno.focus()
//-->
</script>
<iframe name="superframe" src="conf_listen.php?confno=<?php echo "$_SESSION[confno]&pin=$_SESSION[pin]"; ?>" BGCOLOR=white      width=750 height=400 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=yes>
</iframe>

</center>

	</div>	
		<!-- content END -->
		
		<!-- footer BEGIN -->
		<div id="fedora-footer">
		</div>
		<!-- footer END -->
	</body>
</html>
