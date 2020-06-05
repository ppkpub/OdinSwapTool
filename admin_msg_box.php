<?php
/* PPK JoyAsset SwapService DEMO              */
/*         PPkPub.org  20200306           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

if( $g_currentUserODIN != ADMIN_ODIN_URI ){
  Header('Location: login.php');
  exit(-1);
}

require_once "page_header.inc.php";


//过滤条件
//$user_uri = addslashes($g_currentUserODIN);
$friend_uri = \PPkPub\Util::safeReqChrStr('friend_uri');
$status_code=\PPkPub\Util::safeReqNumStr('status_code');

$str_query_reqs='friend_uri='.urlencode($friend_uri).'&status_code='.urlencode($status_code).'&';

$pagenum=10;
$start=@(0+\PPkPub\Util::safeReqNumStr('start'));

?>
<div class="table-responsive">
<p>
<?php 
echo getLang('快速查询'),': ';

$array_status_codes=array(
    PPK_ODINSWAP_MSG_STATUS_NEW=>'新消息',
    PPK_ODINSWAP_MSG_STATUS_READ=>'已读消息',
    PPK_ODINSWAP_MSG_STATUS_SENT=>'发出消息',
    PPK_ODINSWAP_MSG_STATUS_DELED=>'消息回收站'
);

foreach($array_status_codes as $tmp_status_code=>$tmp_status_label){
    if( $tmp_status_code == $status_code ) //正在显示的状态
        echo ' 	&diams;<strong>',$tmp_status_label,'</strong>&diams; | ';
    else
        echo '<a href="?status_code=',$tmp_status_code,'">',$tmp_status_label,'</a> | ';
}

?>
</p>
<table class="table table-striped">
<thead>
    <tr>
        <th><?php echo getLang('消息时间');?></th>
        <th><?php echo getLang('状态');?></th>
        <th><?php echo getLang('内容提要');?></th>
    </tr>
</thead>

<tbody>
<?php
//查询我的消息数据
$sqlstr = "SELECT * FROM private_message  WHERE 1 ";

if( strlen($friend_uri)>0)
    $sqlstr .=  " AND friend_uri='$friend_uri' ";

if(strlen($status_code)>0)
    $sqlstr .=  " AND status_code='$status_code' ";
else
    $sqlstr .=  " AND NOT status_code = ".PPK_ODINSWAP_MSG_STATUS_DELED; //默认显示除删除外的全部消息

$sqlstr .= '  ORDER BY msg_id DESC LIMIT '.$start.','.$pagenum.';';
//echo $sqlstr;

$result_num=0;

$rs = mysqli_query($g_dbLink,$sqlstr);
if (false !== $rs) {
    while ($row = mysqli_fetch_assoc($rs)) {
        echo '<tr>';
        
        echo '<td><font size="-1">',\PPkPub\Util::formatTimestampForView($row['send_utc'],false),'<br>',\PPkPub\Util::friendlyTime($row['send_utc']),'</font></td>';
        
        echo '<td>',getMsgStatusLabel($row['status_code']);
        if($row['status_code']==PPK_ODINSWAP_MSG_STATUS_NEW)
            echo '<br><font color="#F00">NEW!</font>';
        echo '</td>';
        
        echo '<td>';
        echo '<p><font size="-1">';
        echo \PPkPub\Util::safeEchoTextToPage($row['user_uri']),' ';
        if($row['message_type'] == PPK_ODINSWAP_MSG_TYPE_SYSTEM){
            echo '收到[',getLang('系统通知'),']';
        }else{
            if( $row['receiver_uri'] == $row['user_uri'] ){
                echo getLang('收到');
                $friend_uri = $row['sender_uri'];
            }else{
                echo getLang('发给');
                $friend_uri = $row['receiver_uri'];
            }
            
            echo ' ',\PPkPub\Util::safeEchoTextToPage($friend_uri);
        }
        
        echo '</font></p>';
        echo '<p>&ensp;&ensp;<font size="-1">',\PPkPub\Util::getSafeEchoTextToPage(\PPkPub\Util::friendlyLongStrUTF8($row['message_content'],80,true)),'</font><br>&ensp;&ensp;<font size="-2"><a href="admin_msg_view.php?msg_id=',$row['msg_id'],'">查看详情</a></font></p>';
        
        echo '</td>';
        
        echo '</tr>';
        
        $result_num++;
    }
}
?>
</tbody>
</table>
</div>

<center>
<p>
<?php
$page_base_url='?'.$str_query_reqs.'&start=';

if($start>=$pagenum) {//说明有上一页
    echo '<a class="btn btn-success" role="button"  href="'.$page_base_url.($start-$pagenum).'">《',getLang('上一页'),'</a> ';
}

echo " ",getLang('当前为第'),($start/$pagenum)+1,getLang('页')," ";

if($result_num==$pagenum) {//说明有下一页
    echo ' <a class="btn btn-success" role="button"  href="'.$page_base_url.($start+$pagenum).'">',getLang('下一页'),'》</a>';
}
?>
</p>
<p>

</center>
<?php
require_once "page_footer.inc.php";
?>