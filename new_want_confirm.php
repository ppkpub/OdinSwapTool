<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20200221           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

if(strlen($g_currentUserODIN)==0){
  Header('Location: login.php');
  exit(-1);
}
/*
if($g_currentUserLevel<2){
  error_exit('./', '该奥丁号帐户只能参与报价！<br>需设置有效身份验证密钥并通过验证后才能发起求购。<br>This account only can bid.');
}
*/

//在本地数据库保存拍卖纪录
$coin_type=safeReqChrStr('coin_type');
$offer_amount=0+safeReqNumStr('offer_amount');
$want_names=safeReqChrStr('want_names');
$remark=safeReqChrStr('remark');
$bid_hours=safeReqNumStr('bid_hours');
$pub_utc=time();
$start_utc=$pub_utc; //缺省是发布即开始

$end_utc = ($bid_hours>0) ? $start_utc + $bid_hours*60*60  : PPK_ODINSWAP_LONGTIME_UTC ;

$sql_str="insert into wants (wanter_uri,want_names, remark, coin_type, offer_amount, status_code, start_utc,end_utc,pub_utc) values ('$g_currentUserODIN','$want_names','$remark','$coin_type','$offer_amount','".PPK_ODINSWAP_STATUS_WANT."','$start_utc','$end_utc','$pub_utc')";
//echo $sql_str;
$result=@mysqli_query($g_dbLink,$sql_str);
if(!$result)
{
    echo '无效参数. Invalid argus';
    exit(-1);
}
$new_want_rec_id=mysqli_insert_id($g_dbLink);

require_once "page_header.inc.php";
?>
<p><?php echo getLang('求购信息已发布。');?><br><a href="want.php?want_rec_id=<?php echo $new_want_rec_id;?>"><?php echo getLang('点击这里查看');?></a></p>
<?php
require_once "page_footer.inc.php";
?>