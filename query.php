<?php
/*      PPK JoyAseet Swap Toolkit         */
/*         PPkPub.org  20190809           */  
/*    Released under the MIT License.     */
require_once "ppk_swap.inc.php";

//$asset_id=safeReqChrStr('asset_id');

require_once "page_header.inc.php";
?>

<h3>查询拍卖记录</h3>
<form class="form-horizontal" action="index.php" method="get">
  <div class="form-group">
    <label for="q_include_keys" class="col-sm-2 control-label">包含：</label>
    <div class="col-sm-10">
      <input type="text" class="form-control"  name="q_include_keys" id="q_include_keys" value="" placeholder='多个关键词请用逗号","隔开；如6,8,9'>
      <br>常用:<a href="#" onclick="setIncludeKeys('6,8')">含6和8</a>,<a href="#" onclick="setIncludeKeys('111,222,333,444,555,666,777,888,999,000')">含3个相同数字</a>,<a href="#" onclick="setIncludeKeys('123,234,345,456,567,678,789')">含3个连续数字</a>
    </div>
  </div>

  
  <div class="form-group">
    <label for="q_exclude_keys" class="col-sm-2 control-label">排除：</label>
    <div class="col-sm-10">
      <select class="form-control" name="q_exclude_keys" id="q_exclude_keys">
          <option value=""></option>
          <option value="4">4,g,h</option>
          <option value="0">0,o</option>
          <option value="1">1,a,i,l</option>
          <option value="2">2,b,c,z</option>
          <option value="3">3,d,e,f</option>
          <option value="5">5,j,k,s</option>
          <option value="6">6,m,n</option>
          <option value="7">7,p,q,r</option>
          <option value="8">8,t,u,v</option>
          <option value="9">9,w,x,y</option>
      </select>
    </div>
  </div>
  
  <div class="form-group">
    <label for="q_key_posn" class="col-sm-2 control-label">关键词位置：</label>
    <div class="col-sm-10">
      <select class="form-control" name="q_key_posn" id="q_key_posn">
          <option value="">不限</option>
          <option value="s">开始</option>
          <option value="e">结束</option>
          <option value="se">开始和结束</option>
      </select>
    </div>
  </div>
  
  <div class="form-group">
    <label for="q_length_limit" class="col-sm-2 control-label">长度：</label>
    <div class="col-sm-10">
      <select class="form-control" name="q_length_limit" id="q_length_limit">
          <option value="">不限</option>
          <option value="1">1位</option>
          <option value="2">2位</option>
          <option value="3">3位</option>
          <option value="4">4位</option>
          <option value="5">5位</option>
          <option value="6">6位</option>
          <option value="7">7位</option>
          <option value="8">8位</option>
          <option value="9">9位</option>
      </select>
    </div>
  </div>
  
  <div class="form-group">
    <label for="q_sell_status" class="col-sm-2 control-label">状态：</label>
    <div class="col-sm-10">
      <select class="form-control" name="q_sell_status" id="q_sell_status">
          <option value="">不限</option>
          <?php
          for($ss=0;$ss<10;$ss++){
              echo '<option value="',$ss,'">',getStatusLabel($ss),'</option>';  
          }
          ?>
      </select>
    </div>
  </div>

  
  <div class="form-group" align="center">
    <div class="col-sm-offset-2 col-sm-10">
      <button class="btn btn-success btn-lg" type="submit"  >马上查询</button>
    </div>
  </div>

</form>
<script type="text/javascript">
function setIncludeKeys(str_keys){
    document.getElementById("q_include_keys").value=str_keys;
}

function updateRmbValue(){
    var btc_value=document.getElementById("bid_amount").value;
    document.getElementById("bid_rmb_value").innerHTML= Math.ceil( btc_value * <?php echo getCoinValueOfCNY(1,$tmp_sell_record['coin_type']);?>);
}
</script>

<?php
require_once "page_footer.inc.php";
?>