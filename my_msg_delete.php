<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20200306           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

$msg_id=\PPkPub\Util::safeReqNumStr('msg_id');

if(strlen($msg_id)==0){
  echo 'Invalid record ID.';
  exit(-1);
}

$sqlstr = "SELECT * FROM private_message where msg_id='$msg_id' ;";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (!$rs) {
  echo 'Not existed record.';
  exit(-1);  
}
$tmp_msg_record = mysqli_fetch_assoc($rs);
 
$bCurrentUserIsOwner = ($g_currentUserODIN==$tmp_msg_record['user_uri']) ? true:false;
if (!$bCurrentUserIsOwner) {
  echo 'Only can delete the message by self.';
  exit(-1);  
}    

$sqlstr = "UPDATE private_message set status_code='".PPK_ODINSWAP_MSG_STATUS_DELED."' where msg_id='$msg_id' ;";
mysqli_query($g_dbLink,$sqlstr);

require_once "page_header.inc.php";
?>
<p align="center"><?php echo getLang('指定消息已被删除至回收站，将在一周后被自动清理！');?></p> 
<?php 
require_once "page_footer.inc.php";
?>