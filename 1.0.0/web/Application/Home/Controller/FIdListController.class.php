<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\FIdListService;
use Home\Service\UserService;

/**
 * FId一览Controller
 *
 * @author 艾格林门信息服务（大连）有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class FIdListController extends PSIBaseController
{
  /**
   * FId一览 - 主页面
   * 
   * web\Application\Home\View\FIdList\index.html
   */
  public function index()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::FID_LIST)) {
      $this->initVar();

      $this->assign("title", "FId一览");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/FIdList");
    }
  }

  /**
   * 查询全部FId数据
   */
  public function fidList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::FID_LIST)) {
        die("没有权限");
      }


      $service = new FIdListService();
      $this->ajaxReturn($service->fidList());
    }
  }

  /**
   * 编辑fid
   */
  public function editFId()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::FID_LIST)) {
        die("没有权限");
      }

      $params = [
        "fid" => I("post.fid"),
        "code" => I("post.code"),
        "py" => I("post.py"),
      ];

      $service = new FIdListService();
      $this->ajaxReturn($service->editFId($params));
    }
  }
}
