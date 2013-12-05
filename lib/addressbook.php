<?php
//include ("./database.php");
$col=30;
getpost_ifset(array('name', 'surname', 'number', 'email', 'search', 'of', 'deladdres', 'confno', 'book','rx','tx', 'listnum', 'listupdate'));
$name = substr(iconv("cp1251","UTF-8",preg_replace("/[^\x30-\x39\x41-\x5A\x61-\x7A\x20\x5F\xC0-\xFF\xA8\xB8]/","",iconv("UTF-8","cp1251",$name))),0,80);
$surname = substr(iconv("cp1251","UTF-8",preg_replace("/[^\x30-\x39\x41-\x5A\x61-\x7A\x20\x5F\xC0-\xFF\xA8\xB8]/","",iconv("UTF-8","cp1251",$surname))),0,80);
$number = substr(preg_replace("/[^\x30-\x39]/","",$number),0,20);
$search = substr(iconv("cp1251","UTF-8",preg_replace("/[^\x30-\x39\x41-\x5A\x61-\x7A\x20\x5F\xC0-\xFF\xA8\xB8]/","",iconv("UTF-8","cp1251",$search))),0,80);
$of = preg_replace("/[^\x30-\x39]/","",$of);
$deladdres = preg_replace("/[^\x30-\x39]/","",$deladdres);
$confno = preg_replace("/[^\x30-\x39]/","",$confno);
$book = preg_replace("/[^\x30-\x39]/","",$book);
$rx = preg_replace("/[^\x2D\x30-\x39]/","",$rx);
$tx = preg_replace("/[^\x2D\x30-\x39]/","",$tx);

if (($rx < -20) || (20 < $rx)) { $rx=0; }
if (($tx < -20) || (20 < $tx)) { $tx=0; }

if (strlen($name)==0) unset($name);
if (strlen($surname)==0) unset($surname);
if (strlen($number)==0) unset($number);
if (strlen($email)==0) unset($email);
if (strlen($search)==0) unset($search);
if (strlen($of)==0) $of=0;
if (strlen($deladdres)==0) unset($deladdres);
if (strlen($confno)==0) unset($confno);
if (strlen($book)==0) unset($book);

if (isset($confno)) $col=7;

$error_flag=0;

$FG_USER=$_SESSION['userid'];

$NumOfLists=3;

if ($listupdate) {
    foreach($_POST['id'] as $ids=> $vals){
        $str = '';
        $sql = '';
        foreach ($vals as $val){
            $str .= $val;
        }
        for ($i=1;$i<=$NumOfLists;$i++){
            if (strpos($str,"$i") !== false){
                $sql .= " list_$i=1, ";
            } else {
                $sql .= " list_$i=0, ";
            }
        }
        $sql = rtrim($sql,", ");
        $query = "UPDATE addressbook SET $sql WHERE id=$ids;";
        $update_result = $db->query($query);
    }
    if ($update_result) echo '<center><font color="green">'._("Lists successfully updated").'.</font></center>';
}

if ($listnum) {
    $LIST = "AND list_$listnum=1";
} else {
    $LIST = "";
}

if ($deladdres>0 && !isset($confno))
{
	$query = "DELETE FROM `addressbook` WHERE `id`=$deladdres AND `listOwner`='$FG_USER' LIMIT 1";
	$result = $db->query($query);
	if ($result)
	{
		echo '<center><font color="red">'._("The record is deleted").'.</font></center>';
	} else {
		echo '<center><font color="red">'._("Error deleting record").'.</font></center>';
	}
}


if (isset($name) && isset($number) && isset($search) && !$error_flag)
{
	echo '<center><font color="red">'._("Itrusion detected! The data of the current session transferred to the administrator").'.</font></center>';
	$error_flag=1;
}


if ((isset($name) || isset($surname)) && !isset($number) && !isset($search) && !$error_flag)
{
	echo '<center><font color="red">'._("Unacceptable number").'.</font></center>';
	$error_flag=1;
}

if (!isset($name) && isset($number) && !isset($search) && !$error_flag)
{
	echo '<center><font color="red">'._("Unacceptable name").'.</font></center>';
	$error_flag=1;
}

if (!isset($name) && !isset($number) && isset($search) && (strlen($search)<3) && !$error_flag)
{
	echo '<center><font color="red">'._("Unacceptable search string (minimum length - three characters)").'.</font></center>';
	$error_flag=1;
}

