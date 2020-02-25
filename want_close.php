<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20200221           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

$want_rec_id=safeReqNumStr('want_rec_id');

if(strlen($want_rec_id)==0){
  echo 'Invalid record ID.';
  exit(-1);
}

$sqlstr = "SELECT wants.* FROM wants where want_rec_id='$want_rec_id' ;";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (!$rs) {
  echo 'Not existed record.';
  exit(-1);  
}
$tmp_want_record = mysqli_fetch_assoc($rs);
 
$bCurrentUserIsWanter = ($g_currentUserODIN==$tmp_want_record['wanter_uri']) ? true:false;
if (!$bCurrentUserIsWanter) {
  echo 'Only can close the want by self.';
  exit(-1);  
}    

$sqlstr = "UPDATE wants set status_code='".PPK_ODINSWAP_STATUS_CLOSED."' where want_rec_id='$want_rec_id' ;";
mysqli_query($g_dbLink,$sqlstr);

require_once "page_header.inc.php";
?>
<p><?php echo getLang('指定求购已结束。');?><br><a href="want.php?want_rec_id=<?php echo $want_rec_id;?>"><?php echo getLang('点击这里查看');?></a></p> 
<?php 
require_once "page_footer.inc.php";
?>