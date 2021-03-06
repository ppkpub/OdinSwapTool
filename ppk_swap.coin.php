<?php
//导入币种基础信息
$gArrayCoinTypeSet=@json_decode(file_get_contents('config/coin_info.json'),true);

//导入币种参考价格（单位：人民币）
$gArrayCoinPriceCNY=@json_decode(file_get_contents('config/coin_price_cny.json'),true);

//获得指定类型货币数额的参考金额（单位：人民币元）
function getCoinValueOfCNY($amount,$coin_type){
    global $gArrayCoinPriceCNY;
    if(array_key_exists($coin_type,$gArrayCoinPriceCNY)){
        $tmp_val=ceil($amount*$gArrayCoinPriceCNY[$coin_type]*10000);
        if($tmp_val<100){
            $tmp_val=$tmp_val/10000;
        }else if($tmp_val<10000){
            $tmp_val=ceil($tmp_val/100)/100;
        }else{
            $tmp_val=ceil($tmp_val/10000);
        }
        return $tmp_val;
    }else
        return 0;
}

//获得指定类型货币的代码，如BTC,BTM等
function getCoinSymbol($coin_type){
    global $gArrayCoinTypeSet;
    if(array_key_exists($coin_type,$gArrayCoinTypeSet))
        return $gArrayCoinTypeSet[$coin_type]['symbol'];
    else
        return $coin_type;
}

//获得指定类型货币的名称，如Bitcoin/比特币,Bytom/比原链等
function getCoinName($coin_type,$lang_code=null){
    global $gArrayCoinTypeSet;
    if(array_key_exists($coin_type,$gArrayCoinTypeSet)){
        global $g_currentLang;
        $tmp_name_index = 'name_'.(  $lang_code==null ? $g_currentLang : $lang_code );
        
        if(array_key_exists($tmp_name_index,$gArrayCoinTypeSet[$coin_type]))
            return $gArrayCoinTypeSet[$coin_type][ $tmp_name_index ];
        else
            return $gArrayCoinTypeSet[$coin_type]['name'];
    }else
        return $coin_type;
}

//从最小整数单位（类似satoshi）转换获得指定类型货币的一般单位的金额值（允许带最多8位小数）
function getNormalAmount($coin_type,$amount_as_satoshi){
    global $gArrayCoinTypeSet;
    if(array_key_exists($coin_type,$gArrayCoinTypeSet))
        return number_format($amount_as_satoshi/pow(10,$gArrayCoinTypeSet[$coin_type]['decimals']),8,'.','');
    else
        return null;
}


//调用币种接口查询指定用户标识已关联的该币种钱包地址
function bindedAddress($coin_uri,$owner_odin_uri){
    
    if( \PPkPub\Util::startsWith($coin_uri,\PPkPub\PTAP02ASSET::COIN_TYPE_MOV) 
        && \PPkPub\Util::startsWith($owner_odin_uri,\PPkPub\PTAP02ASSET::COIN_TYPE_MOV))
        return \PPkPub\PTAP02ASSET::removeCoinPrefix($owner_odin_uri,\PPkPub\PTAP02ASSET::COIN_TYPE_MOV);
    else if( \PPkPub\Util::startsWith($coin_uri,\PPkPub\PTAP02ASSET::COIN_TYPE_BYTOM) 
             && \PPkPub\Util::startsWith($owner_odin_uri,\PPkPub\PTAP02ASSET::COIN_TYPE_BYTOM))
        return \PPkPub\PTAP02ASSET::removeCoinPrefix($owner_odin_uri,\PPkPub\PTAP02ASSET::COIN_TYPE_BYTOM);
    
    $tmp_json_hex = \PPkPub\Util::strToHex($owner_odin_uri);
    $query_ppk_uri=$coin_uri.'bindedAddress('.$tmp_json_hex.')';
    $tmp_data=\PPkPub\PTTP::getPPkResource($query_ppk_uri);
    //echo "ppk_swap.coin.php bindedAddress($coin_uri,$owner_odin_uri):  \n";print_r($tmp_data);
    if($tmp_data['status_code']==200){
        $tmp_array=json_decode($tmp_data['content'],true);
        return @$tmp_array['address'];
    }else{
        return null;
    }
}

/*
更新旧数据库
update  sells set COIN_TYPE='bitcoin' where   COIN_TYPE='BITCOIN';
update  sells set COIN_TYPE='ppk:joy/btm/' where   COIN_TYPE='ppk:btm/';

update  bids set COIN_TYPE='bitcoin' where   COIN_TYPE='BITCOIN';
update  bids set COIN_TYPE='ppk:joy/btm/' where   COIN_TYPE='ppk:btm/';

*/