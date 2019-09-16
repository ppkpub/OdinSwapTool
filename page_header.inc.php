<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo getLang('奥丁号拍卖交换工具');?></title>
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
        <li><a href="./"><?php echo getLang('查看竞拍');?></a></li>
        <li><a href="user.php"><?php echo getLang('发布拍卖');?></a></li>
        <!--<li><a href="buy_list.php">求购资产</a></li>-->
        <?php if(strlen($g_currentUserODIN)>0){echo '<li><a href="user.php"><img src="image/user.png" width=16 height=16>',getLang('我的帐号'),'[',getSafeEchoTextToPage(friendlyLongID($g_currentUserODIN)),']</a>';} else { echo '<li><a href="login.php">',getLang('以奥丁号登录'),'</a></li><!--<li><a href="new_user.php">',getLang('注册新用户'),'</a></li>-->';}   ?>
        <li><a href="help.html"><?php echo getLang('帮助');?></a></a></li>
        <li><a href="lang_change.php"><?php echo getLang('English');?></a></a></li>
    </ul>
  </div>
</div>
</nav>
