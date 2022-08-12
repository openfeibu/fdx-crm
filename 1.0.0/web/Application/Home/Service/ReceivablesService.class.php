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
		  $html.='</td></tr>';
		  if($v['refType'] == '销售退货入库')
		  {
			  $html .= '
		   <tr><td>应退：' . abs($v['rvMoney']) . '</td><td>已退：' . abs($v['actMoney']) . '</td><td>账单金额：';
			  if($v['balanceMoney'] < 0 )
			  {
				  $html .= '<span color="red">'.$v['balanceMoney'].'</span>';
			  }else{
				  $html .= '<span>'.$v['balanceMoney'].'</span>';
			  }
			  $html .= '</td></tr>
				</table>
				';
			  $html .= '<table border="1" cellpadding="1">
					<tr><td>商品名称</td><td>退货数量</td><td>单位</td>
						<td>单价</td><td>销售金额</td>
					</tr>
				';
			  foreach ($v['bill']["items"] as $bill_v) {
				  $html .= '<tr>';
				  $html .= '<td>' . $bill_v["goodsName"] . $bill_v["goodsSpec"]. '</td>';
				  $html .= '<td align="right">' . $bill_v["rejCount"] . '</td>';
				  $html .= '<td>' . $bill_v["unitName"] . '</td>';
				  $html .= '<td align="right">' . $bill_v["rejPrice"] . '</td>';
				  $html .= '<td align="right">' . $bill_v["rejMoney"] . '</td>';
				  //$html .= '<td align="right">' . $bill_v["taxRate"] . '</td>';
				  //$html .= '<td align="right">' . $bill_v["rejMoneyWithTax"] . '</td>';
				  $html .= '</tr>';
			  }
			  if(isset($v['bill']['freight']))
			  {
				  $html .= '<tr>';
				  $html .= '<td>运费</td>';
				  $html .= '<td></td>';
				  $html .= '<td></td>';
				  $html .= '<td></td>';
				  $html .= '<td align="right">' . $v['bill']['freight'] . '</td>';
				  $html .= '</tr>';
			  }
			  
			  $html .= "";
			
			  $html .= '</table>';
		  }else{
			  $html .= '
		   <tr><td>应收：' . $v['rvMoney'] . '</td><td>已收：' . $v['actMoney'] . '</td><td>账单金额：';
			  if($v['balanceMoney'] < 0 )
			  {
				  $html .= '<span color="red">'.$v['balanceMoney'].'</span>';
			  }else{
				  $html .= '<span>'.$v['balanceMoney'].'</span>';
			  }
			  $html .= '</td></tr>
				</table>
				';
			  $html .= '<table border="1" cellpadding="1">
					<tr><td>商品名称</td><td>数量</td><td>单位</td>
						<td>单价</td><td>销售金额</td>
					</tr>
				';
			  foreach ($v['bill']["items"] as $bill_v) {
				  $html .= '<tr>';
				  $html .= '<td>' . $bill_v["goodsName"] . $bill_v["goodsSpec"]. '</td>';
				  $html .= '<td align="right">' . $bill_v["goodsCount"] . '</td>';
				  $html .= '<td>' . $bill_v["unitName"] . '</td>';
				  $html .= '<td align="right">' . $bill_v["goodsPrice"] . '</td>';
				  $html .= '<td align="right">' . $bill_v["goodsMoney"] . '</td>';
				  //$html .= '<td align="right">' . $bill_v["taxRate"] . '%</td>';
				  //$html .= '<td align="right">' . $bill_v["moneyWithTax"] . '</td>';
				  $html .= '</tr>';
			  }
			  if(isset($v['bill']['freight']))
			  {
				  $html .= '<tr>';
				  $html .= '<td>运费</td>';
				  $html .= '<td></td>';
				  $html .= '<td></td>';
				  $html .= '<td></td>';
				  $html .= '<td align="right">' . $v['bill']['freight'] . '</td>';
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
	public function rvExcel($ids)
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
		require_once __DIR__ . '/../Common/PhpSpreadsheet/vendor/autoload.php';
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
		
		$excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		
		$sheet = $excel->getActiveSheet();
		if (!$sheet) {
			$sheet = $excel->createSheet();
		}
		
		$sheet->setTitle("应收款业务单据");
		$sheet->getRowDimension('1')->setRowHeight(22);
		$sheet->setCellValue("C1", "应收款业务单据");
		$sheet->setCellValue("A2", $ca['caType']);
		$sheet->setCellValue("B2", $ca['caName']);
		$sheet->setCellValue("A3", "账单金额");
		$sheet->setCellValue("B3", $totalBalanceMoney);
		$sheet->getColumnDimension('A')->setWidth(20);
		$sheet->getColumnDimension('B')->setWidth(15);
		$sheet->getColumnDimension('C')->setWidth(12);
		$sheet->getColumnDimension('D')->setWidth(12);
		$sheet->getColumnDimension('E')->setWidth(12);
		$captionIndex = 5;
		foreach ($dataList as $v)
		{
			$sheet->setCellValue("A{$captionIndex}", "单号：".$v['refNumber']);
			$sheet->setCellValue("B{$captionIndex}",  $v['refType']);
			$sheet->setCellValue("C{$captionIndex}", "业务日期：");
			$sheet->setCellValue("D{$captionIndex}", $v["bizDT"]);
			
			$captionIndex++;
			if($v['refType'] == '销售退货入库')
			{
				$sheet->setCellValue("A{$captionIndex}", "应退：");
				$sheet->setCellValue("B{$captionIndex}", abs($v['rvMoney']));
				$sheet->setCellValue("C{$captionIndex}", "已退：");
				$sheet->setCellValue("D{$captionIndex}", abs($v['actMoney']));
				$sheet->setCellValue("E{$captionIndex}", "账单金额：");
				$sheet->setCellValue("F{$captionIndex}", $v['balanceMoney']);
				if($v['balanceMoney'] < 0)
				{
					$sheet->getStyle("F{$captionIndex}")->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
				}
				$captionIndex++;
				$startIndex = $captionIndex;
				$sheet->setCellValue("A{$captionIndex}", "商品名称");
				$sheet->setCellValue("B{$captionIndex}", "数量");
				$sheet->setCellValue("C{$captionIndex}", "单位");
				$sheet->setCellValue("D{$captionIndex}", "单价");
				$sheet->setCellValue("E{$captionIndex}", "金额");
				
				foreach ($v['bill']["items"] as $bill_v) {
					$captionIndex++;
					$sheet->setCellValue("A{$captionIndex}", $bill_v["goodsName"] . $bill_v["goodsSpec"]);
					$sheet->setCellValue("B{$captionIndex}",  $bill_v["rejCount"]);
					$sheet->setCellValue("C{$captionIndex}", $bill_v["unitName"]);
					$sheet->setCellValue("D{$captionIndex}", $bill_v["rejPrice"] );
					$sheet->setCellValue("E{$captionIndex}", $bill_v["rejMoney"]);
				}
				$itemCount = count($v['bill']["items"]);
				if(isset($v['bill']['freight']))
				{
					$captionIndex++;
					$itemCount++;
					$sheet->setCellValue("A{$captionIndex}", "运费");
					$sheet->setCellValue("E{$captionIndex}",  $v['bill']['freight']);
				}
				
				$captionIndex += 2;
				// 画表格边框
				$styleArray = [
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
						]
					]
				];
				$idx = $startIndex;
				$lastRow = $itemCount + $startIndex;
				$sheet->getStyle("A{$idx}:E{$lastRow}")->applyFromArray($styleArray);
				
			}
			else{
				$sheet->setCellValue("A{$captionIndex}", "应收：");
				$sheet->setCellValue("B{$captionIndex}", $v['rvMoney']);
				$sheet->setCellValue("C{$captionIndex}", "已收：");
				$sheet->setCellValue("D{$captionIndex}", $v['actMoney']);
				$sheet->setCellValue("E{$captionIndex}", "账单金额：");
				$sheet->setCellValue("F{$captionIndex}", $v['balanceMoney']);
				if($v['balanceMoney'] < 0)
				{
					$sheet->getStyle("F{$captionIndex}")->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
				}
				$captionIndex++;
				$startIndex = $captionIndex;
				$sheet->setCellValue("A{$captionIndex}", "商品名称");
				$sheet->setCellValue("B{$captionIndex}", "数量");
				$sheet->setCellValue("C{$captionIndex}", "单位");
				$sheet->setCellValue("D{$captionIndex}", "单价");
				$sheet->setCellValue("E{$captionIndex}", "金额");
				
				foreach ($v['bill']["items"] as $bill_v) {
					$captionIndex++;
					$sheet->setCellValue("A{$captionIndex}", $bill_v["goodsName"] . $bill_v["goodsSpec"]);
					$sheet->setCellValue("B{$captionIndex}",  $bill_v["goodsCount"]);
					$sheet->setCellValue("C{$captionIndex}", $bill_v["unitName"]);
					$sheet->setCellValue("D{$captionIndex}", $bill_v["goodsPrice"] );
					$sheet->setCellValue("E{$captionIndex}", $bill_v["goodsMoney"]);
				}
				$itemCount = count($v['bill']["items"]);
				if(isset($v['bill']['freight']))
				{
					$captionIndex++;
					$itemCount++;
					$sheet->setCellValue("A{$captionIndex}", "运费");
					$sheet->setCellValue("E{$captionIndex}",  $v['bill']['freight']);
				}
				
				$captionIndex += 2;
				// 画表格边框
				$styleArray = [
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
						]
					]
				];
				$idx = $startIndex;
				$lastRow = $itemCount + $startIndex;
				$sheet->getStyle("A{$idx}:E{$lastRow}")->applyFromArray($styleArray);
				
			}
			
			

		}
		
	
		
		// 把焦点放置在A1
		$sheet->setSelectedCells("A1");
		
		$dt = date("YmdHis");
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="应收款业务单据_' . $dt . '.xlsx"');
		header('Cache-Control: max-age=0');
		
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($excel);
		$writer->save("php://output");
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
