<?php

namespace Home\Service;

use Home\DAO\ReceivablesDAO;

/**
 * 应收账款Service
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class ReceivablesService extends PSIBaseExService
{
  private $LOG_CATEGORY = "应收账款管理";

  /**
   * 往来单位分类
   */
  public function rvCategoryList($params)
  {
    if ($this->isNotOnline()) {
      return $this->emptyResult();
    }

    $params["loginUserId"] = $this->getLoginUserId();

    $dao = new ReceivablesDAO($this->db());
    return $dao->rvCategoryList($params);
  }

  /**
   * 应收账款列表
   */
  public function rvList($params)
  {
    if ($this->isNotOnline()) {
      return $this->emptyResult();
    }

    $params["loginUserId"] = $this->getLoginUserId();

    $dao = new ReceivablesDAO($this->db());
    return $dao->rvList($params);
  }

  /**
   * 应收账款的明细记录
   */
  public function rvDetailList($params)
  {
    if ($this->isNotOnline()) {
      return $this->emptyResult();
    }

    $dao = new ReceivablesDAO($this->db());
    return $dao->rvDetailList($params);
  }
	
  public function rvPdf($ids)
  {
	  if ($this->isNotOnline()) {
		  return;
	  }
		$id_arr = array_filter(explode(',',$ids));
	  if (!$id_arr) {
		  return $this->bad("请选择数据");
	  }
	  
	  $dao = new ReceivablesDAO($this->db());
	  $rvDetailList = $dao->getRvDetailDataForPDF($id_arr);
	  
	  if (!$rvDetailList) {
		  return;
	  }
		$ca = $rvDetailList['ca'];
	  $dataList = $rvDetailList['dataList'];
	  $totalBalanceMoney = $rvDetailList['totalBalanceMoney'];
	  
	  $bs = new BizConfigService();
	  $productionName = $bs->getProductionName();
	
	  // 记录业务日志
	  $log = "应收账款业务单据生成PDF文件";
	  $bls = new BizlogService($this->db());
	  $bls->insertBizlog($log, $this->LOG_CATEGORY);
	
	  ob_start();
	
	  $utilService = new UtilService();
	
	  $ps = new PDFService();
	  $pdf = $ps->getInstance();
	  $pdf->SetTitle("应收款业务单据");
	
	  $pdf->setHeaderFont([
		  "stsongstdlight",
		  "",
		  16
	  ]);
	
	  $pdf->setFooterFont([
		  "stsongstdlight",
		  "",
		  14
	  ]);
	
	  $pdf->SetHeaderData("", 0, $productionName, "应收款业务单据");
	
	  $pdf->SetFont("stsongstdlight", "", 10);
	  $pdf->AddPage();
	
	  /**
	   * 注意：
	   * TCPDF中，用来拼接HTML的字符串需要用单引号，否则HTML中元素的属性就不会被解析
	   */
	  $html =  '
				<table>
				<tr>
				<td colspan="2">'.$ca['caType'].'：' . $ca['caName'] . '</td>
				<td>账单金额：' . $totalBalanceMoney . '</td></tr>
				</table>';
	  
	  $pdf->writeHTML($html);
	  
	  foreach ($dataList as $v)
	  {
		  $html = '<hr/><br/>
				<table>
					<tr><td>单号：' . $v['refNumber'] . '('.$v['refType'].')</td><td>业务日期：' . $v["bizDT"] . '</td><td>';
		  if(isset($v['bill']['freight']))
		  {
			  $html .= '运费：' . $v['bill']['freight'];
		  }
			$html.='</td></tr>';
		  if($v['refType'] == '销售退货入库')
		  {
			  $html .= '
		   <tr><td>应退：' . abs($v['rvMoney']) . '</td><td>已退：' . abs($v['actMoney']) . '</td><td>未退金额：' . -$v['balanceMoney'] . '</td></tr>';
			  $html .= '
				</table>
				';
			  $html .= '<table border="1" cellpadding="1">
					<tr><td>商品名称</td><td>规格型号</td><td>退货数量</td><td>单位</td>
						<td>单价</td><td>销售金额</td>
					</tr>
				';
			  foreach ($v['bill']["items"] as $bill_v) {
				  $html .= '<tr>';
				  $html .= '<td>' . $bill_v["goodsName"] . '</td>';
				  $html .= '<td>' . $bill_v["goodsSpec"] . '</td>';
				  $html .= '<td align="right">' . $bill_v["rejCount"] . '</td>';
				  $html .= '<td>' . $bill_v["unitName"] . '</td>';
				  $html .= '<td align="right">' . $bill_v["rejPrice"] . '</td>';
				  $html .= '<td align="right">' . $bill_v["rejMoney"] . '</td>';
				  //$html .= '<td align="right">' . $bill_v["taxRate"] . '</td>';
				  //$html .= '<td align="right">' . $bill_v["rejMoneyWithTax"] . '</td>';
				  $html .= '</tr>';
			  }
			  $html .= "";
			
			  $html .= '</table>';
		  }else{
			  $html .= '
		   <tr><td>应收：' . $v['rvMoney'] . '</td><td>已收：' . $v['actMoney'] . '</td><td>未收：' . $v['balanceMoney'] . '</td></tr>';
			  $html .= '
				</table>
				';
			  $html .= '<table border="1" cellpadding="1">
					<tr><td>商品名称</td><td>规格型号</td><td>数量</td><td>单位</td>
						<td>单价</td><td>销售金额</td>
					</tr>
				';
			  foreach ($v['bill']["items"] as $bill_v) {
				  $html .= '<tr>';
				  $html .= '<td>' . $bill_v["goodsName"] . '</td>';
				  $html .= '<td>' . $bill_v["goodsSpec"] . '</td>';
				  $html .= '<td align="right">' . $bill_v["goodsCount"] . '</td>';
				  $html .= '<td>' . $bill_v["unitName"] . '</td>';
				  $html .= '<td align="right">' . $bill_v["goodsPrice"] . '</td>';
				  $html .= '<td align="right">' . $bill_v["goodsMoney"] . '</td>';
				  //$html .= '<td align="right">' . $bill_v["taxRate"] . '%</td>';
				  //$html .= '<td align="right">' . $bill_v["moneyWithTax"] . '</td>';
				  $html .= '</tr>';
			  }
			  $html .= "";
			
			  $html .= '</table>';
		  }
		  
		
		  
		  $pdf->writeHTML($html, true, false, true, false, '');
	  }
	  
	 
	 
	
	  ob_end_clean();
	
	  $pdf->Output(date('YmdHisu').".pdf", "I");
  }
  /**
   * 应收账款的收款记录
   */
  public function rvRecordList($params)
  {
    if ($this->isNotOnline()) {
      return $this->emptyResult();
    }

    $dao = new ReceivablesDAO($this->db());
    return $dao->rvRecordList($params);
  }

  /**
   * 收款记录
   */
  public function addRvRecord($params)
  {
    if ($this->isNotOnline()) {
      return $this->notOnlineError();
    }

    $params["companyId"] = $this->getCompanyId();
    $params["dataOrg"] = $this->getLoginUserDataOrg();
    $params["loginUserId"] = $this->getLoginUserId();

    $db = $this->db();
    $db->startTrans();

    $dao = new ReceivablesDAO($db);
    $rc = $dao->addRvRecord($params);
    if ($rc) {
      $db->rollback();
      return $rc;
    }

    // 记录业务日志
    $refType = $params["refType"];
    $refNumber = $params["refNumber"];
    $actMoney = $params["actMoney"];
    $log = "为 {$refType} - 单号：{$refNumber} 收款：{$actMoney}元";
    $bs = new BizlogService($db);
    $bs->insertBizlog($log, $this->LOG_CATEGORY);

    $db->commit();

    return $this->ok();
  }

  public function refreshRvInfo($params)
  {
    if ($this->isNotOnline()) {
      return $this->emptyResult();
    }

    $dao = new ReceivablesDAO($this->db());
    return $dao->refreshRvInfo($params);
  }

  public function refreshRvDetailInfo($params)
  {
    if ($this->isNotOnline()) {
      return $this->emptyResult();
    }

    $dao = new ReceivablesDAO($this->db());
    return $dao->refreshRvDetailInfo($params);
  }
}
