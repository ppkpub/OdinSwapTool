<?php
/*      PPK JoyAsset SwapService          */
/*         PPkPub.org  20200221           */  
/*    Released under the MIT License.     */
require_once('ppk_common_define.php');
require_once('ppk_common_function.php');

//获取用户指定币种的钱包地址URI
function getCoinAddressURI($coin_type,$owner_uri){
    $tmp_address = bindedAddress($coin_type,$owner_uri);
    if($tmp_address!=null){
        global $gArrayCoinTypeSet;
        if(array_key_exists($coin_type,$gArrayCoinTypeSet)){
            $tmp_coin_set = $gArrayCoinTypeSet[$coin_type];
            if(array_key_exists('base_coin_uri',$tmp_coin_set)){
                return $tmp_coin_set['base_coin_uri'].$tmp_address; //使用基础币种URI作为前缀
            }
        }
        return $coin_type.$tmp_address;
    }
    
    //如果未登记附加地址，则尝试使用该ODIN标识注册者的默认钱包地址
    $tmp_owner_info=getPubUserInfo($owner_uri);
    if(startsWith($tmp_owner_info['register'],$coin_type)){
        return $tmp_owner_info['register'];
    }else if($coin_type==COIN_TYPE_BITCOINCASH){
        if(startsWith($tmp_owner_info['register'],COIN_TYPE_BITCOIN)){
            return COIN_TYPE_BITCOINCASH.removeCoinPrefix($tmp_owner_info['register'],COIN_TYPE_BITCOIN);
        }
    }else if($coin_type==COIN_TYPE_BITCOIN){
        if(startsWith($tmp_owner_info['register'],COIN_TYPE_BITCOIN)){
            return $tmp_owner_info['register'];
        }
    }
        
    return "";
}   

//按用户BTC地址获取所拥有的ODIN根标识列表
function  getUserOwnedRootODINs($user_btc_address,$start=0,$limit=100){
    $odin_list=array();
    
    if( startsWith($user_btc_address,'bitcoin:')){
        $user_btc_address=substr($user_btc_address,8);
    }

    $ppk_url='http://tool.ppkpub.org/ppkapi/query.php?address='.$user_btc_address.'&start='.$start.'&limit='.$limit;
    $tmp_ppk_resp_str=file_get_contents($ppk_url);
    //echo '$ppk_url=',$ppk_url,',$tmp_ppk_resp=',$tmp_ppk_resp_str;
    $tmp_obj_resp=@json_decode($tmp_ppk_resp_str,true);
    if($tmp_obj_resp['status']=='OK'){
        $odin_list=$tmp_obj_resp['list'];
    }
    
    return $odin_list;
}

//获取拍卖交易状态码对应文字名称
function getStatusLabel($status_code){
    $tmp_status_str=null;
    switch($status_code){
        case PPK_ODINSWAP_STATUS_BID:
            $tmp_status_str = '报价中';
            break;
        case PPK_ODINSWAP_STATUS_ACCEPT:
            $tmp_status_str = '达成意向';
            break;
        case PPK_ODINSWAP_STATUS_PAID:
            $tmp_status_str = '已付款';
            break;
        case PPK_ODINSWAP_STATUS_TRANSFER:
            $tmp_status_str = '拍卖方已发出过户';
            break;
        case PPK_ODINSWAP_STATUS_CANCEL:
            $tmp_status_str = '交易取消';
            break;
        case PPK_ODINSWAP_STATUS_EXPIRED:
            $tmp_status_str = '到期确拍中';
            break;
        case PPK_ODINSWAP_STATUS_NONE:
            $tmp_status_str = '到期流拍';
            break;
        case PPK_ODINSWAP_STATUS_UNCONFIRM:
            $tmp_status_str = '等待确拍超时而流拍';
            break;
        case PPK_ODINSWAP_STATUS_UNPAID:
            $tmp_status_str = '等待支付超时而流拍';
            break;
        case PPK_ODINSWAP_STATUS_FINISH:
            $tmp_status_str = '已完成';
            break;
        case PPK_ODINSWAP_STATUS_LOSE:
            $tmp_status_str = '未中标';
            break;
        case PPK_ODINSWAP_STATUS_WANT:
            $tmp_status_str = '求购中';
            break;
        case PPK_ODINSWAP_STATUS_CLOSED:
            $tmp_status_str = '已结束';
            break;
    }
    
    if($tmp_status_str!=null)
        return getLang($tmp_status_str);
    else
        return getLang('未知').'['.$status_code.']';
} 


