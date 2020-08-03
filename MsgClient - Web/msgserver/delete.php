<?php 

include('config.php');
include('kdb.class.php');
$db = new kdb();

if ($allow_delete){
	$db->delete_all('msg');
}

header("Location: index.php");

?>