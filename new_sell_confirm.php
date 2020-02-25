<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20190415           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

if(strlen($g_currentUserODIN)==0){
  Header('Location: login.php');
  exit(-1);
}

if($g_currentUserLevel<2){
  error_exit('./', '该奥丁号帐户只能参与报价！<br>需设置有效身份验证密钥并通过验证后才能发起拍卖。<br>This account only can bid.');
}

$from_want_rec_id=safeReqNumStr('from_want_rec_id');

$asset_id=safeReqChrStr('asset_id');
if(strlen($asset_id)==0){
  error_exit('./', '无效的奥丁号. Invalid ODIN.');
}

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

//检查所拍卖资产标识有效性
$full_odin_uri=PPK_URI_PREFIX.$asset_id.PPK_URI_RES_FLAG;

$tmp_data=getPPkResource($full_odin_uri);
if($tmp_data['status_code']!=200){
  echo '获取标识资源信息出错. Failed to get ODIN data.';
  exit(-1);
}
$full_odin_uri=$tmp_data['uri'];
$tmp_odin_info=@json_decode($tmp_data['content'],true);

//检查有权拍卖该标识
$tmp_user_info=getPubUserInfo($g_currentUserODIN);

if($tmp_user_info['register']!= $tmp_odin_info['register'] && $tmp_user_info['register']!= 'bitcoin:'.$tmp_odin_info['register']){
  echo '当前用户',$tmp_user_info['register'],'无权拍卖不属于自己的数字资产. Unable to sell ODIN belong to others(',$tmp_odin_info['register'],').';
  exit(-1);
}


//检查是否已存在重复拍卖记录
//待加

//在本地数据库保存拍卖纪录
$coin_type=safeReqChrStr('coin_type');
$start_amount=0+safeReqNumStr('start_amount');
$recommend_names=safeReqChrStr('recommend_names');
$remark=safeReqChrStr('remark');
$bid_hours=safeReqNumStr('bid_hours');
$pub_utc=time();
$start_utc=$pub_utc; //缺省是发布即开始

$end_utc = ($bid_hours>0) ? $start_utc + $bid_hours*60*60  : PPK_ODINSWAP_LONGTIME_UTC ;

$sql_str="insert into sells (seller_uri,full_odin_uri,asset_id ,recommend_names, remark, coin_type, start_amount, status_code, start_utc,end_utc,pub_utc,from_want_rec_id) values ('$g_currentUserODIN','$full_odin_uri','$asset_id','$recommend_names','$remark','$coin_type','$start_amount','".PPK_ODINSWAP_STATUS_BID."','$start_utc','$end_utc','$pub_utc',".(strlen($from_want_rec_id)>0 ? $from_want_rec_id:"null").")";
//echo $sql_str;
$result=@mysqli_query($g_dbLink,$sql_str);
if(!$result)
{
    echo '无效参数. Invalid argus';
    exit(-1);
}
$new_sell_rec_id=mysqli_insert_id($g_dbLink);

require_once "page_header.inc.php";
?>
<p><?php echo getLang('对应奥丁号');?>[<?php echo $asset_id;?>]<?php echo getLang('的拍卖信息已发布。');?><br><a href="sell.php?sell_rec_id=<?php echo $new_sell_rec_id;?>"><?php echo getLang('点击这里查看');?></a></p>
<?php
require_once "page_footer.inc.php";
?>