//自动更新已到期的拍卖纪录状态
function autoUpdateExpiredSells(){ 
    Global $g_dbLink;
    $nowtime=time();
    
    //更新到期但有效参拍的状态
    $sql_str="update sells,bids set sells.status_code='".PPK_ODINSWAP_STATUS_EXPIRED."',update_utc='".time()."' where sells.end_utc<=".$nowtime." and sells.status_code=".PPK_ODINSWAP_STATUS_BID." and sells.sell_rec_id=bids.sell_rec_id and bids.status_code=".PPK_ODINSWAP_STATUS_BID." ;";
    //echo $sql_str;
    $result=@mysqli_query($g_dbLink,$sql_str);
    
    //更新剩下的到期但无有效参拍的记录状态
    $sql_str="update sells set status_code='".PPK_ODINSWAP_STATUS_NONE."' where end_utc<=".$nowtime." and status_code=".PPK_ODINSWAP_STATUS_BID.";";
    $result=@mysqli_query($g_dbLink,$sql_str);
    
    //更新已达成意向的其它未中标的记录状态
    $sql_str="update bids set status_code='".PPK_ODINSWAP_STATUS_LOSE."' where 	status_code='".PPK_ODINSWAP_STATUS_BID."' and sell_rec_id	in (select sell_rec_id from sells where status_code=".PPK_ODINSWAP_STATUS_ACCEPT.");";
    //echo $sql_str;
    $result=@mysqli_query($g_dbLink,$sql_str);
    
    //更新未及时确拍达成意向的报价状态
    $sql_str="update sells set sells.status_code='".PPK_ODINSWAP_STATUS_UNCONFIRM."' where sells.end_utc<=".($nowtime-PPK_ODINSWAP_OVEETIME_SECONDS)." and sells.status_code=".PPK_ODINSWAP_STATUS_EXPIRED."  ;";
    //echo $sql_str;
    $result=@mysqli_query($g_dbLink,$sql_str);
    
    //更新未及时付款的报价状态
    $sql_str="update sells set sells.status_code='".PPK_ODINSWAP_STATUS_UNPAID."' where sells.accepted_utc<=".($nowtime-PPK_ODINSWAP_OVEETIME_SECONDS)." and sells.status_code=".PPK_ODINSWAP_STATUS_ACCEPT."  ;";
    //echo $sql_str;
    $result=@mysqli_query($g_dbLink,$sql_str);
    
    //删除到期流拍的已过期7天以上记录
    $sql_str="delete from sells where status_code='".PPK_ODINSWAP_STATUS_NONE."' and end_utc<=".($nowtime-60*60*24*7).";";
    $result=@mysqli_query($g_dbLink,$sql_str);
    
    //删除等待确拍超时流拍的已过期15天以上记录
    $sql_str="delete from sells where status_code='".PPK_ODINSWAP_STATUS_UNCONFIRM."' and end_utc<=".($nowtime-60*60*24*15).";";
    $result=@mysqli_query($g_dbLink,$sql_str);
    
    //更新到期的求购状态
    $sql_str="update wants set wants.status_code='".PPK_ODINSWAP_STATUS_CLOSED."',update_utc='".time()."' where wants.end_utc<=".$nowtime." and wants.status_code=".PPK_ODINSWAP_STATUS_WANT." ;";
    //echo $sql_str;
    $result=@mysqli_query($g_dbLink,$sql_str);
}

//获取奥丁号配置管理权限对应文字名称
function getOdinAuthSetLabel($set_code){
    switch($set_code){
        case 0:
            return getLang('注册者或管理者任一方都可以修改配置');
        case 1:
            return getLang('只有管理者能修改配置');
        case 2:
            return getLang('注册者和管理者必须共同确认才能修改配置');
        default:
            return getLang('无效设置').'['.$set_code.']';
    }
}

//构建包含报价确认信息的数据对象
function genAcceptBidArray( $source_owner_odin,$source_address_uri, $dest_owner_odin,$dest_address_uri, $asset_id,$full_odin_uri,$coin_type,$bid_amount,$service_uri ){
  global $gArrayCoinTypeSet;
  //组织交易信息数据块
  $str_coin_symbol=getCoinSymbol($coin_type);
  //if($str_coin_symbol!=$coin_type){
  //    $str_coin_symbol = $str_coin_symbol . '('.$coin_type.')';
  //}
  
  $str_data = PPK_ODINSWAP_FLAG  
      .":accepted to sell ODIN[" .$asset_id
      ."] to (".$dest_owner_odin
      .") for ". trimz($bid_amount) 
      ." " . $str_coin_symbol;    

  $tmp_array=array(
    'from_uri' => $source_address_uri,
    'to_uri' => $dest_address_uri,
    'asset_uri' => $coin_type,
    'amount_satoshi' => $gArrayCoinTypeSet[$coin_type]['min_transfer_amount'],
    'fee_satoshi' => $gArrayCoinTypeSet[$coin_type]['base_miner_fee'],
    'data' => $str_data,
    'data_size' => strlen($str_data), //for test
  );

  return $tmp_array;
}

//构建包含支付报价信息的数据对象
function genPayBidArray( $source_owner_odin,$source_address_uri, $dest_owner_odin,$dest_address_uri, $asset_id,$full_odin_uri,$coin_type,$bid_amount,$service_uri ){
  global $gArrayCoinTypeSet;
  
  //组织交易信息数据块
  $str_coin_symbol=getCoinSymbol($coin_type);
  
  $str_data = PPK_ODINSWAP_FLAG  
      .": paid " . trimz($bid_amount) 
      ." ". $str_coin_symbol
      ." to (".$dest_owner_odin
      .") for ODIN[". $asset_id 
      ."]";  
  
  $amount_satoshi = round($bid_amount*pow(10,$gArrayCoinTypeSet[$coin_type]['decimals']));
  
  $tmp_array=array(
    'from_uri' => $source_address_uri,
    'to_uri' => $dest_address_uri,
    'asset_uri' => $coin_type,
    'amount_satoshi' => $amount_satoshi,
    'fee_satoshi' => $gArrayCoinTypeSet[$coin_type]['base_miner_fee'],
    'data' => $str_data, 
    'data_size' => strlen($str_data), //for test
  );
  
  return $tmp_array;
}

