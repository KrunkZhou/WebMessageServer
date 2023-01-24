# WebMessageServer
在网页上查看放在家里的手机上的短信, 帮助多卡用户接收短信以及验证码而不需要随身携带所有的SIM卡

Demo: <a href="https://api.krunk.cn/krunk/msgserver/">https://api.krunk.cn/krunk/msgserver/</a>

# 准备工作
此项目的作用为帮助多卡用户接收短信以及验证码而不需要随身携带所有的SIM卡

## 安装服务器端
复制 MsgClient - Web/MsgClient 文件夹到服务器，并给予数据库文件夹 "kdb/" 可读写权限
- 在 config.php 中填写主页地址 (必须 https:// 并且以 "/" 结尾)
- 填写密钥以及绑定密钥 (密钥可以随意填写不用记住)

```
//主页地址
$server_url="https://krunk.cn/msgserver/";
//Server连接Client用密钥
$krunkkey="yjkg509499y5";
//手动绑定QR密钥
$connection_key="e465454ujty";
```

## 准备手机端
安装 MsgServer.apk 到需要使用的移动设备并：
- 启用短信以及文件写入权限
- 关闭电池优化
- 锁定到最近任务
- 点击右上角"连接服务器"扫描网页上的二维码进行绑定 (如设备无GMS服务，则需要手动输入二维码内容)
- 如果主页上出现"一台新的设备注册到了此服务器"代表绑定成功

## 一切完成！
尝试发一条短信到目标手机看看有没有更新
