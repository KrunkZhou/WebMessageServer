<?php

function verifyCode ($token_v,$sso_url) {
	$check=@file_get_contents($sso_url."?check=".$token_v);
	if ($check=="1"){
		return true;
	}
	return false;;
}

function logOutSSO ($sso_url) {
	$check=@file_get_contents($sso_url."?logout=yes");
	if ($check=="1"){
		return true;
	}
	return false;;
}

?>