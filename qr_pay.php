<?php
/**
 * 用于前端获取支付交易用二维码
 */
require_once "ppk_swap.inc.php";

$hex=safeReqChrStr('hex');

if(strlen($hex)==0){
    $arr = array('code'=> 1,  'msg' => 'invalid TX hex' );
    
    echo json_encode($arr);
    exit(-1);   
}

$array_tx_define=@json_decode(@hexToStr($hex),true);
//print_r($array_tx_define);
$asset_uri=$array_tx_define['asset_uri'];

$arr = array('code' => 1, 'msg' => 'Not supported asset_uri: '.$asset_uri);

foreach($gArraySupportedCoinTypeList as $tmp_coin_type){
  if( $asset_uri == $tmp_coin_type ){
      $tmp_ppk_uri = $tmp_coin_type.'qrCodeOfPay('.$hex.')#1.0';
      $tmp_data=getPPkResource($tmp_ppk_uri);
      
      if($tmp_data['status_code']==200){
        $tmp_qr_info=@json_decode($tmp_data['content'],true);
        
        //print_r($tmp_qr_info);
        if($tmp_qr_info!=null && array_key_exists('qrcode',$tmp_qr_info) ){ 
            global $g_currentLang;
            $tmp_prompt_str= ( $g_currentLang=='cn') ?  $tmp_qr_info['prompt_cn'] :  $tmp_qr_info['prompt'];
            $arr = array('code'=> 0, 
                     'msg' => 'qrcode get ok',
                     'data'=> array(
                        'qrcode'=>$tmp_qr_info['qrcode'],
                        'prompt'=>$tmp_prompt_str,
                        //'ppk-uri'=>@$tmp_data['uri'],
                        //'ppk-content'=>@$tmp_data['content'],
                        'poll_url'=>'qr_pay_txid.php?hex='.$hex,
                     )
                    );
        }else{
            $arr = array('code'=> 3, 'msg' => 'invalid ap content');
        }
      }else{
        $arr = array('code'=> 2, 'msg' => 'invalid ap response');
      }
      
      break;
  }
}

echo json_encode($arr);
