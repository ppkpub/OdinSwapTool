<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20200306           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

if(strlen($g_currentUserODIN)==0){
  Header('Location: login.php');
  exit(-1);
}

$receiver_odin_uri=\PPkPub\Util::safeReqChrStr('receiver_odin_uri');
if( strlen($receiver_odin_uri)==0 
   || !\PPkPub\Util::startsWith( $receiver_odin_uri ,\PPkPub\ODIN::PPK_URI_PREFIX )
   || $receiver_odin_uri == $g_currentUserODIN ){
  \PPkPub\Util::error_exit('./', 'Invalid receiver ODIN URI!<br>无效的消息接收者！');
}

//在本地数据库保存拍卖纪录
$message_content=\PPkPub\Util::safeReqChrStr('message_content');
$result=sendMsg($g_currentUserODIN,$receiver_odin_uri,PPK_ODINSWAP_MSG_TYPE_MORMAL,$message_content);
if(!$result)
{
    echo '无效参数. Invalid argus';
    exit(-1);
}

require_once "page_header.inc.php";
?>

<p align="center"><?php echo getLang('已成功发送私信消息给');?><br><?php \PPkPub\Util::safeEchoTextToPage( $receiver_odin_uri );?></p>

<?php
require_once "page_footer.inc.php";
?>