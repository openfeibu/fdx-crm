<?php

namespace FEIBU0001\Controller;

use Home\Common\FIdConst;
use Home\Service\UserService;
use FEIBU0001\Service\ExpressService;
use Home\Controller\PSIBaseController;

/**
 * 仓库Controller
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class ExpressController extends PSIBaseController
{

 
  /**
   * 仓库自定义字段，查询数据
   */
  public function queryData()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission("ct20220531145123-dataorg")) {
        die("没有权限");
      }

      $queryKey = I("post.queryKey");
      $fid = I("post.fid");
      $express = new ExpressService();
      $this->ajaxReturn($express->queryData($queryKey, $fid));
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
      $express = new ExpressService();
      $this->ajaxReturn($express->editDataOrg($params));
    }
  }
}
