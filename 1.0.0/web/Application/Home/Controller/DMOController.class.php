<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\UserService;
use Home\Service\DMOBillService;

/**
 * 成品委托生产订单Controller
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class DMOController extends PSIBaseController
{

  /**
   * 成品委托生产订单 - 主页面
   */
  public function dmobillIndex()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::DMO)) {
      $this->initVar();

      // 按钮权限：新建成品委托生产订单
      $this->assign("pAdd", $us->hasPermission(FIdConst::DMO_ADD) ? "1" : "0");
      // 按钮权限：编辑成品委托生产订单
      $this->assign("pEdit", $us->hasPermission(FIdConst::DMO_EDIT) ? "1" : "0");
      // 按钮权限：删除成品委托生产订单
      $this->assign("pDelete", $us->hasPermission(FIdConst::DMO_DELETE) ? "1" : "0");
      // 按钮权限：审核、取消审核
      $this->assign("pCommit", $us->hasPermission(FIdConst::DMO_COMMIT) ? "1" : "0");
      // 按钮权限：生成成品委托生产入库单
      $this->assign("pGenDMWBill", $us->hasPermission(FIdConst::DMO_GEN_DMW_BILL) ? "1" : "0");
      // 按钮权限：新建或编辑订单的时候，录入物料的弹出窗口新建物料按钮的权限
      $this->assign("showAddGoodsButton", $us->hasPermission(FIdConst::GOODS_ADD) ? "1" : "0");
      // 按钮权限：新建或编辑订单的时候，录入工厂的弹出窗口中新建工厂按钮的权限
      $this->assign(
        "showAddFactoryButton",
        $us->hasPermission(FIdConst::FACTORY_ADD) ? "1" : "0"
      );
      // 按钮权限：关闭订单、取消订单关闭状态
      $this->assign("pCloseBill", $us->hasPermission(FIdConst::DMO_CLOSE_BILL) ? "1" : "0");
      // 按钮权限：单据生成PDF
      $this->assign("pGenPDF", $us->hasPermission(FIdConst::DMO_PDF) ? "1" : "0");
      // 按钮权限：打印预览、直接打印
      $this->assign("pPrint", $us->hasPermission(FIdConst::DMO_PRINT) ? "1" : "0");

      $this->assign("title", "成品委托生产订单");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/DM/dmobillIndex");
    }
  }

  /**
   * 获得成品委托生产订单的信息
   */
  public function dmoBillInfo()
  {
    if (IS_POST) {
      $us = new UserService();

      $id = I("post.id");
      if ($id) {
        // 编辑
        if (!$us->hasPermission(FIdConst::DMO_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新建
        if (!$us->hasPermission(FIdConst::DMO_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new DMOBillService();
      $this->ajaxReturn($service->dmoBillInfo($params));
    }
  }

  /**
   * 新增或编辑成品委托生产订单
   */
  public function editDMOBill()
  {
    if (IS_POST) {
      $us = new UserService();

      $adding = I("post.adding");
      if ($adding == "1") {
        // 新建
        if (!$us->hasPermission(FIdConst::DMO_ADD)) {
          die("没有权限");
        }
      } else {
        // 编辑
        if (!$us->hasPermission(FIdConst::DMO_EDIT)) {
          die("没有权限");
        }
      }

      $json = I("post.jsonStr");
      $service = new DMOBillService();
      $this->ajaxReturn($service->editDMOBill($json));
    }
  }

  /**
   * 获得成品委托生产订单主表信息列表
   */
  public function dmobillList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::DMO)) {
        die("没有权限");
      }

      $params = [
        "billStatus" => I("post.billStatus"),
        "ref" => I("post.ref"),
        "fromDT" => I("post.fromDT"),
        "toDT" => I("post.toDT"),
        "factoryId" => I("post.factoryId"),
        "goodsId" => I("post.goodsId"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];

      $service = new DMOBillService();
      $this->ajaxReturn($service->dmobillList($params));
    }
  }

  /**
   * 获得成品委托生产订单的明细信息
   */
  public function dmoBillDetailList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::DMO)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new DMOBillService();
      $this->ajaxReturn($service->dmoBillDetailList($params));
    }
  }

  /**
   * 成品委托生产订单的入库情况列表
   */
  public function dmoBillDMWBillList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::DMO)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new DMOBillService();
      $this->ajaxReturn($service->dmoBillDMWBillList($params));
    }
  }

  /**
   * 删除成品委托生产订单
   */
  public function deleteDMOBill()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::DMO_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new DMOBillService();
      $this->ajaxReturn($service->deleteDMOBill($params));
    }
  }

  /**
   * 审核成品委托生产订单
   */
  public function commitDMOBill()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::DMO_COMMIT)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new DMOBillService();
      $this->ajaxReturn($service->commitDMOBill($params));
    }
  }

  /**
   * 取消审核成品委托生产订单
   */
  public function cancelConfirmDMOBill()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::DMO_COMMIT)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new DMOBillService();
      $this->ajaxReturn($service->cancelConfirmDMOBill($params));
    }
  }

  /**
   * 关闭成品委托生产订单
   */
  public function closeDMOBill()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::DMO_CLOSE_BILL)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new DMOBillService();
      $this->ajaxReturn($service->closeDMOBill($params));
    }
  }

  /**
   * 取消关闭成品委托生产订单
   */
  public function cancelClosedDMOBill()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::DMO_CLOSE_BILL)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new DMOBillService();
      $this->ajaxReturn($service->cancelClosedDMOBill($params));
    }
  }

  /**
   * 成品委托生产订单生成PDF文件
   */
  public function dmoBillPdf()
  {
    $us = new UserService();
    if (!$us->hasPermission(FIdConst::DMO_PDF)) {
      die("没有权限");
    }

    $params = [
      "ref" => I("get.ref")
    ];

    $ps = new DMOBillService();
    $ps->pdf($params);
  }

  /**
   * 生成打印成品委托生产订单的页面
   */
  public function genDMOBillPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::DMO_PRINT)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ss = new DMOBillService();
      $data = $ss->getDMOBillDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }
}
