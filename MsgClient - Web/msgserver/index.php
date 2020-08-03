<!doctype html>
<?php 

session_start();

include('config.php');
include ('krunksso.php');

function currentUrl( $trim_query_string = false ) {
    $pageURL = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
    $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    if( ! $trim_query_string ) {
        return $pageURL;
    } else {
        $url = explode( '?', $pageURL );
        return $url[0];
    }
}

$logedin=true;
if ($enable_sso){
	if (!isset($_SESSION["msg-logedin"])&&!$_SESSION["msg-logedin"]==true){
		$logedin=false;
		if (isset($_GET["code"])){
			if (verifyCode($_GET["code"],$sso_url)==true){
				$_SESSION["msg-logedin"] = true;
				header("Location: index.php");
			}else{
				header("Location: index.php");
			}
		}
	}if (isset($_GET["logout"])&&$_GET["logout"]=="yes"){
		 session_destroy();
		 //logOutSSO ();
		 header("Location: ".$sso_url."?logout=yes&r=".currentUrl(true));
	}
}

include('kdb.class.php');
$db = new kdb();

$users =  $db->find('msg');

function date_compare($element1, $element2) { 
    $datetime1 = strtotime($element1['time']); 
    $datetime2 = strtotime($element2['time']); 
    return $datetime1 - $datetime2; 
}  

usort($users, 'date_compare'); 
$users=array_reverse($users);

?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <title>MsgClient - KrunkMsgServer</title>

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" sizes="192x192" href="images/android-desktop.png">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="MsgClient">
    <link rel="apple-touch-icon-precomposed" href="images/ios-desktop.png">

    <!-- Tile icon for Win8 (144x144 + tile color) -->
    <meta name="msapplication-TileImage" content="images/touch/ms-touch-icon-144x144-precomposed.png">
    <meta name="msapplication-TileColor" content="#3372DF">

    <link rel="shortcut icon" href="images/favicon.png">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.deep_purple-pink.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
    #view-source {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 40px;
      margin-bottom: 40px;
      z-index: 900;
    }
    #qrcode img {
	  width:120px;
	  height:120px;
	}
    </style>
  </head>
  <body class="mdl-demo mdl-color--grey-100 mdl-color-text--grey-700 mdl-base">
    <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
      <header class="mdl-layout__header mdl-layout__header--scroll mdl-color--primary">
        <div class="mdl-layout--large-screen-only mdl-layout__header-row">
        </div>
        <div class="mdl-layout--large-screen-only mdl-layout__header-row">
          <h3>MsgClient 用户端</h3>
        </div>
        <div class="mdl-layout--large-screen-only mdl-layout__header-row">
        </div>
        <div class="mdl-layout__tab-bar mdl-js-ripple-effect mdl-color--primary-dark">
          <a href="#overview" class="mdl-layout__tab is-active">仪表盘</a>
          <a href="#features" class="mdl-layout__tab">准备工作</a>
          <a href="#connect" class="mdl-layout__tab">绑定设备</a>
        </div>
      </header>
      <main class="mdl-layout__content">
        <div class="mdl-layout__tab-panel is-active" id="overview">

          <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp">
            <div class="mdl-card mdl-cell mdl-cell--12-col">
              <div class="mdl-card__supporting-text mdl-grid mdl-grid--no-spacing">
                <h4 class="mdl-cell mdl-cell--12-col">信息</h4>


<?php

if ($logedin){
	foreach($users as $user){
		echo '
	  				<div class="section__circle-container mdl-cell mdl-cell--2-col mdl-cell--1-col-phone">
	                  <div class="section__circle-container__circle mdl-color--primary"></div>
	                </div>
	                <div class="section__text mdl-cell mdl-cell--10-col-desktop mdl-cell--6-col-tablet mdl-cell--3-col-phone">
	                  <h5><b>来自: '.$user["number"].'</b><br>'.$user["time"].'</h5>
	                  '.$user["msg"].'<br><br>
	                </div>
	  ';
	}
}else{
	
	echo '
	  				<div class="section__circle-container mdl-cell mdl-cell--2-col mdl-cell--1-col-phone">
	                  <div class="section__circle-container__circle mdl-color--primary"></div>
	                </div>
	                <div class="section__text mdl-cell mdl-cell--10-col-desktop mdl-cell--6-col-tablet mdl-cell--3-col-phone">
	                  <h5><b>来自: MsgServer System Notification</b></h5>
	                  <br>您没有查看此系统的权限<br><br><a href="'.$sso_url."?r=".currentUrl(true).'">点击登录</a><br><br>
	                </div>
	  ';
}

