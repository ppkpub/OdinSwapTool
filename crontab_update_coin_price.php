<?php
//Crobtab:update the coin prices
require_once "ppk_swap.inc.php";

//Get base bitcoin/usd price
$base_btc_price_cny = getCoinValueOfCNY(1,\PPkPub\PTAP02ASSET::COIN_TYPE_BITCOIN);

//Get the supported coin prices
$new_coin_price_cny_list=array(
    \PPkPub\PTAP02ASSET::COIN_TYPE_BITCOIN => $base_btc_price_cny
);


foreach($gArraySupportedCoinTypeList as $tmp_coin_type){
    $query_ppk_uri=$tmp_coin_type.'marketPrice()';
    $tmp_data=\PPkPub\PTTP::getPPkResource($query_ppk_uri);
    //print_r($tmp_data);
    if($tmp_data['status_code']==200){
        $tmp_array=json_decode($tmp_data['content'],true);
        $new_coin_price_cny_list[$tmp_coin_type] = number_format($tmp_array['bitcoin'] * $base_btc_price_cny,8,'.','');
    }else{
        $new_coin_price_cny_list[$tmp_coin_type] = $gArrayCoinPriceCNY[$tmp_coin_type];
    }
}

$data_file_name='config/coin_price_cny.json';

file_put_contents($data_file_name,json_encode($new_coin_price_cny_list));

echo $data_file_name," updated.";