<?php
/*      PPK ODIN Swap Toolkit         */
/*         PPkPub.org  20200306           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

$receiver_odin_uri=\PPkPub\Util::safeReqChrStr('receiver_odin_uri');
if( strlen($receiver_odin_uri)==0 ){
  //不指定发送对象时是默认发给系统维护员
  $receiver_odin_uri = ADMIN_ODIN_URI;
}

if( !\PPkPub\Util::startsWith( $receiver_odin_uri ,\PPkPub\ODIN::PPK_URI_PREFIX  ) ){
  \PPkPub\Util::error_exit('./', 'Invalid receiver ODIN URI!');
  exit;
}


if(strlen($g_currentUserODIN)==0){
  Header( 'Location: login.php?backpage=new_msg&receiver_odin_uri='.urlencode($receiver_odin_uri) );
  exit(-1);
}

/*
if($g_currentUserLevel<2){
  \PPkPub\Util::error_exit('./','该奥丁号帐户只能参与报价！<br>需设置有效身份验证密钥并通过验证后才能发布求购信息。<br>This account only can bid.');
}
*/
require_once "page_header.inc.php";
?>
<div class="row section">
  <div class="form-group">
    <label for="top_buttons" class="col-sm-5 control-label"><h3><?php echo getLang('发送站内私信');?></h3></label>
    <div class="col-sm-7" id="top_buttons" align="right">
    </div>
  </div>
</div>

<form class="form-horizontal" action="new_msg_confirm.php" method="post">
  <input type="hidden" name="form" value="new_msg">
  <input type="hidden" name="receiver_odin_uri" value="<?php \PPkPub\Util::safeEchoTextToPage( $receiver_odin_uri );?>">

  <div class="form-group">
    <label for="receiver_odin" class="col-sm-2 control-label"><?php echo getLang('发送给');?></label>
    <div class="col-sm-10">
      <span id="receiver_odin"><?php \PPkPub\Util::safeEchoTextToPage( $receiver_odin_uri );?></span>
    </div>
  </div> 
  
  <div class="form-group">
    <label for="message_content" class="col-sm-2 control-label"><?php echo getLang('消息正文');?></label>
    <div class="col-sm-10">
     <textarea class="form-control" name="message_content" id="message_content" rows=10 placeholder="<?php echo getLang('在这里输入消息内容（500字以内）');?>" ></textarea>
    </div>
  </div>
  
  
  <div class="form-group" align="center">
    <div class="col-sm-offset-2 col-sm-10">
      <button class="btn btn-success btn-lg" type="submit" ><?php echo getLang('马上发送');?></button>
    </div>
  </div>

</form>

<?php
require_once "page_footer.inc.php";
?>