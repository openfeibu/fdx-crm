<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\BizlogService;
use Home\Service\FIdService;
use Home\Service\MainMenuService;
use Home\Service\UserService;

/**
 * 主菜单Controller
 *
 * @author 艾格林门信息服务（大连）有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class MainMenuController extends PSIBaseController
{

  /**
   * 页面跳转
   */
  public function navigateTo()
  {
    $this->assign("uri", __ROOT__ . "/");

    $fid = I("get.fid");

    // $t == 1的时候，是从常用功能链接点击而来的
    // $t == 2的时候，是从快捷访问而来
    $t = I("get.t");

    $fidService = new FIdService();
    $fidService->insertRecentFid($fid);
    $fidName = $fidService->getFIdName($fid);
    if ($fidName) {
      // 记录业务日志

      $bizLogService = new BizlogService();

      if ($t == "1") {
        $bizLogService->insertBizlog("通过常用功能进入模块：" . $fidName, "常用功能");
      } else if ($t == "2") {
        $bizLogService->insertBizlog("通过快捷访问进入模块：" . $fidName, "快捷访问");
      } else {
        $bizLogService->insertBizlog("通过主菜单进入模块：" . $fidName);
      }
    }
    if (!$fid) {
      redirect(__ROOT__ . "/Home");
    }

    if (substr($fid, 0, 2) == "ct") {
      // 码表 - 运行期
      redirect(__ROOT__ . "/Home/CodeTableRuntime/index/fid/{$fid}");
    } else if (substr($fid, 0, 2) == "fm") {
      // 自定义表单 - 运行期
      redirect(__ROOT__ . "/Home/Form/run?fid={$fid}");
    } else {
      // 以下是系统固有模块
      if ($fid == FIdConst::RELOGIN) {
        // 重新登录
        $us = new UserService();
        $us->clearLoginUserInSession();
        redirect(__ROOT__ . "/Home");
      } else {
        $ms = new MainMenuService();
        $params = [
          "fid" => $fid
        ];
        $nav = $ms->getNav($params);
        if ($nav) {
          $url = $nav["url"];
          redirect(__ROOT__ . $url);
        } else {
          // 最后的异常处理，都跳转到首页
          redirect(__ROOT__ . "/Home");
        }
      }
    }
  }

  /**
   * 返回生成主菜单的JSON数据
   * 目前只能处理到生成三级菜单的情况
   * 
   * JS: web\Public\Scripts\PSI\App.js
   */
  public function mainMenuItems()
  {
    if (IS_POST) {
      $ms = new MainMenuService();

      $this->ajaxReturn($ms->mainMenuItems());
    }
  }

  /**
   * 常用功能
   * 
   * JS: web\Public\Scripts\PSI\App.js
   */
  public function recentFid()
  {
    if (IS_POST) {
      $fidService = new FIdService();
      $data = $fidService->recentFid();

      $this->ajaxReturn($data);
    }
  }

  /**
   * Fid自定义字段 - 查询数据
   * 
   * JS: web\Public\Scripts\PSI\Fid\FidField.js
   */
  public function queryDataForFid()
  {
    if (IS_POST) {
      $params = [
        "queryKey" => I("post.queryKey")
      ];

      $service = new MainMenuService();
      $this->ajaxReturn($service->queryDataForFid($params));
    }
  }

  /**
   * 菜单项自定义字段 - 查询数据
   * 
   * JS: web\Public\Scripts\PSI\MainMenu\MenuItemField.js
   */
  public function queryDataForMenuItem()
  {
    if (IS_POST) {
      $params = [
        "queryKey" => I("post.queryKey")
      ];

      $service = new MainMenuService();
      $this->ajaxReturn($service->queryDataForMenuItem($params));
    }
  }

  /**
   * 菜单项快捷访问自定义字段 - 查询数据
   * 
   * JS: web\Public\Scripts\PSI\MainMenu\ShortcutField.js
   */
  public function queryDataForShortcut()
  {
    if (IS_POST) {
      $params = [
        "queryKey" => I("post.queryKey")
      ];

      $service = new MainMenuService();
      $this->ajaxReturn($service->queryDataForShortcut($params));
    }
  }

  /**
   * 主菜单维护 - 主界面
   * 
   * web\Application\Home\View\MainMenu\maintainIndex.html
   */
  public function maintainIndex()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::MAIN_MENU)) {
      $this->initVar();

      $this->assign("title", "主菜单维护");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/MainMenu/maintainIndex");
    }
  }

  /**
   * 查询所有的主菜单项 - 主菜单维护模块中使用
   * 
   * JS: web\Public\Scripts\PSI\MainMenu\MainForm.js
   */
  public function allMenuItemsForMaintain()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::MAIN_MENU)) {
        die("没有权限");
      }

      $params = [];
      $service = new MainMenuService();
      $this->ajaxReturn($service->allMenuItemsForMaintain($params));
    }
  }

  /**
   * 主菜单维护 - 新增或编辑菜单项
   * 
   * JS: web\Public\Scripts\PSI\MainMenu\MenuItemEditForm.js
   */
  public function editMenuItem()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::MAIN_MENU)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "fid" => I("post.fid"),
        "caption" => I("post.caption"),
        "parentMenuId" => I("post.parentMenuId"),
        "showOrder" => I("post.showOrder")
      ];

      $service = new MainMenuService();
      $this->ajaxReturn($service->editMenuItem($params));
    }
  }

  /**
   * 删除菜单项
   * 
   * JS: web\Public\Scripts\PSI\MainMenu\MainForm.js
   */
  public function deleteMenuItem()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::MAIN_MENU)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new MainMenuService();
      $this->ajaxReturn($service->deleteMenuItem($params));
    }
  }

  /**
   * 某个菜单项的详情信息
   * 
   * JS: web\Public\Scripts\PSI\MainMenu\MenuItemEditForm.js
   */
  public function menuItemInfo()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::MAIN_MENU)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new MainMenuService();
      $this->ajaxReturn($service->menuItemInfo($params));
    }
  }

  /**
   * 主菜单数据导出SQL
   * 
   * JS： web\Public\Scripts\PSI\MainMenu\GenSQLForm.js
   */
  public function genSQL()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::MAIN_MENU)) {
        die("没有权限");
      }

      $service = new MainMenuService();
      $this->ajaxReturn($service->genSQL());
    }
  }
}
