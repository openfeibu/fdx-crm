<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\SOBillService;
use Home\Service\UserService;
use Home\Service\WSBillService;

/**
 * 销售订单Controller
 *
 * @author 艾格林门信息服务（大连）有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class SaleOrderController extends PSIBaseController
{

  /**
   * 销售订单 - 主页面
   */
  public function soIndex()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::SALE_ORDER)) {
      $this->initVar();

      $this->assign("title", "销售订单");

      // 按钮权限：审核、取消审核
      $this->assign("pConfirm", $us->hasPermission(FIdConst::SALE_ORDER_CONFIRM) ? "1" : "0");
      // 按钮权限：生成销售出库单
      $this->assign(
        "pGenWSBill",
        $us->hasPermission(FIdConst::SALE_ORDER_GEN_WSBILL) ? "1" : "0"
      );
      // 按钮权限：生成采购订单
      $this->assign(
        "pGenPOBill",
        $us->hasPermission(FIdConst::SALE_ORDER_GEN_POBILL) ? "1" : "0"
      );
      // 按钮权限：新建销售订单
      $this->assign("pAdd", $us->hasPermission(FIdConst::SALE_ORDER_ADD) ? "1" : "0");
      // 按钮权限：编辑销售订单
      $this->assign("pEdit", $us->hasPermission(FIdConst::SALE_ORDER_EDIT) ? "1" : "0");
      // 按钮权限：删除销售订单
      $this->assign("pDelete", $us->hasPermission(FIdConst::SALE_ORDER_DELETE) ? "1" : "0");
      // 按钮权限：单据生成PDF
      $this->assign("pGenPDF", $us->hasPermission(FIdConst::SALE_ORDER_PDF) ? "1" : "0");
      // 按钮权限：打印预览、直接打印
      $this->assign("pPrint", $us->hasPermission(FIdConst::SALE_ORDER_PRINT) ? "1" : "0");
      // 按钮权限：关闭订单、取消订单关闭状态
      $this->assign(
        "pCloseBill",
        $us->hasPermission(FIdConst::SALE_ORDER_CLOSE_BILL) ? "1" : "0"
      );
      // 按钮权限：新建或编辑销售订单时，录入物料弹出窗体中的新增物料按钮
      $this->assign("showAddGoodsButton", $us->hasPermission(FIdConst::GOODS_ADD) ? "1" : "0");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Sale/soIndex");
    }
  }

  /**
   * 获得销售订单主表信息列表
   */
  public function sobillList()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::SALE_ORDER)) {
        die("没有权限");
      }

      $ps = new SOBillService();
      $params = [
        "billStatus" => I("post.billStatus"),
        "ref" => I("post.ref"),
        "fromDT" => I("post.fromDT"),
        "toDT" => I("post.toDT"),
        "customerId" => I("post.customerId"),
        "receivingType" => I("post.receivingType"),
        "goodsId" => I("post.goodsId"),
        "userId" => I("post.userId"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];
      $this->ajaxReturn($ps->sobillList($params));
    }
  }

  /**
   * 获得销售订单的信息
   */
  public function soBillInfo()
  {
    if (IS_POST) {
      $us = new UserService();

      $id = I("post.id");
      if ($id) {
        // 编辑
        if (!$us->hasPermission(FIdConst::SALE_ORDER_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新建
        if (!$us->hasPermission(FIdConst::SALE_ORDER_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id"),
        "genBill" => I("post.genBill"),
        "scbillRef" => I("post.scbillRef")
      ];

      $ps = new SOBillService();
      $this->ajaxReturn($ps->soBillInfo($params));
    }
  }

  /**
   * 新增或编辑销售订单
   */
  public function editSOBill()
  {
    if (IS_POST) {
      $us = new UserService();

      $adding = I("post.adding");
      if ($adding == "1") {
        // 新建
        if (!$us->hasPermission(FIdConst::SALE_ORDER_ADD)) {
          die("没有权限");
        }
      } else {
        // 编辑
        if (!$us->hasPermission(FIdConst::SALE_ORDER_EDIT)) {
          die("没有权限");
        }
      }
      $json = I("post.jsonStr");
      $ps = new SOBillService();
      $this->ajaxReturn($ps->editSOBill($json));
    }
  }

  /**
   * 获得销售订单的明细信息
   */
  public function soBillDetailList()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::SALE_ORDER)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ps = new SOBillService();
      $this->ajaxReturn($ps->soBillDetailList($params));
    }
  }

  /**
   * 删除销售订单
   */
  public function deleteSOBill()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::SALE_ORDER_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ps = new SOBillService();
      $this->ajaxReturn($ps->deleteSOBill($params));
    }
  }

  /**
   * 审核销售订单
   */
  public function commitSOBill()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::SALE_ORDER_CONFIRM)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ps = new SOBillService();
      $this->ajaxReturn($ps->commitSOBill($params));
    }
  }

  /**
   * 取消销售订单审核
   */
  public function cancelConfirmSOBill()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::SALE_ORDER_CONFIRM)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ps = new SOBillService();
      $this->ajaxReturn($ps->cancelConfirmSOBill($params));
    }
  }

  /**
   * 销售订单 - 订单变更
   */
  public function changeSaleOrder()
  {
    if (IS_POST) {
      $us = new UserService();

      // TODO 新增一个单独的权限项，目前临时使用审核这个权限项
      if (!$us->hasPermission(FIdConst::SALE_ORDER_CONFIRM)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "goodsCount" => I("post.goodsCount"),
        "goodsPrice" => I("post.goodsPrice")
      ];

      $ps = new SOBillService();
      $this->ajaxReturn($ps->changeSaleOrder($params));
    }
  }

  /**
   * 查询主表金额相关数据 - 订单变更后刷新界面用
   */
  public function getSOBillDataAterChangeOrder()
  {
    if (IS_POST) {
      $us = new UserService();

      // TODO 新增一个单独的权限项，目前临时使用审核这个权限项
      if (!$us->hasPermission(FIdConst::SALE_ORDER_CONFIRM)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ps = new SOBillService();
      $this->ajaxReturn($ps->getSOBillDataAterChangeOrder($params));
    }
  }

  /**
   * 销售订单生成pdf文件
   */
  public function soBillPdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::SALE_ORDER_PDF)) {
      die("没有权限");
    }

    $params = [
      "ref" => I("get.ref")
    ];

    $ws = new SOBillService();
    $ws->pdf($params);
  }

  /**
   * 查询销售订单出库情况
   */
  public function soBillWSBillList()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::SALE_ORDER)) {
        die("没有权限");
      }

      $soBillId = I("post.id");

      $ws = new WSBillService();
      $this->ajaxReturn($ws->soBillWSBillList($soBillId));
    }
  }

  /**
   * 关闭销售订单
   */
  public function closeSOBill()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::SALE_ORDER_CLOSE_BILL)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new SOBillService();
      $this->ajaxReturn($service->closeSOBill($params));
    }
  }

  /**
   * 取消订单关闭状态
   */
  public function cancelClosedSOBill()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::SALE_ORDER_CLOSE_BILL)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new SOBillService();
      $this->ajaxReturn($service->cancelClosedSOBill($params));
    }
  }

  /**
   * 根据销售订单单号查询其生成的采购订单
   * 用于：在销售订单生成采购订单之前，提醒用户
   */
  public function getPOBillRefListBySOBillRef()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SALE_ORDER_GEN_POBILL)) {
        die("没有权限");
      }

      $params = [
        "soRef" => I("post.soRef")
      ];

      $service = new SOBillService();
      $this->ajaxReturn($service->getPOBillRefListBySOBillRef($params));
    }
  }

  /**
   * 根据销售订单单号查询其生成的销售出库单
   * 用于：在销售订单生成销售出库单之前，提醒用户
   */
  public function getWSBillRefListBySOBillRef()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SALE_ORDER_GEN_WSBILL)) {
        die("没有权限");
      }

      $params = [
        "soRef" => I("post.soRef")
      ];

      $service = new SOBillService();
      $this->ajaxReturn($service->getWSBillRefListBySOBillRef($params));
    }
  }

  /**
   * 生成打印销售订单的页面
   */
  public function genSOBillPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::SALE_ORDER_PRINT)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $ss = new SOBillService();
      $data = $ss->getSOBillDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }
}
