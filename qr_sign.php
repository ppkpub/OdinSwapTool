<?php
/**
 * App端扫描二维码生成签名等信息传入
 */
 
require_once "ppk_swap.inc.php";

$owner_odin_uri=safeReqChrStr('owner_odin_uri');
$hex=safeReqChrStr('hex');
$owner_sign=safeReqChrStr('owner_sign');
$response_type=safeReqChrStr('response_type');

if(empty($owner_odin_uri) || empty($hex) )
{
    echo '无效参数. Invalid argus';
    exit(-1);
}

$str_original= hexToStr($hex);
if( !empty($owner_odin_uri) &&!empty($hex)  && !empty($owner_sign))
{  
    $tmp_array=array(
        'original'=>hexToStr($hex),
        'sign'=>$owner_sign,
    );
    $tmp_json_hex = strToHex(json_encode($tmp_array));
    
    $tmp_array=json_decode($str_original,true);
    $update_ppk_uri=$tmp_array['coin_uri'].'bindAddress('.$tmp_json_hex.')#';
    //echo '$update_ppk_uri=',$update_ppk_uri,"<br>\n";
    
    $tmp_data=getPPkResource($update_ppk_uri);
    //print_r($tmp_data);
    if($tmp_data['status_code']==200){
        if($response_type=='html'){
            echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/></head>';
            echo '扫码签名确认通过，请回到所登录设备上继续访问。<br>Verified ok as ',getSafeEchoTextToPage($owner_odin_uri);
        }else{
            $arr = array('code' => 0, 'msg' => '扫码签名确认通过，请回到所登录设备上继续访问。owner_sign verified ok as '.$owner_odin_uri);
            echo json_encode($arr);
        }
    }else{
        if($response_type=='html'){
            echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/></head>';
            echo '调用API接口出错。Call API failed.',"\n",$tmp_data['status_detail'];
        }else{
            $arr = array('code' => $tmp_data['status_code'], 'msg' => '调用API接口出错。Call API failed. '.$tmp_data['status_detail']);
            echo json_encode($arr);
        }
    }

    
    exit(0);
}

require_once "page_header.inc.php";

?>

<h3>扫码确认签名</h3>

<form class="form-horizontal"  action="qr_sign.php" method="get" id="form_confirm">
<input type="hidden" name="hex" id="auth_txt_hex" value="<?php safeEchoTextToPage($hex) ;?>">
<input type="hidden" name="owner_sign" id="owner_sign" value="">
<input type="hidden" name="response_type" value="html">

<div class="form-group">
    <label for="exist_odin_uri" class="col-sm-2 control-label">签名者身份(Owner)</label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  id="exist_odin_uri" name="owner_odin_uri" value="<?php safeEchoTextToPage($owner_odin_uri) ;?>"  onchange="getUserOdinInfo();"  >
    </div>
    
    <label for="exist_odin_uri" class="col-sm-2 control-label">待签名内容(Original)</label>
    <div class="col-sm-10">
      <textarea class="form-control" rows=3 ><?php safeEchoTextToPage($str_original) ;?></textarea>
    </div>
</div>
  
<p align="center"><input type='button' class="btn btn-success"  id="btn_confirm_sign" value=' 确 认 签 名 ' onclick='confirmSign();' disabled="true"></p>
</form>


<script src="js/common_func.js"></script>
<script type="text/javascript">
var mObjUserInfo;
var mObjUserPubKey;
var mTempDataHex;

window.onload=function(){
    init();
}

function init(){
    console.log("init...");
    if(typeof(PeerWeb) !== 'undefined'){ //检查PPk开放协议相关PeerWeb JS接口可用性
        console.log("PeerWeb enabled");
        
        document.getElementById("btn_confirm_sign").disabled=false;
    }else{
        console.log("PeerWeb not valid");
        //alert("PeerWeb not valid. Please visit by PPk Browser For Android v0.2.6 above.");
        document.getElementById("btn_confirm_sign").value="请使用PPk浏览器安卓版APP来扫码签名！";
    }
}

function confirmSign(){
    document.getElementById("btn_confirm_sign").value="请稍候...";
    
    var exist_odin_uri=getUserPPkURI(document.getElementById("exist_odin_uri").value);
    var requester_uri=window.location.href;
    var auth_txt_hex=document.getElementById("auth_txt_hex").value;  //需要签名的原文
    
    //请求用指定资源密钥来生成签名
    PeerWeb.signWithPPkResourcePrvKey(
        exist_odin_uri,
        requester_uri ,
        auth_txt_hex,
        'callback_signWithPPkResourcePrvKey'  //回调方法名称
    );
}

function callback_signWithPPkResourcePrvKey(status,obj_data){
    try{
        if('OK'==status){
        
            //alert("res_uri="+obj_data.res_uri+" \nsign="+obj_data.sign+" \algo="+obj_data.algo);
            
            document.getElementById("owner_sign").value=obj_data.algo+":"+obj_data.sign;
        
            document.getElementById("form_confirm").submit();
        }else{
            document.getElementById("btn_confirm_sign").value=" 重 试 ";
            
            alert("无法签名指定资源！\n请检查确认该资源已配置有效的验证密钥.");
        }
    }catch(e){
        document.getElementById("btn_confirm_sign").value=" 重 试 ";
    }
}

//兼容DID的用户标识处理，得到以ppk:起始的URI
function getUserPPkURI(user_uri){ 
    if(user_uri.substring(0,"did:ppk:".length).toLowerCase()=="did:ppk:" ) { 
        user_uri=user_uri.substring("did:".length);
    }
    return user_uri;
}
</script>