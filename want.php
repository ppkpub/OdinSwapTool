<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20200221           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

$want_rec_id=\PPkPub\Util::safeReqNumStr('want_rec_id');

if(strlen($want_rec_id)==0){
  echo 'Invalid record ID.';
  exit(-1);
}

$sqlstr = "SELECT wants.* FROM wants where want_rec_id='$want_rec_id' ;";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (!$rs) {
  echo 'DB failed.';
  exit(-1);  
}
$tmp_want_record = mysqli_fetch_assoc($rs);
if (!$tmp_want_record) {
  echo 'Not existed record.';
  exit(-1);  
}

$bCurrentUserIsWanter = ($g_currentUserODIN==$tmp_want_record['wanter_uri']) ? true:false;
     

require_once "page_header.inc.php";
?>
<div class="row section">
  <div class="form-group">
    <label for="top_buttons" class="col-sm-5 control-label"><h3><?php echo getLang('求购奥丁号');?></h3></label>
    <div class="col-sm-7" id="top_buttons" align="right">
    </div>
  </div>
</div>
 
<form class="form-horizontal" action="update_want_confirm.php" method="post">
  <input type="hidden" name="form" value="new_want">
  <input type="hidden" name="want_rec_id" value="<?php echo $want_rec_id;?>">

 
  <div class="form-group">
    <label for="want_names" class="col-sm-2 control-label"><?php echo getLang('想买的奥丁号');?></label>
    <div class="col-sm-10">
      <span id="want_names"><?php \PPkPub\Util::safeEchoTextToPage( $tmp_want_record['want_names'] );?></span>
    </div>
  </div>      
  
  <div class="form-group">
    <label for="offer_amount" class="col-sm-2 control-label"><?php echo getLang('期望价格');?></label>
    <div class="col-sm-10">
      <span id="offer_amount"><?php echo $tmp_want_record['offer_amount']==0 ? getLang('无底价'):\PPkPub\Util::trimz($tmp_want_record['offer_amount']);?> (<?php echo \PPkPub\Util::getSafeEchoTextToPage(getCoinSymbol($tmp_want_record['coin_type'])); ?>)</span>
    </div>
  </div>
  
  
  <div class="form-group">
    <label for="remark" class="col-sm-2 control-label"><?php echo getLang('详细说明');?></label>
    <div class="col-sm-10">
     <textarea class="form-control" name="remark" id="remark" readonly rows=3 ><?php \PPkPub\Util::safeEchoTextToPage( $tmp_want_record['remark'] );?></textarea>
     <span><?php echo getLang('注意：出于资金安全，建议采用求购方身份标识对应钱包地址作为奥丁号转移地址，并仔细核对币种数额。不要随意向通过微信、Telegram等聊天工具联系时提供的地址转账。');?></span>
    </div>
  </div>
  
  <div class="form-group">
    <label for="status_code" class="col-sm-2 control-label"><?php echo getLang('求购状态');?></label>
    <div class="col-sm-10">
      <span id="status_code"><?php echo getStatusLabel($tmp_want_record['status_code']);
      if($bCurrentUserIsWanter && $tmp_want_record['status_code']==PPK_ODINSWAP_STATUS_WANT){
          echo ' <a class="btn btn-warning" role="button" href="want_close.php?want_rec_id=',$want_rec_id,'">',getLang('结束求购'),'</a>';
      }
      ?></span>
    </div>
  </div>   
  
  <div class="form-group">
    <label for="end_utc" class="col-sm-2 control-label"><?php echo getLang('求购结束时间');?></label>
    <div class="col-sm-10">
      <span id="end_utc"><?php 
      if($tmp_want_record['status_code']==PPK_ODINSWAP_STATUS_WANT){
        if($tmp_want_record['end_utc']==PPK_ODINSWAP_LONGTIME_UTC)  
            echo getLang('长期');
        else
            echo \PPkPub\Util::formatTimestampForView($tmp_want_record['end_utc'],false),' , ' , \PPkPub\Util::friendlyTime($tmp_want_record['end_utc']); 
     }else if($tmp_want_record['status_code']==PPK_ODINSWAP_STATUS_CLOSED){
        echo getLang('已结束');
     }else{
        echo \PPkPub\Util::formatTimestampForView($tmp_want_record['end_utc'],false); 
     }
      ?> </span>
    </div>
  </div>
  
  <div class="form-group">
    <label for="wanter_uri" class="col-sm-2 control-label"><?php echo getLang('求购方身份标识');?></label>
    <div class="col-sm-10">
      <span id="wanter_uri"><?php echo getUserLabelHTML($tmp_want_record['wanter_uri']); ?></span>
    </div>
  </div>
  
  <?php if($bCurrentUserIsWanter){ ?>
  <div class="form-group" align="center">
    <div class="col-sm-offset-2 col-sm-10">
      <?php
         echo ' <a class="btn btn-primary" role="button" href="want_edit.php?want_rec_id=',$want_rec_id,'">',getLang(' 修改求购信息 '),'</a>';
      ?>
    </div>
  </div>
  <?php } ?>
 
</form>

<h3><?php echo getLang('备选拍卖列表');?></h3>
<?php
$str_bid_buttun_html='';
if($tmp_want_record['status_code']==PPK_ODINSWAP_STATUS_WANT
   && $g_currentUserODIN != $tmp_want_record['wanter_uri']){
    $str_bid_buttun_html='<p align=center><a class="btn btn-success" role="button" href="user.php?from_want_rec_id='.$want_rec_id.'">'.getLang('我要卖出').'</a></p>';
}


$sqlstr = "SELECT * FROM sells where from_want_rec_id='$want_rec_id' order by start_amount desc,from_want_rec_id desc;";

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
        <th><?php echo getLang('拍卖奥丁号');?></th>
        <th><?php echo getLang('报价');?></th>
        <th><?php echo getLang('时间');?></th>
        <th><?php echo getLang('拍卖方');?></th>
        <th><?php echo getLang('状态');?></th>
    </tr>
</thead>

<tbody>
<?php
    while ($row = mysqli_fetch_assoc($rs)) {
        $tmp_title=$row['recommend_names'];
        if(empty($tmp_title)){
            $tmp_title=$row['asset_id'];
        }
?>
    <tr>
        <td><a href="sell.php?sell_rec_id=<?php echo $row['sell_rec_id'];?>"><?php \PPkPub\Util::safeEchoTextToPage($tmp_title);?></a><br><font size="-1"><?php  echo \PPkPub\ODIN::PPK_URI_PREFIX,\PPkPub\Util::getSafeEchoTextToPage(\PPkPub\Util::friendlyLongID($row['asset_id']));?></font></td>
        
        <td><a href="sell.php?sell_rec_id=<?php echo $row['sell_rec_id'];?>"><?php echo \PPkPub\Util::trimz($row['start_amount']) ;?></a> <?php \PPkPub\Util::safeEchoTextToPage(getCoinSymbol($row['coin_type'])) ;?><br>
        <font size="-1"><?php echo getLang('约');?> ¥<?php echo getCoinValueOfCNY($row['start_amount'],$row['coin_type']);?><?php echo getLang('元');?></font>
        </td>
        <td><?php echo \PPkPub\Util::formatTimestampForView($row['start_utc']) ;?></td>
        <td><?php \PPkPub\Util::safeEchoTextToPage($row['seller_uri']) ;?></td>
        <td><?php echo '<a class="btn btn-success" role="button" href="sell.php?sell_rec_id=',$row['sell_rec_id'],'">',getStatusLabel($row['status_code']),'</a>';?></td>
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