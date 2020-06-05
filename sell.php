<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20190415           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

$sell_rec_id=\PPkPub\Util::safeReqNumStr('sell_rec_id');

if(strlen($sell_rec_id)==0){
  echo 'Invalid record ID.';
  exit(-1);
}

$sqlstr = "SELECT sells.* FROM sells where sell_rec_id='$sell_rec_id' ;";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (!$rs) {
  echo 'DB failed.';
  exit(-1);  
}
$tmp_sell_record = mysqli_fetch_assoc($rs);
if (!$tmp_sell_record) {
  echo 'Not existed record.';
  exit(-1);  
}

$asset_id=$tmp_sell_record['asset_id'] ;
/*
//演示忽略
$tmp_data=getPPkResource(\PPkPub\ODIN::PPK_URI_PREFIX.$asset_id.\PPkPub\ODIN::PPK_URI_RESOURCE_MARK);
if($tmp_data['status_code']!=200){
  echo '获取比原资产标识资源信息出错. Failed to get ODIN data.';
  exit(-1);
}
$full_odin_uri=$tmp_data['uri'];
$tmp_odin_info=@json_decode($tmp_data['content'],true);
*/        
$bCurrentUserIsSeller = ($g_currentUserODIN==$tmp_sell_record['seller_uri']) ? true:false;
     

require_once "page_header.inc.php";
?>
<div class="row section">
  <div class="form-group">
    <label for="top_buttons" class="col-sm-5 control-label"><h3><?php echo getLang('拍卖奥丁号');?></h3></label>
    <div class="col-sm-7" id="top_buttons" align="right">
    </div>
  </div>
</div>
 
<form class="form-horizontal" action="update_sell_confirm.php" method="post">
  <input type="hidden" name="form" value="new_sell">
  <input type="hidden" name="sell_rec_id" value="<?php echo $sell_rec_id;?>">

  <div class="form-group">
    <label for="asset_id" class="col-sm-2 control-label"><?php echo getLang('奥丁号');?></label>
    <div class="col-sm-10">
      <span id="asset_id"><a href="http://tool.ppkpub.org:9876/odin-detail?odin=<?php echo urlencode($asset_id);?>" target="_blank"><?php echo \PPkPub\Util::getSafeEchoTextToPage($asset_id);?></a></span>
    </div>
  </div>
  
  <div class="form-group">
    <label for="recommend_names" class="col-sm-2 control-label"><?php echo getLang('推荐标题');?></label>
    <div class="col-sm-10">
      <span id="recommend_names"><?php \PPkPub\Util::safeEchoTextToPage( $tmp_sell_record['recommend_names'] );?></span>
    </div>
  </div>      
  
  <div class="form-group">
    <label for="start_amount" class="col-sm-2 control-label"><?php echo getLang('起始报价');?></label>
    <div class="col-sm-10">
      <span id="start_amount"><?php echo $tmp_sell_record['start_amount']==0 ? getLang('无底价'):\PPkPub\Util::trimz($tmp_sell_record['start_amount']);?> (<?php echo \PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($tmp_sell_record['coin_type'])); ?>)</span>
    </div>
  </div>
  
  
  <div class="form-group">
    <label for="remark" class="col-sm-2 control-label"><?php echo getLang('详细说明');?></label>
    <div class="col-sm-10">
     <textarea class="form-control" name="remark" id="remark" readonly rows=3 ><?php \PPkPub\Util::safeEchoTextToPage( $tmp_sell_record['remark'] );?></textarea>
     <span><?php echo getLang('注意：出于资金安全，建议采用拍卖方身份标识对应钱包地址作为转账地址，并仔细核对币种数额。不要随意向通过微信、Telegram等聊天工具联系时提供的地址转账。');?></span>
    </div>
  </div>
  
  <div class="form-group">
    <label for="status_code" class="col-sm-2 control-label"><?php echo getLang('拍卖状态');?></label>
    <div class="col-sm-10">
      <span id="status_code"><?php echo getStatusLabel($tmp_sell_record['status_code']);
      if($tmp_sell_record['status_code']==PPK_ODINSWAP_STATUS_EXPIRED){
          echo '(',getLang('等待拍卖方确拍,'),\PPkPub\Util::friendlyTime($tmp_sell_record['end_utc']+PPK_ODINSWAP_OVEETIME_SECONDS),',',getLang('超时将流拍'),')';
      }
      if($tmp_sell_record['status_code']==PPK_ODINSWAP_STATUS_ACCEPT){
          echo '(',getLang('等待报价方支付,'),\PPkPub\Util::friendlyTime($tmp_sell_record['accepted_utc']+PPK_ODINSWAP_OVEETIME_SECONDS),',',getLang('超时将流拍'),')';
      }
      ?></span>
    </div>
  </div>   
  
  <div class="form-group">
    <label for="end_utc" class="col-sm-2 control-label"><?php echo getLang('竞拍结束时间');?></label>
    <div class="col-sm-10">
      <span id="end_utc"><?php 
      if($tmp_sell_record['status_code']==PPK_ODINSWAP_STATUS_BID){
        if($tmp_sell_record['end_utc']==PPK_ODINSWAP_LONGTIME_UTC)  
            echo getLang('长期');
        else
            echo \PPkPub\Util::formatTimestampForView($tmp_sell_record['end_utc'],false),' , ' , \PPkPub\Util::friendlyTime($tmp_sell_record['end_utc']); 
     }else if($tmp_sell_record['status_code']==PPK_ODINSWAP_STATUS_EXPIRED || $tmp_sell_record['status_code']==PPK_ODINSWAP_STATUS_UNCONFIRM){
        echo \PPkPub\Util::formatTimestampForView($tmp_sell_record['end_utc'],false); 
     }else{
        echo \PPkPub\Util::formatTimestampForView($tmp_sell_record['accepted_utc'],false); 
     }
        
      ?> </span>
    </div>
  </div>
  
  <div class="form-group">
    <label for="seller_uri" class="col-sm-2 control-label"><?php echo getLang('拍卖方身份标识');?></label>
    <div class="col-sm-10">
      <span id="seller_uri"><?php echo getUserLabelHTML($tmp_sell_record['seller_uri']);?></span>
    </div>
  </div>
  
  <?php if($bCurrentUserIsSeller){ ?>
  <!--
  <div class="form-group" align="center">
    <div class="col-sm-offset-2 col-sm-10">
      <button class="btn btn-warning btn-lg" type="submit"  onclick="this.innerHTML='Waiting';this.disabled=true;form.submit();"  >更新</button>
    </div>
  </div>
  -->
  <?php } ?>
 
