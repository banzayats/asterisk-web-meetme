<?php

$paypal = '';

print <<<LEFTNAV1
<div id="fedora-side-left">
<div id="fedora-side-nav-label">Site Navigation:</div>	<ul id="fedora-side-nav">
LEFTNAV1;

	$nkey=array_keys($array);
    	$i=0;
    	while($i<sizeof($nkey))
	{
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
       		}
		else
		{					
			echo "\n\t<li>$op_strong<a href=\"$racine?s=$i\">".$array[$nkey[$i]]."</a>$cl_strong";
		}
		echo "</li>\n";
       		$i++;
	}
	echo "</ul>";
if ($paypal=="OK")
{
print <<<PAYPAL
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
PAYPAL;
}
echo "</div>";
?>
