<?php
define('PTTP_NODE_API_URL','http://tool.ppkpub.org/odin/');   //此处配置PTTP协议代理节点
define('WEIXIN_QR_SERVICE_URL','https://ppk001.sinaapp.com/odin/');   //此处配置微信扫码登录服务网址

//MYSQL数据库信息
$dbhost="localhost";                                     // 数据库主机名
$dbuser="root";                                          // 数据库用户名
$dbpass="xm123";                                          // 数据库密码
$dbname="odinswap";                                         // 数据库名

//设置系统支持的支付币种列表
$gArraySupportedCoinTypeList=array(
        'ppk:bch/',
        'ppk:joy/btm/',
    );
    
define('DEFAULT_COIN_TYPE','ppk:bch/');   //默认使用的币种

define('FORCE_HTTPS',true);   //强制使用HTTPS