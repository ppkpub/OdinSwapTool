<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20190415           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

$bid_rec_id=\PPkPub\Util::safeReqNumStr('bid_rec_id');

if(strlen($bid_rec_id)==0){
  \PPkPub\Util::error_exit('./', 'Invalid bid record ID.');
}

$sqlstr = "SELECT bids.*,sells.seller_uri FROM bids,sells where sells.sell_rec_id=bids.sell_rec_id and bid_rec_id='$bid_rec_id';";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (!$rs) {
  echo 'DB failed.';
  exit(-1);  
}
$tmp_bid_record = mysqli_fetch_assoc($rs);
if (!$tmp_bid_record) {
  echo 'Not existed bid record.';
  exit(-1);  
}

$sell_rec_id=$tmp_bid_record['sell_rec_id'];
$asset_id=$tmp_bid_record['asset_id'] ;
$full_odin_uri=$tmp_bid_record['full_odin_uri'] ;

$bCurrentUserIsSeller = ($g_currentUserODIN==$tmp_bid_record['seller_uri']) ? true:false;
$bCurrentUserIsBidder = ($g_currentUserODIN==$tmp_bid_record['bidder_uri']) ? true:false;

//$str_created_time = \PPkPub\Util::formatTimestampForView($tmp_user_info['block_time'],false);

require_once "page_header.inc.php";
?>
<div class="row section">
  <div class="form-group">
    <label for="top_buttons" class="col-sm-5 control-label"><h3><?php echo getLang('报价单详情');?></h3></label>
    <div class="col-sm-7" id="top_buttons" align="right">
    </div>
  </div>
</div>

<form class="form-horizontal" action="bid_action.php" method="post" id="bid_form">
  <input type="hidden" name="bid_rec_id" value="<?php echo $bid_rec_id;?>">
  <input type="hidden" name="action_type" id="action_type" value="">

  <div class="form-group">
    <label for="asset_id" class="col-sm-2 control-label"><?php echo getLang('标的奥丁号');?></label>
    <div class="col-sm-10">
      <span id="asset_id"><a href="http://tool.ppkpub.org:9876/odin-detail?odin=<?php echo urlencode($asset_id);?>" target="_blank"><?php echo \PPkPub\Util::getSafeEchoTextToPage($asset_id);?></a></span>
    </div>
  </div>
  
  <div class="form-group">
    <label for="bidder_uri" class="col-sm-2 control-label"><?php echo getLang('报价方');?></label>
    <div class="col-sm-10">
      <span id="bidder_uri"><?php echo getUserLabelHTML($tmp_bid_record['bidder_uri']);?></span>
    </div>
  </div>
  
  <div class="form-group">
    <label for="bid_amount" class="col-sm-2 control-label"><?php echo getLang('报价金额');?></label>
    <div class="col-sm-10">
      <span id="bid_amount"><?php echo \PPkPub\Util::trimz($tmp_bid_record['bid_amount']);?> <?php echo \PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($tmp_bid_record['coin_type']));?></span>
    </div>
  </div>
  
  <div class="form-group">
    <label for="status_code" class="col-sm-2 control-label"><?php echo getLang('状态');?></label>
    <div class="col-sm-10">
      <span id="status_code"><?php echo getStatusLabel($tmp_bid_record['status_code']);?></span>
    </div>
  </div>   

  
