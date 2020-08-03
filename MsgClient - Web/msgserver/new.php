<?php 

header("Access-Control-Allow-Origin: *");
include('config.php');

include('kdb.class.php');
$db = new kdb();

if ($_POST['krunkkey']!=$krunkkey){
	die('KeyNotValid');
}

$user = $db->find_one('msg',array('number' => $_POST['number'],'msg' => $_POST['msg']));

if (empty($user)){
	$data = array(
		'time' => date('Y/m/d G:i:s', time()),
		'ip'  => $_SERVER['REMOTE_ADDR'],
		'count'  => 1,
		'number' => $_POST['number'],
		'msg' => $_POST['msg'],
		'systime' => $_POST['time']
	);
	$db->insert('msg',$data);
}else{
	$data = array(
		'time' => date('Y/m/d G:i:s', time()),
		'ip'  => $_SERVER['REMOTE_ADDR'],
		'count'  => $user[key($user)]['count']+1
	);
	$db->update('msg',$data,key($user));
}
echo "1";

?>