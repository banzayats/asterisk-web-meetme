
<?php } elseif ($section=="section30"){ ?>

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
				<font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("User Name"); ?> :</b></font>
			</td>
			<td class="bar-search" align="center" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="userName" value="<?php print $userName ?>"></td>
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
		<tr>
		<td align="left" bgcolor="#000033">                     
				<font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("User Email"); ?> :</b></font>
			</td>
			<td class="bar-search" align="center" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="text" NAME="userEmail" value="<?php print $userEmail ?>"></td>
			</tr></table></td>
		</tr>
		<tr>
		<td align="left" bgcolor="#000033">
				<font face="verdana" size="1" color="#ffffff"><b>&nbsp;&nbsp;<?php print _("Is Admin"); ?>? :</b></font>
			</td>
			<td class="bar-search" align="center" bgcolor="#acbdee">
			<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>&nbsp;&nbsp;<INPUT TYPE="radio" NAME="userAdmin" value="0" <?php if(!$userAdmin) echo 'checked'; ?>> No<br />&nbsp;&nbsp;<INPUT TYPE="radio" NAME="userAdmin" value="1" <?php if($userAdmin) echo 'checked'; ?>> Yes</td>
			</tr></table></td>
		</tr>


		<tr>
		<td class="bar-search" align="left" bgcolor="#555577"> </td>

			<td class="bar-search" align="center" bgcolor="#cddeff">
	<?php if ($userName) { ?>
				<input type="Submit"  name="update" align="top" border="0" value="Update User"/>
                                <input type="Submit"  name="remove" align="top" border="0" value="Remove User"/>

	<?php } else { ?>
				<input type="Submit"  name="add" align="top" border="0" value="Add User"/>
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
<iframe name="superframe" src="user_add_<?php echo AUTH_TYPE; ?>.php" BGCOLOR=white  width=750 height=400 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=no>

</iframe>
<center>


<?php }elseif ($section=="section31"){?>

<!-- ** ** ** ** ** Part to select the conference(s) to delete ** ** ** ** -->
&nbsp;
<br/>
<center><?php print _("Select the user that you want to edit"); ?>
</center>
<center>
<iframe name="superframe" src="user_edit_<?php echo AUTH_TYPE; ?>.php?&s=3&t=0&order=<?php echo $order?>&sens=<?php echo $sens?>&current_page=<?php echo $current_page?>" BGCOLOR=white      width=750 height=400 marginWidth=0 marginHeight=0  frameBorder=0  scrolling=yes>

</iframe>
</center>

<?php }elseif ($section=="section5"){?>
