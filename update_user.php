<?php
include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./lib/database.php");
include ("locale.php");

getpost_ifset(array('s', 't', 'uuid'));
                                                                                                                             
if (defined('AUTH_TYPE')){
        getpost_ifset(array('AUTH_USER', 'AUTH_PW'));
        session_set_cookie_params(0, '/' );
        session_start();
                                                                                                                             
        if ($_SESSION['auth']) {
                if (($_SESSION['lifetime']) <= time()){
                        unset($_SESSION['auth']);
                        unset($_SESSION['privilege']);
                        unset($_SESSION['userid']);
                        unset($AUTH_USER);
                        unset($AUTH_PW);
                }
        }
                                                                                                                             
        if ( $AUTH_USER != NULL &&  $AUTH_PW != NULL ){
                $user = new userSec();
                $user -> authenticate($AUTH_USER, $AUTH_PW);
                $user -> isAdmin($AUTH_USER);
        }
                                                                                                                             
        if ( !($_SESSION['auth']) ) {
                $section="section99";
        }
}
include ("./lib/header.php");
include ("./lib/leftnav.php");


$query = "SELECT u.first_name, u.last_name, u.email, u.telephone, u.admin, u.password FROM user u WHERE u.id =?";
$data = array($uuid);
$result=$db->query($query, $data);
$row = $result->fetchRow(DB_FETCHMODE_ASSOC);

if (is_array($row))
	extract($row);
if($admin == 'Admin')
{
	$userAdmin = 1;
}
else
{
	$userAdmin = 0;
}
?>
<!-- content BEGIN -->
<div id="fedora-middle-two">
<div class="fedora-corner-tr">&nbsp;</div>
<div class="fedora-corner-tl">&nbsp;</div>
<div id="fedora-content">


<!-- ** ** ** ** ** Part to add the user ** ** ** ** ** -->
&nbsp;
<br/>
<center><?php print _("Enter the details about the user to add"); ?>
<?php if ($_SESSION['privilege'] == 'Admin') { ?>
<FORM METHOD=POST NAME="WMAdd" ACTION="user_add_<?php echo AUTH_TYPE; ?>.php?&s=3&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php echo $current_page?>" target="superframe">
<INPUT TYPE="hidden" NAME="current_page" value=0>
<INPUT TYPE="hidden" NAME="uuid" value="<?php echo $uuid; ?>">
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
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="password" NAME="userPass" value="<?php print $password ?>"></td>
			</tr></table></td>
		</tr>

		<tr>
		<td align="left" bgcolor="#000033">
				<font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Is Admin"); ?>? :</b></font>
			</td>
			<td class="bar-search" align="center" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;
			<INPUT TYPE="radio" NAME="userAdmin" value="0" <?php if(!$userAdmin) echo 'checked'; ?>> <?php print _("No"); ?>
			<br />&nbsp;&nbsp;
			<INPUT TYPE="radio" NAME="userAdmin" value="1" <?php if($userAdmin) echo 'checked'; ?>> <?php print _("Yes"); ?>
			</td>
			</tr></table></td>
		</tr>

		<tr>
		<td class="bar-search" align="left" bgcolor="#555577"> </td>

			<td class="bar-search" align="center" bgcolor="#cddeff">
	<?php if ($email) { ?>
				<input type="Submit"  name="update" align="top" border="0" value="<?php print _("Update User"); ?>" onclick="if(confirm('<?php print _("Are you sure"); ?>?'))return true; else return false;">
                                <input type="Submit"  name="remove" align="top" border="0" value="<?php print _("Remove User"); ?>" onclick="if(confirm('<?php print _("Are you sure"); ?>?'))return true; else return false;">

	<?php } else { ?>
				<input type="Submit"  name="add" align="top" border="0" value="Add User"/>
	<?php } ?>
			</td>
	        </tr>
<?php }  else { ?>
	<br><br>
	<b><?php print _("You Are not an Administrator of this system"); ?>. </b>
<?php } ?>

	</tbody></table>
</FORM>
<script language="javascript">
<!--
document.WMAdd.confDesc.focus()
//-->
</script>

</center>
<iframe name="superframe" src="user_add_<?php echo AUTH_TYPE; ?>.php" BGCOLOR=white  width=750 height=400 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=no>

</iframe>
<center>
        </div>
                <!-- content END -->
                                                                                                                             
                <!-- footer BEGIN -->
                <div id="fedora-footer">
                </div>
                <!-- footer END -->
                                                                                                                             
        </body>
</html>
