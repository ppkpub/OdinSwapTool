<?php
/*        PPK ODIN Swap Toolkit           */
/*         PPkPub.org  20200306           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

$msg_id=\PPkPub\Util::safeReqNumStr('msg_id');

if(strlen($msg_id)==0){
  echo 'Invalid message ID.';
  exit(-1);
}

$sqlstr = "SELECT * FROM private_message where msg_id='$msg_id' and user_uri='".addslashes($g_currentUserODIN)."' ;";
$rs = mysqli_query($g_dbLink,$sqlstr);
if (!$rs) {
  echo 'DB failed.';
  exit(-1);  
}
$tmp_msg_record = mysqli_fetch_assoc($rs);
if (!$tmp_msg_record) {
  echo 'Not existed message record.';
  exit(-1);  
}

$bCurrentUserIsOwner = ($g_currentUserODIN==$tmp_msg_record['user_uri']) ? true:false;
$bCurrentUserIsReceiver = ($g_currentUserODIN==$tmp_msg_record['receiver_uri']) ? true:false;

if( $bCurrentUserIsOwner && $tmp_msg_record['status_code'] == PPK_ODINSWAP_MSG_STATUS_NEW ){
    updateMsgStatus($tmp_msg_record['msg_id'],PPK_ODINSWAP_MSG_STATUS_READ);
    $tmp_msg_record['status_code']=PPK_ODINSWAP_MSG_STATUS_READ;
}     

require_once "page_header.inc.php";
?>
<div class="row section">
  <div class="form-group">
    <label for="top_buttons" class="col-sm-5 control-label"><h3><?php echo getLang($bCurrentUserIsReceiver?'收到的消息详情':'发出的消息详情');?></h3></label>
    <div class="col-sm-7" id="top_buttons" align="right">
    </div>
  </div>
</div>
 
<form class="form-horizontal" action="update_want_confirm.php" method="post">
  <input type="hidden" name="form" value="new_want">
  <input type="hidden" name="msg_id" value="<?php echo $msg_id;?>">

  <div class="form-group">
    <label for="sender_uri" class="col-sm-2 control-label"><?php echo getLang($bCurrentUserIsReceiver?'来自':'发给');?></label>
    <div class="col-sm-10">
      <span id="sender_uri"><?php 
        if($tmp_msg_record['message_type'] == PPK_ODINSWAP_MSG_TYPE_SYSTEM){
            echo '<a href="my_msg_box.php?friend_uri=',urlencode($tmp_msg_record['friend_uri']),'">',getLang('系统通知'),'</a>';
        }else{
            if( $bCurrentUserIsReceiver ){
                $friend_uri = $tmp_msg_record['sender_uri'];
            }else{
                $friend_uri = $tmp_msg_record['receiver_uri'];
            }
            
            echo '<a href="my_msg_box.php?friend_uri=',urlencode($friend_uri),'">',\PPkPub\Util::safeEchoTextToPage($friend_uri),'</a>';
        }?></span>
    </div>
  </div>    

  <div class="form-group">
    <label for="end_utc" class="col-sm-2 control-label"><?php echo getLang('时间');?></label>
    <div class="col-sm-10">
      <span id="end_utc"><?php 
      echo \PPkPub\Util::formatTimestampForView($tmp_msg_record['send_utc'],false),' , ' , \PPkPub\Util::friendlyTime($tmp_msg_record['send_utc']); 
      ?> </span>
    </div>
  </div>  
  
  <div class="form-group">
    <label for="message_content" class="col-sm-2 control-label"><?php echo getLang('内容');?></label>
    <div class="col-sm-10">
     <?php 
     if($tmp_msg_record['message_type'] == PPK_ODINSWAP_MSG_TYPE_SYSTEM){
         echo $tmp_msg_record['message_content'] ; //系统消息允许显示HTML格式原文
     }else{
         echo '<textarea class="form-control" name="message_content" id="message_content" readonly rows=10 >',\PPkPub\Util::getSafeEchoTextToPage( $tmp_msg_record['message_content'] ),'</textarea>';
     }
     ?>
    </div>
  </div>
  
  <div class="form-group">
    <label for="status_code" class="col-sm-2 control-label"><?php echo getLang('状态');?></label>
    <div class="col-sm-10">
      <span id="status_code"><?php echo getMsgStatusLabel($tmp_msg_record['status_code']);?></span>
    </div>
  </div>   
  
  <?php if($bCurrentUserIsOwner){ ?>
  <div class="form-group" align="center">
    <div class="col-sm-offset-2 col-sm-10">
      <?php
         if($tmp_msg_record['message_type'] != PPK_ODINSWAP_MSG_TYPE_SYSTEM){
             if($bCurrentUserIsReceiver)
                 echo '<a class="btn btn-primary" role="button" href="new_msg.php?receiver_odin_uri=',urlencode($tmp_msg_record['sender_uri']),'">',getLang(' 回复消息 '),'</a>';
             else
                 echo '<a class="btn btn-primary" role="button" href="new_msg.php?receiver_odin_uri=',urlencode($tmp_msg_record['receiver_uri']),'">',getLang(' 发新消息给他 '),'</a>';
         }
         echo '&ensp;&ensp;&ensp;&ensp;<a class="btn btn-danger" role="button" href="my_msg_delete.php?msg_id=',$msg_id,'">',getLang(' 删 除 '),'</a>';
      ?>
    </div>
  </div>
  <?php } ?>
 
</form>

<?php 

require_once "page_footer.inc.php";
?>