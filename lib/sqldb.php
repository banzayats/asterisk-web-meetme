<?php

function authsql ($user, $password)
{

	global $db;

	$xuser = strtolower($user);
	$data = array( $xuser, $password);
	$query = "SELECT id, admin FROM user WHERE email=? AND password=?";
	$result = $db->query($query, $data);
	if($result->numRows())
	{
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$_SESSION['privilege'] = $row['admin'];
	        return $row['id'];
	}
	else
	{
	        return false;
	}
}
?>
