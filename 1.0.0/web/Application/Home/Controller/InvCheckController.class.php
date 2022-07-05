<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\ICBillService;
use Home\Service\UserService;

/**
 * 库存盘点Controller
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class InvCheckController extends PSIBaseController
{

  /**
   * 库存盘点 - 主页面
   */
  public function index()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::INVENTORY_CHECK)) {
      $this->initVar();

      $this->assign("title", "库存盘点");

      // 按钮权限：新建盘点单
      $this->assign("pAdd", $us->hasPermission(FIdConst::INVENTORY_CHECK_ADD) ? "1" : "0");
      // 按钮权限：编辑盘点单
      $this->assign("pEdit", $us->hasPermission(FIdConst::INVENTORY_CHECK_EDIT) ? "1" : "0");
      // 按钮权限：删除盘点单
      $this->assign(
        "pDelete",
        $us->hasPermission(FIdConst::INVENTORY_CHECK_DELETE) ? "1" : "0"
      );
      // 按钮权限：提交盘点单
      $this->assign(
        "pCommit",
        $us->hasPermission(FIdConst::INVENTORY_CHECK_COMMIT) ? "1" : "0"
      );
      // 按钮权限：单据生成PDF
      $this->assign("pGenPDF", $us->hasPermission(FIdConst::INVENTORY_CHECK_PDF) ? "1" : "0");
      // 按钮权限：打印预览、直接打印
      $this->assign("pPrint", $us->hasPermission(FIdConst::INVENTORY_CHECK_PRINT) ? "1" : "0");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/InvCheck/index");
    }
  }

  /**
   * 盘点单，主表
   */
  public function icbillList()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::INVENTORY_CHECK)) {
        die("没有权限");
      }

      $params = [
        "billStatus" => I("post.billStatus"),
        "ref" => I("post.ref"),
        "fromDT" => I("post.fromDT"),
        "toDT" => I("post.toDT"),
        "warehouseId" => I("post.warehouseId"),
        "goodsId" => I("post.goodsId"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];

      $ic = new ICBillService();
      $this->ajaxReturn($ic->icbillList($params));
    }
  }

  /**
   * 获得某个盘点单的信息
   */
  public function icBillInfo()
  {
    if (IS_POST) {
      $us = new UserService();

      $id = I("post.id");
      if ($id) {
        // 编辑
        if (!$us->hasPermission(FIdConst::INVENTORY_CHECK_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新建
        if (!$us->hasPermission(FIdConst::INVENTORY_CHECK_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id")
      ];

      $ic = new ICBillService();

      $this->ajaxReturn($ic->icBillInfo($params));
    }
  }

  /**
   * 新增或编辑盘点单
   */
  public function editICBill()
  {
    if (IS_POST) {
      $us = new UserService();
      $adding = I("post.adding");
      if ($adding == "1") {
        // 新建
        if (!$us->hasPermission(FIdConst::INVENTORY_CHECK_ADD)) {
          die("没有权限");
        }
      } else {
        // 编辑
        if (!$us->hasPermission(FIdConst::INVENTORY_CHECK_EDIT)) {
          die("没有权限");
        }
      }

      $params = [
        "jsonStr" => I("post.jsonStr")
      ];

      $ic = new ICBillService();

      $this->ajaxReturn($ic->editICBill($params));
    }
  }

  /**
   * 盘点单明细记录
   */
  public function icBillDetailList()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::INVENTORY_CHECK)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ic = new ICBillService();

      $this->ajaxReturn($ic->icBillDetailList($params));
    }
  }

  /**
   * 删除盘点单
   */
  public function deleteICBill()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::INVENTORY_CHECK_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ic = new ICBillService();

      $this->ajaxReturn($ic->deleteICBill($params));
    }
  }

  /**
   * 提交盘点单
   */
  public function commitICBill()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::INVENTORY_CHECK_COMMIT)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ic = new ICBillService();

      $this->ajaxReturn($ic->commitICBill($params));
    }
  }

  /**
   * 盘点单生成pdf文件
   */
  public function pdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::INVENTORY_CHECK_PDF)) {
      die("没有权限");
    }

    $params = [
      "ref" => I("get.ref")
    ];

    $ws = new ICBillService();
    $ws->pdf($params);
  }

  /**
   * 生成打印盘点单的页面
   */
  public function genICBillPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::INVENTORY_CHECK_PRINT)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ss = new ICBillService();
      $data = $ss->getICBillDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }
}
