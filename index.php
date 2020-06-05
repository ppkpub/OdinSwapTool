<?php
/*       PPK JoyAsset SwapService         */
/*         PPkPub.org  20200315           */  
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


$orderby=\PPkPub\Util::safeReqChrStr('orderby');
$orderby_col_name='';

$str_query_reqs='';

?>
<style type="text/css">
    .cardBox {
        width: 200px;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        text-align: center;
        float: left;
        margin-right: 10px;
        padding: 5px;
        padding-top: 15px;
    }

    .headerBox {
        color: #fff;
        padding: 10px;
        font-size: 15px;
        height: 60px;
    }

    .bodyBox {
        padding: 10px;
    }

    .bodyBox p {
        margin-left: 5px;
    }
</style>
        
<h2>　<?php echo getLang('拍卖');?></h2>

<div class="container">
<?php
//查询带有拍卖数据的数据库记录
$sqlstr = 'SELECT sells.*,view_sell_bid.*,IFNULL(max_bid_amount,start_amount) AS last_price,GREATEST(IFNULL(last_bid_utc,pub_utc),update_utc) AS last_change_utc FROM sells LEFT JOIN (select sell_rec_id AS bid_sell_rec_id,MAX(bid_amount) AS max_bid_amount,MAX(bid_rec_id) AS last_bid_rec_id,MAX(bid_utc) AS last_bid_utc  from bids group by bid_sell_rec_id ) AS view_sell_bid ON view_sell_bid.bid_sell_rec_id=sells.sell_rec_id ORDER BY last_change_utc DESC,sell_rec_id DESC LIMIT 5';
//echo $sqlstr;

$result_num=0;
$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        $tmp_title=$row['recommend_names'];
        if(empty($tmp_title)){
            $tmp_title=$row['asset_id'];
        }
        
        //$tmp_title=\PPkPub\Util::friendlyLongStrUTF8($tmp_title,20,false);
        
        $tmp_view_rec_link = 'sell.php?sell_rec_id='.$row['sell_rec_id'];
        
        if(isset($row['max_bid_amount'])){
            $tmp_bid_info = \PPkPub\Util::trimz($row['max_bid_amount']).' <small>'.\PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($row['coin_type'])).'</small>';
            $tmp_bid_rmb_value=getCoinValueOfCNY($row['max_bid_amount'],$row['coin_type']);
        }else if($row['start_amount']==0){
            $tmp_bid_info = getLang('无底价');
            $tmp_bid_rmb_value=0;
        }else{
            $tmp_bid_info = \PPkPub\Util::trimz($row['start_amount']).' <small>'.\PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($row['coin_type'])).'</small>';
            $tmp_bid_rmb_value=getCoinValueOfCNY($row['start_amount'],$row['coin_type']);
        }
        
        if($tmp_bid_rmb_value>0){
           $tmp_bid_rmb_info=getLang('约').' ¥'.$tmp_bid_rmb_value.' '.getLang('元');
        }else{
           $tmp_bid_rmb_info='&emsp;';
        }
        
        if( $row['status_code']==PPK_ODINSWAP_STATUS_BID ){
            $tmp_end_time_info = $row['end_utc']==PPK_ODINSWAP_LONGTIME_UTC  
                                    ? getLang('长期'):\PPkPub\Util::friendlyTime($row['end_utc']); 

            if(strlen($row['last_bid_utc'])>0){
                $tmp_last_change_info = getLang('最新报价').': '.\PPkPub\Util::friendlyTime($row['last_bid_utc']);
            }else{
                $tmp_last_change_info = getLang('开始拍卖').': '.\PPkPub\Util::friendlyTime($row['start_utc']);
            }
        }else{
            $tmp_end_time_info = '&emsp;';
            $tmp_last_change_info = '&emsp;';
        }
        
        //$str_pub_time = \PPkPub\Util::formatTimestampForView($obj_set['pub_utc'],false);
?>
  <div class="cardBox">
    <div class="headerBox <?php echo getStatusStyle($row['status_code']);?>" >
        <p>
            <a title="<?php echo getLang('查看详情');?>" style="cursor: pointer; color:white" href="<?php echo $tmp_view_rec_link; ?>"><?php \PPkPub\Util::safeEchoTextToPage($tmp_title);?></a>
        </p>
    </div>
    <div class="bodyBox">
        <p><?php echo getLang('拍卖奥丁号');?>: <a href="<?php echo $tmp_view_rec_link; ?>" class="label <?php echo getStatusStyle($row['status_code']);?>" style="border-radius: .25em;"><?php \PPkPub\Util::safeEchoTextToPage(\PPkPub\Util::friendlyLongID($row['asset_id']));?></a></p>

        <p><?php echo $tmp_bid_info;?></p>

        <!--<p><small><?php echo $tmp_bid_rmb_info;?></small></p>-->
        
        <p><small><?php echo $tmp_last_change_info;?></small></p>
        <p>
            <a href="<?php echo $tmp_view_rec_link; ?>" class="label <?php echo getStatusStyle($row['status_code']);?>" style="border-radius: .25em;"><?php echo getStatusLabel($row['status_code']);?></a> <small><?php echo $tmp_end_time_info;?></small>
        </p>
        
        <p><?php echo getLang('拍卖方');?>: <?php echo getUserLabelHTML($row['seller_uri'],false,$tmp_view_rec_link);?></p>
    </div>
  </div>    
