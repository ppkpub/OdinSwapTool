<?php
/*       PPK JoyAsset SwapService         */
/*         PPkPub.org  20200313           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";
require_once "page_header.inc.php";

//检查是否HTTPS安全访问
$current_url=\PPkPub\Util::getCurrentUrl(true);
if( FORCE_HTTPS && strtolower(substr($current_url,0,5)) == 'http:' ){
    $https_url='https'.substr($current_url,4);
    header("location: ".$https_url);
    exit(-1);
}

$pagenum=50;
$start=@(0+\PPkPub\Util::safeReqNumStr('start'));

$orderby=\PPkPub\Util::safeReqChrStr('orderby');
$orderby_col_name='';
switch($orderby){
    case 'lastprice':
        $orderby_col_name='last_price';
        break;
    case 'lastpricedesc':
        $orderby_col_name='last_price desc';
        break;
    case 'lastpub':
        $orderby_col_name='sell_rec_id desc';
        break;
    case 'lastbid':
        $orderby_col_name='status_code,last_bid_rec_id desc';
        break;
    case 'odin':
        $orderby_col_name='LENGTH(asset_id),asset_id';
        break;
    case 'odindesc':
        $orderby_col_name='LENGTH(asset_id) DESC,asset_id DESC';
        break;
    case 'status':
        $orderby_col_name='status_code,end_utc';
        break;
    case 'statusdesc':
        $orderby_col_name='status_code desc,end_utc desc';
        break;
}

//组织查询条件
$str_query_reqs='';
$str_query_sets='1';


$q_key_posn=\PPkPub\Util::safeReqChrStr('q_key_posn');
if(strlen($q_key_posn)>0)
    $str_query_reqs .="&q_key_posn=".urlencode($q_key_posn);


$q_include_keys=\PPkPub\Util::safeReqChrStr('q_include_keys');
if(strlen($q_include_keys)>0){
    $tmp_sub_query='';
    $str_query_reqs .="&q_include_keys=".urlencode($q_include_keys);
    $tmp_keys=explode(',',$q_include_keys);
    
    if($q_key_posn=='se'){
        $tmp_sub_query1="";
        $tmp_sub_query2="";
        
        for($kk=0;$kk<count($tmp_keys);$kk++){
            $tmp_key=trim($tmp_keys[$kk]);
            
            if(strlen($tmp_key)>0){
               $tmp_key=convertLetterToNumberInRootODIN($tmp_key);
               if(strlen($tmp_sub_query1)>0){
                 $tmp_sub_query1 .=' or ';
                 $tmp_sub_query2 .=' or ';
               }
               
               $tmp_sub_query1 .= " sells.asset_id like '%$tmp_key' "; 
               $tmp_sub_query2 .= " sells.asset_id like '$tmp_key%' "; 
            }
        }
        
        $tmp_sub_query .= "($tmp_sub_query1) and ($tmp_sub_query2)";
    }else{
        for($kk=0;$kk<count($tmp_keys);$kk++){
            $tmp_key=trim($tmp_keys[$kk]);
            
            if(strlen($tmp_key)>0){
               $tmp_key=convertLetterToNumberInRootODIN($tmp_key);
               
               if(strlen($tmp_sub_query)>0)
                 $tmp_sub_query .=' or ';
             
               if($q_key_posn=='s')
                   $tmp_sub_query .= " sells.asset_id like '$tmp_key%' "; 
               else if($q_key_posn=='e')
                   $tmp_sub_query .= " sells.asset_id like '%$tmp_key' "; 
               else
                   $tmp_sub_query .= " sells.asset_id like '%$tmp_key%' "; 
            }
        }
    }
    if(strlen($tmp_sub_query)>0)
        $str_query_sets .= " and ($tmp_sub_query) ";
    
} 

$q_exclude_keys=\PPkPub\Util::safeReqChrStr('q_exclude_keys');
if(strlen($q_exclude_keys)>0){
    $str_query_reqs .="&q_exclude_keys=".urlencode($q_exclude_keys);
    $tmp_sub_query='';
    $tmp_keys=explode(',',$q_exclude_keys);
    for($kk=0;$kk<count($tmp_keys);$kk++){
        $tmp_key=trim($tmp_keys[$kk]);
        if(strlen($tmp_key)>0){
           $tmp_key=convertLetterToNumberInRootODIN($tmp_key);
           
           if(strlen($tmp_sub_query)>0)
               $tmp_sub_query .=' or ';
           
           if($q_key_posn=='s')
               $tmp_sub_query .= " sells.asset_id like '$tmp_key%' "; 
           else if($q_key_posn=='e')
               $tmp_sub_query .= " sells.asset_id like '%$tmp_key' "; 
           else if($q_key_posn=='se')
               $tmp_sub_query .= " (sells.asset_id like '%$tmp_key' or sells.asset_id like '$tmp_key%'  ) "; 
           else
               $tmp_sub_query .= " sells.asset_id like '%$tmp_key%' "; 
           
        }
    }
   
    if(strlen($tmp_sub_query)>0)
        $str_query_sets .= " and NOT ($tmp_sub_query) ";
}

$q_seller_uri=\PPkPub\Util::safeReqChrStr('q_seller_uri');
if(strlen($q_seller_uri)>0){
    $str_query_reqs .="&q_seller_uri=".urlencode($q_seller_uri);
    $str_query_sets .= " and sells.seller_uri='$q_seller_uri' "; 
}


$q_sell_status=\PPkPub\Util::safeReqNumStr('q_sell_status');
if(strlen($q_sell_status)>0){
    $str_query_reqs .="&q_sell_status=".urlencode($q_sell_status);
    $str_query_sets .= " and sells.status_code='$q_sell_status' "; 
}

$q_length_limit=\PPkPub\Util::safeReqNumStr('q_length_limit');
if(strlen($q_length_limit)>0){
    $str_query_reqs .="&q_length_limit=".urlencode($q_length_limit);
    $str_query_sets .= " and length(sells.asset_id)=$q_length_limit "; 
}

//echo $str_query_sets;

?>
<div class="table-responsive">

<h3><?php echo strlen($q_seller_uri)>0 ? '“'.$q_seller_uri.'” 发布的拍卖纪录' : '拍卖纪录' ;?></h3>

<?php
/*
$array_fast_query_sets= array(
    'q_include_keys=6,8&q_key_posn=e' => getLang('尾数6和8'),
    'q_exclude_keys=4' => getLang('不带4'),
    'q_include_keys=111,222,333,444,555,666,777,888,999,000' => getLang('类似666'),
    'q_include_keys=123,234,345,456,567,678,789' => getLang('类似123'),
    'q_sell_status='.PPK_ODINSWAP_STATUS_BID.'&orderby=lastpub' => getLang('新发布'),
    'q_sell_status='.PPK_ODINSWAP_STATUS_BID.'&orderby=lastbid' => getLang('最近报价'),
    'q_sell_status='.PPK_ODINSWAP_STATUS_ACCEPT.'&orderby=' => getLang('达成意向'),
    'q_sell_status='.PPK_ODINSWAP_STATUS_PAID.'&orderby=' => getLang('已付款'),
    'q_sell_status='.PPK_ODINSWAP_STATUS_FINISH.'&orderby=' => getLang('已完成'),
  );
*/

