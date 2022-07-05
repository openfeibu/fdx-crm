<?php

namespace Home\Service;

use Home\DAO\HelpDAO;

/**
 * 指南Service
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class HelpService extends PSIBaseExService
{
  /**
   * 获得某个指南页面的导航信息
   */
  public function getNav($params)
  {
    $dao = new HelpDAO($this->db());

    return $dao->getNav($params);
  }
}
