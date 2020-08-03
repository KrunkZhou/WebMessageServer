<?php 

header("Access-Control-Allow-Origin: *");
include('config.php');

include('kdb.class.php');
$db = new kdb();


if ($_POST['connectionkey']!=$connection_key){
	die('KeyNotValid');
}

echo $krunkkey;



$user = $db->find_one('msg',array('number' => "MsgServer System Notification",'msg' => "一台新的设备注册到了此服务器"));

if (empty($user)){
	$data = array(
		'time' => date('Y/m/d G:i:s', time()),
		'ip'  => $_SERVER['REMOTE_ADDR'],
		'count'  => 1,
		'number' => "MsgServer System Notification",
		'msg' => "一台新的设备注册到了此服务器",
		'systime' => date('Y/m/d G:i:s', time())
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

?>