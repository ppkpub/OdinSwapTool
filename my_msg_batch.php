<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20200310           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

if(strlen($g_currentUserODIN)==0){
  Header('Location: login.php');
  exit(-1);
}

$op_code=\PPkPub\Util::safeReqChrStr('op');
if($op_code=='ReadAllNew'){
  $sqlstr = "UPDATE private_message SET status_code='".PPK_ODINSWAP_MSG_STATUS_READ."' WHERE user_uri='".addslashes($g_currentUserODIN)."' AND status_code='".PPK_ODINSWAP_MSG_STATUS_NEW."' ;";
  
  mysqli_query($g_dbLink,$sqlstr);
  $affected_rows=mysqli_affected_rows($g_dbLink);
  
  $result_msg = $affected_rows.getLang('条新消息已被更改为已读.');
}else if($op_code=='DelAllRead'){
  $sqlstr = "UPDATE private_message SET status_code='".PPK_ODINSWAP_MSG_STATUS_DELED."' WHERE user_uri='".addslashes($g_currentUserODIN)."' AND status_code in ('".PPK_ODINSWAP_MSG_STATUS_READ."','".PPK_ODINSWAP_MSG_STATUS_SENT."') ;";
  
  mysqli_query($g_dbLink,$sqlstr);
  $affected_rows=mysqli_affected_rows($g_dbLink);

  $result_msg = $affected_rows.getLang('条消息已被删除至回收站，将在一周后被自动清理！');
}else{
  echo 'Invalid Operation.';
  exit(-1);
}



 

require_once "page_header.inc.php";
?>
<center>
<p><?php echo $result_msg ;?></p> 
<p><a class="btn btn-success" role="button" href="my_msg_box.php"><?php echo getLang("返回“我的消息”");?></a></p> 
</center>
<?php 
require_once "page_footer.inc.php";
?>