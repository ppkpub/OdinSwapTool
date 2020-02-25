<?php
/*      PPK ODIN Swap Toolkit         */
/*         PPkPub.org  20190415           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

if(strlen($g_currentUserODIN)==0){
  Header('Location: login.php?backpage=new_sell');
  exit(-1);
}

if($g_currentUserLevel<2){
  error_exit('./','该奥丁号帐户只能参与报价！<br>需设置有效身份验证密钥并通过验证后才能发起拍卖。<br>This account only can bid.');
}

$suggest_coin_type = DEFAULT_COIN_TYPE;
$suggest_start_amount = "0.0";
    
$from_want_rec_id=safeReqNumStr('from_want_rec_id');
if(strlen($from_want_rec_id)>0){
    $sqlstr = "SELECT wants.* FROM wants where want_rec_id='$from_want_rec_id' and status_code='".PPK_ODINSWAP_STATUS_WANT."';";
    $rs = mysqli_query($g_dbLink,$sqlstr);
    if (!$rs) {
      echo 'Not existed want record.';
      exit(-1);  
    }
    $from_want_record = mysqli_fetch_assoc($rs);

    if(!$from_want_record){
      echo 'Not existed want record.';
      exit(-1);  
    }
    
    $suggest_coin_type = $from_want_record['coin_type'];
    $suggest_start_amount = $from_want_record['offer_amount'];
}

$asset_id=safeReqChrStr('asset_id');
if(strlen($asset_id)==0){
  error_exit('./', '无效的ODIN短标识. Invalid Short ODIN.');
}

$tmp_data=getPPkResource(PPK_URI_PREFIX.$asset_id.PPK_URI_RES_FLAG);

if($tmp_data['status_code']!=200){
  error_exit('./','获取ODIN短标识资源信息出错. Failed to get ODIN data.');
}

$full_odin_uri=$tmp_data['uri'];
$tmp_odin_info=@json_decode($tmp_data['content'],true);
        
//$str_created_time = formatTimestampForView($tmp_user_info['block_time'],false);

require_once "page_header.inc.php";
?>
<div class="row section">
  <div class="form-group">
    <label for="top_buttons" class="col-sm-5 control-label"><h3><?php echo getLang('发布新拍卖');?> <?php echo getLang('奥丁号');?>[<?php safeEchoTextToPage( $asset_id );?>]:<?php safeEchoTextToPage( $full_odin_uri);?></h3></label>
    <div class="col-sm-7" id="top_buttons" align="right">
    </div>
  </div>
</div>
<!--资产记录信息
<ul>
<li>资产ID : <?php safeEchoTextToPage( $asset_id);?></li>
<li>资产名称 : <?php safeEchoTextToPage( $tmp_asset_info['name']);?></li>
<li>资产代号 : <?php safeEchoTextToPage( $tmp_asset_info['symbol']);?></li>
<li>资产URI: <?php safeEchoTextToPage( $full_odin_uri);?></li>
<li>详细说明 : <?php safeEchoTextToPage( $tmp_asset_info['description']);?></li>
</ul>
-->
<!--ODIN资产信息-->
<ul>
<li><?php echo getLang('管理者比特币地址');?> : <?php safeEchoTextToPage( $tmp_odin_info['admin']);?></li>
<li><?php echo getLang('附注名称');?> : <?php safeEchoTextToPage( $tmp_odin_info['title']);?></li>
<li><?php echo getLang('电子邮件');?> : <?php safeEchoTextToPage( $tmp_odin_info['email']);?></li>

<li><?php echo getLang('配置权限');?> : <?php safeEchoTextToPage( $tmp_odin_info['auth']);?></li>

<li><?php echo getLang('注册者比特币地址');?> : <?php safeEchoTextToPage( $tmp_odin_info['register']);?></li>
</ul>
<?php
//检查该标识是否已在拍卖中
$sqlstr = "SELECT sells.sell_rec_id FROM sells WHERE asset_id='$asset_id' AND seller_uri='$g_currentUserODIN' AND NOT status_code IN (".PPK_ODINSWAP_STATUS_CANCEL.",".PPK_ODINSWAP_STATUS_NONE.",".PPK_ODINSWAP_STATUS_UNCONFIRM.",".PPK_ODINSWAP_STATUS_UNPAID.");";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (!$rs) {
  error_exit('./', '数据库查询有误，请稍候再试. DB failed,Please retry later!');
}

$row= mysqli_fetch_assoc($rs);
if ($row) {
  error_exit('./', '指定资产标识有拍卖正在进行中[<a href="sell.php?sell_rec_id='.$row['sell_rec_id'].'">'.$row['sell_rec_id'].'</a>]，不能重复发布. Existed same sell record.');
}

//检查有权拍卖该标识
/* 暂时禁用
$tmp_user_info=getPubUserInfo($g_currentUserODIN);

if($tmp_user_info['register']!= $tmp_odin_info['register']){
  echo '当前用户无权拍卖不属于自己的资产. Unable to sell ODIN belong to others.';
  exit(-1);
}
*/
?>

