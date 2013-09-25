<?php
include_once 'DB.php';
$database = 'meet1';
$host = 'localhost';
$username = 'meetme1';
$password = 'jdweWWFEai345udwd';
$dsn = "mysql://$username:$password@$host/$database";
$db = DB::connect($dsn);
if (DB::isError($db))
{
        die ($db->getMessage());
}
?>
