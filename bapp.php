<?php
require_once "ppk_swap.inc.php";

$scan_qruuid=\PPkPub\Util::safeReqChrStr('qruuid');

$qruuid= empty($scan_qruuid) ? generateSessionSafeUUID() : $scan_qruuid ; 

$bapp_address=\PPkPub\Util::safeReqChrStr('address');

if(strlen($bapp_address)>0 ){
    $odin_prefix='';
    if(\PPkPub\Util::startsWith($bapp_address,'v'))
        $odin_prefix = \PPkPub\PTAP02ASSET::COIN_TYPE_MOV ;
    else if(\PPkPub\Util::startsWith($bapp_address,'b'))
        $odin_prefix = \PPkPub\PTAP02ASSET::COIN_TYPE_BYTOM ;
    else{
        \PPkPub\Util::error_exit('./', "Invalid address : ",$bapp_address);
    }
    
    $user_odin_uri = $odin_prefix . $bapp_address . \PPkPub\ODIN::PPK_URI_RESOURCE_MARK;
    //$user_odin_uri = \PPkPub\ODIN::formatPPkURI($user_odin_uri,true);

    $user_loginlevel=1;
    
    //保存登录状态
    $sql = "REPLACE INTO qrcodelogin (qruuid,user_odin_uri,user_sign,status_code) values ('$qruuid','$user_odin_uri','bapp',$user_loginlevel)";
    $result = @mysqli_query($g_dbLink,$sql);
    
    if( !empty($scan_qruuid) ){ //扫码登录其它设备
        echo '<html xmlns="http://www.w3.org/1999/xhtml"><head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODIN verified OK</title>
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://netdna.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>';
        echo '<center><br><br><h3>扫码验证奥丁号通过<br>ODIN verified OK</h3><br><P><font color="#FF7026">'.\PPkPub\Util::getSafeEchoTextToPage($user_odin_uri).'</font><br><br>请回到所登录设备或网站上继续访问。<br>Please go back the device or page to continue. </p></center>';
        echo "<p align=center><br><input type=button value=' << 返回 '  name=B1 onclick='history.back(-1)'></p>";
    }else{ //本机直接登录
        header("location: ./");
    }
    exit(0);
}

require_once "page_header.inc.php";
?>

<br>
<br>
<br>
<center>
<h3>奥丁号拍卖交换工具(PPkSwapTool Bapp)</h3>
<br>
<p>当前比原钱包的MOV侧链地址:<br><span id="current_mov_address"></span></p>
<br>
<input type='button' class="btn btn-primary" onclick="loginMOV( );" value="使用上述侧链地址登录" >
</center>

<br>
<br>
<p>
提示:<br>
此Bapp应用是将PPk开放协议和比原链结合体现的示例，请安装使用比原Bycoin钱包应用来体验，就可以用比原MOV侧链地址直接登录奥丁号拍卖应用，使用MOV-USDT等数字加密货币轻松完成支付。<br>
下载链接: <a href="https://blockmeta.com/wallet/">https://blockmeta.com/wallet/</a><br>
使用说明: <a href="https://www.chainnode.com/post/287660">https://www.chainnode.com/post/287660</a><br>
</p>

  
<script>
  document.addEventListener('chromeBytomLoaded', bytomExtension => {
      window.bytom.enable().then(accounts => {
        //init();
        myalert("Bytom enabled");
      });
      
      init();
  });
  
  function init(){
      window.bytom.setChain('vapor').then(function (resp) {
        if(resp.status=="success"){
            currentAddress = window.bytom.defaultAccount.address;
            
            document.getElementById("current_mov_address").innerText=currentAddress;

        }else{
            myalert(resp.status);
        }
      }).catch(function (err){
        myalert(err)
      })
  }
  
  
  function checkSigned(){
    currentAddress = window.bytom.defaultAccount.address;
    
    //判断是否已签名登录过
    var str_signed_info = getLocalConfigData('signed_address_'+currentAddress);
    var signed_info = JSON.parse(str_signed_info);
    if( signed_info!=null && currentAddress == signed_info.address ){
        window.location.href= "bapp.php?address="+signed_info.address+"&message="+signed_info.message+"&signature="+signed_info.signature;
        return;
    }
  }
  
  function myalert(obj){
    alert(JSON.stringify(obj));
  }
  
  function loginMOV( ){
    currentAddress = document.getElementById("current_mov_address").innerText;

    if(currentAddress.length == 0 ){
        alert("没有可用的钱包地址！\n请使用Bycoin钱包应用并授权访问账户信息再试下。");       
        return;
    }
    
    window.location.href= "bapp.php?address="+currentAddress;
  }
  
  /*
  //生成签名后登录
  function signAndLoginMOV( ){
    currentAddress = document.getElementById("currentAddress").innerText;
    
    checkSigned();

    originalMessage = 'ODINSWAP,'+currentAddress+',<?php echo $qruuid;?>';
    //myalert(originalMessage);
    var params = {
      address:currentAddress,
      message:originalMessage
    }

    window.bytom.signMessage(params).then(function (resp) {
        signed_info = {
              address:currentAddress,
              message:originalMessage,
              signature:resp.signature
            }
        myalert(signed_info);
        
        saveLocalConfigData('signed_address_'+currentAddress,JSON.stringify(signed_info));

        window.location.href= "bapp.php?address="+signed_info.address+"&message="+signed_info.message+"&signature="+signed_info.signature;
      }).catch(function (err){
        alert(err)
      })
  }
  */
  function getLocalConfigData(key){
    if(typeof(Storage)!=="undefined")
    {
        // 是的! 支持 localStorage  sessionStorage 对象!
        return localStorage.getItem(key);
    } else {
        // 抱歉! 不支持 web 存储。
        return null;
    }
  }

  function saveLocalConfigData(key,value){
    if(typeof(Storage)!=="undefined")
    {
        // 是的! 支持 localStorage  sessionStorage 对象!
        return localStorage.setItem(key,value);
    } else {
        // 抱歉! 不支持 web 存储。
        return false;
    }
  }

</script>
    
<script>
/*
window.onload=function(){
    init();
}

function init(){
  console.log("init...");
  if( !!window.bytom ){
     window.bytom.enable().then(accounts => {
            alert(bytom.defaultAccount)
          }
     )
  }else{
    window.location.href= "./";
  }
}
*/

/*
  mBoolBytomEnabled=false;
  
  document.addEventListener('chromeBytomLoaded', bytomExtension => {
      window.bytom.enable().then(accounts => {
        //window.accounts = accounts
        mBoolBytomEnabled=true;
        //alert(window.bytom.defaultAccount);
        window.bycoin.callAPI('native.toastInfo', JSON.stringify(window.bytom.defaultAccount));
      })
  });
  if( !!window.bycoin ){
     window.bytom.enable().then(accounts => {
            alert(bytom.defaultAccount)
          }
     )
  }else{
    window.location.href= "./";
  }
*/
</script>

</body> 