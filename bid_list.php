<?php
/* PPK JoyAsset SwapService DEMO              */
/*         PPkPub.org  20190415           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";
require_once "page_header.inc.php";

//过滤条件
$seller_uri=safeReqChrStr('seller_uri');
$bidder_uri=safeReqChrStr('bidder_uri');
$status_code=safeReqNumStr('status_code');

?>
<div class="table-responsive">

<table class="table table-striped">
<thead>
    <tr>
        <th><?php echo getLang('参拍奥丁号');?></th>
        <th><?php echo getLang('报价');?></th>
        <th><?php echo getLang('报价方');?></th>
        <th><?php echo getLang('状态');?></th>
        <th><?php echo getLang('拍卖方');?></th>
    </tr>
</thead>

<tbody>
<?php
//查询带有拍卖数据的数据库记录
$sqlstr = 'SELECT bids.*,sells.seller_uri,sells.accepted_bid_rec_id,sells.status_code as sell_status_code,sells.end_utc FROM sells,bids WHERE sells.sell_rec_id=bids.sell_rec_id  ';

if(strlen($bidder_uri)>0)
    $sqlstr .=  " AND bids.bidder_uri='$bidder_uri' ";

if(strlen($status_code)>0)
    $sqlstr .=  " AND bids.status_code='$status_code' ";

$sqlstr .= '  order by bid_rec_id desc;';
//echo $sqlstr;

$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        //$str_pub_time = formatTimestampForView($obj_set['pub_utc'],false);
        echo '<tr>';
        echo '<td><a href="sell.php?sell_rec_id=',$row['sell_rec_id'],'">',getSafeEchoTextToPage($row['asset_id']),'</a><br><font size="-1">',safeEchoTextToPage($row['full_odin_uri']),'</font></td>';
        
        echo '<td><a href="bid.php?bid_rec_id=',$row['bid_rec_id'],'">';
        echo trimz($row['bid_amount']),' ',getSafeEchoTextToPage(getCoinSymbol($row['coin_type']));
        $tmp_rmb_value=getCoinValueOfCNY($row['bid_amount'],$row['coin_type']);
        echo '</a>';
        if($tmp_rmb_value>0){
            echo '<br><font size="-1">',getLang('约'),' ¥',$tmp_rmb_value,' ',getLang('元'),'</font>';
        }
        echo '</td>';
        
        echo '<td>',getSafeEchoTextToPage($row['bidder_uri']),'</td>';
        
        echo '<td>';
        if( $row['sell_status_code']==PPK_ODINSWAP_STATUS_BID )  {
            echo getStatusLabel($row['status_code']);
            if( $row['status_code']==PPK_ODINSWAP_STATUS_BID && $row['end_utc']!=PPK_ODINSWAP_LONGTIME_UTC)
                echo '<br><font size="-1">' , friendlyTime($row['end_utc']).'</font>'; 
        }else if( $row['sell_status_code']==PPK_ODINSWAP_STATUS_CANCEL )  {
            echo getLang('该拍卖已取消');
        }else if($row['accepted_bid_rec_id']==$row['bid_rec_id']){
            echo getLang('已中标'),',',getStatusLabel($row['sell_status_code']);
        }else{
            echo getLang('未中标');
        }
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