<?php
        $result_num++;
    }
}


?>
</div>

<h3 align="center"><a href="sell_list.php" class="btn btn-primary"><?php echo getLang('更多拍卖记录');?> >></a></h3>

<h2>　<?php echo getLang('求购');?></h2>

<div class="container">
<?php
$sqlstr = 'SELECT wants.*,view_want_sell.*,GREATEST(wants.pub_utc,IFNULL(last_sell_pub_utc,0)) AS last_change_utc FROM wants LEFT JOIN (SELECT last_sell_rec_id,from_want_rec_id ,asset_id as last_sell_asset_id,recommend_names as last_sell_recommend_names,pub_utc AS last_sell_pub_utc FROM sells,(SELECT MAX(sell_rec_id) as last_sell_rec_id  FROM sells WHERE NOT from_want_rec_id is NULL GROUP BY from_want_rec_id)  last_want_sells WHERE sells.sell_rec_id=last_want_sells.last_sell_rec_id   ) AS view_want_sell ON view_want_sell.from_want_rec_id=wants.want_rec_id ORDER BY last_change_utc DESC,want_rec_id DESC LIMIT 5 ';

$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        $tmp_title=$row['want_names'];
        
        $tmp_view_rec_link = 'want.php?want_rec_id='.$row['want_rec_id'];
        
        if(isset($row['max_bid_amount'])){
            $tmp_bid_info = \PPkPub\Util::trimz($row['max_bid_amount']).' <small>'.\PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($row['coin_type'])).'</small>';
            $tmp_bid_rmb_value=getCoinValueOfCNY($row['max_bid_amount'],$row['coin_type']);
        }else if($row['offer_amount']==0){
            $tmp_bid_info = getLang('无底价');
            $tmp_bid_rmb_value=0;
        }else{
            $tmp_bid_info = \PPkPub\Util::trimz($row['offer_amount']).' <small>'.\PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($row['coin_type'])).'</small>';
            $tmp_bid_rmb_value=getCoinValueOfCNY($row['offer_amount'],$row['coin_type']);
        }
        
        if($tmp_bid_rmb_value>0){
           $tmp_bid_rmb_info=getLang('约').' ¥'.$tmp_bid_rmb_value.' '.getLang('元');
        }else{
           $tmp_bid_rmb_info='&emsp;';
        }
        
        if( $row['status_code']==PPK_ODINSWAP_STATUS_WANT){
            $tmp_end_time_info=\PPkPub\Util::friendlyTime($row['end_utc']);
            
            if(isset($row['last_sell_rec_id'])){
                $tmp_last_change_info = getLang('最新回应').': '.\PPkPub\Util::friendlyTime($row['last_sell_pub_utc']);
            }else{
                $tmp_last_change_info = getLang('开始求购').': '.\PPkPub\Util::friendlyTime($row['start_utc']);
            }
            
        }else{
            $tmp_end_time_info = '&emsp;';
            $tmp_last_change_info = '&emsp;';
        }

        //$str_pub_time = \PPkPub\Util::formatTimestampForView($obj_set['pub_utc'],false);
?>
  <div class="cardBox">
    <div class="headerBox <?php echo getStatusStyle($row['status_code']);?>" >
        <p>
            <a title="<?php echo getLang('查看详情');?>" style="cursor: pointer; color:white" href="<?php echo $tmp_view_rec_link; ?>"><?php \PPkPub\Util::safeEchoTextToPage($tmp_title);?></a>
        </p>
    </div>
    <div class="bodyBox">
        <p><?php echo $tmp_bid_info;?></p>
        
        <!--<p><small><?php echo $tmp_want_rmb_info;?></small></p>-->
        
        <p><small><?php echo $tmp_last_change_info;?></small></p>
        
        <p><a href="<?php echo $tmp_view_rec_link; ?>" class="label <?php echo getStatusStyle($row['status_code']);?>" style="border-radius: .25em;"><?php echo getStatusLabel($row['status_code']);?></a> <small><?php echo $tmp_end_time_info;?></small>
        </p>
        
        <p><?php echo getLang('求购方');?>: <?php echo getUserLabelHTML($row['wanter_uri'],false,$tmp_view_rec_link);?></p>
    </div>
  </div>    
<?php
    }
}
?>
</div>

<h3 align="center"><a href="want_list.php" class="btn btn-info"><?php echo getLang('更多求购记录');?> >></a></h3>

<?php
require_once "page_footer.inc.php";
?>