if (isset($name) && isset($number) && (strlen($search)==0) && !isset($confno) && !$error_flag)
{
	$data = array($name,$surname,$number,$email,$rx,$tx,$FG_USER);
	$query = "INSERT INTO `addressbook` (`name`,`surname`,`number`,`email`,`rx`,`tx`,`listOwner`) VALUES (?,?,?,?,?,?,?)";
	$result = $db->query($query, $data);

	if ($result)
	{
		echo '<center><font color="green">'._("The record has added").'.</font></center>';
	} else {
		echo '<center><font color="red">'._("Error adding  record").'.</font></center>';
	}
}




if (!isset($confno))
{
?>
<center>
<table border=0 align="center" >
<thead>
    <tr>
        <th align="center"><?php echo _("Name");?></th>
        <th align="center"><?php echo _("Surname");?></th>
        <th align="center"><?php echo _("Telephone");?></th>
        <th align="center"><?php echo _("User Email");?></th>
        <th align="center"><?php echo _("RX");?></th>
        <th align="center"><?php echo _("TX");?></th>
        <th align="center">&nbsp;</th>
    </tr>
</thead>
<tbody>
    <tr>
        <form method="POST" action="<?php echo preg_replace("/&of=\d+|&deladdres=\d+/","",$_SERVER['REQUEST_URI']);?>">
        <td align="left"><input type="text" name="name" size="30" maxlength="60" value="<?php if (isset($name)) echo $name; ?>"/></td>
        <td align="left"><input type="text" name="surname" size="30" maxlength="60" value="<?php if (isset($surname)) echo $surname; ?>"/></td>
        <td align="center"><input type="text" name="number" size="20" maxlength="20" value="<?php if (isset($number)) echo $number; ?>"/></td>
        <td align="center"><input type="text" name="email" size="30" maxlength="60" value="<?php if (isset($email)) echo $email; ?>"/></td>
        <td align="center">
            <select name="rx">
            <?php
                for ($i = 20; $i >= -20; $i--){
                    if ($i == 0) {
                        echo "<option value=\"$i\" selected=\"selected\">$i</option>";
                    } else {
                        echo "<option value=\"$i\">$i</option>";
                    }
                }
            ?>
            </select></td>
        <td align="center">
            <select name="tx">
            <?php
                for ($i = 20; $i >= -20; $i--){
                    if ($i == 0) {
                        echo "<option value=\"$i\" selected=\"selected\">$i</option>";
                    } else {
                        echo "<option value=\"$i\">$i</option>";
                    }
                }
            ?>
            </select></td>
        <td align="center"><input type="submit" value="<?php echo _("Add");?>" /></td>
        </form>
    </tr>
<?php
} else {
    echo "<table border=0 align=\"center\">";
}
?>
    <tr>
        <form method="POST" action="<?php echo preg_replace("/&of=\d+|&deladdres=\d+/","",$_SERVER['REQUEST_URI']);?>">
        <td align="center" colspan=2><input type="text" name="search" size="67" maxlength="67" value="<?php if (isset($search)) echo $search; ?>"/></td>
        <td align="center"><input type="submit" value="<?php echo _("Search");?>" /></td>
        </form>
    </tr>
</tbody>
</table>

<?php

if (defined ('AUTH_TYPE'))
{
    $client_clause = "listOwner='$FG_USER' $LIST";
    $client_clause2 = "AND $client_clause";

    if ($FG_CLAUSE=="") {
        $FG_CLAUSE = $client_clause;
    } else {
        $FG_CLAUSE  = "$FG_CLAUSE $client_clause2";
    }
}

