<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\PortalService;

/**
 * Portal Controller
 *
 * @author 广州飞步信息科技有限公司
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
      $params = [
	      "type" => I("post.type"), //
      ];
      $this->ajaxReturn($ps->salePortal($params));
    }
  }

  /**
   * 采购看板
   */
  public function purchasePortal()
  {
    if (IS_POST) {
      $ps = new PortalService();
	    $params = [
		    "type" => I("post.type"), //
	    ];
      $this->ajaxReturn($ps->purchasePortal($params));
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
  
  /**
   * 销售简要统计（当日，当月，当年，全部）
   */

	public function saleBriefPortal()
	{
		if (IS_POST) {
			$ps = new PortalService();
			
			$this->ajaxReturn($ps->saleBriefPortal());
		}
	}
	
	/**
	 * 销售出库量简要统计（当日，当月，当年，全部）
	 */
	
	public function saleCntBriefPortal()
	{
		if (IS_POST) {
			$ps = new PortalService();
			
			$this->ajaxReturn($ps->saleCntBriefPortal());
		}
	}
	
	public function saleTopPortal()
	{
		if (IS_POST) {
			$ps = new PortalService();
			
			$this->ajaxReturn($ps->saleTopPortal());
		}
	}
}
