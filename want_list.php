<?php
/* PPK JoyAsset SwapService DEMO              */
/*         PPkPub.org  20200221           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";



require_once "page_header.inc.php";

//过滤条件
$wanter_uri=safeReqChrStr('wanter_uri');
$status_code=safeReqNumStr('status_code');

?>
<div class="table-responsive">

<table class="table table-striped">
<thead>
    <tr>
        <th><?php echo getLang('求购奥丁号');?></th>
        <th><?php echo getLang('期望价格');?></th>
        <th><?php echo getLang('状态');?></th>
        <th><?php echo getLang('求购方');?></th>
    </tr>
</thead>

<tbody>
<?php
//查询带有拍卖数据的数据库记录
//$sqlstr = 'SELECT wants.*,view_want_bid.*,GREATEST(last_bid_utc,update_utc) as last_change_utc FROM wants left join (select want_rec_id as bid_want_rec_id,max(bid_amount) as max_bid_amount,max(bid_rec_id) as last_bid_rec_id,max(bid_utc) as last_bid_utc  from bids group by bid_want_rec_id ) as view_want_bid on view_want_bid.bid_want_rec_id=wants.want_rec_id WHERE 1 ';

$sqlstr = 'SELECT wants.*,start_utc as last_change_utc from wants WHERE 1 ';

if(strlen($wanter_uri)>0)
    $sqlstr .=  " AND wants.wanter_uri='$wanter_uri' ";

if(strlen($status_code)>0)
    $sqlstr .=  " AND wants.status_code='$status_code' ";

$sqlstr .= '  order by last_change_utc desc,want_rec_id desc;';
//echo $sqlstr;

$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        //$str_pub_time = formatTimestampForView($obj_set['pub_utc'],false);
        echo '<tr>';
        
        $tmp_title=$row['want_names'];
        
        echo '<td><a href="want.php?want_rec_id=',$row['want_rec_id'],'">',getSafeEchoTextToPage($tmp_title),'</a></td>';
        
        echo '<td><a href="want.php?want_rec_id=',$row['want_rec_id'],'">';
        if(isset($row['max_bid_amount'])){
            echo trimz($row['max_bid_amount']),' ',getSafeEchoTextToPage(getCoinSymbol($row['coin_type']));
            $tmp_rmb_value=getCoinValueOfCNY($row['max_bid_amount'],$row['coin_type']);
        }else if($row['offer_amount']==0){
            echo getLang('无底价');
            $tmp_rmb_value=0;
        }else{
            echo trimz($row['offer_amount']),' ',getSafeEchoTextToPage(getCoinSymbol($row['coin_type']));
            $tmp_rmb_value=getCoinValueOfCNY($row['offer_amount'],$row['coin_type']);
        }
        echo '</a>';
        if($tmp_rmb_value>0){
            echo '<br><font size="-1">',getLang('约'),' ¥',$tmp_rmb_value,' ',getLang('元'),'</font>';
            if(strlen($row['last_bid_utc'])>0)
                echo ' <font size="-2">(',friendlyTime($row['last_bid_utc']),')</font>';
        }
        echo '</td>';
        
        echo '<td>',getStatusLabel($row['status_code']);
        if( $row['status_code']==PPK_ODINSWAP_STATUS_WANT && $row['end_utc']!=PPK_ODINSWAP_LONGTIME_UTC)  
            echo '<br><font size="-1">' , friendlyTime($row['end_utc']).'</font>'; 
        echo '</td>';
        
        echo '<td>',getSafeEchoTextToPage($row['wanter_uri']),'</td>';
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