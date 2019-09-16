<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20190415           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

if(strlen($g_currentUserODIN)==0){
  Header('Location: login.php');
  exit(-1);
}

$bid_rec_id=safeReqNumStr('bid_rec_id');
$action_type=safeReqChrStr('action_type');
$seller_address=safeReqChrStr('seller_address');
$bidder_address=safeReqChrStr('bidder_address');
$service_uri=PPK_ODINSWAP_SERVICE_URI_PREFIX.'bid/'.$bid_rec_id.'#';

if(strlen($bid_rec_id)==0){
  error_exit('./', 'Invalid record ID.');
}

if(strlen($action_type)==0){
  error_exit('./','Invalid action_type.');
}


$sqlstr = "SELECT bids.*,sells.seller_uri FROM bids,sells where sells.sell_rec_id=bids.sell_rec_id and bid_rec_id='$bid_rec_id';";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (!$rs) {
  error_exit('./','Not existed record.');
}
$tmp_bid_record = mysqli_fetch_assoc($rs);
$asset_id=$tmp_bid_record['asset_id'] ;
$full_odin_uri=$tmp_bid_record['full_odin_uri'] ;
$coin_type=$tmp_bid_record['coin_type'] ;
$bid_amount=$tmp_bid_record['bid_amount'] ;

$bCurrentUserIsSeller = ($g_currentUserODIN==$tmp_bid_record['seller_uri']) ? true:false;
$bCurrentUserIsBidder = ($g_currentUserODIN==$tmp_bid_record['bidder_uri']) ? true:false;

if($action_type=='accept'){
    if( !$bCurrentUserIsSeller ){
      error_exit('./', '只有拍卖方才能确认接受报价单. Only seller can accpet bid.');
    }

    //组织交易信息
    $array_witness_tx_data=genAcceptBidArray($tmp_bid_record['seller_uri'],$seller_address,  $tmp_bid_record['bidder_uri'],$bidder_address,  $asset_id,$full_odin_uri,$coin_type,$bid_amount,$service_uri);
    
}else if($action_type=='pay'){
    if( !($bCurrentUserIsBidder && ($tmp_bid_record['status_code']==PPK_ODINSWAP_STATUS_ACCEPT||$tmp_bid_record['status_code']==PPK_ODINSWAP_STATUS_PAID) ) ) {
      error_exit('./','只有已被接受的报价方才能确认付款. Only accepted bidder can pay for the bid.');
    }
    
    //组织交易信息
    $array_witness_tx_data=genPayBidArray($tmp_bid_record['bidder_uri'],$bidder_address, $tmp_bid_record['seller_uri'],$seller_address,  $asset_id,$full_odin_uri,$coin_type,$bid_amount,$service_uri);
}else{
    error_exit('./', '无效的操作类型. Invalid action_type.');
}

$array_witness_tx_data['amount_satoshi'] = $array_witness_tx_data['amount_satoshi'] + ($bid_rec_id %100) * 10 + 5  ; //将存证交易的交易金额的倒数第2和第3位设为交易记录号的最后两位，最后一位固定为5，作为特别标识来关联查询

$tx_define_json_hex = strToHex(json_encode($array_witness_tx_data));

//兼容生成PPkAndroid APP需要的数据
$array_witness_tx_data['source']=removeCoinPrefix($array_witness_tx_data['from_uri'],COIN_TYPE_BITCOINCASH);
$array_witness_tx_data['destination']=removeCoinPrefix($array_witness_tx_data['to_uri'],COIN_TYPE_BITCOINCASH);
$array_witness_tx_data['coin_type']=$array_witness_tx_data['asset_uri'];
$array_witness_tx_data['data_hex']=strToHex($array_witness_tx_data['data']);

//print_r($array_witness_tx_data);
$ppkapp_tx_define_json_hex = strToHex(json_encode($array_witness_tx_data));

require_once "page_header.inc.php";
?>
<div class="row section">
  <div class="form-group">
    <label for="top_buttons" class="col-sm-5 control-label"><h3><?php echo getLang('处理拍卖交易');?>[<?php safeEchoTextToPage( $asset_id );?>]</h3></label>
    <div class="col-sm-7" id="top_buttons" align="right">
    </div>
  </div>
</div>

