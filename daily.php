<?php
include ("./lib/defines.php");
include ("./lib/functions.php");
include ("locale.php");

session_start(); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<title><?php print GUI_TITLE; ?> <?php print _("control"); ?></title>
		<meta http-equiv="Content-Type" content="text/html">
		
		<link rel="stylesheet" type="text/css">
		<style type="text/css" media="screen">
			@import url("css/content.css");
			@import url("css/docbook.css");
		</style>
		
		<script language="JavaScript" type="text/JavaScript">
		<!--
		function MM_openBrWindow(theURL,winName,features) { //v2.0
		  window.open(theURL,winName,features);
		}
		
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
			if (i < 9){
				 objForm.day.options[i].value = "0"+(i+1);
			} else {
				objForm.day.options[i].value = i+1;
			}
        	}
        	objForm.day.options.selectedIndex = 0;
	
		reportUpdate(objForm);
        }
	function reportUpdate(objForm){
		var ReportId = document.getElementById('ReportPNG');
                var Reportsrc="./lib/report-gen.php?now="+objForm.year.options[objForm.year.selectedIndex].value+"-"+objForm.month.options[objForm.month.selectedIndex].value+"-"+objForm.day.options[objForm.day.options.selectedIndex].value;
                ReportId.src=Reportsrc;
	}

		//-->

		</script>

	</head>
	<body>



<br><br>

<!-- ** ** ** ** ** Part to display the conference  ** ** ** ** ** -->
<center>

<?php 
	$now=subStr(getConfDate(),0,10);
	$starttime=strtotime(getConfDate());
?>                  
<FORM METHOD=POST NAME="WMReport">

<b><?php print _("Total Conferences Scheduled and used on"); ?>: </b>

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
<SELECT NAME="day" onChange="reportUpdate(this.form)">
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

<SELECT NAME="year" onChange="monthPop(this.form,this.form.month.selectedIndex)" >
<?php  for ($i=0; $i<=3; $i++){
$tmp=intval(date("Y", $starttime));
   if ( $tmp-$i == intval(date("Y", $starttime)) ){ ?>
        <OPTION SELECTED VALUE="<?php echo $tmp; ?>"><?php echo $tmp; ?>
<?php  } else { ?>
        <OPTION VALUE="<?php echo $tmp-$i; ?>"><?php echo $tmp-$i; ?>
<?php } ?>
<?php } ?>
</SELECT>
</FORM>

<br></br>
<img src="./lib/report-gen.php?now=<?php echo $now ?>" id="ReportPNG" border=1>
</center>
<br></br>&nbsp;&nbsp;
<br></br>&nbsp;&nbsp;

		<!-- END -->
	</body>
</html>
