<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\PWBillService;
use Home\Service\UserService;

/**
 * 采购Controller
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class PurchaseController extends PSIBaseController
{

  /**
   * 采购入库 - 主页面
   */
  public function pwbillIndex()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::PURCHASE_WAREHOUSE)) {
      $this->initVar();

      $this->assign("title", "采购入库");

      // 按钮权限：新建采购入库单
      $this->assign("pAdd", $us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_ADD) ? "1" : "0");

      // 按钮权限：编辑采购入库单
      $this->assign(
        "pEdit",
        $us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_EDIT) ? "1" : "0"
      );

      // 按钮权限：删除采购入库单
      $this->assign(
        "pDelete",
        $us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_DELETE) ? "1" : "0"
      );

      // 按钮权限：提交入库
      $this->assign(
        "pCommit",
        $us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_COMMIT) ? "1" : "0"
      );

      // 按钮权限：单据生成PDF
      $this->assign(
        "pGenPDF",
        $us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_PDF) ? "1" : "0"
      );

      // 按钮权限：新增商品
      // 在单据编辑界面中，明细表里面的商品弹出选择框中的新增商品按钮
      $this->assign("showAddGoodsButton", $us->hasPermission(FIdConst::GOODS_ADD) ? "1" : "0");

      // 字段权限：单价可见
      $this->assign(
        "pViewPrice",
        $us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_CAN_VIEW_PRICE) ? "1" : "0"
      );

      // 按钮权限：打印
      $this->assign(
        "pPrint",
        $us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_PRINT) ? "1" : "0"
      );

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Purchase/pwbillIndex");
    }
  }

  /**
   * 获得采购入库单主表列表
   */
  public function pwbillList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_WAREHOUSE)) {
        die("没有权限");
      }

      $ps = new PWBillService();
      $params = [
        "billStatus" => I("post.billStatus"),
        "ref" => I("post.ref"),
        "fromDT" => I("post.fromDT"),
        "toDT" => I("post.toDT"),
        "warehouseId" => I("post.warehouseId"),
        "supplierId" => I("post.supplierId"),
        "paymentType" => I("post.paymentType"),
        "goodsId" => I("post.goodsId"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];
      $this->ajaxReturn($ps->pwbillList($params));
    }
  }

  /**
   * 获得采购入库单的商品明细记录
   */
  public function pwBillDetailList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_WAREHOUSE)) {
        die("没有权限");
      }

      $pwbillId = I("post.pwBillId");
      $ps = new PWBillService();
      $this->ajaxReturn($ps->pwBillDetailList($pwbillId));
    }
  }

  /**
   * 新增或编辑采购入库单
   */
  public function editPWBill()
  {
    if (IS_POST) {
      $us = new UserService();
      $adding = I("post.adding");
      if ($adding == "1") {
        // 新建
        if (!$us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_ADD)) {
          die("没有权限");
        }
      } else {
        // 编辑
        if (!$us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_EDIT)) {
          die("没有权限");
        }
      }
      $json = I("post.jsonStr");
      $ps = new PWBillService();
      $this->ajaxReturn($ps->editPWBill($json));
    }
  }

  /**
   * 获得采购入库单的信息
   */
  public function pwBillInfo()
  {
    if (IS_POST) {
      $us = new UserService();
      $id = I("post.id");
      if ($id) {
        // 编辑
        if (!$us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新建
        if (!$us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id"),
        "pobillRef" => I("post.pobillRef")
      ];

      $ps = new PWBillService();
      $this->ajaxReturn($ps->pwBillInfo($params));
    }
  }

  /**
   * 删除采购入库单
   */
  public function deletePWBill()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_DELETE)) {
        die("没有权限");
      }

      $id = I("post.id");
      $ps = new PWBillService();
      $this->ajaxReturn($ps->deletePWBill($id));
    }
  }

  /**
   * 提交采购入库单
   */
  public function commitPWBill()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_COMMIT)) {
        die("没有权限");
      }

      $id = I("post.id");
      $ps = new PWBillService();
      $this->ajaxReturn($ps->commitPWBill($id));
    }
  }

  /**
   * 采购入库单生成PDF文件
   */
  public function pwBillPdf()
  {
    $us = new UserService();
    if (!$us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_PDF)) {
      die("没有权限");
    }

    $params = [
      "ref" => I("get.ref")
    ];

    $ps = new PWBillService();
    $ps->pdf($params);
  }

  /**
   * 生成打印采购入库单的页面
   */
  public function genPWBillPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_PRINT)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ss = new PWBillService();
      $data = $ss->getPWBillDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }
}
