<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\UserService;
use Home\Service\WSBillService;

/**
 * 销售出库Controller
 * 
 * 最初的代码中，销售订单、销售出库、销售退货入库，都是写在这一个Controller里面的，
 * 之后把销售订单和销售退货入库移到单独的Contoller里面了，就只剩下销售出库了
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class SaleController extends PSIBaseController
{

  /**
   * 销售出库 - 主页面
   */
  public function wsIndex()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::WAREHOUSING_SALE)) {
      $this->initVar();

      $this->assign("title", "销售出库");

      // 按钮权限：新建销售出库单
      $this->assign("pAdd", $us->hasPermission(FIdConst::WAREHOUSING_SALE_ADD) ? "1" : "0");
      // 按钮权限：编辑销售出库单
      $this->assign("pEdit", $us->hasPermission(FIdConst::WAREHOUSING_SALE_EDIT) ? "1" : "0");
      // 按钮权限：删除销售出库单
      $this->assign(
        "pDelete",
        $us->hasPermission(FIdConst::WAREHOUSING_SALE_DELETE) ? "1" : "0"
      );
      // 按钮权限：提交出库
      $this->assign(
        "pCommit",
        $us->hasPermission(FIdConst::WAREHOUSING_SALE_COMMIT) ? "1" : "0"
      );
      // 按钮权限：单据生成PDF
      $this->assign("pGenPDF", $us->hasPermission(FIdConst::WAREHOUSING_SALE_PDF) ? "1" : "0");
      // 按钮权限：打印预览、直接打印
      $this->assign(
        "pPrint",
        $us->hasPermission(FIdConst::WAREHOUSING_SALE_PRINT) ? "1" : "0"
      );

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Sale/wsIndex");
    }
  }

  /**
   * 获得销售出库单的信息
   */
  public function wsBillInfo()
  {
    if (IS_POST) {
      $us = new UserService();
      $id = I("post.id");
	    $sobillRef = I("post.sobillRef");
	    
      if ($id) {
        // 编辑
        if (!$us->hasPermission(FIdConst::WAREHOUSING_SALE_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新建
	      if($sobillRef)
	      {
		      if (!$us->hasPermission(FIdConst::SALE_ORDER_GEN_WSBILL)) {
			      die("没有权限");
		      }
	      }else{
		      if (!$us->hasPermission(FIdConst::WAREHOUSING_SALE_ADD)) {
			      die("没有权限");
		      }
	      }
        
      }

      $params = [
        "id" => I("post.id"),
        "sobillRef" => I("post.sobillRef")
      ];

      $ws = new WSBillService();
      $this->ajaxReturn($ws->wsBillInfo($params));
    }
  }

  /**
   * 新建或编辑销售出库单
   */
  public function editWSBill()
  {
    if (IS_POST) {
      $us = new UserService();

      $adding = I("post.adding");
	    $isGenBill = I("post.isGenBill");
      if ($adding == "1") {
      	if($isGenBill == "true")
	      {
		      if (!$us->hasPermission(FIdConst::SALE_ORDER_GEN_WSBILL)) {
			      die("没有权限");
		      }
	      }else{
		      // 新建
		      if (!$us->hasPermission(FIdConst::WAREHOUSING_SALE_ADD)) {
			      die("没有权限");
		      }
	      }
        
      } else {
        // 编辑
        if (!$us->hasPermission(FIdConst::WAREHOUSING_SALE_EDIT)) {
          die("没有权限");
        }
      }

      $params = [
        "jsonStr" => I("post.jsonStr"),
        "checkInv" => I("post.checkInv")
      ];

      $ws = new WSBillService();
      $this->ajaxReturn($ws->editWSBill($params));
    }
  }

  /**
   * 销售出库单主表信息列表
   */
  public function wsbillList()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::WAREHOUSING_SALE)) {
        die("没有权限");
      }

      $params = [
        "billStatus" => I("post.billStatus"),
        "ref" => I("post.ref"),
        "fromDT" => I("post.fromDT"),
        "toDT" => I("post.toDT"),
        "warehouseId" => I("post.warehouseId"),
        "customerId" => I("post.customerId"),
        "receivingType" => I("post.receivingType"),
        "sn" => I("post.sn"),
        "goodsId" => I("post.goodsId"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];

      $ws = new WSBillService();
      $this->ajaxReturn($ws->wsbillList($params));
    }
  }

  /**
   * 销售出库单明细信息列表
   */
  public function wsBillDetailList()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::WAREHOUSING_SALE)) {
        die("没有权限");
      }

      $params = [
        "billId" => I("post.billId")
      ];

      $ws = new WSBillService();
      $this->ajaxReturn($ws->wsBillDetailList($params));
    }
  }

  /**
   * 删除销售出库单
   */
  public function deleteWSBill()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::WAREHOUSING_SALE_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ws = new WSBillService();
      $this->ajaxReturn($ws->deleteWSBill($params));
    }
  }

  /**
   * 提交销售出库单
   */
  public function commitWSBill()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::WAREHOUSING_SALE_COMMIT)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ws = new WSBillService();
      $this->ajaxReturn($ws->commitWSBill($params));
    }
  }

  /**
   * 销售出库单生成pdf文件
   */
  public function pdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::WAREHOUSING_SALE_PDF)) {
      die("没有权限");
    }

    $params = [
      "ref" => I("get.ref")
    ];

    $ws = new WSBillService();
    $ws->pdf($params);
  }

  /**
   * 生成打印销售出库单的页面
   */
  public function genWSBillPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::WAREHOUSING_SALE_PRINT)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ss = new WSBillService();
      $data = $ss->getWSBillDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }
}