<?php
//只有对应拍卖方和投标者才能更新报价单
if($bCurrentUserIsSeller || $bCurrentUserIsBidder ){
    $bidder_address=getCoinAddressURI($tmp_bid_record['coin_type'],$tmp_bid_record['bidder_uri']);
    
    $seller_address=getCoinAddressURI($tmp_bid_record['coin_type'],$tmp_bid_record['seller_uri']);

?>
  <div class="form-group">
    <label for="bidder_address" class="col-sm-2 control-label"><?php echo $bCurrentUserIsBidder ? getLang('我的钱包地址'):getLang('报价方钱包地址');?></label>
    <div class="col-sm-10">
      <input type="text" class="form-control" readonly  name="bidder_address" value="<?php \PPkPub\Util::safeEchoTextToPage( $bidder_address );?>" >
      <?php
        if($bCurrentUserIsBidder && strlen($bidder_address)==0 ){
            echo getLang('注意：尚未关联设置钱包地址'),' , <a href="bind_address.php?coin_type=',urlencode($tmp_bid_record['coin_type']),'">',getLang('请点击这里设置'),'...</a>';
        }
        ?>
    </div>
  </div>
  
  <div class="form-group">
    <label for="remark" class="col-sm-2 control-label"><?php echo getLang('备注说明');?><br></label>
    <div class="col-sm-10">
     <textarea class="form-control" id="remark" readonly rows=3 ><?php \PPkPub\Util::safeEchoTextToPage( $tmp_bid_record['remark'] );?></textarea>
    </div>
  </div>
  
  <div class="form-group">
    <label for="seller_address" class="col-sm-2 control-label"><?php echo $bCurrentUserIsSeller ? getLang('我的钱包地址'):getLang('拍卖方钱包地址');?></label>
    <div class="col-sm-10">
      <input type="text" class="form-control" readonly  name="seller_address" value="<?php \PPkPub\Util::safeEchoTextToPage( $seller_address );?>" >
      <?php
        if($bCurrentUserIsSeller && strlen($seller_address)==0 ){
            echo '<font color="#F00">',getLang('注意：尚未关联设置钱包地址'),' , <a href="bind_address.php?coin_type=',urlencode($tmp_bid_record['coin_type']),'">',getLang('请点击这里设置'),'...</a><font>';
            exit(-1);
        }
        ?>
    </div>
    
  </div>

  <?php if($bCurrentUserIsSeller && $tmp_bid_record['status_code']==PPK_ODINSWAP_STATUS_BID){ ?>
  <div class="form-group" align="center">
    <a class="btn btn-warning" role="button" href="#" onclick="this.innerHTML='<?php echo getLang('请稍候');?>...';this.disabled=true;doBidAction('accept');" ><?php echo getLang('接受报价');?></a>
  </div>
  <?php } ?>
  
  <?php if($bCurrentUserIsSeller && $tmp_bid_record['status_code']==PPK_ODINSWAP_STATUS_PAID){ ?>
  <div class="form-group" align="center">
    <a class="btn btn-warning" role="button" href="bid_action_ok.php?bid_rec_id=<?php echo $bid_rec_id;?>&action_type=transfer" ><?php echo getLang('已转移资产拥有权');?></a>
  </div>
  <?php } ?>
  
  <?php if($bCurrentUserIsBidder && $tmp_bid_record['status_code']==PPK_ODINSWAP_STATUS_ACCEPT){ ?>
  <div class="form-group" align="center">
    <a class="btn btn-danger" role="button" href="#" onclick="this.innerHTML='<?php echo getLang('请稍候');?>...';this.disabled=true;doBidAction('pay');" ><?php echo getLang('现在付款给拍卖方');?></a>
  </div>
  <?php } ?>
  
  <?php if($bCurrentUserIsBidder && $tmp_bid_record['status_code']==PPK_ODINSWAP_STATUS_TRANSFER){ ?>
  <div class="form-group" align="center">
    <a class="btn btn-warning" role="button" href="bid_action_ok.php?bid_rec_id=<?php echo $bid_rec_id;?>&action_type=finish" ><?php echo getLang('确认收到资产，交易完成');?></a>
  </div>
  <?php 
  } 
}
?>

  <h3 align=center><?php echo getLang('相关链上存证信息');?>(<?php echo getCoinName($tmp_bid_record['coin_type']);?>)</h3>
  <div class="form-group">
    <label for="accepted_txid" class="col-sm-2 control-label"><?php echo $bCurrentUserIsSeller ? getLang('我接受报价的记录'):getLang('拍卖方接受报价的记录');?></label>
    <div class="col-sm-10">
      <?php
      if(strlen($tmp_bid_record['accepted_txid'])>0){
          echo '<input type="text" class="form-control" readonly  name="accepted_txid" value="',$tmp_bid_record['coin_type'],\PPkPub\Util::getSafeEchoTextToPage($tmp_bid_record['accepted_txid']),'" >';
          
          echo getLang('原始交易ID'),': ',\PPkPub\Util::getSafeEchoTextToPage( $tmp_bid_record['accepted_txid'] ),'<br><a target="_blank" href="',$gArrayCoinTypeSet[$tmp_bid_record['coin_type']]['tx_explorer_url'],urldecode($tmp_bid_record['accepted_txid']),'">',getLang('查看对应的链上存证信息'),'</a>';
      }
      ?>     
    </div>
  </div>
  <div class="form-group">
    <label for="payment_txid" class="col-sm-2 control-label"><?php echo $bCurrentUserIsBidder ? getLang('我确认付款的记录'):getLang('报价方确认付款的记录');?></label>
    <div class="col-sm-10">
      <?php
      if(strlen($tmp_bid_record['payment_txid'])>0){
          echo '<input type="text" class="form-control" readonly  name="payment_txid" value="',$tmp_bid_record['coin_type'],\PPkPub\Util::getSafeEchoTextToPage($tmp_bid_record['payment_txid']),'" >';
          echo getLang('原始交易ID'),': ',\PPkPub\Util::getSafeEchoTextToPage( $tmp_bid_record['payment_txid'] ),'<br><a target="_blank" href="',$gArrayCoinTypeSet[$tmp_bid_record['coin_type']]['tx_explorer_url'],urldecode($tmp_bid_record['payment_txid']),'">',getLang('查看对应的链上存证信息'),'</a>';
      }
      ?>
    </div>
  </div>
</form>

<p align=center><a href="sell.php?sell_rec_id=<?php echo $sell_rec_id;?>"><?php echo getLang('点击返回所属拍卖纪录');?> [<?php \PPkPub\Util::safeEchoTextToPage( \PPkPub\Util::friendlyLongID($asset_id) );?>]</a></p>


<script type="text/javascript">

function doBidAction(action_type){
    document.getElementById("action_type").value=action_type;
    document.getElementById("bid_form").submit();
}
</script>
<?php
require_once "page_footer.inc.php";
?>