$str_fast_query_prefix = '?q_seller_uri='.urlencode($q_seller_uri);

?>

<p><?php echo getLang('快速查询');?>: <a href="<?php echo $str_fast_query_prefix; ?>&q_include_keys=6,8&q_key_posn=e"><?php echo getLang('尾数6和8');?></a> | <a href="<?php echo $str_fast_query_prefix; ?>&q_exclude_keys=4"><?php echo getLang('不带4');?></a> | <a href="<?php echo $str_fast_query_prefix; ?>&q_include_keys=111,222,333,444,555,666,777,888,999,000"><?php echo getLang('类似666');?></a> | <a href="<?php echo $str_fast_query_prefix; ?>&q_include_keys=123,234,345,456,567,678,789"><?php echo getLang('类似123');?></a> | <a href="<?php echo $str_fast_query_prefix; ?>&q_sell_status=<?php echo PPK_ODINSWAP_STATUS_BID;?>&orderby=lastpub"><?php echo getLang('新发布');?></a> | <a href="<?php echo $str_fast_query_prefix; ?>&q_sell_status=<?php echo PPK_ODINSWAP_STATUS_BID;?>&orderby=lastbid"><?php echo getLang('最近报价');?></a> | <a href="<?php echo $str_fast_query_prefix; ?>&q_sell_status=<?php echo PPK_ODINSWAP_STATUS_ACCEPT;?>"><?php echo getLang('达成意向');?></a> |  <a href="<?php echo $str_fast_query_prefix; ?>&q_sell_status=<?php echo PPK_ODINSWAP_STATUS_PAID;?>"><?php echo getLang('已付款');?></a> |  <a href="<?php echo $str_fast_query_prefix; ?>&q_sell_status=<?php echo PPK_ODINSWAP_STATUS_FINISH;?>"><?php echo getLang('已完成');?></a> | <a href="query.php"><?php echo getLang('自定义');?></a></p>

