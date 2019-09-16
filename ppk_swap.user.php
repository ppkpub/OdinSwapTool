<?php
/*      PPK JoyAsset SwapService DEMO         */
/*         PPkPub.org  20180925           */  
/*    Released under the MIT License.     */

require_once('common_func.php');

session_start();

$g_logonUserInfo=getLogonUserInfo();
if($g_logonUserInfo!=null){
  $g_currentUserODIN=$g_logonUserInfo["user_uri"];
  //$g_currentUserName=$g_logonUserInfo["name"];
  $g_currentUserLevel=$g_logonUserInfo["level"];
  //$g_currentUserAvtar=$_SESSION["swap_user_avtar_url"];
}else{
  $g_currentUserODIN='';
  //$g_currentUserName='';
  $g_currentUserLevel=0;
}

//$g_currentUserODIN=DID_URI_PREFIX.JOYDID_PPK_URI_PREFIX.'alice#'; //tm1qzymnxuzlt6e8sjf4vc0ct6f6vkk25y27dtzdwe
//$g_currentUserName='TesterAlice';

//$g_currentUserODIN=DID_URI_PREFIX.JOYDID_PPK_URI_PREFIX.'bob#'; //tm1q8sarfnju2gyft56hh38w8n0s8xwq4tcsfaeqq8
//$g_currentUserName='测试Bob';

$g_cachedUserInfos=array();

//获取当前登录用户信息
function getLogonUserInfo(){
    global $g_dbLink;
    
    //echo 'seesion_id=[',session_id(),']';
    $qruuid=session_id();
    $sql = "select * from qrcodelogin where qruuid='" . $qruuid . "'";
    //echo $sql;
    $rs = mysqli_query($g_dbLink,$sql);
    if (!$rs) {
      return null;  
    }
    $row = mysqli_fetch_assoc($rs);

    if (empty($row['user_odin_uri']))
        return null;
        
    return array(
            'user_uri' => $row["user_odin_uri"],
            'name' => $row['user_odin_uri'],  //待完善用户名称
            'level' => $row['status_code']
        );
}

//撤消当前登录用户信息
function unsetLogonUser(){
    global $g_dbLink;
    $qruuid=session_id();
    $sql = "delete from qrcodelogin where qruuid='" . $qruuid . "'";
    //echo $sql;
    mysqli_query($g_dbLink,$sql);
}
