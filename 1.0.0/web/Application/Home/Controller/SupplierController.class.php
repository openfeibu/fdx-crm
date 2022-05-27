<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\SupplierService;
use Home\Service\UserService;

/**
 * 供应商档案Controller
 *
 * @author 艾格林门信息服务（大连）有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class SupplierController extends PSIBaseController
{

  /**
   * 供应商档案 - 主页面
   */
  public function index()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::SUPPLIER)) {
      $this->initVar();

      $this->assign("title", "供应商档案");

      // 按钮权限：新增供应商分类
      $this->assign("pAddCategory", $us->hasPermission(FIdConst::SUPPLIER_CATEGORY_ADD) ? 1 : 0);
      // 按钮权限：编辑供应商分类
      $this->assign("pEditCategory", $us->hasPermission(FIdConst::SUPPLIER_CATEGORY_EDIT) ? 1 : 0);
      // 按钮权限：删除供应商分类
      $this->assign("pDeleteCategory", $us->hasPermission(FIdConst::SUPPLIER_CATEGORY_DELETE) ? 1 : 0);
      // 按钮权限：新增供应商
      $this->assign("pAddSupplier", $us->hasPermission(FIdConst::SUPPLIER_ADD) ? 1 : 0);
      // 按钮权限：编辑供应商
      $this->assign("pEditSupplier", $us->hasPermission(FIdConst::SUPPLIER_EDIT) ? 1 : 0);
      // 按钮权限：删除供应商
      $this->assign("pDeleteSupplier", $us->hasPermission(FIdConst::SUPPLIER_DELETE) ? 1 : 0);

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Supplier/index");
    }
  }

  /**
   * 供应商分类
   */
  public function categoryList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SUPPLIER)) {
        die("没有权限");
      }

      $params = [
        "code" => I("post.code"),
        "name" => I("post.name"),
        "address" => I("post.address"),
        "contact" => I("post.contact"),
        "mobile" => I("post.mobile"),
        "tel" => I("post.tel"),
        "qq" => I("post.qq"),
        "recordStatus" => I("post.recordStatus")
      ];
      $ss = new SupplierService();
      $this->ajaxReturn($ss->categoryList($params));
    }
  }

  /**
   * 供应商档案列表
   */
  public function supplierList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SUPPLIER)) {
        die("没有权限");
      }

      $params = [
        "categoryId" => I("post.categoryId"),
        "code" => I("post.code"),
        "name" => I("post.name"),
        "address" => I("post.address"),
        "contact" => I("post.contact"),
        "mobile" => I("post.mobile"),
        "tel" => I("post.tel"),
        "qq" => I("post.qq"),
        "recordStatus" => I("post.recordStatus"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];
      $ss = new SupplierService();
      $this->ajaxReturn($ss->supplierList($params));
    }
  }

  /**
   * 新建或编辑供应商分类
   */
  public function editCategory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (I("post.id")) {
        // 编辑供应商分类
        if (!$us->hasPermission(FIdConst::SUPPLIER_CATEGORY_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新增供应商分类
        if (!$us->hasPermission(FIdConst::SUPPLIER_CATEGORY_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id"),
        "code" => strtoupper(I("post.code")),
        "name" => I("post.name")
      ];
      $ss = new SupplierService();
      $this->ajaxReturn($ss->editCategory($params));
    }
  }

  /**
   * 删除供应商分类
   */
  public function deleteCategory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SUPPLIER_CATEGORY_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $ss = new SupplierService();
      $this->ajaxReturn($ss->deleteCategory($params));
    }
  }

  /**
   * 新建或编辑供应商档案
   */
  public function editSupplier()
  {
    if (IS_POST) {
      $us = new UserService();
      if (I("post.id")) {
        // 编辑供应商档案
        if (!$us->hasPermission(FIdConst::SUPPLIER_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新增供应商档案
        if (!$us->hasPermission(FIdConst::SUPPLIER_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id"),
        "code" => strtoupper(I("post.code")),
        "name" => I("post.name"),
        "address" => I("post.address"),
        "addressShipping" => I("post.addressShipping"),
        "contact01" => I("post.contact01"),
        "mobile01" => I("post.mobile01"),
        "tel01" => I("post.tel01"),
        "qq01" => I("post.qq01"),
        "contact02" => I("post.contact02"),
        "mobile02" => I("post.mobile02"),
        "tel02" => I("post.tel02"),
        "qq02" => I("post.qq02"),
        "bankName" => I("post.bankName"),
        "bankAccount" => I("post.bankAccount"),
        "tax" => I("post.tax"),
        "fax" => I("post.fax"),
        "taxRate" => I("post.taxRate"),
        "note" => I("post.note"),
        "categoryId" => I("post.categoryId"),
        "initPayables" => I("post.initPayables"),
        "initPayablesDT" => I("post.initPayablesDT"),
        "recordStatus" => I("post.recordStatus"),
        "goodsRange" => I("post.goodsRange")
      ];
      $ss = new SupplierService();
      $this->ajaxReturn($ss->editSupplier($params));
    }
  }

  /**
   * 删除供应商档案
   */
  public function deleteSupplier()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SUPPLIER_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $ss = new SupplierService();
      $this->ajaxReturn($ss->deleteSupplier($params));
    }
  }

  /**
   * 供应商自定义字段，查询数据
   */
  public function queryData()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SUPPLIER_BILL)) {
        die("没有权限");
      }
      
      $queryKey = I("post.queryKey");
      $ss = new SupplierService();
      $this->ajaxReturn($ss->queryData($queryKey));
    }
  }

  /**
   * 获得某个供应商的信息
   */
  public function supplierInfo()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SUPPLIER)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $ss = new SupplierService();
      $this->ajaxReturn($ss->supplierInfo($params));
    }
  }

  /**
   * 关联物料 - 添加物料分类
   */
  public function addGRCategory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SUPPLIER)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "categoryId" => I("post.categoryId")
      ];
      $service = new SupplierService();
      $this->ajaxReturn($service->addGRCategory($params));
    }
  }

  /**
   * 关联物料 - 已经设置的物料分类
   */
  public function grCategoryList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SUPPLIER)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $service = new SupplierService();
      $this->ajaxReturn($service->grCategoryList($params));
    }
  }

  /**
   * 关联物料 - 移除物料分类
   */
  public function deleteGRCategory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SUPPLIER)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "idList" => I("post.idList")
      ];
      $service = new SupplierService();
      $this->ajaxReturn($service->deleteGRCategory($params));
    }
  }

  /**
   * 关联物料 - 已经设置的物料列表
   */
  public function grGoodsList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SUPPLIER)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $service = new SupplierService();
      $this->ajaxReturn($service->grGoodsList($params));
    }
  }

  /**
   * 关联物料 - 添加个别物料
   */
  public function addGRGoods()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SUPPLIER)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "goodsId" => I("post.goodsId")
      ];
      $service = new SupplierService();
      $this->ajaxReturn($service->addGRGoods($params));
    }
  }

  /**
   * 关联物料 - 移除物料
   */
  public function deleteGRGoods()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SUPPLIER)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "idList" => I("post.idList")
      ];
      $service = new SupplierService();
      $this->ajaxReturn($service->deleteGRGoods($params));
    }
  }
}
