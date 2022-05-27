<?php if (!defined('THINK_PATH')) exit();?>
<html>

<body>
  <table border=0 cellSpacing=0 cellPadding=0 width="100%">
    <tr>
      <td colspan="6" align="center">采购订单</td>
    </tr>
    <tr>
      <td width="12%"><span style='font-size: 13'>单号</span></td>
      <td width="15%"><span style='font-size: 13'><?php echo ($data["ref"]); ?></span></td>
      <td width="12%"><span style='font-size: 13'>交货日期</span></td>
      <td width="15%"><span style='font-size: 13'><?php echo ($data["dealDate"]); ?></span></td>
      <td width="12%"><span style='font-size: 13'>打印时间</span></td>
      <td><span style='font-size: 13'><?php echo ($data["printDT"]); ?></span></td>
    </tr>
    <tr>
      <td><span style='font-size: 13'>供应商</span></td>
      <td colspan="3"><span style='font-size: 13'><?php echo ($data["supplierName"]); ?></span></td>
      <td><span style='font-size: 13'>联系电话</span></td>
      <td colspan="3"><span style='font-size: 22'><?php echo ($data["tel"]); ?></span></td>
    </tr>
    <tr>
      <td><span style='font-size: 13'>交货地址</span></td>
      <td colspan="5"><span style='font-size: 13'><?php echo ($data["dealAddress"]); ?></span></td>
    </tr>
    <tr>
      <td><span style='font-size: 13'>订单备注</span></td>
      <td colspan="5"><span style='font-size: 13'><?php echo ($data["billMemo"]); ?></span></td>
    </tr>
  </table>
  <br />
  <table border=1 cellSpacing=0 cellPadding=1 width="100%" style="border-collapse: collapse" bordercolor="#333333">
    <tr>
      <td>
        <div align="center" style='font-size: 13'>序号</div>
      </td>
      <td>
        <div align="center" style='font-size: 13'>物料编码</div>
      </td>
      <td>
        <div align="center" style='font-size: 13'>品名</div>
      </td>
      <td>
        <div align="center" style='font-size: 13'>规格型号</div>
      </td>
      <td>
        <div align="center" style='font-size: 13'>数量</div>
      </td>
      <td>
        <div align="center" style='font-size: 13'>单位</div>
      </td>
      <td>
        <div align="center" style='font-size: 13'>采购单价</div>
      </td>
      <td>
        <div align="center" style='font-size: 13'>采购金额</div>
      </td>
      <td>
        <div align="center" style='font-size: 13'>税率</div>
      </td>
      <td>
        <div align="center" style='font-size: 13'>价税合计</div>
      </td>
    </tr>
    <?php if(is_array($data['items'])): foreach($data['items'] as $i=>$v): ?><tr>
        <td>
          <div align="center" style='font-size: 13'><?php echo ($i+1); ?></div>
        </td>
        <td>
          <div style='font-size: 13'><?php echo ($v["goodsCode"]); ?></div>
        </td>
        <td>
          <div style='font-size: 13'><?php echo ($v["goodsName"]); ?></div>
        </td>
        <td>
          <div style='font-size: 13'><?php echo ($v["goodsSpec"]); ?></div>
        </td>
        <td>
          <div align="center" style='font-size: 13'><?php echo ($v["goodsCount"]); ?></div>
        </td>
        <td>
          <div style='font-size: 13'><?php echo ($v["unitName"]); ?></div>
        </td>
        <td>
          <div align="right" style='font-size: 13'><?php echo ($v["goodsPrice"]); ?></div>
        </td>
        <td>
          <div align="right" style='font-size: 13'><?php echo ($v["goodsMoney"]); ?></div>
        </td>
        <td>
          <div align="right" style='font-size: 13'><?php echo ($v["taxRate"]); ?>%</div>
        </td>
        <td>
          <div align="right" style='font-size: 13'><?php echo ($v["goodsMoneyWithTax"]); ?></div>
        </td>
      </tr><?php endforeach; endif; ?>
    <tr>
      <td>
        <div align="center">合计</div>
      </td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td>
        <div align="right">￥<?php echo ($data["goodsMoney"]); ?></div>
      </td>
      <td></td>
      <td>
        <div align="right">￥<?php echo ($data["moneyWithTax"]); ?></div>
      </td>
    </tr>
  </table>
</body>

</html>