<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20190415           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

$sell_rec_id=safeReqNumStr('sell_rec_id');

if(strlen($sell_rec_id)==0){
  error_exit('./', 'Invalid auction record ID.');
}

if(strlen($g_currentUserODIN)==0){
  Header('Location: login.php?backpage=new_bid&sell_rec_id='.$sell_rec_id);
  exit(-1);
}


$sqlstr = "SELECT sells.* FROM sells where sells.sell_rec_id='$sell_rec_id';";
//echo $sqlstr ;
$rs = mysqli_query($g_dbLink,$sqlstr);
if (!$rs) {
  error_exit('./', 'Not existed auction record.');
}
$tmp_sell_record = mysqli_fetch_assoc($rs);
$asset_id=$tmp_sell_record['asset_id'] ;
$full_odin_uri=$tmp_sell_record['full_odin_uri'] ;

//查询已有最高报价，并设置缺省建议报价
$sqlstr = "SELECT sell_rec_id,max(bid_amount) as max_bid_amount FROM bids where sell_rec_id='$sell_rec_id';";
//echo $sqlstr ;
$rs_max_bid = mysqli_query($g_dbLink,$sqlstr);
if ($rs_max_bid) {
   $tmp_max_bid_record = mysqli_fetch_assoc($rs_max_bid);
   $suggest_bid_amount=$tmp_max_bid_record['max_bid_amount']+0.001 ;
}else{
   $suggest_bid_amount=0.001; 
}

if($suggest_bid_amount<$tmp_sell_record['start_amount']){
    $suggest_bid_amount=$tmp_sell_record['start_amount'];
}
        
//$str_created_time = formatTimestampForView($tmp_user_info['block_time'],false);


require_once "page_header.inc.php";
?>
<div class="row section">
  <div class="form-group">
    <label for="top_buttons" class="col-sm-5 control-label"><h3><?php echo getLang('报价参拍');?></h3></label>
    <div class="col-sm-7" id="top_buttons" align="right">
    </div>
  </div>
</div>

<?php
//检查有权参拍该标识
//$tmp_user_info=getPubUserInfo($g_currentUserODIN);

echo '<p><strong>',getLang('标的奥丁号'),': </strong> <a href="http://tool.ppkpub.org:9876/odin-detail?odin=',urlencode($asset_id),'" target="_blank">',getSafeEchoTextToPage($asset_id),'</a></p>';

echo '<p><strong>',getLang('参拍用户身份'),': </strong> ',getSafeEchoTextToPage($g_currentUserODIN),'</p>';

if($g_currentUserODIN== $tmp_sell_record['seller_uri']  ){
  error_exit('./', '不能参拍自己的数字资产. Unable to bid asset belong to yourself.');
}

$bidder_address=getCoinAddressURI($tmp_sell_record['coin_type'],$g_currentUserODIN);

?>

<h3><?php echo getLang('填写我的报价信息');?></h3>
<form class="form-horizontal" action="new_bid_confirm.php" method="post">
  <input type="hidden" name="form" value="new_sell">
  <input type="hidden" name="sell_rec_id" value="<?php echo $sell_rec_id;?>">

  <div class="form-group">
    <label for="coin_type" class="col-sm-2 control-label"><?php echo getLang('出价币种');?></label>
    <div class="col-sm-10">
      <select class="form-control" name="coin_type" id="coin_type">
          <option value="<?php echo getSafeEchoTextToPage($tmp_sell_record['coin_type']); ?>"><?php echo getSafeEchoTextToPage(getCoinSymbol($tmp_sell_record['coin_type'])); ?></option>
      </select>
    </div>
  </div>
  
  <div class="form-group">
    <label for="bidder_address" class="col-sm-2 control-label"><?php echo getLang('我的钱包地址');?></label>
    <div class="col-sm-10">
      <?php
      if( empty($bidder_address)  ){
          $tmp_set_url='bind_address.php?coin_type='.urlencode($tmp_sell_record['coin_type']);
          echo '<font color="#F00">',getLang('尚未关联设置对应的钱包地址'),' , <a href="'.$tmp_set_url.'">',getLang('请点击这里设置'),'...</a></font>';
          exit(-1);
      }else{
          echo '<input type="text" class="form-control" name="bidder_address" id="bidder_address" value="',getSafeEchoTextToPage($bidder_address),'">';
      }
      ?>
    </div>
  </div>
  
  <div class="form-group">
    <label for="bid_amount" class="col-sm-2 control-label"><?php echo getLang('我的报价');?></label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  name="bid_amount" id="bid_amount" value="<?php echo $suggest_bid_amount;?>" onchange="updateRmbValue();"><br>
      <font size="-1"><?php echo getLang('约');?> ¥<span id='bid_rmb_value'><?php echo getCoinValueOfCNY($suggest_bid_amount,$tmp_sell_record['coin_type']);?></span><?php echo getLang('元');?></font>
    </div>
  </div>
  
  <div class="form-group">
    <label for="remark" class="col-sm-2 control-label"><?php echo getLang('备注说明');?><br></label>
    <div class="col-sm-10">
     <textarea class="form-control" name="remark" id="remark" rows=10 ></textarea>
     <span><?php echo getLang('可以填写报价方自己的可选联系方式如Email/微信/Telegram等。');?><br><strong><?php echo getLang('');?><?php echo getLang('注意：报价内容一旦提交，将不能更改和撤销，请仔细填写！');?></strong></span>
    </div>
  </div>
  
  <div class="form-group" align="center">
    <div class="col-sm-offset-2 col-sm-10">
      <button class="btn btn-success btn-lg" type="submit"  onclick="return confirmInput();" ><?php echo getLang('提交报价');?></button>
      
    </div>
  </div>

</form>
<script type="text/javascript">
function updateRmbValue(){
    var tmp_value=document.getElementById("bid_amount").value;
    document.getElementById("bid_rmb_value").innerHTML= Math.ceil( tmp_value * <?php echo getCoinValueOfCNY(1,$tmp_sell_record['coin_type']);?>);
}

function confirmInput(){
    var obj_coinlist = document.getElementById("coin_type");
    var coin_type=obj_coinlist.options[obj_coinlist.selectedIndex].value;
    
    if (window.confirm('<?php echo getLang('注意：报价一旦提交，将不能更改和撤销，确认提交吗？');?>')){
        this.innerHTML='Waiting';this.disabled=true;form.submit();  
    }else 
        return false;
}

</script>
<?php
require_once "page_footer.inc.php";
?>