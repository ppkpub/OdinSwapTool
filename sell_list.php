<?php
/* PPK JoyAsset SwapService DEMO              */
/*         PPkPub.org  20190415           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";



require_once "page_header.inc.php";

//过滤条件
$seller_uri=safeReqChrStr('seller_uri');
$status_code=safeReqNumStr('status_code');

?>
<div class="table-responsive">

<table class="table table-striped">
<thead>
    <tr>
        <th><?php echo getLang('拍卖奥丁号');?></th>
        <th><?php echo getLang('最新报价');?></th>
        <th><?php echo getLang('状态');?></th>
        <th><?php echo getLang('拍卖方');?></th>
    </tr>
</thead>

<tbody>
<?php
//查询带有拍卖数据的数据库记录
$sqlstr = 'SELECT sells.*,view_sell_bid.*,GREATEST(last_bid_utc,update_utc) as last_change_utc FROM sells left join (select sell_rec_id as bid_sell_rec_id,max(bid_amount) as max_bid_amount,max(bid_rec_id) as last_bid_rec_id,max(bid_utc) as last_bid_utc  from bids group by bid_sell_rec_id ) as view_sell_bid on view_sell_bid.bid_sell_rec_id=sells.sell_rec_id WHERE 1 ';

if(strlen($seller_uri)>0)
    $sqlstr .=  " AND sells.seller_uri='$seller_uri' ";

if(strlen($status_code)>0)
    $sqlstr .=  " AND sells.status_code='$status_code' ";

$sqlstr .= '  order by last_change_utc desc,sell_rec_id desc;';
//echo $sqlstr;

$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        //$str_pub_time = formatTimestampForView($obj_set['pub_utc'],false);
        echo '<tr>';
        
        $tmp_title=$row['recommend_names'];
        if(empty($tmp_title)){
            $tmp_title=$row['asset_id'];
        }
        
        echo '<td><a href="sell.php?sell_rec_id=',$row['sell_rec_id'],'">',getSafeEchoTextToPage($tmp_title),'</a><br><font size="-1">',PPK_URI_PREFIX.getSafeEchoTextToPage(friendlyLongID($row['asset_id'])),'</font></td>';
        
        echo '<td><a href="sell.php?sell_rec_id=',$row['sell_rec_id'],'">';
        if(isset($row['max_bid_amount'])){
            echo trimz($row['max_bid_amount']),' ',getSafeEchoTextToPage(getCoinSymbol($row['coin_type']));
            $tmp_rmb_value=getCoinValueOfCNY($row['max_bid_amount'],$row['coin_type']);
        }else if($row['start_amount']==0){
            echo getLang('无底价');
            $tmp_rmb_value=0;
        }else{
            echo trimz($row['start_amount']),' ',getSafeEchoTextToPage(getCoinSymbol($row['coin_type']));
            $tmp_rmb_value=getCoinValueOfCNY($row['start_amount'],$row['coin_type']);
        }
        echo '</a>';
        if($tmp_rmb_value>0){
            echo '<br><font size="-1">',getLang('约'),' ¥',$tmp_rmb_value,' ',getLang('元'),'</font>';
            if(strlen($row['last_bid_utc'])>0)
                echo ' <font size="-2">(',friendlyTime($row['last_bid_utc']),')</font>';
        }
        echo '</td>';
        
        echo '<td>',getStatusLabel($row['status_code']);
        if( $row['status_code']==PPK_ODINSWAP_STATUS_BID && $row['end_utc']!=PPK_ODINSWAP_LONGTIME_UTC)  
            echo '<br><font size="-1">' , friendlyTime($row['end_utc']).'</font>'; 
        echo '</td>';
        
        echo '<td>',getSafeEchoTextToPage($row['seller_uri']),'</td>';
        echo '</tr>';
    }
}


?>
</tbody>
</table>
</div>


<?php
require_once "page_footer.inc.php";
?>