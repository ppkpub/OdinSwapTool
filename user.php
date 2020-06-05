<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20200306           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

$max_odin_list_num=50;  //一次显示ODIN列表的数量

if(strlen($g_currentUserODIN)==0){
  Header('Location: login.php');
  exit(-1);
}

$original_user_odin=\PPkPub\Util::originalReqChrStr('user_odin');

if(strlen($original_user_odin)==0)
    $original_user_odin=$g_currentUserODIN;

if(
    \PPkPub\Util::startsWith($original_user_odin,\PPkPub\PTAP02ASSET::COIN_TYPE_MOV)
  || \PPkPub\Util::startsWith($original_user_odin,\PPkPub\PTAP02ASSET::COIN_TYPE_BYTOM)  
){
    header("Location: bapp_user.php?user_odin=".$original_user_odin);
    exit(0);
}

if(stripos($original_user_odin,\PPkPub\ODIN::PPK_URI_PREFIX)!==0 && stripos($original_user_odin,DID_URI_PREFIX)!==0){
  echo 'Invalid User ODIN.';
  exit(-1);
}

$from_want_rec_id=\PPkPub\Util::safeReqNumStr('from_want_rec_id');

$tmp_user_info=\PPkPub\PTAP01DID::getPubUserInfo($original_user_odin);
$owner_address=@$tmp_user_info['register'];

$str_created_time = \PPkPub\Util::formatTimestampForView(@$tmp_user_info['block_time'],false);

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
$array_more_address_list=array();
foreach($gArraySupportedCoinTypeList as $tmp_coin_type){
    $array_more_address_list[$tmp_coin_type]=getCoinAddressURI($tmp_coin_type,$original_user_odin);
}

$is_owner=false;
if($tmp_user_info['user_odin']==$g_currentUserODIN 
  || $tmp_user_info['user_odin'].'*'==$g_currentUserODIN 
  || $tmp_user_info['user_odin']==$g_currentUserODIN.'*' ){ 
    $is_owner=true;
  }

require_once "page_header.inc.php";
?>

<div id='pub_top'>
  <table width="100%" border="0">
  <tr>
  <td align="left" width="100">
  <img  style="float:left"  src="<?php \PPkPub\Util::safeEchoTextToPage( $tmp_user_info['avtar']);?>" width=64 height=64>
  </td>
  <td>
  <h1><?php \PPkPub\Util::safeEchoTextToPage( $tmp_user_info['name']);?></h1>
  </td>
  </tr>
  </table>
</div>

