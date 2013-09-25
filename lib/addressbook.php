<?php
//include ("./database.php");
$col=30;
getpost_ifset(array('name', 'number', 'search', 'of', 'deladdres', 'confno', 'book','rx','tx'));
$name = iconv("cp1251","UTF-8",preg_replace("/[^\x30-\x39\x41-\x5A\x61-\x7A\x20\x5F\xC0-\xFF\xA8\xB8]/","",iconv("UTF-8","cp1251",$name)));
$name = substr($name,0,80);
$deladdres = preg_replace("/[^\x30-\x39]/","",$deladdres);
$number = preg_replace("/[^\x30-\x39]/","",$number);
$number = substr($number,0,20);
$of = preg_replace("/[^\x30-\x39]/","",$of);
$search = iconv("cp1251","UTF-8",preg_replace("/[^\x30-\x39\x41-\x5A\x61-\x7A\x20\x5F\xC0-\xFF\xA8\xB8]/","",iconv("UTF-8","cp1251",$search)));
$search = substr($search,0,80);
$confno = preg_replace("/[^\x30-\x39]/","",$confno);
$book = preg_replace("/[^\x30-\x39]/","",$book);
$rx = preg_replace("/[^\x2D\x30-\x39]/","",$rx);
if (($rx < -20) || (20 < $rx)) { $rx=0; }
$tx = preg_replace("/[^\x2D\x30-\x39]/","",$tx);
if (($tx < -20) || (20 < $tx)) { $tx=0; }

//echo $name.$number.$search;

//$db->query("SET NAMES UTF8");

if (strlen($name)==0) unset($name);
if (strlen($number)==0) unset($number);
if (strlen($search)==0) unset($search);
if (strlen($of)==0) $of=0;
if (strlen($deladdres)==0) unset($deladdres);
if (strlen($confno)==0) unset($confno);
if (strlen($book)==0) unset($book);

if (isset($confno)) $col=7;

$error_flag=0;

if ($deladdres>0 && !isset($confno))
{
	$query = "DELETE FROM `addressbook` WHERE `id`=$deladdres LIMIT 1";
	$result = $db->query($query);
	if ($result)
	{
		echo "<center><font color=\"red\">Запись удалена.</font></center>";
	} else {
		echo "<center><font color=\"red\">Ошибка удавения записи.</font></center>";
	}
}


if (isset($name) && isset($number) && isset($search) && !$error_flag)
{
	echo "<center><font color=\"red\">Распознана попытка взлома. Данные текущей сессии переданы администратору.</font></center>";
	$error_flag=1;
}


if (isset($name) && !isset($number) && !isset($search) && !$error_flag)
{
	echo "<center><font color=\"red\">Недопустимый номер.</font></center>";
	$error_flag=1;
}

if (!isset($name) && isset($number) && !isset($search) && !$error_flag)
{
	echo "<center><font color=\"red\">Недопустимое имя.</font></center>";
	$error_flag=1;
}

if (!isset($name) && !isset($number) && isset($search) && (strlen($search)<3) && !$error_flag)
{
	echo "<center><font color=\"red\">Недопустимое выражение для поиска (минимальная длина три символа).</font></center>";
	$error_flag=1;
}

if (isset($name) && isset($number) && (strlen($search)==0) && !isset($confno) && !$error_flag)
{
	$data = array($name,$number,$rx,$tx);
	$query = "INSERT INTO `addressbook` (`name`,`number`,`rx`,`tx`) VALUES (?,?,?,?)";
	$result = $db->query($query, $data);

	if ($result)
	{
		echo "<center><font color=\"green\">Добавлена запись.</font></center>";
	} else {
		echo "<center><font color=\"red\">Ошибка добавления записи.</font></center>";
	}
}




