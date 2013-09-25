<?php
include ("./lib/defines.php");
include ("./lib/functions.php");
include ("./lib/database.php");
include ("locale.php");

session_start();
getpost_ifset(array('view','add','update','remove','uuid','fname','lname','userName','userPass','phone','userEmail','userAdmin'));

// this variable specifie the debug type (0 => nothing, 1 => sql result, 2 => boucle checking, 3 other value checking)
$FG_DEBUG = 0;

// The variable FG_TABLE_NAME define the table name to use
$FG_TABLE_NAME=DB_TABLEUSERS;

// The variable Var_col would define the col that we want show in your table
// First Name of the column in the html page, second name of the field
$FG_TABLE_COL = array();

$FG_TABLE_COL[]=array (_("ID"), "userName", "8%", "center", "", "30");
$FG_TABLE_COL[]=array (_("ConfId"), "confno", "12%", "center", "", "19");
$FG_TABLE_COL[]=array (_("Password"), "pin", "12%", "center", "", "30");
$FG_TABLE_COL[]=array (_("starttime"), "starttime", "15%", "center", "", "30");
$FG_TABLE_COL[]=array (_("endtime"), "endtime", "15%", "center", "", "30");
$FG_TABLE_COL[]=array (_("Callers"), "maxUser", "12%", "center", "", "30","list", $mode_list);

$FG_TABLE_DEFAULT_ORDER = "UserName";
$FG_TABLE_DEFAULT_SENS = "DESC";

// This Variable store the argument for the SQL query
$FG_COL_QUERY='id, email, password, first_name,last_name,telephone, admin';

// The variable LIMITE_DISPLAY define the limit of record to display by page
$FG_LIMITE_DISPLAY=30;
$FG_LIMITE_DISPLAY_BLANK_LINE=5;

// Number of column in the html table

//This variable will store the total number of column
$FG_TOTAL_TABLE_COL = $FG_NB_TABLE_COL;

//This variable define the Title of the HTML table
$FG_HTML_TABLE_TITLE=" - Users : - ";

//This variable define the width of the HTML table
$FG_HTML_TABLE_WIDTH="100%";

if ($FG_DEBUG == 3) echo "<br>Table : $FG_TABLE_NAME  	- 	Col_query : $FG_COL_QUERY";

//if ( is_null ($order) || is_null($sens) ){
	$order = $FG_TABLE_DEFAULT_ORDER;
	$sens  = $FG_TABLE_DEFAULT_SENS;
//}

/* ACTION  *   *   *  * * * *********************************************************/

if($userAdmin)
{
	$userAdmin = 'Admin';
}
else
{
	$userAdmin = 'User';
}
if ($add){
	if ($_SESSION['privilege'] == 'Admin') {
		if (checkEmail($userEmail)) {
			$FG_TABLE_CLAUSE="email='$userEmail'";
        		$conflict = $db->getOne("SELECT COUNT(*) FROM $FG_TABLE_NAME WHERE $FG_TABLE_CLAUSE");

			if (!intval($conflict)){
//      	        	  $userPass = md5($userPass);
				$data = array(NULL,$userEmail,$userPass,$fname,$lname,$phone,$userAdmin);
				$query = "INSERT INTO $FG_TABLE_NAME VALUES (?,?,?,?,?,?,?)";
				$result = $db->query($query, $data);
			}
		} else {
			$Error = "You have entered an invalid email address";
		}
	}
}

if (($update)){
	if ($_SESSION['privilege'] == 'Admin') {
		if (checkEmail($userEmail)) {
			$FG_EDITION_CLAUSE=" id='$uuid' ";

			$FG_TABLE_CLAUSE="id='$uuid'";

	  	      $conflict = $db->getOne("SELECT COUNT(*) FROM $FG_TABLE_NAME WHERE $FG_EDITION_CLAUSE");

        		if($userPass)
        		{
//      	      		$userPass = md5($userPass);
	            		$userPass = "$userPass";
        		}
	        	if (intval($conflict) == 1){
        			$data = array($userEmail,$userAdmin,$fname,$lname,$phone,$userPass);
				$query = "UPDATE $FG_TABLE_NAME SET email=? ,admin=? ,first_name=? ,last_name=? ,telephone=? ,password=? WHERE $FG_EDITION_CLAUSE";
				$result = $db->query($query, $data);
				$conflict=0;
			}
		} else {
			 $Error = "You have entered an invalid email address";
		}
	}
}

if ($remove){
	if ($_SESSION['privilege'] == 'Admin') {
		$query = "DELETE FROM $FG_TABLE_NAME WHERE id=?";
		$data = array($uuid);
		$result = $db->query($query, $data);
	}
}
/*    *    *   *   *  * * * *********************************************************/
	
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
		
		//-->
		</script>
	</head>
	<body>

<?php	if ($userAdmin == "Admin") {
		$isAdmin="Yes";
	} else {
		 $isAdmin="No";
	}
?>

<?php if (($add || $update) && (intval($conflict) == 0) && !($remove)) { 
	if (!strlen($Error)) { ?>
		<center><strong> <?php print _("User Created"); ?>: </strong></center><br>
		<center><?php print _("User Name"); ?>: <?php print $fname." ".$lname ?> <br></center>
		<center><?php print _("User Password"); ?>:  <?php print $userPass ?> <br></center>
		<center><?php print _("User Email"); ?>:  <?php print $userEmail ?> <br></center>
		<center><?php print _("Is Admin"); ?>:  <?php print $isAdmin  ?><br></center>

		<FORM METHOD=POST ACTION="./meetme_control.php?&s=3&t=1" target="_top">
		<center><INPUT TYPE="Submit" VALUE="Continue"/></center>
		</FORM>
	<?php } else { ?>
		<center><strong> <?php print $Error; ?> </strong></center>
	<?php }  ?>
<?php } ?>

<?php if($remove) { ?>

<center><strong> <?php print _("User Deleted"); ?> </strong></center><br>
<FORM METHOD=POST ACTION="./meetme_control.php?&s=3&t=1" target="_top">
<center><INPUT TYPE="Submit" VALUE="Continue"/></center>
</FORM>

<?php } ?>

<?php if ((intval($conflict) != 0) ) { ?>

<center><strong> <?php print _("User Not Created"); ?>: </strong></center><br>


<?php } ?>
<br><br>
	</body>
</html>

