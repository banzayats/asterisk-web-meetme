<?php

// gettext reworked

include ("./lib/defines.php");
include ("./lib/functions.php");

getpost_ifset(array('s', 't'));


$array = array ("MEETME", "CONTACT");
$s = $s ? $s : 0;
$section="section$s$t";

$racine=$_SERVER[PHP_SELF];
$update = "21 March 2005";


$paypal="OK"; //OK || NOK

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>		
		<title><?php print GUI_TITLE; print _("control"); ?></title>
		<meta http-equiv="Content-Type" content="text/html">
		<SCRIPT LANGUAGE="JavaScript" SRC="encrypt.js"></SCRIPT>
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
				 <table border="0" cellpadding="0" cellspacing="0"><tr><td><img src="images/asterisk.gif"  alt="CDR (Call Detail Records)"></td><td>
				 <H1><font color=#990000>&nbsp;&nbsp;&nbsp;<?php print GUI_TITLE; print _("Control"); ?></font></H1></td></tr></table>
			</div>

		</div>
		<div id="fedora-nav"></div>
		<!-- header END -->
		
		<!-- leftside BEGIN -->
		<div id="fedora-side-left">
		<div id="fedora-side-nav-label"><?php print _("Site Navigation").":" ?></div>	<ul id="fedora-side-nav">
		<?php 
			$nkey=array_keys($array);
    		$i=0;
    		while($i<sizeof($nkey)){
			
				$op_strong = (($i==$s) && (!is_string($t))) ? '<strong>' : '';
				$cl_strong = (($i==$s) && (!is_string($t))) ? '</strong>' : '';
									
        		if(is_array($array[$nkey[$i]])){
					
					
					
					echo "\n\t<li>$op_strong<a href=\"$racine?s=$i\">".$nkey[$i]."</a>$cl_strong";
									
					$j=0;
					while($j<sizeof($array[$nkey[$i]] )){
						$op_strong = (($i==$s) && (isset($t)) && ($j==intval($t))) ? '<strong>' : '';
						$cl_strong = (($i==$s) && (isset($t))&& ($j==intval($t))) ? '</strong>' : '';						
						echo "<ul>";						
						echo "\n\t<li>$op_strong<a href=\"$racine?s=$i&t=$j\">".$array[$nkey[$i]][$j]."</a>$cl_strong";
						echo "</ul>";
						$j++;						
					}
						
        		}else{					
					echo "\n\t<li>$op_strong<a href=\"$racine?s=$i\">".$array[$nkey[$i]]."</a>$cl_strong";
				}
				echo "</li>\n";
        		
        		$i++;
    		}
			
		?>

			</ul>
			<?php if ($paypal=="OK"){?>
		<center>
			<br><br>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="info@areski.net">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="EUR">
<input type="hidden" name="tax" value="0">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
</form>
</center>
			<?php } ?>
		</div>

		<!-- leftside END -->

		<!-- content BEGIN -->
		<div id="fedora-middle-two">
			<div class="fedora-corner-tr">&nbsp;</div>
			<div class="fedora-corner-tl">&nbsp;</div>
			<div id="fedora-content">



<?php if ($section=="section0"){?>


<h1><center><?php print GUI_TITLE;?> <?php print _("Control") ?></center></h1>

<?php print _("Intro-Text-About"); ?>
<!--
<h2>Communicate and control your audience.</h2>

  <p>Conferencing puts you in complete control of your virtual meetings, bringing them to life in a fully interactive meeting 
  room over the Internet.</p>

  <p>Now, you can control your conference on your PC screen and manage a dynamic visual presentation over the Internet. </p>

    
<h2>Dial phonenumber and open the room</h2>

  <p>Open your virtual conference room by dialing the number over the Internet. 
  Call the participants to afford them to join the room.</p>
    
  <b>Control options :</b>
	<ul>
		<li>call out and join new participants to the conference room</li>
		<li>control voice rights (user can talk&listen or only listen )</li>
		<li>give voice to the next user depending of the entrance order</li>
		<li>eject user from the conference room</li>		
		<li>call out the operator of each room when an user join an empty room</li>
		<li>...</li>		
	</ul>
	
	<br>
	
	
<br>	
<br>
<h3>Installation Instruction & Download</h3>

<ul>
	<li><b>1)</b> cp phpagi.example.conf /etc/asterisk/phpagi.conf<br/></li>
	
	<li><b>2)</b> vi /etc/asterisk/phpagi.conf<br/>Change the file according to your manager settings</li>
	
	<li><b>3)</b> Enjoy the Web-MeetME UI !<br/>I told you it was easy...</li>
	
	
	<li><b>4)</b> Don't forget to make a donation ;) <br>you will feel better after this...
");
-->	
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="info@areski.net">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="EUR">
<input type="hidden" name="tax" value="0">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
</form>

	</li>
	
	
</ul>
<br>





<br><br>
<h3><?php print _("Screen-shot"); ?></h3>
<a href="images/Screenshot-0.png"><img src="images/Screenshot-0.png" width=650 alt="<?php print GUI_TITLE; ?> Control"></a>
<br><br>

<a href="images/Screenshot-1.png"><img src="images/Screenshot-1.png" width=650 alt="<?php print GUI_TITLE; ?> Control"></a>
<br><br>

	

<?php }elseif ($section=="section1"){?>
		<h1><?php print _("Developer team ..."); ?></h1>        
		<br>
        <table width="90%">
          
          <tr> 
            <td background="./images/div_grad.gif">&nbsp;</td>
          </tr>
		  <tr> 
            <td>
			<a href='javascript:bite("3721 945 4728 2762 3565 3554 2008 1380 654 3721 3554 4468 3007 3877 4828 654",5123,2981)'><?php print _("Click to email me"); ?></a>
			
            </td>
          </tr>          
          
        </table>
		<br><br><em><strong><?php print _("Last update").":"; ?></strong></em> <?php echo $update?><br>


<?php }else{?>
	<h1><?php print _("Coming soon")." ..."; ?></h1>
   
<?php }?>
		</div>

			<div class="fedora-corner-br">&nbsp;</div>
			<div class="fedora-corner-bl">&nbsp;</div>
		</div>
		<!-- content END -->
		
		<!-- footer BEGIN -->
		<div id="fedora-footer">

			<br>
			<?php
				$fp = fopen("counter.txt","r");
				$count = fread ($fp, filesize ("counter.txt"));
				fclose($fp);
				$count = intval($count);
				$count++;
				$fp = fopen("counter.txt","w+");
				fputs($fp, $count);
				fclose($fp);
				echo "Hits: $count";
			?>


		</div>
		<!-- footer END -->
	</body>
</html>