</form>

<h3><?php echo getLang('已参拍报价列表');?></h3>
<?php
$str_bid_buttun_html='';
if( !$bCurrentUserIsSeller  && $tmp_sell_record['status_code']==PPK_ODINSWAP_STATUS_BID){
    $str_bid_buttun_html='<p align=center><a class="btn btn-success" role="button" href="new_bid.php?sell_rec_id='.$sell_rec_id.'">'.getLang('我要报价').'</a></p>';
}

$sqlstr = "SELECT * FROM bids where sell_rec_id='$sell_rec_id' order by bid_amount desc,bid_rec_id desc;";

$rs = mysqli_query($g_dbLink,$sqlstr);
if (!$rs) {
    echo '<p>',getLang('获取报价列表出错，请稍候再试！'),'</p>';
}else{
    echo $str_bid_buttun_html;
?>
<div class="table-responsive">

<table class="table table-striped">
<thead>
    <tr>
        <th><?php echo getLang('报价');?></th>
        <th><?php echo getLang('时间');?></th>
        <th><?php echo getLang('报价方');?></th>
        <th><?php echo getLang('状态');?></th>
    </tr>
</thead>

<tbody>
<?php
    while ($row = mysqli_fetch_assoc($rs)) {
?>
    <tr>
        <td><a href="bid.php?bid_rec_id=<?php echo $row['bid_rec_id'];?>"><?php echo \PPkPub\Util::trimz($row['bid_amount']) ;?></a> <?php \PPkPub\Util::safeEchoTextToPage(getCoinSymbol($row['coin_type'])) ;?><br>
        <font size="-1"><?php echo getLang('约');?> ¥<?php echo getCoinValueOfCNY($row['bid_amount'],$row['coin_type']);?><?php echo getLang('元');?></font>
        </td>
        <td><?php echo \PPkPub\Util::formatTimestampForView($row['bid_utc']) ;?></td>
        <td><?php \PPkPub\Util::safeEchoTextToPage($row['bidder_uri']) ;?></td>
        <td><?php 
      if($bCurrentUserIsSeller){ 
          echo '<a class="btn btn-warning" role="button" href="bid.php?bid_rec_id=',$row['bid_rec_id'],'">',getStatusLabel($row['status_code']),'</a>';
      }else if($g_currentUserODIN==$row['bidder_uri']){
          echo '<a class="btn btn-warning" role="button" href="bid.php?bid_rec_id=',$row['bid_rec_id'],'">',getStatusLabel($row['status_code']),'</a>';
      }else{
          echo getStatusLabel($row['status_code']);
      } ?></td>
    </tr>
<?php
    }
?>
</tbody>
</table>
</div>
<?php 
    echo $str_bid_buttun_html;
}
require_once "page_footer.inc.php";
?>