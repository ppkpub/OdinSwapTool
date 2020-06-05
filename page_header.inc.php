<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo getLang('奥丁号拍卖交换工具');?><?php if(IS_DEMO) echo getLang('[演示]'); ?></title>
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://netdna.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<nav class="navbar navbar-default" role="navigation">
<div class="container-fluid">
  <!-- Brand and toggle get grouped for better mobile display -->
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-navbar-collapse-1">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
    <a class="navbar-brand logo" href="./"><?php echo getLang('拍卖&交换');?></a>
  </div>

  <!-- Collect the nav links, forms, and other content for toggling -->
  <div class="collapse navbar-collapse" id="bs-navbar-collapse-1">
    <ul class="nav navbar-nav">
        <li><a href="sell_list.php"><?php echo getLang('查看竞拍');?></a></li>
        <?php 
        if($g_currentUserLevel==2){
            echo '<li><a href="user.php">',getLang('发布拍卖'),'</a></li>';
        } ?>
        <li><a href="want_list.php"><?php echo getLang('查看求购');?></a></li>
        <li><a href="new_want.php"><?php echo getLang('我要求购');?></a></li>

        <?php 
        if(strlen($g_currentUserODIN)>0){
            echo '<li><a href="user.php"><img src="image/user.png" width=16 height=16>',getLang('我的帐号'),'[',\PPkPub\Util::getSafeEchoTextToPage(\PPkPub\Util::friendlyLongID($g_currentUserODIN)),']</a></li>';
            echo '<li><a href="my_msg_box.php">',getLang('消息');
            $new_msg_counter = getMsgCounter($g_currentUserODIN,PPK_ODINSWAP_MSG_STATUS_NEW);
            if($new_msg_counter>0){
                echo '(<font color="#F00" size="-2">',$new_msg_counter,'</font>)';
            }
            echo '</a></li>';
        } else { 
            echo '<li><a href="login.php">',getLang('以奥丁号登录'),'</a></li> <li><a href="https://www.chainnode.com/post/386612">',getLang('注册奥丁号'),'</a></li>';
        } ?>
        <li><a href="help.html"><?php echo getLang('帮助');?></a></li>
        <li><a href="lang_change.php"><?php echo getLang('English');?></a></li>
    </ul>
  </div>
</div>
</nav>