<ul>
<div id='user_info'>
    <hr>
    <P><?php echo getLang('身份标识');?>: <?php \PPkPub\Util::safeEchoTextToPage( $tmp_user_info['user_odin']); ?> <?php 
    if(!$is_owner) echo '[<a href="new_msg.php?receiver_odin_uri=',urlencode($tmp_user_info['user_odin']),'">',getLang('给他发私信'),'</a>]'; ?> </p>
    <P><?php echo getLang('对应PPk协议URI');?>: <?php \PPkPub\Util::safeEchoTextToPage( $tmp_user_info['full_odin_uri']); ?></p>
    <P><?php echo getLang('电子邮件');?>: <?php \PPkPub\Util::safeEchoTextToPage( $tmp_user_info['email']); ?></p>
    <P><?php echo getLang('创建时间');?>: <?php \PPkPub\Util::safeEchoTextToPage( $str_created_time); ?></p>
    <P><?php echo getLang('拥有者主钱包地址');?>: <?php \PPkPub\Util::safeEchoTextToPage( $owner_address); ?></p>
    <?php
    if(!\PPkPub\Util::startsWith($owner_address,\PPkPub\PTAP02ASSET::COIN_TYPE_BYTOM)){
        echo '<P>',getLang('关联钱包地址'),': <br><ul>';
        
        foreach($array_more_address_list as $tmp_coin_type=>$tmp_address_uri){
            echo '<li>',getCoinName($tmp_coin_type),'(',$tmp_coin_type,') : ';
            if(strlen($tmp_address_uri)>0)
                \PPkPub\Util::safeEchoTextToPage( \PPkPub\PTAP02ASSET::removeCoinPrefix($tmp_address_uri,$tmp_coin_type) );
            
            if( $tmp_coin_type != \PPkPub\PTAP02ASSET::COIN_TYPE_BITCOINCASH){ //比特现金地址是自动与注册者BTC地址相同的，不需要设置
                if( $is_owner ){
                    if(strlen($tmp_address_uri)>0)
                        echo ' <!--<a href="',$tmp_coin_type,'">',getLang('修改'),'</a>--> <a href="bind_address.php?coin_type=',urlencode($tmp_coin_type),'">',getLang('修改'),'</a>';
                    else
                        echo ' <a href="bind_address.php?coin_type=',urlencode($tmp_coin_type),'">',getLang('设置'),'</a> <!--<a href="',$tmp_coin_type,'">',getLang('访问该币种的PPk网址主页（需要使用PPkBrowser安卓应用0.3.5以上版本）'),'--></a> ';
                }
                
            }
            echo '</li>';
        }    
        echo '</ul></p>';
    }
    ?>
    <P><?php echo getLang('发布拍卖总次数');?>: <?php 
    echo '<a href="sell_list.php?q_seller_uri=',urlencode($tmp_user_info['user_odin']),'">',$tmp_user_sell_stat['total'],'</a> <!--（好评率 ..%）-->, '; 
    if(count($tmp_user_sell_stat['status_stat'])>0){
        foreach($tmp_user_sell_stat['status_stat'] as $status_code=>$counter){
            echo getStatusLabel($status_code),'(<a href="sell_list.php?q_seller_uri=',urlencode($tmp_user_info['user_odin']),'&q_sell_status=',$status_code,'">',$counter,'</a>) ';
        }
    }
    ?></p>
    <P><?php echo getLang('参与报价总次数');?>: <?php 
    echo '<a href="bid_list.php?bidder_uri=',urlencode($tmp_user_info['user_odin']),'">',$tmp_user_bid_stat['total'],'</a> <!--（好评率 ..%）-->, '; 
    if(count($tmp_user_bid_stat['status_stat'])>0){
        foreach($tmp_user_bid_stat['status_stat'] as $status_code=>$counter){
            echo getStatusLabel($status_code),'(<a href="bid_list.php?bidder_uri=',urlencode($tmp_user_info['user_odin']),'&status_code=',$status_code,'">',$counter,'</a>) ';
        }
    }
    ?></p>
    <P><?php echo getLang('发布求购总次数');?>: <?php 
    echo '<a href="want_list.php?wanter_uri=',urlencode($tmp_user_info['user_odin']),'">',$tmp_user_want_stat['total'],'</a> <!--（好评率 ..%）-->, '; 
    if(count($tmp_user_want_stat['status_stat'])>0){
        foreach($tmp_user_want_stat['status_stat'] as $status_code=>$counter){
            echo getStatusLabel($status_code),'(<a href="want_list.php?wanter_uri=',urlencode($tmp_user_info['user_odin']),'&status_code=',$status_code,'">',$counter,'</a>) ';
        }
    }
    ?></p>
<?php

//echo $tmp_user_info['user_odin'],',',$g_currentUserODIN; 
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
</div>
</ul>

<h3><?php echo getLang('主钱包地址所注册数字资产');?></h3>
<div class="table-responsive">

<table class="table table-striped">
<thead>
    <tr>
        <th><?php echo getLang('短标识');?></th>
        <th><?php echo getLang('完整标识');?></th>
        <th><?php echo getLang('拍卖状态');?></th>
    </tr>
</thead>

<tbody>
<?php
//查询该用户标识的相关拍卖记录
$array_user_sells=array();
$sqlstr = "SELECT sells.*,view_sell_bid.* FROM sells left join (select sell_rec_id as bid_sell_rec_id,max(bid_amount) as max_bid_amount  from bids group by bid_sell_rec_id ) as view_sell_bid on view_sell_bid.bid_sell_rec_id=sells.sell_rec_id  where  sells.seller_uri='".addslashes($original_user_odin)."' ;";

