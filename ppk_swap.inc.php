<?php
/*      PPK JoyAsset SwapService Setting  */
/*         PPkPub.org  20200306           */  
/*    Released under the MIT License.     */

//ini_set("display_errors", "On"); 
//error_reporting(E_ALL | E_STRICT);

require_once 'config/config.inc.php';

//Include PPk Lib
require_once(PPK_LIB_DIR_PREFIX.'Util.php');
require_once(PPK_LIB_DIR_PREFIX.'ODIN.php');
require_once(PPK_LIB_DIR_PREFIX.'PTTP.php');
require_once(PPK_LIB_DIR_PREFIX.'PTAP01DID.php');
require_once(PPK_LIB_DIR_PREFIX.'PTAP02ASSET.php');

require_once "ppk_swap.coin.php";
require_once "lang.php";
 
define('APP_BASE_URL',\PPkPub\Util::getCurrentPagePath(true)); //应用网址的基础路径

define('PPK_ODINSWAP_FLAG','ODINSWAP'); //备注信息的特别标志
define('PPK_ODINSWAP_SERVICE_URI_PREFIX','ppk:JOY/swap/'); //服务资源前缀

define('PPK_ODINSWAP_LONGTIME_UTC',2123456789); //不设置持续时间的拍卖结束最大时间戳值，对应2037-04-16 09:06

define('PPK_ODINSWAP_OVEETIME_SECONDS',7*24*60*60); //等待确拍和支付的超时时间为7天

define('PPK_ODINSWAP_STATUS_BID',0); //状态定义:拍卖中
define('PPK_ODINSWAP_STATUS_ACCEPT',1); //状态定义:达成意向
define('PPK_ODINSWAP_STATUS_PAID',2); //状态定义:已付款
define('PPK_ODINSWAP_STATUS_TRANSFER',3); //状态定义:资产已过户
define('PPK_ODINSWAP_STATUS_CANCEL',4); //状态定义:交易取消
define('PPK_ODINSWAP_STATUS_EXPIRED',5); //状态定义:到期确拍中
define('PPK_ODINSWAP_STATUS_NONE',6); //状态定义:到期流拍
define('PPK_ODINSWAP_STATUS_UNCONFIRM',7); //状态定义:等待确拍超时
define('PPK_ODINSWAP_STATUS_UNPAID',8); //状态定义: 等待支付超时
define('PPK_ODINSWAP_STATUS_FINISH',9); //状态定义:已完成
define('PPK_ODINSWAP_STATUS_LOSE',20); //状态定义:未中标

define('PPK_ODINSWAP_STATUS_WANT',100); //状态定义:求购中
define('PPK_ODINSWAP_STATUS_CLOSED',101); //状态定义:已结束

define('PPK_ODINSWAP_MSG_USER_SYSTEM','SYS'); //系统发送通知消息对应的用户名
define('PPK_ODINSWAP_MSG_TYPE_MORMAL',1); //普通消息
define('PPK_ODINSWAP_MSG_TYPE_SYSTEM',2); //系统消息
define('PPK_ODINSWAP_MSG_STATUS_NEW',1); //消息状态:未读
define('PPK_ODINSWAP_MSG_STATUS_READ',2); //消息状态:已读
define('PPK_ODINSWAP_MSG_STATUS_DELED',3); //消息状态:已删除
define('PPK_ODINSWAP_MSG_STATUS_SENT',9); //消息状态:已发送

//maintenance_exit('系统升级维护中,预计11点上线,请稍后访问...<br>System Maintaining...');

//初始化数据库连接
$g_dbLink=@mysqli_connect($dbhost,$dbuser,$dbpass,$dbname) or die("Can not connect to the mysql server!");
@mysqli_query($g_dbLink,"Set Names 'UTF8'");

//已登录用户信息
require_once('ppk_swap.user.php');

require_once 'ppk_swap.function.php';

//自动更新相关数据记录
autoUpdateExpiredRecords();

