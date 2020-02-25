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
  echo 'Only can edit the want by self.';
  exit(-1);  
}    

$coin_type=safeReqChrStr('coin_type');
$offer_amount=0+safeReqNumStr('offer_amount');
$want_names=safeReqChrStr('want_names');
$remark=safeReqChrStr('remark');
$bid_hours=safeReqNumStr('bid_hours');

$start_utc=$tmp_want_record['start_utc'];

$end_utc = ($bid_hours>0) ? $start_utc + $bid_hours*60*60  : PPK_ODINSWAP_LONGTIME_UTC ;

$sqlstr = "UPDATE wants set want_names='$want_names', remark='$remark', coin_type='$coin_type', offer_amount='$offer_amount', end_utc='$end_utc',status_code='".PPK_ODINSWAP_STATUS_WANT."' where want_rec_id='$want_rec_id' ;";
$result=@mysqli_query($g_dbLink,$sqlstr);
if(!$result)
{
    echo '无效参数. Invalid argus';
    exit(-1);
}

require_once "page_header.inc.php";
?>
<p><?php echo getLang('指定求购信息已更新。');?><br><a href="want.php?want_rec_id=<?php echo $want_rec_id;?>"><?php echo getLang('点击这里查看');?></a></p> 
<?php 
require_once "page_footer.inc.php";
?>