<table class="table table-striped">
<thead>
    <tr>
        <th><?php echo getLang('拍卖奥丁号');?><a href="?orderby=<?php echo $orderby=='odin'?'odindesc':'odin',$str_query_reqs; ?>"><font size="-2">[<?php echo getLang('排序');?>]</font></a></th>
        <th><?php echo getLang('最新报价');?><a href="?orderby=<?php echo $orderby=='lastprice'?'lastpricedesc':'lastprice',$str_query_reqs; ?>"><font size="-2">[<?php echo getLang('排序');?>]</font></a></th>
        <th><?php echo getLang('状态');?><a href="?orderby=<?php echo $orderby=='status'?'statusdesc':'status',$str_query_reqs; ?>"><font size="-2">[<?php echo getLang('排序');?>]</font></a></th>
        <th><?php echo getLang('拍卖方');?></th>
    </tr>
</thead>

<tbody>
<?php
//查询带有拍卖数据的数据库记录
$sqlstr = 'SELECT sells.*,view_sell_bid.*,IFNULL(max_bid_amount,start_amount) AS last_price,GREATEST(IFNULL(last_bid_utc,pub_utc),update_utc) AS last_change_utc FROM sells LEFT JOIN (select sell_rec_id AS bid_sell_rec_id,MAX(bid_amount) AS max_bid_amount,MAX(bid_rec_id) AS last_bid_rec_id,MAX(bid_utc) AS last_bid_utc  from bids group by bid_sell_rec_id ) AS view_sell_bid ON view_sell_bid.bid_sell_rec_id=sells.sell_rec_id  WHERE '.$str_query_sets.' ORDER BY '.( strlen($orderby_col_name)>0?$orderby_col_name.',':'' ).' last_change_utc DESC,sell_rec_id DESC LIMIT '.$start.','.$pagenum;
//echo $sqlstr;

$result_num=0;
$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        //$str_pub_time = \PPkPub\Util::formatTimestampForView($obj_set['pub_utc'],false);
        echo '<tr>';
        
        $tmp_title=$row['recommend_names'];
        if(empty($tmp_title)){
            $tmp_title=$row['asset_id'];
        }
        
        $tmp_view_rec_link = 'sell.php?sell_rec_id='.$row['sell_rec_id'];
            
        echo '<td><a href="',$tmp_view_rec_link,'">',\PPkPub\Util::getSafeEchoTextToPage($tmp_title),'</a><br><font size="-1">',\PPkPub\ODIN::PPK_URI_PREFIX.\PPkPub\Util::getSafeEchoTextToPage(\PPkPub\Util::friendlyLongID($row['asset_id'])),'</font></td>';
        
        echo '<td><a href="',$tmp_view_rec_link,'">';
        if(isset($row['max_bid_amount'])){
            echo \PPkPub\Util::trimz($row['max_bid_amount']),' ',\PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($row['coin_type']));
            $tmp_rmb_value=getCoinValueOfCNY($row['max_bid_amount'],$row['coin_type']);
        }else if($row['start_amount']==0){
            echo getLang('无底价');
            $tmp_rmb_value=0;
        }else{
            echo \PPkPub\Util::trimz($row['start_amount']),' ',\PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($row['coin_type']));
            $tmp_rmb_value=getCoinValueOfCNY($row['start_amount'],$row['coin_type']);
        }
        echo '</a>';
        if($tmp_rmb_value>0){
            echo '<br><font size="-1">',getLang('约'),' ¥',$tmp_rmb_value,' ',getLang('元'),'</font>';
            if(strlen($row['last_bid_utc'])>0)
                echo ' <font size="-2">(',\PPkPub\Util::friendlyTime($row['last_bid_utc']),')</font>';
        }
        echo '</td>';
        
        echo '<td><p><a href="',$tmp_view_rec_link,'" class="label ',getStatusStyle($row['status_code']),'">',getStatusLabel($row['status_code']),'</a></p>';
        if( $row['status_code']==PPK_ODINSWAP_STATUS_BID && $row['end_utc']!=PPK_ODINSWAP_LONGTIME_UTC)  
            echo '<p><small>' , \PPkPub\Util::friendlyTime($row['end_utc']).'</small></p>'; 
        echo '</td>';
        
        echo '<td>',\PPkPub\Util::getSafeEchoTextToPage($row['seller_uri']),'</td>';
        echo '</tr>';
        
        $result_num++;
    }
}


?>
</tbody>
</table>
</div>
<center>
<?php
$page_base_url='?orderby='.$orderby.$str_query_reqs.'&start=';

if($start>=$pagenum) {//说明有上一页
    echo '<a class="btn btn-primary" role="button"  href="'.$page_base_url.($start-$pagenum).'">《',getLang('上一页'),'</a> ';
}

echo " ",getLang('当前为第'),($start/$pagenum)+1,getLang('页')," ";

if($result_num==$pagenum) {//说明有下一页
    echo ' <a class="btn btn-primary" role="button"  href="'.$page_base_url.($start+$pagenum).'">',getLang('下一页'),'》</a>';
}
?>
</center>

<?php
require_once "page_footer.inc.php";
?>