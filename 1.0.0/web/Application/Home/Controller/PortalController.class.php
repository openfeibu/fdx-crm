<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\PortalService;

/**
 * Portal Controller
 *
 * @author 艾格林门信息服务（大连）有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class PortalController extends Controller
{

  /**
   * 库存看板
   */
  public function inventoryPortal()
  {
    if (IS_POST) {
      $ps = new PortalService();

      $this->ajaxReturn($ps->inventoryPortal());
    }
  }

  /**
   * 销售看板
   */
  public function salePortal()
  {
    if (IS_POST) {
      $ps = new PortalService();

      $this->ajaxReturn($ps->salePortal());
    }
  }

  /**
   * 采购看板
   */
  public function purchasePortal()
  {
    if (IS_POST) {
      $ps = new PortalService();

      $this->ajaxReturn($ps->purchasePortal());
    }
  }

  /**
   * 资金看板
   */
  public function moneyPortal()
  {
    if (IS_POST) {
      $ps = new PortalService();

      $this->ajaxReturn($ps->moneyPortal());
    }
  }
}
