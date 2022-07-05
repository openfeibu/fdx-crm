<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\UserService;
use Home\Service\FactoryService;

/**
 * 工厂Controller
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class FactoryController extends PSIBaseController
{

  /**
   * 工厂 - 主页面
   */
  public function index()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::FACTORY)) {
      $this->initVar();

      // 按钮权限：新增工厂分类
      $this->assign("pAddCategory", $us->hasPermission(FIdConst::FACTORY_CATEGORY_ADD) ? 1 : 0);
      // 按钮权限：编辑工厂分类
      $this->assign("pEditCategory", $us->hasPermission(FIdConst::FACTORY_CATEGORY_EDIT) ? 1 : 0);
      // 按钮权限：删除工厂分类
      $this->assign("pDeleteCategory", $us->hasPermission(FIdConst::FACTORY_CATEGORY_DELETE) ? 1 : 0);
      // 按钮权限：新增工厂
      $this->assign("pAdd", $us->hasPermission(FIdConst::FACTORY_ADD) ? 1 : 0);
      // 按钮权限：编辑工厂
      $this->assign("pEdit", $us->hasPermission(FIdConst::FACTORY_EDIT) ? 1 : 0);
      // 按钮权限：删除工厂
      $this->assign("pDelete", $us->hasPermission(FIdConst::FACTORY_DELETE) ? 1 : 0);

      $this->assign("title", "工厂");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Factory/index");
    }
  }

  /**
   * 工厂分类
   */
  public function categoryList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::FACTORY_CATEGORY)) {
        die("没有权限");
      }

      $params = [
        "code" => I("post.code"),
        "name" => I("post.name"),
        "address" => I("post.address"),
        "contact" => I("post.contact"),
        "mobile" => I("post.mobile"),
        "tel" => I("post.tel"),
        "recordStatus" => I("post.recordStatus")
      ];
      $service = new FactoryService();
      $this->ajaxReturn($service->categoryList($params));
    }
  }

  /**
   * 新建或编辑工厂分类
   */
  public function editCategory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (I("post.id")) {
        // 编辑工厂分类
        if (!$us->hasPermission(FIdConst::FACTORY_CATEGORY_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新增工厂分类
        if (!$us->hasPermission(FIdConst::FACTORY_CATEGORY_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id"),
        "code" => strtoupper(I("post.code")),
        "name" => I("post.name")
      ];

      $service = new FactoryService();
      $this->ajaxReturn($service->editCategory($params));
    }
  }

  /**
   * 删除工厂分类
   */
  public function deleteCategory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::FACTORY_CATEGORY_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new FactoryService();
      $this->ajaxReturn($service->deleteCategory($params));
    }
  }

  /**
   * 工厂列表
   */
  public function factoryList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::FACTORY)) {
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
        "recordStatus" => I("post.recordStatus"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];
      $service = new FactoryService();
      $this->ajaxReturn($service->factoryList($params));
    }
  }

  /**
   * 新建或编辑工厂
   */
  public function editFactory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (I("post.id")) {
        // 编辑工厂
        if (!$us->hasPermission(FIdConst::FACTORY_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新增工厂
        if (!$us->hasPermission(FIdConst::FACTORY_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id"),
        "code" => strtoupper(I("post.code")),
        "name" => I("post.name"),
        "address" => I("post.address"),
        "contact01" => I("post.contact01"),
        "mobile01" => I("post.mobile01"),
        "tel01" => I("post.tel01"),
        "contact02" => I("post.contact02"),
        "mobile02" => I("post.mobile02"),
        "tel02" => I("post.tel02"),
        "bankName" => I("post.bankName"),
        "bankAccount" => I("post.bankAccount"),
        "tax" => I("post.tax"),
        "fax" => I("post.fax"),
        "note" => I("post.note"),
        "categoryId" => I("post.categoryId"),
        "initPayables" => I("post.initPayables"),
        "initPayablesDT" => I("post.initPayablesDT"),
        "recordStatus" => I("post.recordStatus")
      ];
      $service = new FactoryService();
      $this->ajaxReturn($service->editFactory($params));
    }
  }

  /**
   * 获得某个工厂的信息
   */
  public function factoryInfo()
  {
    if (IS_POST) {
      $us = new UserService();
      $id = I("post.id");
      if ($id) {
        // 编辑
        if (!$us->hasPermission(FIdConst::FACTORY_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新建
        if (!$us->hasPermission(FIdConst::FACTORY_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new FactoryService();
      $this->ajaxReturn($service->factoryInfo($params));
    }
  }

  /**
   * 删除工厂
   */
  public function deleteFactory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::FACTORY_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new FactoryService();
      $this->ajaxReturn($service->deleteFactory($params));
    }
  }

  /**
   * 工厂自定义字段，查询数据
   */
  public function queryData()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::FACTORY_BILL)) {
        die("没有权限");
      }

      $queryKey = I("post.queryKey");
      $service = new FactoryService();
      $this->ajaxReturn($service->queryData($queryKey));
    }
  }
}
