<?php
//Crobtab:update the coin metadatas
require_once "ppk_swap.inc.php";

//Get the supported coin prices
$new_array_coin_set=$gArrayCoinTypeSet;

foreach($gArraySupportedCoinTypeList as $tmp_coin_type){
    $query_ppk_uri=$tmp_coin_type.'metadata()';
    $tmp_data= \PPkPub\PTTP::getPPkResource($query_ppk_uri);
    //print_r($tmp_data);
    if($tmp_data['status_code']==200){
        $tmp_array=@json_decode($tmp_data['content'],true);
        if( array_key_exists('name', $tmp_array) ) {
            $new_array_coin_set[$tmp_coin_type] = $tmp_array;
        }
    }
}

$data_file_name='config/coin_info.json';

file_put_contents($data_file_name,json_encode($new_array_coin_set));

echo $data_file_name," updated.";