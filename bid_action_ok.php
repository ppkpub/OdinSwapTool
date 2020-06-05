<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20190415           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

if(strlen($g_currentUserODIN)==0){
  Header('Location: login.php');
  exit(-1);
}

$bid_rec_id=\PPkPub\Util::safeReqNumStr('bid_rec_id');
$action_type=\PPkPub\Util::safeReqChrStr('action_type');
$signed_txid=\PPkPub\Util::safeReqChrStr('signed_txid');

if(strlen($bid_rec_id)==0){
  echo '无效的纪录ID. Invalid record ID.';
  exit(-1);
}

if(strlen($action_type)==0){
  echo '无效的操作类型. Invalid action_type.';
  exit(-1);
}

$sqlstr = "SELECT bids.*,sells.seller_uri FROM bids,sells where sells.sell_rec_id=bids.sell_rec_id and bid_rec_id='$bid_rec_id';";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (!$rs) {
  echo '指定纪录不存在. Not existed record.';
  exit(-1);  
}
$tmp_bid_record = mysqli_fetch_assoc($rs);
$sell_rec_id=$tmp_bid_record['sell_rec_id'] ;
$asset_id=$tmp_bid_record['asset_id'] ;
$full_odin_uri=$tmp_bid_record['full_odin_uri'] ;
$coin_type=$tmp_bid_record['coin_type'];
$bid_amount=$tmp_bid_record['bid_amount'];

$bCurrentUserIsSeller = ($g_currentUserODIN==$tmp_bid_record['seller_uri']) ? true:false;
$bCurrentUserIsBidder = ($g_currentUserODIN==$tmp_bid_record['bidder_uri']) ? true:false;

if($action_type=='accept'){
    if( !$bCurrentUserIsSeller ){
      echo '只有拍卖方才能确认接受报价单. Only seller can accpet bid.';
      exit(-1);
    }
    
    $txid_colname='accepted_txid';
    $new_status_code=PPK_ODINSWAP_STATUS_ACCEPT;
    
    $update_accepted_utc=",accepted_utc='".time()."'";
    
    $tmp_notif_receiver_uri = $tmp_bid_record['bidder_uri'];
    $tmp_notif_conetent='卖家已接受你对奥丁号['.\PPkPub\ODIN::PPK_URI_PREFIX.$asset_id.']的报价，请留意查看和支付拍卖款（<a href="help.html">帮助</a>）。';

}else if($action_type=='pay'){
    if( !($bCurrentUserIsBidder && ($tmp_bid_record['status_code']==PPK_ODINSWAP_STATUS_ACCEPT || $tmp_bid_record['status_code']==PPK_ODINSWAP_STATUS_PAID ) )) {
      echo '只有已被接受的报价方才能确认付款. Only accepted bidder can pay for the bid.';
      exit(-1);
    }
    
    $txid_colname='payment_txid';
    $new_status_code=PPK_ODINSWAP_STATUS_PAID;
    
    $tmp_notif_receiver_uri = $tmp_bid_record['seller_uri'];
    $tmp_notif_conetent='买家已支付奥丁号['.\PPkPub\ODIN::PPK_URI_PREFIX.$asset_id.']的款项，请留意确认到款后，使用PPk安卓应用，将对应奥丁号转移过户给卖家（<a href="help.html">帮助</a>）！ ';
}else if($action_type=='transfer'){
    if( !($bCurrentUserIsSeller && ($tmp_bid_record['status_code']==PPK_ODINSWAP_STATUS_PAID ) )) {
      echo '只有拍卖方才能确认转移注册权. Only seller can transfer ODIN.';
      exit(-1);
    }
    
    $txid_colname='';
    $new_status_code=PPK_ODINSWAP_STATUS_TRANSFER;
    
    $tmp_notif_receiver_uri = $tmp_bid_record['bidder_uri'];
    $tmp_notif_conetent='你拍下付款的奥丁号['.\PPkPub\ODIN::PPK_URI_PREFIX.$asset_id.']已由卖家发出过户，请留意使用PPk安卓应用确认接收，才能完成获得对应奥丁号的所有权（<a href="help.html">帮助</a>）！';
}else if($action_type=='finish'){
    if( !($bCurrentUserIsBidder && ($tmp_bid_record['status_code']==PPK_ODINSWAP_STATUS_TRANSFER ) )) {
      echo '只有收到标识的报价方才能确认交易完成. Only payed bidder can finish the bid.';
      exit(-1);
    }
    
    $txid_colname='';
    $new_status_code=PPK_ODINSWAP_STATUS_FINISH;
    
    $tmp_notif_receiver_uri = $tmp_bid_record['seller_uri'];
    $tmp_notif_conetent='买家已确认接收奥丁号['.\PPkPub\ODIN::PPK_URI_PREFIX.$asset_id.']的拥有权，祝贺你的拍卖交易已顺利完成. ';
}else{
    echo '无效的操作类型. Invalid action_type.';
    exit(-1);
}


//检查signed_txid附带的信息是否一致
//待加

//在本地数据库更新报价记录的状态
$sql_str="update bids set status_code='$new_status_code' ".( strlen($txid_colname)>0? ",$txid_colname='$signed_txid'":""  )." where bid_rec_id='$bid_rec_id'";
//echo $sql_str;
$result=@mysqli_query($g_dbLink,$sql_str);
if(!$result)
{
    echo '保存数据出错. Invalid datas';
    exit(-1);
}

$sql_str="update sells set status_code='$new_status_code',update_utc='".time()."',accepted_bid_rec_id='$bid_rec_id' ".$update_accepted_utc." where sell_rec_id='$sell_rec_id'";
//echo $sql_str;

$result=@mysqli_query($g_dbLink,$sql_str);
if(!$result)
{
    echo '保存数据出错. Invalid datas';
    exit(-1);
}

//发送通知
sendMsg(
    PPK_ODINSWAP_MSG_USER_SYSTEM,
    $tmp_notif_receiver_uri,
    PPK_ODINSWAP_MSG_TYPE_SYSTEM,
    $tmp_notif_conetent.'<a href="bid.php?bid_rec_id='.$bid_rec_id.'">查看&gt;&gt;</a>'
 );

require_once "page_header.inc.php";
?>
<center>
<p><?php echo getLang('奥丁号');?>[<?php \PPkPub\Util::safeEchoTextToPage( $asset_id );?>]:<?php \PPkPub\Util::safeEchoTextToPage( $full_odin_uri );?><?php echo getLang('的拍卖交易状态已更新。');?></p> 
<p><a class="btn btn-success" role="button" href="sell.php?sell_rec_id=<?php echo $sell_rec_id;?>"><?php echo getLang('点击这里查看');?></a></p>
</center>

<?php
require_once "page_footer.inc.php";
?>