if (!$error_flag)
{
    if (isset($search)) $FG_CLAUSE="`listOwner`='$FG_USER' AND (`name` like '%$search%' OR `surname` like '%$search%' OR `number` like '%$search%' OR `email` like '%$search%') ";
    $FG_CLAUSE .= " ORDER BY `name` ASC ";
    if (isset($of)) $FG_CLAUSE .= "LIMIT $of, $col";
    for ($i = 1; $i <= $NumOfLists; $i++){
        $FG_LIST .= ",`list_$i`";
    }
    $query = "SELECT SQL_CALC_FOUND_ROWS `id`,`name`,`surname`,`number`, `email`,`rx`,`tx` $FG_LIST FROM `addressbook` WHERE $FG_CLAUSE";
    $result = $db->query($query);
    $nb_record = $db->getOne("SELECT FOUND_ROWS()");
    echo _("Records found").": <b>$nb_record</b>";
    $stran = stran($nb_record,$col,$of,$_SERVER['REQUEST_URI']."&of=");
    echo $stran;
?>

<form method="POST" action="<?php echo preg_replace("/&of=\d+|&deladdres=\d+/","",$_SERVER['REQUEST_URI'])."&listupdate=1";?>">
<table border="1" cellpadding="5" cellspacing="0" align="center" width="800">
    <thead>
        <tr>
            <th align="center"><?php echo _("Name");?></th>
            <th align="center"><?php echo _("Surname");?></th>
            <th align="center" width="20%"><?php echo _("Telephone");?></th>
            <th align="center" width="20%"><?php echo _("User Email");?></th>
            <th align="center" width="6%"> RX </th>
            <th align="center" width="6%"> ТX </th>
            <?php
            if ($listnum == "" || isset($confno)) {
                echo "<th align=\"center\" width=\"5%\">"._("Action")."</th>";
                if (!isset($confno)){
                    echo "<th align=\"center\" width=\"15%\">"._("Lists")."</th>";
                }
            }
            ?>
        </tr>
    </thead>
    <tbody>

<?php
if (!isset($confno)) {
    while($row = $result->fetchRow()) {
        for($i = 0; $i <= 6; $i++) {
            if ($row[$i] == "") $row[$i] = "&nbsp;";
        }
        echo "<tr><td>$row[1]</td><td>$row[2]</td><td>$row[3]</td><td>$row[4]</td><td>$row[5]</td><td>$row[6]";
        if ($listnum == "") {
            echo "</td><td><a href=\"".$_SERVER['REQUEST_URI']."&deladdres=$row[0]\">"._("Delete")."</td>\n<td align=\"center\">\n\t";
            echo "<input style=\"display:none\" type=\"checkbox\"  name=\"id[$row[0]][]\" title=\""._("Add contact to list #")."0\"  value=\"0\" checked>";
            for ($i = 1; $i <= $NumOfLists; $i++){
                echo "<input type=\"checkbox\" name=\"id[$row[0]][]\" title=\""._("Add contact to list #").$i."\" value=\"$i\"";
                if ($row[6+$i] == 1) {
                    echo " checked";
                }
                echo ">\n\t";
            }
        }
        echo "</td></tr>\n";
    }
    if ($listnum == "") {
        echo "<tr><td colspan=8 align=\"right\"><input type=\"submit\" value=\""._("Submit lists")."\" /></td></tr>";
    }
} else {
    while($row = $result->fetchRow()) {
        for($i = 0; $i <= 6; $i++) {
            if ($row[$i] == "") $row[$i] = "&nbsp;";
        }
        $name = translit("$row[1] $row[2]");
        echo "<tr><td height=\"35\">$row[1]</td><td>$row[2]</td><td>$row[3]</td><td>$row[4]</td><td>$row[5]</td><td>$row[6]</td><td><a href=\"#\" onclick=\"call_add('$name','$row[3]','$row[5]','$row[6]','$confno','$book')\" type=\"button\" />"._("Invite")."</a><div align=\"center\" id=\"$row[3]\"></div></td></tr>\n";
    }
}
echo "</tbody></table></form>\n$stran";

}

?>

<br />
<table border="1" cellpadding="5" cellspacing="0" align="center" width="800">
    <tr>
        <td align="center" colspan="<?php echo $NumOfLists+1; ?>"><?php echo _("Personal lists"); ?></td>
    </tr>
    <tr>
        <td align="center" height="37">
        <?php
            $strpos = stripos($_SERVER['REQUEST_URI'], "&listnum");
            if ($strpos) {
                $_SERVER['REQUEST_URI']=substr_replace($_SERVER['REQUEST_URI'],"",$strpos);
            }
            echo "<a href=\"".$_SERVER['REQUEST_URI']."\">"._("Main list")."</a></td>";
            for ($i = 1; $i <= $NumOfLists; $i++) {
                echo "<td align=\"center\" height=\"35\"><a href=\"".$_SERVER['REQUEST_URI']."&listnum=$i\">"._("List #").$i."</a></td>";
            }
        ?>
    </tr>
</table>
</center>