if (!isset($confno))
{
?>
<table border=0 align="center" >
	<tr>
		<td align="center">Имя</td>
		<td align="center">Номер</td>
		<td align="center">RX</td>
		<td align="center">TX</td>
		<td align="center">&nbsp;</td>
	</tr>
	<tr>
		<form method="POST" action="<?php echo preg_replace("/&of=\d+|&deladdres=\d+/","",$_SERVER['REQUEST_URI']);?>">
			<td align="center"><input type="text" name="name" size="40" maxlength="40" value="<?php if (isset($name)) echo $name; ?>"/></td>
			<td align="center"><input type="text" name="number" size="20" maxlength="20" value="<?php if (isset($number)) echo $number; ?>"/></td>
			<td align="center"><select name="rx">
				<option value="20">20</option>
				<option value="19">19</option>
				<option value="18">18</option>
				<option value="17">17</option>
				<option value="16">16</option>
				<option value="15">15</option>
				<option value="14">14</option>
				<option value="13">13</option>
				<option value="12">12</option>
				<option value="11">11</option>
				<option value="10">10</option>
				<option value="9">9</option>
				<option value="8">8</option>
				<option value="7">7</option>
				<option value="6">6</option>
				<option value="5">5</option>
				<option value="4">4</option>
				<option value="3">3</option>
				<option value="2">2</option>
				<option value="1">1</option>
				<option value="0" selected="selected">0</option>
				<option value="-1">-1</option>
				<option value="-2">-2</option>
				<option value="-3">-3</option>
				<option value="-4">-4</option>
				<option value="-5">-5</option>
				<option value="-6">-6</option>
				<option value="-7">-7</option>
				<option value="-8">-8</option>
				<option value="-9">-9</option>
				<option value="-10">-10</option>
				<option value="-11">-11</option>
				<option value="-12">-12</option>
				<option value="-13">-13</option>
				<option value="-14">-14</option>
				<option value="-15">-15</option>
				<option value="-16">-16</option>
				<option value="-17">-18</option>
				<option value="-18">-18</option>
				<option value="-19">-19</option>
				<option value="-20">-20</option>
			</select></td>
			<!--<td align="center"><input type="text" name="rx" size="2" maxlength="2" value="<?php if (isset($number)) echo $rx; ?>"/></td>//-->
			<!--<td align="center"><input type="text" name="tx" size="2" maxlength="2" value="<?php if (isset($number)) echo $tx; ?>"/></td>//-->
			<td align="center"><select name="tx">
				<option value="20">20</option>
				<option value="19">19</option>
				<option value="18">18</option>
				<option value="17">17</option>
				<option value="16">16</option>
				<option value="15">15</option>
				<option value="14">14</option>
				<option value="13">13</option>
				<option value="12">12</option>
				<option value="11">11</option>
				<option value="10">10</option>
				<option value="9">9</option>
				<option value="8">8</option>
				<option value="7">7</option>
				<option value="6">6</option>
				<option value="5">5</option>
				<option value="4">4</option>
				<option value="3">3</option>
				<option value="2">2</option>
				<option value="1">1</option>
				<option value="0" selected="selected">0</option>
				<option value="-1">-1</option>
				<option value="-2">-2</option>
				<option value="-3">-3</option>
				<option value="-4">-4</option>
				<option value="-5">-5</option>
				<option value="-6">-6</option>
				<option value="-7">-7</option>
				<option value="-8">-8</option>
				<option value="-9">-9</option>
				<option value="-10">-10</option>
				<option value="-11">-11</option>
				<option value="-12">-12</option>
				<option value="-13">-13</option>
				<option value="-14">-14</option>
				<option value="-15">-15</option>
				<option value="-16">-16</option>
				<option value="-17">-18</option>
				<option value="-18">-18</option>
				<option value="-19">-19</option>
				<option value="-20">-20</option>
			</select></td>
			<td align="center"><input type="submit" value="Добавить" /></td>
		</form>
	</tr>
<?php
} else {
?>
<table border=0 align="center" >
	<tr>
		<td></td>
		<td></td>
		<td></td>
	</tr>

<?php
}
?>
	<tr>
		<form method="POST" action="<?php echo preg_replace("/&of=\d+|&deladdres=\d+/","",$_SERVER['REQUEST_URI']);?>">
			<td align="center" colspan=2><input type="text" name="search" size="67" maxlength="67" value="<?php if (isset($search)) echo $search; ?>"/></td>
			<td align="center"><input type="submit" value="Найти" /></td>
		</form>
	</tr>
</table>


<?php

if (!$error_flag)
{
	$FG_CLAUSE="1 ";
	if (isset($search)) $FG_CLAUSE="`name` like '%$search%' OR `number` like '%$search%' ";
	$FG_CLAUSE .= "ORDER BY `name` ASC ";
	if (isset($of)) $FG_CLAUSE .= "LIMIT $of, $col";
	//echo $FG_CLAUSE;
	
	//$nb_record = $db->getOne("SELECT COUNT(*) FROM `addressbook` WHERE $FG_CLAUSE");
	//ho $nb_record;
	$query = "SELECT SQL_CALC_FOUND_ROWS `id`,`name`,`number`,`rx`,`tx` FROM `addressbook` WHERE $FG_CLAUSE";
	$result = $db->query($query);
	$nb_record = $db->getOne("SELECT FOUND_ROWS()");
	echo "Найдено записей: <b>$nb_record</b>";
	$stran = stran ($nb_record,$col,$of,$_SERVER['REQUEST_URI']."&of=");
	echo $stran;
?>

<table border="1" cellpadding="5" cellspacing="0" align="center" width="600">
	<tr>
		<td align="center">Имя</td>
		<td align="center" width="20%">Номер</td>
		<td align="center" width="6%"> RX </td>
		<td align="center" width="6%"> ТX </td>
		<td align="center" width="5%">Действие</td>
	</tr>


<?php
if (!isset($confno))
{
	while($row = $result->fetchRow())
	{
		echo "<tr><td>$row[1]</td><td>$row[2]</td><td>$row[3]</td><td>$row[4]</td><td><a href=\"".$_SERVER['REQUEST_URI']."&deladdres=$row[0]\">удалить</td></tr>\n";
	}
} else {
	while($row = $result->fetchRow())
	{
		/*echo "<tr><td>$row[1]</td><td>$row[2]</td><td>
		<FORM action=\"./call_operator.php\" method=\"post\" name=WMOutCall>
		<INPUT type=\"hidden\" name=\"name\" value=\"$row[1]\">
		<INPUT type=\"hidden\" name=\"invite_num\" value=\"$row[2]\">
		<INPUT type=\"hidden\" name=\"action\" value=\"quickcall\">
		<INPUT type=\"hidden\" name=\"data\" value=\"$confno\">
		<INPUT type=\"hidden\" name=\"bookid\" value=\"$book\">
		<input type=\"submit\" value=\"Вызов\"/>\n</td></tr>\n";
		*/
		echo "<tr><td height=\"35\">$row[1]</td><td>$row[2]</td><td>$row[3]</td><td>$row[4]</td><td><a href=\"#\" onclick=\"call_add('$row[1]','$row[2]','$row[3]','$row[4]','$confno','$book')\" type=\"button\" />[ВЫЗОВ]</a><div align=\"center\" id=\"$row[2]\"></div></td></tr>\n";
	}
}
echo "</table>\n$stran";

}

?>

