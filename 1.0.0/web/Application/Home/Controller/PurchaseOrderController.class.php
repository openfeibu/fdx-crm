<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\POBillService;
use Home\Service\PWBillService;
use Home\Service\UserService;

/**
 * 采购订单Controller
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class PurchaseOrderController extends PSIBaseController
{

  /**
   * 采购订单 - 主页面
   */
  public function pobillIndex()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::PURCHASE_ORDER)) {
      $this->initVar();

      $this->assign("title", "采购订单");

      // 按钮权限：新建采购订单
      $this->assign("pAdd", $us->hasPermission(FIdConst::PURCHASE_ORDER_ADD) ? "1" : "0");
      // 按钮权限：编辑采购订单
      $this->assign("pEdit", $us->hasPermission(FIdConst::PURCHASE_ORDER_EDIT) ? "1" : "0");
      // 按钮权限：删除采购订单
      $this->assign(
        "pDelete",
        $us->hasPermission(FIdConst::PURCHASE_ORDER_DELETE) ? "1" : "0"
      );
      // 按钮权限：审核、取消审核
      $this->assign(
        "pConfirm",
        $us->hasPermission(FIdConst::PURCHASE_ORDER_CONFIRM) ? "1" : "0"
      );
      // 按钮权限：生成采购入库单
      $this->assign(
        "pGenPWBill",
        $us->hasPermission(FIdConst::PURCHASE_ORDER_GEN_PWBILL) ? "1" : "0"
      );
      // 新增物料 - 在编辑采购订单页面，录入物料的时候，其弹出窗口中有新增物料的按钮
      //           用于控制用户是否有该按钮的权限
      $this->assign("showAddGoodsButton", $us->hasPermission(FIdConst::GOODS_ADD) ? "1" : "0");
      // 按钮权限：关闭采购订单、取消采购订单关闭状态
      $this->assign(
        "pCloseBill",
        $us->hasPermission(FIdConst::PURCHASE_ORDER_CLOSE) ? "1" : "0"
      );
      // 按钮权限：单据生成PDF
      $this->assign("pGenPDF", $us->hasPermission(FIdConst::PURCHASE_ORDER_PDF) ? "1" : "0");
      // 按钮权限：单据生成Excel
      $this->assign("pGenExcel", $us->hasPermission(FIdConst::PURCHASE_ORDER_EXCEL) ? "1" : "0");
      // 按钮权限：打印预览、直接打印
      $this->assign("pPrint", $us->hasPermission(FIdConst::PURCHASE_ORDER_PRINT) ? "1" : "0");

      // 按钮权限：订单变更
      $this->assign("pChangeOrder", $us->hasPermission(FIdConst::PURCHASE_ORDER_CHANGE) ? "1" : "0");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/PurchaseOrder/pobillIndex");
    }
  }

  /**
   * 获得采购订单主表信息列表
   */
  public function pobillList()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::PURCHASE_ORDER)) {
        die("没有权限");
      }

      $ps = new POBillService();
      $params = [
        "billStatus" => I("post.billStatus"),
        "ref" => I("post.ref"),
        "fromDT" => I("post.fromDT"),
        "toDT" => I("post.toDT"),
        "supplierId" => I("post.supplierId"),
        "paymentType" => I("post.paymentType"),
        "goodsId" => I("post.goodsId"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];
      $this->ajaxReturn($ps->pobillList($params));
    }
  }

  /**
   * 新建或编辑采购订单
   */
  public function editPOBill()
  {
    if (IS_POST) {
      $adding = I("post.adding");
      $us = new UserService();
      if ($adding == "1") {
        // 新建采购订单
        if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_ADD)) {
          die("没有权限");
        }
      } else {
        // 编辑采购订单
        if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_EDIT)) {
          die("没有权限");
        }
      }

      $json = I("post.jsonStr");
      $ps = new POBillService();
      $this->ajaxReturn($ps->editPOBill($json));
    }
  }

  /**
   * 获得采购订单的信息
   */
  public function poBillInfo()
  {
    if (IS_POST) {
      $id = I("post.id");
      $us = new UserService();

      if ($id) {
        // 编辑采购订单
        if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新建采购订单
        if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id"),
        "genBill" => I("post.genBill"),
        "sobillRef" => I("post.sobillRef")
      ];

      $ps = new POBillService();
      $this->ajaxReturn($ps->poBillInfo($params));
    }
  }

  /**
   * 获得采购订单的明细信息
   */
  public function poBillDetailList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_ORDER)) {
        die("没有权限");
      }

      $companyId = $us->getCompanyId();

      $params = [
        "id" => I("post.id"),
        "companyId" => $companyId
      ];

      $ps = new POBillService();
      $this->ajaxReturn($ps->poBillDetailList($params));
    }
  }

  /**
   * 删除采购订单
   */
  public function deletePOBill()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ps = new POBillService();
      $this->ajaxReturn($ps->deletePOBill($params));
    }
  }

  /**
   * 审核采购订单
   */
  public function commitPOBill()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_CONFIRM)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ps = new POBillService();
      $this->ajaxReturn($ps->commitPOBill($params));
    }
  }

  /**
   * 取消审核采购订单
   */
  public function cancelConfirmPOBill()
  {
    if (IS_POST) {
      $us = new UserService();
      // 取消审核使用的是审核的权限项，目前并没有单独的权限项
      if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_CONFIRM)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ps = new POBillService();
      $this->ajaxReturn($ps->cancelConfirmPOBill($params));
    }
  }

  /**
   * 采购订单 - 订单变更
   */
  public function changePurchaseOrder()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_CHANGE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "goodsCount" => I("post.goodsCount"),
        "goodsPrice" => I("post.goodsPrice")
      ];

      $ps = new POBillService();
      $this->ajaxReturn($ps->changePurchaseOrder($params));
    }
  }

  /**
   * 查询主表金额相关数据 - 订单变更后刷新界面用
   */
  public function getPOBillDataAterChangeOrder()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_CHANGE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ps = new POBillService();
      $this->ajaxReturn($ps->getPOBillDataAterChangeOrder($params));
    }
  }

  /**
   * 采购订单生成PDF文件
   */
  public function poBillPdf()
  {
    $us = new UserService();
    if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_PDF)) {
      die("没有权限");
    }

    $params = [
      "ref" => I("get.ref")
    ];

    $ps = new POBillService();
    $ps->pdf($params);
  }

  /**
   * 采购订单生成Excel文件
   */
  public function poBillExcel()
  {
    // 检查权限
    $us = new UserService();
    if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_EXCEL)) {
      die("没有权限");
    }

    $params = [
      "ref" => I("get.ref")
    ];

    $ps = new POBillService();
    $ps->excel($params);
  }

  /**
   * 采购订单执行的采购入库单信息
   */
  public function poBillPWBillList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_ORDER)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ps = new PWBillService();
      $this->ajaxReturn($ps->poBillPWBillList($params));
    }
  }

  /**
   * 关闭采购订单
   */
  public function closePOBill()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_CLOSE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ps = new POBillService();
      $this->ajaxReturn($ps->closePOBill($params));
    }
  }

  /**
   * 取消关闭采购订单
   */
  public function cancelClosedPOBill()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_CLOSE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ps = new POBillService();
      $this->ajaxReturn($ps->cancelClosedPOBill($params));
    }
  }

  /**
   * 生成打印采购订单的页面
   */
  public function genPOBillPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_PRINT)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ss = new POBillService();
      $data = $ss->getPOBillDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 查询物料的税率
   */
  public function queryTaxRate()
  {
    if (IS_POST) {
      $adding = I("post.adding");
      $us = new UserService();
      if ($adding == "1") {
        // 新建采购订单
        if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_ADD)) {
          die("没有权限");
        }
      } else {
        // 编辑采购订单
        if (!$us->hasPermission(FIdConst::PURCHASE_ORDER_EDIT)) {
          die("没有权限");
        }
      }

      $params = [
        "supplierId" => I("post.supplierId"),
        "goodsIdList" => I("post.goodsIdList"),
      ];

      $ps = new POBillService();
      $this->ajaxReturn($ps->queryTaxRate($params));
    }
  }
}
