<?php
include ("./defines.php");
include ("./database.php");
include ("./functions.php");

getpost_ifset('now');
$total=0;
$max=0;
$used_min = 0;

if (!isset($now))
	$now = substr(getConfDate(),0,10);

for ($i=0; $i < 24; $i++)
	$used_count[$i] = 0;

for ($t=0; $t < 24; $t++){

	if ($t < 10){
		$hour = $now." 0".$t;
	} else {
		$hour = $now." ".$t;
	}
	
	$query = "SELECT bookId FROM ".DB_TABLESCHED." WHERE starttime LIKE '%$hour%'";
	$rows = $db->query($query);

	$i = 0;
	$count[$t] = $rows->numRows();
	$total = $total +  $count[$t];

	while ($rows->fetchInto($result)){
		//$query = "Select (SUM(duration)/60) AS uMins FROM ".DB_TABLECDR." WHERE bookId = '$result[0]'";
		$query = "Select (SUM(duration)/60) AS uMins FROM ".DB_TABLECDR." WHERE userfield = '$result[0]'";
		$utemp = $db->query($query);
		$utemp2 = $utemp->fetchRow(DB_FETCHMODE_ASSOC);
		if ($utemp2['uMins'])
			$used_count[$t]++;
		$used_min = $used_min + $utemp2['uMins'];
	}

}


for ($t=0; $t < 24; $t++){
        if ($count[$t] > $max)
                $max = $count[$t];
}

$im_height=300;
$im_width=600;
$im_key=100;
$hpos=20;


$im =imagecreate($im_width, $im_height+$im_key);
$white = imagecolorallocate($im,255,255,255);
$blue = imagecolorallocate($im,0,0,255);
$lightblue = imagecolorallocate($im,0,255,255);
$red = imagecolorallocate($im,255,0,0);
$black = imagecolorallocate($im,0,0,0);

imageline($im, 10, ($im_height-14), $im_width, ($im_height-14), $black);
imageline($im, 0, $im_height, $im_width, $im_height, $black);
imageline($im, 10, 0, 10, ($im_height-14), $black);
imageline($im, 10, 0, 10, ($im_height-14), $black);
imageline($im, 0, $im_height, 10, ($im_height-14), $black);

imagestring($im, 4, 5, ($im_height+10), "Key:", $black);
imagestring($im, 4, 5, ($im_height+25), "    Conferences Scheduled", $blue);
imagestring($im, 4, 5, ($im_height+40), "    Conferences used", $lightblue);
imagestring($im, 4, 5, ($im_height+70), "Minutes Used: $used_min", $black);
if ($max > 0){
	$vscale=(($im_height-20)/$max);
	for ($i=0; $i < $max; $i++){
        	imagestring($im, 4, 2, 20+($i*$vscale), ($max-$i), $blue);
        	imageline($im, 10, 20+($i*$vscale), $im_width, 20+($i*$vscale), $black);
	}

	for ($t=0; $t < 24; $t++){
        	imagestring($im, 2, ($hpos+3), ($im_height-12), $t, $red);
        	if ($count[$t] > 0){
                	$vpos = 300 - ($count[$t]*$vscale);
                	imagerectangle($im,$hpos,$vpos,($hpos+15),($im_height-15),$black);
                	imagefilledrectangle($im,$hpos,$vpos,($hpos+15),($im_height-15),$blue);
        	}
        	if($used_count[$t] > 0){
                	$vpos = 300 - ($used_count[$t]*$vscale);
                	imagefilledrectangle($im,$hpos,$vpos,($hpos+15),($im_height-15),$lightblue);
        	}
        	$hpos += 24;
	}
} else {
	imagestring($im, 10, ($im_width/3), ($im_height/2), _("No Conferences Scheduled"), $red);
}
imagepng($im);
imagedestroy($im);

?>
