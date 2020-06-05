<?php
/* PPK JoyAsset SwapService DEMO              */
/*         PPkPub.org  20200221           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

require_once "page_header.inc.php";

//过滤条件
$wanter_uri=\PPkPub\Util::safeReqChrStr('wanter_uri');
$status_code=\PPkPub\Util::safeReqNumStr('status_code');

$str_query_reqs='wanter_uri='.urlencode($wanter_uri).'&status_code='.urlencode($status_code).'&';

$pagenum=50;
$start=@(0+\PPkPub\Util::safeReqNumStr('start'));
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
//查询求购数据记录
$sqlstr = 'SELECT wants.*,view_want_sell.*,GREATEST(wants.pub_utc,IFNULL(last_sell_pub_utc,0)) AS last_change_utc FROM wants LEFT JOIN (SELECT last_sell_rec_id,from_want_rec_id ,asset_id as last_sell_asset_id,recommend_names as last_sell_recommend_names,pub_utc AS last_sell_pub_utc FROM sells,(SELECT MAX(sell_rec_id) as last_sell_rec_id  FROM sells WHERE NOT from_want_rec_id is NULL GROUP BY from_want_rec_id)  last_want_sells WHERE sells.sell_rec_id=last_want_sells.last_sell_rec_id   ) AS view_want_sell ON view_want_sell.from_want_rec_id=wants.want_rec_id WHERE 1 ';

if(strlen($wanter_uri)>0)
    $sqlstr .=  " AND wants.wanter_uri='$wanter_uri' ";

if(strlen($status_code)>0)
    $sqlstr .=  " AND wants.status_code='$status_code' ";

$sqlstr .= '  ORDER BY last_change_utc DESC,want_rec_id DESC LIMIT '.$start.','.$pagenum;
//echo $sqlstr;

$result_num=0;

$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        //$str_pub_time = \PPkPub\Util::formatTimestampForView($obj_set['pub_utc'],false);
        echo '<tr>';
        
        $tmp_title=$row['want_names'];
        
        $tmp_view_rec_link = 'want.php?want_rec_id='.$row['want_rec_id'];
        
        echo '<td><a href="',$tmp_view_rec_link,'">',\PPkPub\Util::getSafeEchoTextToPage($tmp_title),'</a>';
        if( $row['status_code']==PPK_ODINSWAP_STATUS_WANT){
            if(isset($row['last_sell_rec_id'])){
                //echo '<br><font size="-2">',getLang('最新报价('),\PPkPub\Util::friendlyTime($row['last_sell_pub_utc']),'): <a href="sell.php?sell_rec_id=',$row['last_sell_rec_id'],'">',\PPkPub\ODIN::PPK_URI_PREFIX.\PPkPub\Util::getSafeEchoTextToPage(\PPkPub\Util::friendlyLongID($row['last_sell_asset_id'])),'</a></font>';
                echo '<br><font size="-2">',getLang('最新回应: '),\PPkPub\Util::friendlyTime($row['last_sell_pub_utc']),'</font>';
            }else{
                echo '<br><font size="-2">',getLang('开始求购: '),\PPkPub\Util::friendlyTime($row['pub_utc']),'</font>';
            }
        }
        
        echo '</td>';
        
        echo '<td><a href="',$tmp_view_rec_link,'">';
        if(isset($row['max_bid_amount'])){
            echo \PPkPub\Util::trimz($row['max_bid_amount']),' ',\PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($row['coin_type']));
            $tmp_rmb_value=getCoinValueOfCNY($row['max_bid_amount'],$row['coin_type']);
        }else if($row['offer_amount']==0){
            echo getLang('无底价');
            $tmp_rmb_value=0;
        }else{
            echo \PPkPub\Util::trimz($row['offer_amount']),' ',\PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($row['coin_type']));
            $tmp_rmb_value=getCoinValueOfCNY($row['offer_amount'],$row['coin_type']);
        }
        echo '</a>';
        if($tmp_rmb_value>0){
            echo '<br><font size="-1">',getLang('约'),' ¥',$tmp_rmb_value,' ',getLang('元'),'</font>';
            if(strlen(@$row['last_bid_utc'])>0)
                echo ' <font size="-2">(',\PPkPub\Util::friendlyTime($row['last_bid_utc']),')</font>';
        }
        echo '</td>';
        
        echo '<td><p><a href="',$tmp_view_rec_link,'" class="label ',getStatusStyle($row['status_code']),'"  >',getStatusLabel($row['status_code']),'</a></p>';
        if( $row['status_code']==PPK_ODINSWAP_STATUS_WANT && $row['end_utc']!=PPK_ODINSWAP_LONGTIME_UTC)  
            echo '<p><small>' , \PPkPub\Util::friendlyTime($row['end_utc']).'</small></p>'; 
        echo '</td>';
        
        echo '<td>',\PPkPub\Util::getSafeEchoTextToPage($row['wanter_uri']),'</td>';
        echo '</tr>';
        
        $result_num ++;
    }
}


?>
</tbody>
</table>
</div>

<center>
<?php
$page_base_url='?'.$str_query_reqs.'&start=';

if($start>=$pagenum) {//说明有上一页
    echo '<a class="btn btn-info" role="button"  href="'.$page_base_url.($start-$pagenum).'">《',getLang('上一页'),'</a> ';
}

echo " ",getLang('当前为第'),($start/$pagenum)+1,getLang('页')," ";

if($result_num==$pagenum) {//说明有下一页
    echo ' <a class="btn btn-info" role="button"  href="'.$page_base_url.($start+$pagenum).'">',getLang('下一页'),'》</a>';
}
?>
</center>

<?php
require_once "page_footer.inc.php";
?>