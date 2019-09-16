<?php
/**
 * App端扫描二维码进行BCH交易
 */
require_once "ppk_swap.inc.php";

$coin_type=COIN_TYPE_BITCOINCASH;                    
$from=removeCoinPrefix(safeReqChrStr('from'),$coin_type);
$to=removeCoinPrefix(safeReqChrStr('to'),$coin_type);
$amount_satoshi=safeReqNumStr('amount');
$memo=originalReqChrStr('memo');
$memo_hex=strToHex($memo);

if(empty($to) || empty($amount_satoshi) )
{
    echo '无效参数(to/amount). Invalid argus';
    exit(-1);
}

require_once "page_header.inc.php";

?>

<h3>扫码支付Scan&Pay <?php echo getCoinSymbol($coin_type) ,'(',$coin_type,')';  ?></h3>

<form class="form-horizontal" id="form_confirm">
<div class="form-group">
    <label for="to" class="col-sm-2 control-label">收款地址(To)</label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  id="to" name="to" value="<?php safeEchoTextToPage($to) ;?>"  >
    </div>
    
    <label for="amount_satoshi" class="col-sm-2 control-label">支付金额(Amount)</label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  id="amount" name="amount" value="<?php safeEchoTextToPage(getNormalAmount($coin_type,$amount_satoshi)) ;?>"  >
      <input type="hidden" id="amount_satoshi" name="amount_satoshi" value="<?php safeEchoTextToPage($amount_satoshi) ;?>"  >
    </div>
    
    <label for="memo" class="col-sm-2 control-label">备注(Memo)</label>
    <div class="col-sm-10">
      <textarea class="form-control" rows=3 id="memo" ><?php safeEchoTextToPage($memo) ;?></textarea>
    </div>
    
    <label for="from" class="col-sm-2 control-label">付出地址(From)</label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  id="from" name="from" value="<?php safeEchoTextToPage($from) ;?>"  >
    </div>
</div>
  
<p align="center"><input type='button' class="btn btn-success"  id="btn_confirm_pay" value=' 确认支付 PayNow ' onclick='confirmPay();' disabled="true"></p>
</form>


<script src="js/common_func.js"></script>
<script type="text/javascript">

var MIN_FEE_SATOSHI = 1000;  //给矿工的费用,单位satoshi，即0.00000001 BCH

var mObjUserInfo;

window.onload=function(){
    init();
}

function init(){
    console.log("init...");
    if(typeof(PeerWeb) !== 'undefined'){ //检查PPk开放协议相关PeerWeb JS接口可用性
        console.log("PeerWeb enabled");
        
        document.getElementById("btn_confirm_pay").disabled=false;
    }else{
        console.log("PeerWeb not valid");
        //alert("PeerWeb not valid. Please visit by PPk Browser For Android v0.2.6 above.");
        document.getElementById("btn_confirm_pay").value="请使用PPk浏览器安卓版APP来扫码支付！\nPlease use PPk Android APP to scan&pay.";
    }
}

//确认转账发送
function confirmPay(){
    //var coin_type_uri=document.getElementById("coin_type_uri");
    
    var coin_type_uri='<?php echo $coin_type; ?>';
    
    if(coin_type_uri!='<?php echo COIN_TYPE_BITCOINCASH; ?>'){
        alert('暂时不支持的资产币种：'.coin_type_uri);
        return ;
    }
    
    var from=document.getElementById("from").value.trim();
    if(from.length ==0 ){
        alert('该资产地址尚未设置，请先创建或导入一个该资产对应的币种地址！');
        return;
    }
    
    var to=document.getElementById("to").value.trim();
    if(to.length ==0 ){
        alert('未设置收款地址！');
        return;
    }
    
    if( to.substr(0,1)!='1' ){
        alert("收款的比特现金公开地址需以1起始！");
        return;
    }
    

    var send_amount_satoshi=document.getElementById("amount_satoshi").value.trim();
    if(isNaN(send_amount_satoshi)){
        alert("转账金额数值无效！");
        return;
    }
    
    var str_data_hex='<?php echo $memo_hex;?>';
    
    var tx_argus_json='{"source":"'+from             //交易发送者地址
                     +'","destination":"'+to                 //交易接收者地址
                     +'","amount_satoshi":'+send_amount_satoshi //转账金额,单位satoshi
                     +',"fee_satoshi":'+MIN_FEE_SATOSHI    //给矿工的费用,单位satoshi
                     +',"data_hex":"'+str_data_hex+'"}';
    
    document.getElementById("btn_confirm_pay").disabled=true;
    document.getElementById("btn_confirm_pay").value="正在处理，请稍候...";

    PeerWeb.getSignedTX(
        coin_type_uri,
        stringToHex(tx_argus_json),  //待生成交易的参数数据
        'callback_getExtAssetSignedTX'
      );

}

function callback_getExtAssetSignedTX(status,obj_data){
    if('OK'==status){
        var coin_type_uri='<?php echo $coin_type; ?>';
    
        if(coin_type_uri!='<?php echo COIN_TYPE_BITCOINCASH; ?>'){
            alert('暂时不支持的资产币种：'.coin_type_uri);
            return ;
        }
        
        document.getElementById("memo").value = obj_data.signed_tx_hex;
        
        var from=document.getElementById("from").value.trim();

        //调用PeerWeb接口发送已签名的比特币交易
        PeerWeb.sendSignedTX(
                coin_type_uri,
                from,  //交易发送者地址
                obj_data.signed_tx_hex,  //已签名的交易数据，HEX格式
                'callback_sendExtAssetTX' //回调方法 
            );
    }else{
        if('CANCELED'!=status){
            alert("生成比特现金的转账交易失败!\n(status="+status+")\n请确认当前的地址有足够余额.\nPlease ensure the address has enough balance.");
        }
        document.getElementById("btn_confirm_pay").disabled=false;
        document.getElementById("btn_confirm_pay").value="重新发送 Retry";
    }
}

function callback_sendExtAssetTX(status,obj_data){
    if('OK'==status){
        alert("已发出比特现金转账交易!\nTXID:"+obj_data.txid);
        document.getElementById("btn_confirm_pay").disabled=true;
        document.getElementById("btn_confirm_pay").value="交易已发送 Paid OK";
    }else{
        alert("发送比特现金失败!\n(status="+status+")\n网络服务可能有异常，请稍后再试.\nFailed!Please retry later.");
        document.getElementById("btn_confirm_pay").disabled=false;
        document.getElementById("btn_confirm_pay").value="重新发送 Retry";
    }
}

//兼容DID的用户标识处理，得到以ppk:起始的URI
function getUserPPkURI(user_uri){ 
    if(user_uri.substring(0,"did:ppk:".length).toLowerCase()=="did:ppk:" ) { 
        user_uri=user_uri.substring("did:".length);
    }
    return user_uri;
}

//Ascii/Unicode字符串转换成16进制表示
function stringToHex(str){
    var val="";
    for(var i = 0; i < str.length; i++){
        var tmpstr=str.charCodeAt(i).toString(16);  //Unicode
        val += tmpstr.length==1? '0'+tmpstr : tmpstr;  
    }
    return val;
}
</script>