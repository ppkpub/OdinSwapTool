<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20200319           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

if(strlen($g_currentUserODIN)==0){
  Header('Location: login.php');
  exit(-1);
}

$coin_type=\PPkPub\Util::originalReqChrStr('coin_type');
/*
$sqlstr = "select status_code,count(*) as counter from bids where  bidder_uri='".addslashes($g_currentUserODIN)."' group by status_code order by status_code;";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while($row = mysqli_fetch_assoc($rs)){
        $tmp_user_bid_stat['total'] += $row['counter'];
        $tmp_user_bid_stat['status_stat'][$row['status_code']] = $row['counter'];
    }
}
//print_r($tmp_user_bid_stat);
*/

$binded_address = bindedAddress($coin_type,$g_currentUserODIN);

global $gArrayCoinTypeSet;
$coin_info = $gArrayCoinTypeSet[$coin_type];
//print_r($coin_info);

$str_pttp_api_url = defined('PTTP_NODE_API_URL') ? 
                    PTTP_NODE_API_URL : 'https://tool.ppkpub.org/ppkapi/';

    
require_once "page_header.inc.php";
?>

<h3>登记奥丁号对应资产 <?php echo $coin_info['symbol'],'(',$coin_type,')';?> 的钱包地址</h3>

<form class="form-horizontal">
<div class="form-group">
    <label for="exist_odin_uri" class="col-sm-2 control-label">你的奥丁号</label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  id="exist_odin_uri" value="<?php echo $g_currentUserODIN;?>"  readonly >
    </div>
</div>

<div class="form-group">
    <label for="binded_address" class="col-sm-2 control-label">已关联的钱包地址</label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  id="binded_address" value="<?php echo $binded_address;?>" readonly >
    </div>
</div>

<div class="form-group">
    <label for="new_address" class="col-sm-2 control-label">关联新的钱包地址</label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  id="new_address" value="" placeholder="请输入对应该资产的有效钱包地址" >
    </div>
</div>
  
<div class="form-group">
    <label for="use_exist_odin" class="col-sm-2 control-label"></label>
    <div class="col-sm-10" align="center">
      <input type='button' class="btn btn-success"  id="use_exist_odin" value=' 检查环境插件... ' onclick='checkInputs();' disabled="true">
      <br><br>
      <div id="qrcode_img" ></div><br>
      <!--<font size="-2">（注：需升级到PPkBrowser安卓版0.305以上版本，<a href="https://ppkpub.github.io/docs/help_odin_as_did/">请点击阅读这里的操作说明安装和使用</a>。更多信息，<a href="https://ppkpub.github.io/docs/" target="_blank">可以参考奥丁号和PPk开放协议的资料进一步了解</a>。）</font>-->
    </div>
</div>

</form>
<!--
<div class="form-group">
    <label for="debug_data" class="col-sm-2 control-label">DEBUG调试数据</label>
    <div class="col-sm-10">
      <textarea class="form-control"  id="debug_data" rows=5 readonly></textarea>
    </div>
</div>
-->
</body>

<script type="text/javascript" src="js/common_func.js"></script>
<script type="text/javascript" src="js/qrcode.js"></script>
<script type="text/javascript">
var mCoinUri="<?php echo $coin_type;?>"; //币种标识

var mbIsTestnet=<?php echo $coin_info['testnet']?'true':'false';?>;
var mbSupportPeerWebPlugin=false;

var mTempData;
var mTempDataHex;

window.onload=function(){
    init();
}

function init(){
    console.log("init...");
    
    if(mbIsTestnet){
        console.log("Current asset is on testnet.");
        setSubmitButton("确认提交 ( 注意是测试网络的地址！)",false);
    }else if(typeof(PeerWeb) !== 'undefined'){ //检查PPk开放协议相关PeerWeb JS接口可用性
        console.log("PeerWeb enabled");
        mbSupportPeerWebPlugin=true;
        setSubmitButton("签名验证",false);
    }else{
        console.log("PeerWeb not valid");
        mbSupportPeerWebPlugin=false;
        setSubmitButton("使用支持奥丁号的APP来扫码签名验证",false);
    }
}

function checkInputs(){
    var owner_odin_uri=getUserPPkURI(document.getElementById("exist_odin_uri").value);
    var new_address=document.getElementById("new_address").value.trim();
    
    if(new_address.length==0){
        alert("请输入有效的绑定钱包地址");
        return;
    }
    
    var tmp_obj={"owner_uri":owner_odin_uri,"coin_uri":mCoinUri,"address":new_address,"timestamp":getNowTimeStampTmp()};
    mTempData = JSON.stringify(tmp_obj);//需要签名的原文
    //alert('mTempData:'+mTempData);
    mTempDataHex = stringToHex(mTempData);
    
    if(mbIsTestnet){
        updateOwnerAddressByAjax(owner_odin_uri,mTempData,"test");
    }else if(mbSupportPeerWebPlugin){
        makeConfirm(owner_odin_uri,new_address);
    }else{
        makeQrCode(owner_odin_uri,new_address);
    }
}

