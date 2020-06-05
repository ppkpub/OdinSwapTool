<?php
/**
 * 用于前端页轮询 查询指定交易是否被确认
 */
require_once "ppk_swap.inc.php";

$hex=\PPkPub\Util::safeReqChrStr('hex');

if(strlen($hex)==0){
    $arr = array('code'=> 1,  'msg' => 'invalid TX hex' );
    echo json_encode($arr);
    exit(-1);               
}

$array_tx_define=@json_decode(@\PPkPub\Util::hexToStr($hex),true);
//print_r($array_tx_define);
$asset_uri=$array_tx_define['asset_uri'];

$arr = array('code' => 1, 'msg' => 'Not supported asset_uri: '.$asset_uri);

foreach($gArraySupportedCoinTypeList as $tmp_coin_type){
  if(\PPkPub\Util::startsWith($asset_uri,$tmp_coin_type)){
      $tmp_ppk_uri = $tmp_coin_type.'txOfQrCode('.$hex.')';
      //echo "tmp_ppk_uri=$tmp_ppk_uri\n";
      $tmp_data=\PPkPub\PTTP::getPPkResource($tmp_ppk_uri);
      //print_r($tmp_data);
      if($tmp_data['status_code']==200){
          $tmp_tx_info=@json_decode($tmp_data['content'],true);
          $arr = array('code' => 0, 'msg' => 'Found confirmed transaction','data'=>$tmp_tx_info);
          echo json_encode($arr);
          exit(0);
      }else{
          $arr = array('code'=> 2, 'msg' => 'invalid ap response or pending confirmed');
      }
      
      break;
  }
}

echo json_encode($arr);
