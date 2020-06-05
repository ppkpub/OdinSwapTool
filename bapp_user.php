<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20200523           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

if(strlen($g_currentUserODIN)==0){
  Header('Location: login.php');
  exit(-1);
}

$original_user_odin=\PPkPub\Util::originalReqChrStr('user_odin');

if(strlen($original_user_odin)==0)
    $original_user_odin=$g_currentUserODIN;

$bapp_address='';
if(\PPkPub\Util::startsWith($original_user_odin,\PPkPub\PTAP02ASSET::COIN_TYPE_MOV))
    $bapp_address = \PPkPub\PTAP02ASSET::removeCoinPrefix($original_user_odin,\PPkPub\PTAP02ASSET::COIN_TYPE_MOV);
else if(\PPkPub\Util::startsWith($original_user_odin,\PPkPub\PTAP02ASSET::COIN_TYPE_BYTOM))
    $bapp_address = \PPkPub\PTAP02ASSET::removeCoinPrefix($original_user_odin,\PPkPub\PTAP02ASSET::COIN_TYPE_BYTOM);

if( strlen($bapp_address)==0 ){
  \PPkPub\Util::error_exit('./', "Invalid Bapp address ");
}

//$tmp_user_info=\PPkPub\PTAP01DID::getPubUserInfo($original_user_odin);

//统计该用户标识的相关拍卖和报价记录
$tmp_user_sell_stat=array(
    'total'=>0,
    'status_stat'=>array()
);
$tmp_user_bid_stat=array(
    'total'=>0,
    'status_stat'=>array()
);
$tmp_user_want_stat=array(
    'total'=>0,
    'status_stat'=>array()
);

$sqlstr = "select status_code,count(*) as counter from sells where  seller_uri='".addslashes($original_user_odin)."' group by status_code order by status_code;";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while($row = mysqli_fetch_assoc($rs)){
        $tmp_user_sell_stat['total'] += $row['counter'];
        $tmp_user_sell_stat['status_stat'][$row['status_code']] = $row['counter'];
    }
}
//print_r($tmp_user_sell_stat);

$sqlstr = "select status_code,count(*) as counter from bids where  bidder_uri='".addslashes($original_user_odin)."' group by status_code order by status_code;";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while($row = mysqli_fetch_assoc($rs)){
        $tmp_user_bid_stat['total'] += $row['counter'];
        $tmp_user_bid_stat['status_stat'][$row['status_code']] = $row['counter'];
    }
}
//print_r($tmp_user_bid_stat);

$sqlstr = "select status_code,count(*) as counter from wants where  wanter_uri='".addslashes($original_user_odin)."' group by status_code order by status_code;";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while($row = mysqli_fetch_assoc($rs)){
        $tmp_user_want_stat['total'] += $row['counter'];
        $tmp_user_want_stat['status_stat'][$row['status_code']] = $row['counter'];
    }
}
//print_r($tmp_user_want_stat);

$tmp_user_inbox_stat=array('total'=>0,'status_stat'=>array());

$sqlstr = "select status_code,count(*) as counter from private_message where user_uri='".addslashes($original_user_odin)."' AND receiver_uri='".addslashes($original_user_odin)."'  group by status_code order by status_code;";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while($row = mysqli_fetch_assoc($rs)){
        $tmp_user_inbox_stat['total'] += $row['counter'];
        $tmp_user_inbox_stat['status_stat'][$row['status_code']] = $row['counter'];
    }
}
//print_r($tmp_user_inbox_stat);

//获取附加地址
/*
$array_more_address_list=array();
foreach($gArraySupportedCoinTypeList as $tmp_coin_type){
    $array_more_address_list[$tmp_coin_type]=getCoinAddressURI($tmp_coin_type,$original_user_odin);
}
*/

$is_owner= $original_user_odin==$g_currentUserODIN;

require_once "page_header.inc.php";
?>

<div id='pub_top'>
  <table width="100%" border="0">
  <tr>
  <td align="left" width="100">
  <img  style="float:left"  src="image/bytom.png" width=64 height=64>
  </td>
  <td>
  <h1><?php \PPkPub\Util::safeEchoTextToPage( \PPkPub\Util::friendlyLongID($bapp_address));?></h1>
  </td>
  </tr>
  </table>
</div>

<ul>
<div id='user_info'>
    <hr>
    <P><?php echo getLang('身份标识URI');?>: <?php \PPkPub\Util::safeEchoTextToPage( $original_user_odin ); ?> <?php 
    if(!$is_owner) echo '[<a href="new_msg.php?receiver_odin_uri=',urlencode($original_user_odin),'">',getLang('给他发私信'),'</a>]'; ?> </p>
    <P><?php echo getLang('钱包地址');?>: <?php \PPkPub\Util::safeEchoTextToPage( $bapp_address); ?></p>

    <P><?php echo getLang('参与报价总次数');?>: <?php 
    echo '<a href="bid_list.php?bidder_uri=',urlencode($original_user_odin),'">',$tmp_user_bid_stat['total'],'</a> <!--（好评率 ..%）-->, '; 
    if(count($tmp_user_bid_stat['status_stat'])>0){
        foreach($tmp_user_bid_stat['status_stat'] as $status_code=>$counter){
            echo getStatusLabel($status_code),'(<a href="bid_list.php?bidder_uri=',urlencode($original_user_odin),'&status_code=',$status_code,'">',$counter,'</a>) ';
        }
    }
    ?></p>
    <P><?php echo getLang('发布求购总次数');?>: <?php 
    echo '<a href="want_list.php?wanter_uri=',urlencode($original_user_odin),'">',$tmp_user_want_stat['total'],'</a> <!--（好评率 ..%）-->, '; 
    if(count($tmp_user_want_stat['status_stat'])>0){
        foreach($tmp_user_want_stat['status_stat'] as $status_code=>$counter){
            echo getStatusLabel($status_code),'(<a href="want_list.php?wanter_uri=',urlencode($original_user_odin),'&status_code=',$status_code,'">',$counter,'</a>) ';
        }
    }
    ?></p>
<?php

if( $is_owner )
{ 
?>
<P><?php echo getLang('收件箱');?>: <?php 
    echo '<a href="my_msg_box.php">',$tmp_user_inbox_stat['total'],'</a> , '; 
    if(count($tmp_user_inbox_stat['status_stat'])>0){
        foreach($tmp_user_inbox_stat['status_stat'] as $status_code=>$counter){
            echo getMsgStatusLabel($status_code),'(<a href="my_msg_box.php?status_code=',$status_code,'">',$counter,'</a>) ';
        }
    }
    ?></p>
<p>　　<a class="btn btn-warning" role="button"  href="logout.php"><?php echo getLang('退出登录状态');?></a></p>
<?php
}
?>

</div>
</ul>

<?php
require_once "page_footer.inc.php";
?>