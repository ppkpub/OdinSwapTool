<br>
<p align=center>
<?php 
if(strlen($g_currentUserODIN)>0)
{
    echo '<a href="user.php"><img src="image/user.png" width=16 height=16>',getLang('我的帐号'),'[',\PPkPub\Util::getSafeEchoTextToPage(\PPkPub\Util::friendlyLongID($g_currentUserODIN)),']</a>';
    echo '&nbsp&nbsp&nbsp&nbsp<a href="my_msg_box.php">',getLang('消息');
    $new_msg_counter = getMsgCounter($g_currentUserODIN,PPK_ODINSWAP_MSG_STATUS_NEW);
    if($new_msg_counter>0){
        echo '(<font color="#F00" size="-2">',$new_msg_counter,'</font>)';
    }
    echo '</a>';

} else { 
    echo '<a href="login.php">',getLang('以奥丁号登录'),'</a>&emsp;|&emsp;<a href="https://www.chainnode.com/post/386612">',getLang('注册奥丁号'),'</a>';
}   

?>
</p>

<center>
<p><a href="sell_list.php"><?php echo getLang('查看竞拍');?></a>&emsp;|&emsp;
<?php 
if($g_currentUserLevel==2){
    echo '<li><a href="user.php">',getLang('发布拍卖'),'</a></li>&emsp;|&emsp;';
} ?>
<a href="want_list.php"><?php echo getLang('查看求购');?></a>&emsp;|&emsp;<a href="new_want.php"><?php echo getLang('我要求购');?></a></p>
<p><a href="new_msg.php"><?php echo getLang('留言建议');?></a>&emsp;|&emsp;<a href="help.html"><?php echo getLang('帮助');?></a></p>
</center>
<h3 align="center"><font size="-2" color="#000"><?php echo IS_DEMO ? getLang('演示环境，仅供测试体验，不能用作实际资产交易！！！') : getLang('风险提示：此公测工具仅提供自主、点对点互换奥丁号(ODIN)的信息聚合展示，不提供居中担保和裁判规则，无法保证交易双方的真实意愿和实际行为，请自主决定、自担风险。');?><br><a href="help.html"><?php echo getLang('请阅读帮助详细了解，谨慎操作。');?></a></p></font></h3>

<div class="container-fluid footer">
PPkPub Swap Toolkit for ODIN 0.8.0523 &copy; 2019-2020. Released under the <a href="http://opensource.org/licenses/mit-license.php">MIT License</a>.
</div>

</body>
</html>