function parseJsonObjFromAjaxResult(result){
    return typeof result == 'string ' ?  
                                JSON.parse( result ) : result;
}



function updateOwnerAddressByAjax(owner_uri,original,sign_data){
    var tmp_obj={"original":original,"sign":sign_data};
    
    var tmp_json_str=JSON.stringify(tmp_obj);
    var tmp_json_hex = stringToHex(tmp_json_str);
    var update_uri=mCoinUri+'bindAddress('+tmp_json_hex+')';
    //alert('tmp_json_str='+tmp_json_str);
    //document.getElementById("debug_data").value=update_uri;
    
    var pttp_service_url='<?php echo $str_pttp_api_url;?>?pttp_uri='+encodeURIComponent(update_uri);
    console.log("pttp_service_url="+pttp_service_url);

    console.log("Calling PTTP service by AJAX...");
    setSubmitButton("正在通过PPk网络提交，请稍候...",true);
    $.ajax({
        type: "GET",
        url: pttp_service_url,
        data: {},
        success : function (result) {
            var obj_resp = parseJsonObjFromAjaxResult(result);
            
            if(obj_resp==null){
                setSubmitButton("出错了，请重试",false);
                return;
            }
                                
            obj_payment = JSON.parse( obj_resp.data );
            
            if(obj_payment.status_code==200){
                obj_content = JSON.parse(obj_payment.content);
                resp_address = obj_content.address;
                console.log("resp_address="+resp_address);
                
                document.getElementById("binded_address").value=resp_address;
                document.getElementById("new_address").value="已成功更新";
                setSubmitButton("已成功更新",true);
            }else{
                alert("出错了，请重试！\n"+obj_payment.status_detail);
                setSubmitButton("出错了，请重试",false);
            }
        },
        error:function(xhr,state,errorThrown){
            setSubmitButton("出错了，请重试",false);
        }
    });
    
}
/*
function updateOwnerAddressByAjax(status,obj_data){
    if('OK'==status){
        try{
            //document.getElementById("debug_data").value="status_code="+obj_data.status_code+"\ntype="+obj_data.type+" \nlength="+obj_data.length+"\nservice_url="+obj_data.url;
            
            if(obj_data.status_code!=200){
                showErrorMag("登记更新出错(status_code:"+obj_data.status_code+")！\n请稍后再试");
                return;
            }
            
            var content=window.atob(obj_data.content_base64);
            //var content=obj_data.content_base64;
            //document.getElementById("debug_data").value="type="+obj_data.type+" \nlength="+obj_data.length+"\ncontent="+content+"\nservice_url="+obj_data.url;
            //var obj_content=JSON.parse(content);
            //alert("obj_content.address="+obj_content.address);
            setSubmitButton("已成功提交",true);
            
            window.location.href=document.referrer;
            
        }catch(e){
            showErrorMag("获得的应答消息有误!\n"+e);
        }
    }else{
        showErrorMag("登记更新出错！\n请稍后再试");
    }
}
*/

//打开扫码签名验证
function makeQrCode(owner_odin_uri,new_address) {	
    var confirm_url="<?php echo APP_BASE_URL;?>qr_sign.php?owner_odin_uri="+encodeURIComponent(owner_odin_uri)+"&hex="+mTempDataHex;
    //document.getElementById("debug_data").value=confirm_url;
    generateQrCodeImg(confirm_url);
    
    //轮询 查询地址更新状态 直到更新成功
    var poll_url="get_binded_address.php?owner_odin_uri="+encodeURIComponent(owner_odin_uri)+"&coin_type="+encodeURIComponent(mCoinUri);
    console.log("poll_url="+poll_url);
    var interval1= setInterval(function () {
        console.log("Polling address update status...");
        $.ajax({
            type: "GET",
            url: poll_url,
            data: {},
            success: function (result) {
                var obj_resp = JSON.parse(result);
                console.log("obj_resp.address="+obj_resp.address);
                if (obj_resp.code == 0 && obj_resp.address==new_address) {
                    document.getElementById("binded_address").value=new_address;
                    document.getElementById("new_address").value="已成功更新";
                    //停止轮询
                    clearInterval(interval1);
                    
                    //alert("已成功更新,将继续上一个操作");
                    window.location.href=document.referrer;
                }
            }
        });
    }, 5000);//5秒钟  频率按需求
}

function getNowTimeStampTmp(){
    var timestamp1 = Date.parse( new Date());
    return timestamp1/1000;
}

function generateQrCodeImg(str_data){
    var typeNumber = 0;
    var errorCorrectionLevel = 'L';
    var qr = qrcode(typeNumber, errorCorrectionLevel);
    qr.addData(str_data);
    qr.make();
    document.getElementById('qrcode_img').innerHTML = qr.createImgTag();
}