<form class="form-horizontal" action="new_sell_confirm.php" method="post">
  <input type="hidden" name="form" value="new_sell">
  <input type="hidden" name="asset_id" value="<?php safeEchoTextToPage( $asset_id );?>">
  <input type="hidden" name="from_want_rec_id" value="<?php safeEchoTextToPage( $from_want_rec_id );?>">

  <div class="form-group">
    <label for="seller_odin" class="col-sm-2 control-label"><?php echo getLang('发布者身份标识');?></label>
    <div class="col-sm-10">
      <span id="seller_odin"><?php safeEchoTextToPage( $g_currentUserODIN );?></span>
    </div>
  </div> 
  
  <div class="form-group">
    <label for="recommend_names" class="col-sm-2 control-label"><?php echo getLang('推荐标题');?></label>
    <div class="col-sm-10">
      <input type="text" class="form-control" placeholder="<?php echo getLang('如该ODIN标识可对应的靓号名称举例');?>" name="recommend_names" id="recommend_names" value="">
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
    <label for="start_amount" class="col-sm-2 control-label"><?php echo getLang('起始报价');?></label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  name="start_amount" id="start_amount" value="<?php echo $suggest_start_amount; ?>"  onchange="updateRmbValue();" ><br>
      <font size="-1"><?php echo getLang('约');?> ¥<span id='start_rmb_value'><?php echo ceil($suggest_start_amount * $gArrayCoinPriceCNY[$suggest_coin_type]); ?></span><?php echo getLang('元');?></font> (<?php echo getLang('不填写或输入0表示无底价拍卖');?>)
    </div>
  </div>
  
  <div class="form-group">
    <label for="remark" class="col-sm-2 control-label"><?php echo getLang('详细说明');?></label>
    <div class="col-sm-10">
     <textarea class="form-control" name="remark" id="remark" rows=10 placeholder="<?php echo getLang('可以填写对该数字资产的描述说明、拍卖方的联系方式如Email/微信/Telegram等。');?>" ></textarea>
     <span><strong><?php echo getLang('注意：拍卖内容一旦提交，将不能更改，请谨慎操作！');?></strong></span>
    </div>
  </div>
  
  <div class="form-group">
    <label for="bid_hours" class="col-sm-2 control-label"><?php echo getLang('竞拍持续时间');?></label>
    <div class="col-sm-10">
      <select class="form-control" name="bid_hours" id="bid_hours">
          <option value="1">1<?php echo getLang('小时');?></option>
          <option value="3">3<?php echo getLang('小时');?></option>
          <option value="6">6<?php echo getLang('小时');?></option>
          <option value="24">1<?php echo getLang('天');?></option>
          <option value="48">2<?php echo getLang('天');?></option>
          <option value="72">3<?php echo getLang('天');?></option>
          <option value="168">1<?php echo getLang('周');?> (7<?php echo getLang('天');?>)</option>
          <option value="720" selected="selected">1<?php echo getLang('月');?>(30<?php echo getLang('天');?>)</option>
          <option value="8760" >1<?php echo getLang('年');?> (365<?php echo getLang('天');?>)</option>
          <!--<option value="0">长期，可由拍卖方提前结束</option>-->
      </select>
    </div>
  </div>
  
  <div class="form-group" align="center">
    <div class="col-sm-offset-2 col-sm-10">
      <button class="btn btn-success btn-lg" type="submit"  onclick="if (window.confirm('<?php echo getLang('注意：拍卖内容一旦提交，将不能更改，确认提交吗？');?>')) {this.innerHTML='Waiting';this.disabled=true;form.submit();  } else {return false;}" ><?php echo getLang('马上发布');?></button>
    </div>
  </div>

</form>

<script type="text/javascript">
var mCoinCnyPriceList=<?php echo json_encode($gArrayCoinPriceCNY);?>;

function updateRmbValue(){
    var obj_coinlist = document.getElementById("coin_type");
    var coin_type=obj_coinlist.options[obj_coinlist.selectedIndex].value;
    var coin_value=0+document.getElementById("start_amount").value;
    document.getElementById("start_rmb_value").innerHTML= Math.ceil( coin_value * mCoinCnyPriceList[coin_type] );
}
</script>
<?php
require_once "page_footer.inc.php";
?>