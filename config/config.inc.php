<?php
define('PPK_LIB_DIR_PREFIX',dirname(__FILE__).'/../../ppk-lib2/php/');   //此处配置PPK SDK的引用路径

define('PTTP_NODE_API_URL','http://tool.ppkpub.org/ppkapi2/');   //此处配置PTTP协议代理节点
define('WEIXIN_QR_SERVICE_URL','https://ppk001.sinaapp.com/odin/');   //此处配置微信扫码登录服务网址

//MYSQL数据库信息
$dbhost="localhost";                                     // 数据库主机名
$dbuser="xxxxx";                                          // 数据库用户名
$dbpass="xxxxx";                                          // 数据库密码
$dbname="odinswap";                                         // 数据库名

//设置系统支持的支付币种列表
$gArraySupportedCoinTypeList=array(
        'ppk:bch/',
        'ppk:joy/btm/',
        'ppk:joy/mov/asset/usdt/',
    );
    
define('DEFAULT_COIN_TYPE',$gArraySupportedCoinTypeList[0]);   //默认使用的币种

define('FORCE_HTTPS',false);   //强制使用HTTPS

define('ADMIN_ODIN_URI',"ppk:sysadmin*");   //默认的管理员用户

define('IS_DEMO',true);   //是否为演示版本
define('DEMO_LOGIN_USER_ODIN_URI',"ppk:83850*");   //默认的演示登录用户
define('DEMO_LOGIN_USER_LEVEL',2);   //演示用户权限，1:有限访客（不能发起拍卖） 2:普通用户权限