$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        $array_user_sells[$row['asset_id']]=$row;
    }
}

$tmp_odin_list=getUserOwnedRootODINs($owner_address,0,$max_odin_list_num);
for($ss=0;$ss<count($tmp_odin_list) ;$ss++){
    $tmp_odin_info=$tmp_odin_list[$ss];
    
    $tmp_asset_id=$tmp_odin_info['short'];
    $full_odin_uri=\PPkPub\ODIN::PPK_URI_PREFIX.$tmp_odin_info['full'].\PPkPub\ODIN::PPK_URI_RESOURCE_MARK;
    
    echo '<tr>';
    echo '<td>',\PPkPub\Util::getSafeEchoTextToPage($tmp_asset_id),'</td>';
    //echo '<td><a target="_blank" href="user.php?user_odin=', urlencode($full_odin_uri),'">',\PPkPub\Util::getSafeEchoTextToPage($full_odin_uri),'</a></td>';
    echo '<td>',\PPkPub\Util::getSafeEchoTextToPage($full_odin_uri),'</td>';
    
    if(isset($array_user_sells[$tmp_asset_id])){
        $row=$array_user_sells[$tmp_asset_id];
        
        echo '<td>';
        
        if($row['status_code']==PPK_ODINSWAP_STATUS_CANCEL || $row['status_code']==PPK_ODINSWAP_STATUS_NONE
           || $row['status_code']==PPK_ODINSWAP_STATUS_UNCONFIRM || $row['status_code']==PPK_ODINSWAP_STATUS_UNPAID){
            echo '<a class="btn btn-warning" role="button" href="sell.php?sell_rec_id=',$row['sell_rec_id'],'">',getStatusLabel($row['status_code']),'</a><br>';
            echo '<br><a href="new_sell.php?asset_id=',urlencode($tmp_asset_id),'">',getLang('重新发起拍卖'),'</a>';
        }else{
            echo '<a class="btn btn-success" role="button" href="sell.php?sell_rec_id=',$row['sell_rec_id'],'">',getStatusLabel($row['status_code']),'</a><br>';
            echo '<font size="-1">',getLang('起始报价'),': ',\PPkPub\Util::trimz($row['start_amount']),' ',\PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($row['coin_type'])),'<br>';
            if(isset($row['max_bid_amount']))
                echo getLang('最新报价'),': ',\PPkPub\Util::trimz($row['max_bid_amount']),' ',\PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($row['coin_type']));//,' 来自 <a href="user.php?user_odin=',urlencode($row['bidder_uri']),'">',$row['bidder_uri'],'</a><br>';
            echo '</font>';
        }
        echo '</td>';
    }else if($g_currentUserLevel>=2){
        if(strlen($from_want_rec_id)>0)
            echo '<td><a href="new_sell.php?asset_id=',urlencode($tmp_asset_id),'&from_want_rec_id=',urlencode($from_want_rec_id),'">',getLang('卖出'),'</a></td>';
        else
            echo '<td><a href="new_sell.php?asset_id=',urlencode($tmp_asset_id),'">',getLang('发布拍卖'),'</a></td>';
    }else{
        echo '<td>',getLang('体验帐户，不能发起拍卖'),'[<a href="help.html#testuser">',getLang('说明'),'</a>]</td>';
    }
}
?>
</tbody>
</table>
</div>
<?php
    if(count($tmp_odin_list)>=$max_odin_list_num){
        echo '<p align="center"><a href="user_asset_list.php?user_odin=',urlencode($original_user_odin),'&address=',urlencode($owner_address),'&start=',$max_odin_list_num,'&from_want_rec_id=',urlencode($from_want_rec_id),'">查看该地址注册的更多资产列表...<a/></p>';
    }

}
?>

<!--
  <div class="form-group">
    <label for="remark" class="col-sm-2 control-label">采用DID规范的用户定义</label>
    <div class="col-sm-10">
     <textarea class="form-control" id="original_content" rows=10 ><?php \PPkPub\Util::safeEchoTextToPage($tmp_user_info['original_content']);?></textarea>
    </div>
  </div>
-->
<?php
require_once "page_footer.inc.php";
?>