?>

              </div>
              <div class="mdl-card__actions">
                <a href="index.php" class="mdl-button">刷新</a>
              </div>
            </div>
            <button class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon" id="btn2">
              <i class="material-icons">more_vert</i>
            </button>
            <ul class="mdl-menu mdl-js-menu mdl-menu--bottom-right" for="btn2">
              <?php echo $logedin==true?'<a href="delete.php">
              	<li class="mdl-menu__item"<?php echo $allow_delete==true?"":"disabled"  ?>清空</li>
              </a> 
              <li class="mdl-menu__item"disabled>发送短信</li>':'' ?>
              <?php echo $enable_sso==true&&$logedin==true?'<a href="index.php?logout=yes">
              	<li class="mdl-menu__item">注销</li>
              </a> ':'' ?>
            </ul>
          </section>


          <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp">
            <div class="mdl-card mdl-cell mdl-cell--12-col">
              <div class="mdl-card__supporting-text">
                <h4>关于</h4>
                Krunk Design - Message Client with Message Server
              </div>
              <div class="mdl-card__actions">
                <a href="https://github.com/KrunkZhou/WebMessageServer" class="mdl-button">Read our features</a>
              </div>
            </div>
          </section>
        <br><br>
        </div>

        <div class="mdl-layout__tab-panel" id="features">
          <section class="section--center mdl-grid mdl-grid--no-spacing">
            <div class="mdl-cell mdl-cell--12-col">
              <h4>准备工作</h4>
              此项目的作用为帮助多卡用户接收短信以及验证码而不需要随身携带所有的SIM卡
              
              <ul class="toc">
                <h4>目录</h4>
                <a href="#lorem2">安装服务器端</a>
                <a href="#lorem1">准备手机端</a>
                <a href="#lorem3">完成！</a>
              </ul>

              <h5 id="lorem2">安装服务器端</h5>
              复制 MsgClient 文件夹到服务器，并给予数据库文件夹 "kdb/" 可读写权限
              <ul>
                <li>在 config.php 中填写主页地址 (必须 https:// 并且以 "/" 结尾)</li>
                <li>填写密钥以及绑定密钥 (密钥可以随意填写不用记住)</li>
              </ul>

              <h5 id="lorem1">准备手机端</h5>
              安装 MsgServer.apk 到需要使用的移动设备并：
              <ul>
                <li>启用短信以及文件写入权限</li>
                <li>关闭电池优化</li>
                <li>锁定到最近任务</li>
                <li>点击右上角"连接服务器"扫描网页上的二维码进行绑定</li>
                <li>如果主页上出现"一台新的设备注册到了此服务器"代表绑定成功</li>
                <li><a href="Android-App/MsgServer.apk">点击下载 MsgServer.apk</a></li>
              </ul>

              <h5 id="lorem2">一切完成！</h5>
              尝试发一条短信到目标手机看看有没有更新
              
            </div>
          </section>
        </div>




        <div class="mdl-layout__tab-panel" id="connect">
          <section class="section--center mdl-grid mdl-grid--no-spacing mdl-shadow--2dp">
            <header class="section__play-btn mdl-cell mdl-cell--3-col-desktop mdl-cell--2-col-tablet mdl-cell--4-col-phone mdl-color--teal-100 mdl-color-text--white">
              <div id="qrcode" width="100px"></div>
              <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
              <?php echo $logedin==true?'<script type="text/javascript">
				new QRCode(document.getElementById("qrcode"), "KrunkMsgServer://'. $server_url.'/-kbr-/'.$connection_key.'");
				</script>':'' ?>
            </header>
            <div class="mdl-card mdl-cell mdl-cell--9-col-desktop mdl-cell--6-col-tablet mdl-cell--4-col-phone">
              <div class="mdl-card__supporting-text">
                <h4>MsgServer 设备绑定</h4>
                打开 MsgServer 扫描二维码来绑定手机<br>
                可以绑定多台手机，但是只会显示在一个列表中<br>
                请确保 config.php 中的首页填写正确，不然将无法绑定手机
              </div>
            </div>
          </section>
          <br><br>
        </div>



        <footer class="mdl-mega-footer">
          <div class="mdl-mega-footer--middle-section">
            <div class="mdl-mega-footer--drop-down-section">
              <input class="mdl-mega-footer--heading-checkbox" type="checkbox" checked>
              <h1 class="mdl-mega-footer--heading">Features</h1>
              <ul class="mdl-mega-footer--link-list">
                <li><a href="https://krunk.cn/">About</a></li>
                <li><a href="https://krunk.cn/privacy-policy">Terms</a></li>
                <li><a href="https://krunk.cn/pay">Donate</a></li>
              </ul>
            </div>

          </div>
          <div class="mdl-mega-footer--bottom-section">
            <div class="mdl-logo">
              More Information
            </div>
            <ul class="mdl-mega-footer--link-list">
              <li><a href="https://krunk.cn/">KRUNK DESIGN</a></li>
              <li><a href="https://krunk.cn/privacy-policy">Privacy and Terms</a></li>
            </ul>
          </div>
        </footer>
      </main>
    </div>

    <script src="https://code.getmdl.io/1.3.0/material.min.js"></script>
  </body>
</html>
