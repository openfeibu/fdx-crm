<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\UserService;
use Home\Service\WarehouseService;

/**
 * 仓库Controller
 *
 * @author 艾格林门信息服务（大连）有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class WarehouseController extends PSIBaseController
{

  /**
   * 仓库 - 主页面
   */
  public function index()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::WAREHOUSE)) {
      $this->initVar();

      $this->assign("title", "仓库");

      // 按钮权限： 新增仓库
      $this->assign("pAdd", $us->hasPermission(FIdConst::WAREHOUSE_ADD) ? 1 : 0);

      // 按钮权限：编辑仓库
      $this->assign("pEdit", $us->hasPermission(FIdConst::WAREHOUSE_EDIT) ? 1 : 0);

      // 按钮权限：删除仓库
      $this->assign("pDelete", $us->hasPermission(FIdConst::WAREHOUSE_DELETE) ? 1 : 0);

      // 按钮权限：编辑仓库数据域
      $this->assign("pEditDataOrg", $us->hasPermission(FIdConst::WAREHOUSE_EDIT_DATAORG) ? 1 : 0);

      // 按钮权限：打开库存建账模块
      $this->assign("pInitInv", $us->hasPermission(FIdConst::INVENTORY_INIT) ? 1 : 0);

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Warehouse/index");
    }
  }

  /**
   * 仓库列表
   */
  public function warehouseList()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::WAREHOUSE)) {
        die("没有权限");
      }

      $ws = new WarehouseService();
      $this->ajaxReturn($ws->warehouseList());
    }
  }

  /**
   * 新增或编辑仓库
   */
  public function editWarehouse()
  {
    if (IS_POST) {
      $us = new UserService();
      if (I("post.id")) {
        // 编辑仓库
        if (!$us->hasPermission(FIdConst::WAREHOUSE_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新增仓库
        if (!$us->hasPermission(FIdConst::WAREHOUSE_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id"),
        "code" => strtoupper(I("post.code")),
        "name" => I("post.name"),
        "orgId" => I("post.orgId"),
        "saleArea" => I("post.saleArea"),
        "enabled" => I("post.enabled"),
        "usageType" => I("post.usageType"),
        "limitGoods" => I("post.limitGoods"),
      ];
      $ws = new WarehouseService();
      $this->ajaxReturn($ws->editWarehouse($params));
    }
  }

  /**
   * 删除仓库
   */
  public function deleteWarehouse()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::WAREHOUSE_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $ws = new WarehouseService();
      $this->ajaxReturn($ws->deleteWarehouse($params));
    }
  }

  /**
   * 仓库自定义字段，查询数据
   */
  public function queryData()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::WAREHOUSE_BILL)) {
        die("没有权限");
      }

      $queryKey = I("post.queryKey");
      $fid = I("post.fid");
      $ws = new WarehouseService();
      $this->ajaxReturn($ws->queryData($queryKey, $fid));
    }
  }

  /**
   * 修改数据域
   */
  public function editDataOrg()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::WAREHOUSE_EDIT_DATAORG)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "dataOrg" => I("post.dataOrg")
      ];
      $ws = new WarehouseService();
      $this->ajaxReturn($ws->editDataOrg($params));
    }
  }
}