<form class="form-horizontal" >
<div class="form-group">
    <label for="bidder_address" class="col-sm-2 control-label"><?php echo getLang('报价方钱包地址');?></label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="bidder_address"  id="bidder_address" value="<?php safeEchoTextToPage( removeCoinPrefix($bidder_address,$coin_type)  );?>">
    </div>
</div>

<div class="form-group">
    <label for="bid_data_desc" class="col-sm-2 control-label"><?php echo getLang('待存证到链上的备注信息');?></label>
    <div class="col-sm-10">
      <textarea class="form-control" rows=3 cols=100 id="bid_data_desc"><?php safeEchoTextToPage( $array_witness_tx_data['data'] );?></textarea>
    </div>
</div>

<div class="form-group">
    <label for="seller_address" class="col-sm-2 control-label"><?php echo getLang('拍卖方钱包地址');?></label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="seller_address"  id="seller_address" value="<?php safeEchoTextToPage( removeCoinPrefix($seller_address,$coin_type) );?>">
    </div>
</div>

<div class="form-group" align="center">
<div class="col-sm-offset-2 col-sm-10">
  <button id='send_trans_btn' class="btn btn-warning" type="button"  onclick="sendTX();return false;"  ><?php echo getLang('确认并发送到');?> <?php safeEchoTextToPage( getCoinName($coin_type));?>  <?php echo getLang('链上公开存证');?></button><br>
  <div id="qrcode_img" ></div>
  <img id="qr_loading" value="image/white.png" ><span id="qr_prompt" ></span><br>
  <?php echo getLang('注：1.确认报价或支付并存证到链上会花费若干交易费用。');?><br>
  <?php echo getLang('2.出于资金安全，建议采用拍卖方身份标识对应钱包地址作为转账地址，并仔细核对币种数额。不要随意向通过微信、Telegram等聊天工具联系时提供的地址转账。');?><br>
  <?php echo getLang('3.在通过BTC链进行奥丁号转移过户时，卖方在发出转移和买方在确认转移时，需留意调整所使用奥丁号管理工具的矿工费用，建议在BCT交易拥堵时提高到10000聪或更好水平，以得到比较快的确认。');?></span>
</div>
</div>
</form>

<input type="hidden" id="game_trans_fee_btm" value="<?php echo TX_GAS_AMOUNT_mBTM/1000; ?>" >


<form class="form-horizontal" action="bid_action_ok.php" method="post" id="bid_action_form">
<input type="hidden" name="bid_rec_id" value="<?php echo $bid_rec_id;?>">
<input type="hidden" name="action_type" id="action_type"  value="<?php echo $action_type;?>">
<div class="form-group">
    <label for="signed_txid" class="col-sm-2 control-label"><?php echo getLang('已发出的存证交易编号');?>(TXID)</label>
    <div class="col-sm-10">
        <textarea class="form-control" rows=1 cols=100 name="signed_txid" id="signed_txid"></textarea>
    </div>
</div>

<input type="hidden" id="signed_tx_hex" value="">

<div class="form-group" align="center">
<div class="col-sm-offset-2 col-sm-10">
  <a class="btn btn-warning" role="button" href="#" onclick="doActionOK();return false;"><?php echo getLang('自行更新存证交易记录');?></a>
</div>
</div>

</form>

<script src="js/common_func.js"></script>
<script type="text/javascript" src="js/qrcode.js"></script>
<script type="text/javascript">
var gCointType="<?php echo $coin_type;?>";

window.onload=async function(){
     init();
}

var mPollURL;
var mIntervalPoll;

function init(){
    console.log("init...");

    //检查是否支持PPk PeerWeb钱包插件进行本地签名交易
    if(typeof(PeerWeb) !== 'undefined'){
        console.log("PeerWeb enabled");
    }else{
        console.log("PeerWeb not valid");
        document.getElementById("signed_tx_hex").value="PeerWeb extension not valid. Please visit by PPk Browser For Android v0.3.2 above.";
    }
}

