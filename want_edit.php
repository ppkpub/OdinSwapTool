<?php
/*      PPK ODIN Swap Toolkit         */
/*         PPkPub.org  20200221           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

$want_rec_id=safeReqNumStr('want_rec_id');

if(strlen($want_rec_id)==0){
  echo 'Invalid record ID.';
  exit(-1);
}

$sqlstr = "SELECT wants.* FROM wants where want_rec_id='$want_rec_id' ;";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (!$rs) {
  echo 'Not existed record.';
  exit(-1);  
}
$tmp_want_record = mysqli_fetch_assoc($rs);

$bCurrentUserIsWanter = ($g_currentUserODIN==$tmp_want_record['wanter_uri']) ? true:false;
if (!$bCurrentUserIsWanter) {
  echo 'Only can edit the want by self.';
  exit(-1);  
}   

$suggest_coin_type = $tmp_want_record['coin_type'];
$suggest_start_amount = $tmp_want_record['offer_amount'];
$wait_hours = ($tmp_want_record['end_utc']-$tmp_want_record['start_utc'])/3600;

require_once "page_header.inc.php";
?>
<div class="row section">
  <div class="form-group">
    <label for="top_buttons" class="col-sm-5 control-label"><h3><?php echo getLang('修改求购信息');?></h3></label>
    <div class="col-sm-7" id="top_buttons" align="right">
    </div>
  </div>
</div>

<form class="form-horizontal" action="want_edit_confirm.php" method="post">
  <input type="hidden" name="form" value="edit_want">
  <input type="hidden" name="want_rec_id" value="<?php echo $want_rec_id;?>">

  <div class="form-group">
    <label for="seller_odin" class="col-sm-2 control-label"><?php echo getLang('求购者身份标识');?></label>
    <div class="col-sm-10">
      <span id="seller_odin"><?php safeEchoTextToPage( $g_currentUserODIN );?></span>
    </div>
  </div> 
  
  <div class="form-group">
    <label for="want_names" class="col-sm-2 control-label"><?php echo getLang('想买的奥丁号');?></label>
    <div class="col-sm-10">
      <input type="text" class="form-control" placeholder="<?php echo getLang('列出想要的奥丁号数字或英文名称');?>" name="want_names" id="want_names" value="<?php safeEchoTextToPage( $tmp_want_record['want_names'] );?>">
    </div>
  </div>  
  
  <div class="form-group">
    <label for="coin_type" class="col-sm-2 control-label"><?php echo getLang('出价币种');?></label>
    <div class="col-sm-10">
      <select class="form-control" name="coin_type" id="coin_type" onchange="updateRmbValue();" size=3>
          <?php
          foreach($gArraySupportedCoinTypeList as $tmp_coin_type){
              echo '<option value="',$tmp_coin_type,'" ',( $tmp_coin_type==$suggest_coin_type ? 'selected':'') ,'>',getCoinName($tmp_coin_type),'(',getCoinSymbol($tmp_coin_type),')</option>';
          }
          ?>
      </select>
    </div>
  </div>
  
  <div class="form-group">
    <label for="offer_amount" class="col-sm-2 control-label"><?php echo getLang('期望价格');?></label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  name="offer_amount" id="offer_amount" value="<?php echo $suggest_start_amount; ?>"  onchange="updateRmbValue();" ><br>
      <font size="-1"><?php echo getLang('约');?> ¥<span id='start_rmb_value'><?php echo ceil($suggest_start_amount * $gArrayCoinPriceCNY[$suggest_coin_type]); ?></span><?php echo getLang('元');?></font> (<?php echo getLang('不填写或输入0表示无限价求购');?>)
    </div>
  </div>
  
  <div class="form-group">
    <label for="remark" class="col-sm-2 control-label"><?php echo getLang('详细说明');?></label>
    <div class="col-sm-10">
     <textarea class="form-control" name="remark" id="remark" rows=10 placeholder="<?php echo getLang('可以填写对所购奥丁号的描述说明、求购方的联系方式如Email/微信/Telegram等。');?>" ><?php safeEchoTextToPage( $tmp_want_record['remark'] );?>
     </textarea>
    </div>
  </div>
  
  <div class="form-group">
    <label for="bid_hours" class="col-sm-2 control-label"><?php echo getLang('求购持续时间');?></label>
    <div class="col-sm-10">
      <select class="form-control" name="bid_hours" id="bid_hours">
          <option value="1" <?php if($wait_hours==1){echo 'selected="selected"';}?>>1<?php echo getLang('小时');?></option>
          <option value="3" <?php if($wait_hours==3){echo 'selected="selected"';}?>>3<?php echo getLang('小时');?></option>
          <option value="6" <?php if($wait_hours==6){echo 'selected="selected"';}?>>6<?php echo getLang('小时');?></option>
          <option value="24" <?php if($wait_hours==24){echo 'selected="selected"';}?>>1<?php echo getLang('天');?></option>
          <option value="48" <?php if($wait_hours==48){echo 'selected="selected"';}?>>2<?php echo getLang('天');?></option>
          <option value="72" <?php if($wait_hours==72){echo 'selected="selected"';}?>>3<?php echo getLang('天');?></option>
          <option value="168" <?php if($wait_hours==168){echo 'selected="selected"';}?>>1<?php echo getLang('周');?> (7<?php echo getLang('天');?>)</option>
          <option value="720"  <?php if($wait_hours==720){echo 'selected="selected"';}?>>1<?php echo getLang('月');?>(30<?php echo getLang('天');?>)</option>
          <option value="8760"  <?php if($wait_hours==8760){echo 'selected="selected"';}?>>1<?php echo getLang('年');?> (365<?php echo getLang('天');?>)</option>
          <!--<option value="0">长期，可由求购方提前结束</option>-->
      </select>
    </div>
  </div>
  
  <div class="form-group" align="center">
    <div class="col-sm-offset-2 col-sm-10">
      <button class="btn btn-success btn-lg" type="submit"  ><?php echo getLang('确认修改');?></button>
    </div>
  </div>

</form>

<script type="text/javascript">
var mCoinCnyPriceList=<?php echo json_encode($gArrayCoinPriceCNY);?>;

function updateRmbValue(){
    var obj_coinlist = document.getElementById("coin_type");
    var coin_type=obj_coinlist.options[obj_coinlist.selectedIndex].value;
    var coin_value=0+document.getElementById("offer_amount").value;
    document.getElementById("start_rmb_value").innerHTML= Math.ceil( coin_value * mCoinCnyPriceList[coin_type] );
}
</script>
<?php
require_once "page_footer.inc.php";
?>