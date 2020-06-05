<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20190892           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

$owner_odin_uri=\PPkPub\Util::safeReqChrStr('owner_odin_uri');
$coin_type=\PPkPub\Util::safeReqChrStr('coin_type');

//调用币种接口查询
$tmp_address = bindedAddress($coin_type,$owner_odin_uri);
if($tmp_address==null){
    $tmp_array=array('code'=>1,'msg'=>'No result for coin_uri='.$coin_type.' owner_uri='.$owner_odin_uri);
}else{
    $tmp_array=array('code'=>0,'coin_uri'=>$coin_type,'owner_uri'=>$owner_odin_uri,'address'=>$tmp_address);
}
echo json_encode($tmp_array);