function sendTX(){
    //检查是否支持PPk PeerWeb钱包插件进行本地签名交易
    if(gCointType=="<?php echo COIN_TYPE_BITCOINCASH;?>"){
        if(typeof(PeerWeb) !== 'undefined'){
            console.log("PeerWeb enabled");
            
            document.getElementById("send_trans_btn").innerHTML='Waiting';
            document.getElementById("send_trans_btn").disabled=true;
            PeerWeb.getSignedTX(
                '<?php echo COIN_TYPE_BITCOINCASH;?>',
                '<?php 
                safeEchoTextToPage( $ppkapp_tx_define_json_hex );
                ?>', 
                'callback_getSignedTX'
            );
        }else{
            //没有可用的钱包插件，提供扫码发送交易
            console.log("PeerWeb not valid");
            showQrCode();
        }
    }else{
        //不支持PPk PeerWeb钱包插件时，只能采用扫码支付
        showQrCode();
    }
}

//打开扫码支付
function showQrCode() {		
    $.ajax({
            type: "GET",
            url: "qr_pay.php?hex=<?php echo $tx_define_json_hex; ?>",
            data: {},
            success: function (result) {
                var obj_resp = JSON.parse(result);
                if (obj_resp.code == 0) {
                    //显示对应二维码
                    var tmp_qr_code=obj_resp.data.qrcode;
                    var tmp_qr_prompt=obj_resp.data.prompt;
                    mPollURL=obj_resp.data.poll_url;
                    //elText.value=confirm_url;
                    generateQrCodeImg(tmp_qr_code);
                    
                    document.getElementById("qr_loading").src="image/loading.gif";
                    document.getElementById("qr_prompt").innerText=tmp_qr_prompt;

                    //设置轮询查询该qrcode的支付状态 直到获得确认或者过期(过期这里暂没判断，待完善)
                    mIntervalPoll = setInterval(pollConfirmedTX, 30000);//查询频率按需求调整
                    pollConfirmedTX(); //立即执行一次
                }else{
                    alert("ERROR:"+result);
                }
            }
        });
}

//查询已被确认的交易ID
function pollConfirmedTX(){
    console.log("Polling tx_status ");
    $.ajax({
        type: "GET",
        url: mPollURL,
        data: {},
        success: function (result) {
            var obj_resp = JSON.parse(result);
            if (obj_resp.code == 0) {
                //停止轮询
                clearInterval(mIntervalPoll);
                //然后保存
                document.getElementById("signed_txid").value=obj_resp.data.txid;
                //alert('<?php echo getLang('交易已确认');?>');
                doActionOK();
            }
        }
    });
}


function generateQrCodeImg(str_data){
    var typeNumber = 0;
    var errorCorrectionLevel = 'L';
    var qr = qrcode(typeNumber, errorCorrectionLevel);
    qr.addData(str_data);
    qr.make();
    document.getElementById('qrcode_img').innerHTML = qr.createImgTag();
}

function callback_getSignedTX(status,obj_data){
    if('OK'==status){
        document.getElementById("signed_tx_hex").value=obj_data.signed_tx_hex;
        
        document.getElementById("send_trans_btn").innerHTML='Waiting...';
        document.getElementById("send_trans_btn").disabled=true;
        
        //调用PeerWeb接口发送已签名的比特现金交易
        PeerWeb.sendSignedTX(
                "ppk:bch/",
                document.getElementById("seller_address").value,  //交易发送者地址
                obj_data.signed_tx_hex,  //已签名的比特现金交易数据，HEX格式
                'callback_sendBchTX' //回调方法 
            );
    }else{
        document.getElementById("send_trans_btn").innerHTML='重试';
        document.getElementById("send_trans_btn").disabled=false;
        
        if('CANCELED'!=status){
            alert("生成交易失败!\n(status="+status+")\n请确认当前帐户有效余额至少有 0.00003 BCH.");
        }
    }
}


function callback_sendBchTX(status,obj_data){
    if('OK'==status){
        document.getElementById("signed_txid").value=obj_data.txid;
        alert("已发出链上存证交易!");
        doActionOK();
    }else{
        document.getElementById("send_trans_btn").innerHTML='重试';
        document.getElementById("send_trans_btn").disabled=false;
        
        alert("发送链上存证交易失败!\n(status="+status+")\n网络服务可能有异常，请稍后再试.");
    }

}

function doActionOK(){
    var signed_txid = document.getElementById("signed_txid").value;
    if(signed_txid.length>0){
        document.getElementById("bid_action_form").submit();
    }else{
        alert("请输入有效的链上存证交易编号！");
    }
}


</script>
<?php
require_once "page_footer.inc.php";
?>