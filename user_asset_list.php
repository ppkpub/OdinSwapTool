<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20190415           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

if(strlen($g_currentUserODIN)==0){
  Header('Location: login.php');
  exit(-1);
}

$original_user_odin=\PPkPub\Util::originalReqChrStr('user_odin');
$owner_address=\PPkPub\Util::safeReqChrStr('address');
$from_want_rec_id=\PPkPub\Util::safeReqNumStr('from_want_rec_id');

if(stripos($original_user_odin,\PPkPub\ODIN::PPK_URI_PREFIX)!==0 && stripos($original_user_odin,DID_URI_PREFIX)!==0){
  echo 'Invalid user ODIN.';
  exit(-1);
}

if(strlen($owner_address)==0 ){
  echo 'Invalid owner address.';
  exit(-1);
}

$is_owner=false;
if($original_user_odin==$g_currentUserODIN 
  || $original_user_odin.'*'==$g_currentUserODIN 
  || $original_user_odin==$g_currentUserODIN.'*' ){ 
    $is_owner=true;
  }
  
$pagenum=@(0+\PPkPub\Util::safeReqNumStr('pagenum'));
if($pagenum<=0)
    $pagenum=50;

$start=@(0+\PPkPub\Util::safeReqNumStr('start'));
  
require_once "page_header.inc.php";
?>

<div id='user_info'>
    <hr>
    <P><?php echo getLang('身份标识');?>: <?php \PPkPub\Util::safeEchoTextToPage( $original_user_odin); ?></p>
    <P><?php echo getLang('拥有者主钱包地址');?>: <?php \PPkPub\Util::safeEchoTextToPage( $owner_address); ?></p>
</div>

<?php
if( $is_owner ){ 
    ?>

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

$tmp_odin_list=getUserOwnedRootODINs($owner_address,$start,$pagenum);
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
    echo '<center>';
    $page_base_url='user_asset_list.php?user_odin='.urlencode($original_user_odin).'&address='.urlencode($owner_address).'&from_want_rec_id='.urlencode($from_want_rec_id).'&start=';

    if($start>=$pagenum) {//说明有上一页
        echo '<a class="btn btn-success" role="button"  href="',$page_base_url.($start-$pagenum),'">《',getLang('上一页'),'</a> ';
    }

    echo " ",getLang('当前为第'),($start/$pagenum)+1,getLang('页')," ";

    if(count($tmp_odin_list)==$pagenum) {//说明有下一页
        echo ' <a href="'.$page_base_url.($start+$pagenum).'">',getLang('下一页'),'》</a>';
    }
    
    echo ' <a class="btn btn-success" role="button"  href="'.$page_base_url.'0&pagenum=10000">',getLang('全部显示'),'</a>';
    
    echo '</center>';
}
?>

<?php
require_once "page_footer.inc.php";
?>