//兼容DID的用户标识处理，得到以ppk:起始的URI
function getUserPPkURI(user_uri){ 
    if(user_uri.substring(0,"did:ppk:".length).toLowerCase()=="did:ppk:" ) { 
        user_uri=user_uri.substring("did:".length);
    }
    return user_uri;
}
/*
//通过PeerWeb插件检查已绑定地址
function checkBindedAddress(){
    console.log("Polling address update status...");
    
    var exist_odin_uri=getUserPPkURI(document.getElementById("exist_odin_uri").value);

    var tmp_json_hex = stringToHex(exist_odin_uri);
    var query_uri=mCoinUri+'bindedAddress('+tmp_json_hex+')';
    //alert('query_uri='+query_uri);
    //document.getElementById("debug_data").value=query_uri;
    
    //读取用户身份标识URI对应说明
    PeerWeb.getPPkResource(
        query_uri,
        'content',
        'callback_getBindedAddress'  //回调方法名称
    );
}

function callback_getBindedAddress(status,obj_data){
    if('OK'==status){
        try{
            //document.getElementById("debug_data").value="status_code="+obj_data.status_code+"\ntype="+obj_data.type+" \nlength="+obj_data.length+"\nservice_url="+obj_data.url;
            
            if(obj_data.status_code!=200){
                //alert("未能获得已关联地址信息(status_code:"+obj_data.status_code+")！\n请稍后再试");
                return;
            }
            
            var content=window.atob(obj_data.content_base64);
            //var content=obj_data.content_base64;
            //document.getElementById("debug_data").value="type="+obj_data.type+" \nlength="+obj_data.length+"\ncontent="+content+"\nservice_url="+obj_data.url;
            var obj_content=JSON.parse(content);
            //alert("obj_content.address="+obj_content.address);
            document.getElementById("binded_address").value=obj_content.address;
            
            if(obj_content.address==document.getElementById("new_address").value.trim()){
                setSubmitButton("已成功更新",true);
            }
        }catch(e){
            console.log("获得的已关联地址信息有误!\n"+e);
        }
    }else{
        console.log("未能获得已关联地址信息！\n请稍后再试");
    }
}
*/
function makeConfirm(owner_odin_uri,new_address){
    var requester_uri=window.location.href;

    setSubmitButton("正在处理，请稍后...",true);
    
    //请求用指定资源密钥来生成签名
    PeerWeb.signWithPPkResourcePrvKey(
        owner_odin_uri,
        requester_uri ,
        mTempDataHex,
        'callback_signWithPPkResourcePrvKey'  //回调方法名称
    );
}


function callback_signWithPPkResourcePrvKey(status,obj_data){
    try{
        if('OK'==status){
            var user_uri=document.getElementById("exist_odin_uri").value;
            //alert("res_uri="+obj_data.res_uri+" \nsign="+obj_data.sign+" \algo="+obj_data.algo);
            var signed_data=obj_data.algo+":"+obj_data.sign;
            updateOwnerAddressByPlugin(user_uri,mTempData,signed_data);
        }else{
            showErrorMag("无法签名指定资源！\n请检查确认该资源已配置有效的验证密钥.");
        }
    }catch(e){
        showErrorMag("获得的签名信息有误!\n"+e);
    }
}

function updateOwnerAddressByPlugin(owner_uri,original,sign_data){
    var tmp_obj={"original":original,"sign":sign_data};
    
    var tmp_json_str=JSON.stringify(tmp_obj);
    var tmp_json_hex = stringToHex(tmp_json_str);
    var update_uri=mCoinUri+'bindAddress('+tmp_json_hex+')';
    //alert('tmp_json_str='+tmp_json_str);
    //document.getElementById("debug_data").value=update_uri;
    
    
    //调用API更新登记地址
    PeerWeb.getPPkResource(
        update_uri,
        'content',
        'callback_updateOwnerAddressByPlugin'  //回调方法名称
    );
}

function callback_updateOwnerAddressByPlugin(status,obj_data){
    if('OK'==status){
        try{
            //document.getElementById("debug_data").value="status_code="+obj_data.status_code+"\ntype="+obj_data.type+" \nlength="+obj_data.length+"\nservice_url="+obj_data.url;
            
            if(obj_data.status_code!=200){
                showErrorMag("登记更新出错(status_code:"+obj_data.status_code+")！\n请稍后再试");
                return;
            }
            
            var content=window.atob(obj_data.content_base64);
            //var content=obj_data.content_base64;
            //document.getElementById("debug_data").value="type="+obj_data.type+" \nlength="+obj_data.length+"\ncontent="+content+"\nservice_url="+obj_data.url;
            //var obj_content=JSON.parse(content);
            //alert("obj_content.address="+obj_content.address);
            document.getElementById("use_exist_odin").value="已成功提交";
            
            window.location.href=document.referrer;
            
        }catch(e){
            showErrorMag("获得的应答消息有误!\n"+e);
        }
    }else{
        showErrorMag("登记更新出错！\n请稍后再试");
    }
}

function showErrorMag(msg){
    alert(msg);
    setSubmitButton("重新提交",false);
}

function setSubmitButton(str_label,b_disabled){
    document.getElementById("use_exist_odin").value=str_label;
    document.getElementById("use_exist_odin").disabled=b_disabled;
}

</script>
</html>
<?php
require_once "page_footer.inc.php";
?>