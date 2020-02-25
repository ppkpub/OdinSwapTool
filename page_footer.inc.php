
<p align=center>
<?php if(strlen($g_currentUserODIN)>0){echo '<a href="user.php"><img src="image/user.png" width=16 height=16>',getLang('我的帐号'),'[',getSafeEchoTextToPage($g_currentUserODIN),']</a>';} else { echo '<a href="login.php">',getLang('以奥丁号登录'),'</a><!--　|　<a href="new_user.php">',getLang('注册新用户'),'</a>-->';}   ?>
</p>

<p align=center><a href="./"><?php echo getLang('查看竞拍');?></a>　|　<a href="user.php"><?php echo getLang('发布拍卖');?></a>　|　<a href="want_list.php"><?php echo getLang('查看求购');?></a>　|　<a href="new_want.php"><?php echo getLang('我要求购');?></a>　|　<a href="help.html"><?php echo getLang('帮助');?></a></p>

<h3 align="center"><font size="-2" color="#000"><?php echo getLang('风险提示：此公测工具仅提供自主、点对点互换奥丁号(ODIN)的信息聚合展示，不提供居中担保和裁判规则，无法保证交易双方的真实意愿和实际行为，请自主决定、自担风险。');?><a href="help.html"><?php echo getLang('请阅读帮助详细了解，谨慎操作。');?></a></p></font></h3>

<div class="container-fluid footer">
PPkPub Swap Toolkit for ODIN 0.7.0223 copy; 2019-2020. Released under the <a href="http://opensource.org/licenses/mit-license.php">MIT License</a>.
</div>

</body>
</html>

