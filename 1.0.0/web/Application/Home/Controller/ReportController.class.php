<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\InventoryReportService;
use Home\Service\PayablesReportService;
use Home\Service\PurchaseReportService;
use Home\Service\ReceivablesReportService;
use Home\Service\SaleReportService;
use Home\Service\UserService;

/**
 * 报表Controller
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class ReportController extends PSIBaseController
{
	
	/**
	 * 概览
	 */
	public function overview()
	{
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::OVERVIEW)) {
			$this->initVar();
			
			$this->assign("title", "数据概览");
			
			$this->assign("pSale", $us->hasPermission(FIdConst::PORTAL_SALE) ? 1 : 0);
			$this->assign("pInventory", $us->hasPermission(FIdConst::PORTAL_INVENTORY) ? 1 : 0);
			$this->assign("pPurchase", $us->hasPermission(FIdConst::PORTAL_PURCHASE) ? 1 : 0);
			$this->assign("pMoney", $us->hasPermission(FIdConst::PORTAL_MONEY) ? 1 : 0);
			$this->assign("pSaleBrief", $us->hasPermission(FIdConst::PORTAL_SALE_BRIEF) ? 1 : 0);
			$this->assign("pSaleCntBrief", $us->hasPermission(FIdConst::PORTAL_SALE_CNT_BRIEF) ? 1 : 0);
			$this->assign("pSaleTop", $us->hasPermission(FIdConst::PORTAL_SALE_TOP) ? 1 : 0);
			
			$this->display();
		}else {
			$this->gotoLoginPage("/Home/Report/overview");
		}
	
	}
  /**
   * 销售日报表(按商品汇总)
   */
  public function saleDayByGoods()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_GOODS)) {
      $this->initVar();

      $this->assign("title", "销售日报表(按商品汇总)");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/saleDayByGoods");
    }
  }

  /**
   * 销售日报表(按商品汇总) - 查询数据
   */
  public function saleDayByGoodsQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_GOODS)) {
        die("没有权限");
      }

      $params = [
        "dt" => I("post.dt"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleDayByGoodsQueryData($params));
    }
  }

  /**
   * 销售日报表(按商品汇总) - 查询汇总数据
   */
  public function saleDayByGoodsSummaryQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_GOODS)) {
        die("没有权限");
      }

      $params = [
        "dt" => I("post.dt")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleDayByGoodsSummaryQueryData($params));
    }
  }

  /**
   * 销售日报表(按商品汇总) - 生成打印页面
   */
  public function genSaleDayByGoodsPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_GOODS)) {
        die("没有权限");
      }

      $params = [
        "dt" => I("post.dt"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $service = new SaleReportService();
      $data = $service->getSaleDayByGoodsDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 销售日报表(按商品汇总) - 生成PDF文件
   */
  public function saleDayByGoodsPdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_GOODS)) {
      die("没有权限");
    }

    $params = [
      "dt" => I("get.dt"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleDayByGoodsPdf($params);
  }

  /**
   * 销售日报表(按商品汇总) - 生成Excel文件
   */
  public function saleDayByGoodsExcel()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_GOODS)) {
      die("没有权限");
    }

    $params = [
      "dt" => I("get.dt"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleDayByGoodsExcel($params);
  }

  /**
   * 销售日报表(按客户汇总)
   */
  public function saleDayByCustomer()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_CUSTOMER)) {
      $this->initVar();

      $this->assign("title", "销售日报表(按客户汇总)");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/saleDayByCustomer");
    }
  }

  /**
   * 销售日报表(按客户汇总) - 查询数据
   */
  public function saleDayByCustomerQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_CUSTOMER)) {
        die("没有权限");
      }

      $params = [
        "dt" => I("post.dt"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleDayByCustomerQueryData($params));
    }
  }

  /**
   * 销售日报表(按客户汇总) - 查询汇总数据
   */
  public function saleDayByCustomerSummaryQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_CUSTOMER)) {
        die("没有权限");
      }

      $params = [
        "dt" => I("post.dt")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleDayByCustomerSummaryQueryData($params));
    }
  }

  /**
   * 销售日报表(按客户汇总) - 生成打印页面
   */
  public function genSaleDayByCustomerPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_CUSTOMER)) {
        die("没有权限");
      }

      $params = [
        "dt" => I("post.dt"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $service = new SaleReportService();
      $data = $service->getSaleDayByCustomerDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 销售日报表(按客户汇总) - 生成PDF文件
   */
  public function saleDayByCustomerPdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_CUSTOMER)) {
      die("没有权限");
    }

    $params = [
      "dt" => I("get.dt"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleDayByCustomerPdf($params);
  }

  /**
   * 销售日报表(按客户汇总) - 生成ExcelF文件
   */
  public function saleDayByCustomerExcel()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_CUSTOMER)) {
      die("没有权限");
    }

    $params = [
      "dt" => I("get.dt"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleDayByCustomerExcel($params);
  }

  /**
   * 销售日报表(按仓库汇总)
   */
  public function saleDayByWarehouse()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_WAREHOUSE)) {
      $this->initVar();

      $this->assign("title", "销售日报表(按仓库汇总)");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/saleDayByWarehouse");
    }
  }

  /**
   * 销售日报表(按仓库汇总) - 查询数据
   */
  public function saleDayByWarehouseQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_WAREHOUSE)) {
        die("没有权限");
      }

      $params = [
        "dt" => I("post.dt"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleDayByWarehouseQueryData($params));
    }
  }

  /**
   * 销售日报表(按仓库汇总) - 查询汇总数据
   */
  public function saleDayByWarehouseSummaryQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_WAREHOUSE)) {
        die("没有权限");
      }

      $params = [
        "dt" => I("post.dt")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleDayByWarehouseSummaryQueryData($params));
    }
  }

  /**
   * 销售日报表(按仓库汇总) - 生成打印页面
   */
  public function genSaleDayByWarehousePrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_WAREHOUSE)) {
        die("没有权限");
      }

      $params = [
        "dt" => I("post.dt"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $service = new SaleReportService();
      $data = $service->getSaleDayByWarehouseDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 销售日报表(按仓库汇总) - 生成PDF文件
   */
  public function saleDayByWarehousePdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_WAREHOUSE)) {
      die("没有权限");
    }

    $params = [
      "dt" => I("get.dt"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleDayByWarehousePdf($params);
  }

  /**
   * 销售日报表(按仓库汇总) - 生成Excel文件
   */
  public function saleDayByWarehouseExcel()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_WAREHOUSE)) {
      die("没有权限");
    }

    $params = [
      "dt" => I("get.dt"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleDayByWarehouseExcel($params);
  }

  /**
   * 销售日报表(按业务员汇总)
   */
  public function saleDayByBizuser()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_BIZUSER)) {
      $this->initVar();

      $this->assign("title", "销售日报表(按业务员汇总)");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/saleDayByBizuser");
    }
  }

  /**
   * 销售日报表(按业务员汇总) - 查询数据
   */
  public function saleDayByBizuserQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_BIZUSER)) {
        die("没有权限");
      }

      $params = [
        "dt" => I("post.dt"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleDayByBizuserQueryData($params));
    }
  }

  /**
   * 销售日报表(按业务员汇总) - 查询汇总数据
   */
  public function saleDayByBizuserSummaryQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_BIZUSER)) {
        die("没有权限");
      }

      $params = [
        "dt" => I("post.dt")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleDayByBizuserSummaryQueryData($params));
    }
  }

  /**
   * 销售日报表(按业务员汇总) - 生成打印页面
   */
  public function genSaleDayByBizuserPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_BIZUSER)) {
        die("没有权限");
      }

      $params = [
        "dt" => I("post.dt"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $service = new SaleReportService();
      $data = $service->getSaleDayByBizuserDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 销售日报表(按业务员汇总) - 生成PDF文件
   */
  public function saleDayByBizuserPdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_BIZUSER)) {
      die("没有权限");
    }

    $params = [
      "dt" => I("get.dt"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleDayByBizuserPdf($params);
  }

  /**
   * 销售日报表(按业务员汇总) - 生成Excel文件
   */
  public function saleDayByBizuserExcel()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_DAY_BY_BIZUSER)) {
      die("没有权限");
    }

    $params = [
      "dt" => I("get.dt"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleDayByBizuserExcel($params);
  }

  /**
   * 销售月报表(按商品汇总)
   */
  public function saleMonthByGoods()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_GOODS)) {
      $this->initVar();

      $this->assign("title", "销售月报表(按商品汇总)");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/saleMonthByGoods");
    }
  }

  /**
   * 销售月报表(按商品汇总) - 查询数据
   */
  public function saleMonthByGoodsQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_GOODS)) {
        die("没有权限");
      }

      $params = [
        "year" => I("post.year"),
        "month" => I("post.month"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleMonthByGoodsQueryData($params));
    }
  }

  /**
   * 销售月报表(按商品汇总) - 查询汇总数据
   */
  public function saleMonthByGoodsSummaryQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_GOODS)) {
        die("没有权限");
      }

      $params = [
        "year" => I("post.year"),
        "month" => I("post.month")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleMonthByGoodsSummaryQueryData($params));
    }
  }

  /**
   * 销售月报表(按商品汇总) - 生成打印页面
   */
  public function genSaleMonthByGoodsPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_GOODS)) {
        die("没有权限");
      }

      $params = [
        "year" => I("post.year"),
        "month" => I("post.month"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $service = new SaleReportService();
      $data = $service->getSaleMonthByGoodsDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 销售月报表(按商品汇总) - 生成PDF文件
   */
  public function saleMonthByGoodsPdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_GOODS)) {
      die("没有权限");
    }

    $params = [
      "year" => I("get.year"),
      "month" => I("get.month"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleMonthByGoodsPdf($params);
  }

  /**
   * 销售月报表(按商品汇总) - 生成Excel文件
   */
  public function saleMonthByGoodsExcel()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_GOODS)) {
      die("没有权限");
    }

    $params = [
      "year" => I("get.year"),
      "month" => I("get.month"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleMonthByGoodsExcel($params);
  }

  /**
   * 销售月报表(按客户汇总)
   */
  public function saleMonthByCustomer()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_CUSTOMER)) {
      $this->initVar();

      $this->assign("title", "销售月报表(按客户汇总)");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/saleMonthByCustomer");
    }
  }

  /**
   * 销售月报表(按客户汇总) - 查询数据
   */
  public function saleMonthByCustomerQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_CUSTOMER)) {
        die("没有权限");
      }

      $params = [
        "year" => I("post.year"),
        "month" => I("post.month"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleMonthByCustomerQueryData($params));
    }
  }

  /**
   * 销售月报表(按客户汇总) - 查询汇总数据
   */
  public function saleMonthByCustomerSummaryQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_CUSTOMER)) {
        die("没有权限");
      }

      $params = [
        "year" => I("post.year"),
        "month" => I("post.month")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleMonthByCustomerSummaryQueryData($params));
    }
  }

  /**
   * 销售月报表(按客户汇总) - 生成打印页面
   */
  public function genSaleMonthByCustomerPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_CUSTOMER)) {
        die("没有权限");
      }

      $params = [
        "year" => I("post.year"),
        "month" => I("post.month"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $service = new SaleReportService();
      $data = $service->getSaleMonthByCustomerDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 销售月报表(按客户汇总) - 生成PDF文件
   */
  public function saleMonthByCustomerPdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_CUSTOMER)) {
      die("没有权限");
    }

    $params = [
      "year" => I("get.year"),
      "month" => I("get.month"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleMonthByCustomerPdf($params);
  }

  /**
   * 销售月报表(按客户汇总) - 生成Excel文件
   */
  public function saleMonthByCustomerExcel()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_CUSTOMER)) {
      die("没有权限");
    }

    $params = [
      "year" => I("get.year"),
      "month" => I("get.month"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleMonthByCustomerExcel($params);
  }

  /**
   * 销售月报表(按仓库汇总)
   */
  public function saleMonthByWarehouse()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_WAREHOUSE)) {
      $this->initVar();

      $this->assign("title", "销售月报表(按仓库汇总)");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/saleMonthByWarehouse");
    }
  }

  /**
   * 销售月报表(按仓库汇总) - 查询数据
   */
  public function saleMonthByWarehouseQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_WAREHOUSE)) {
        die("没有权限");
      }

      $params = [
        "year" => I("post.year"),
        "month" => I("post.month"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleMonthByWarehouseQueryData($params));
    }
  }

  /**
   * 销售月报表(按仓库汇总) - 查询汇总数据
   */
  public function saleMonthByWarehouseSummaryQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_WAREHOUSE)) {
        die("没有权限");
      }

      $params = [
        "year" => I("post.year"),
        "month" => I("post.month")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleMonthByWarehouseSummaryQueryData($params));
    }
  }

  /**
   * 销售月报表(按仓库汇总) - 生成打印页面
   */
  public function genSaleMonthByWarehousePrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_WAREHOUSE)) {
        die("没有权限");
      }

      $params = [
        "year" => I("post.year"),
        "month" => I("post.month"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $service = new SaleReportService();
      $data = $service->getSaleMonthByWarehouseDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 销售月报表(按仓库汇总) - 生成PDF文件
   */
  public function saleMonthByWarehousePdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_WAREHOUSE)) {
      die("没有权限");
    }

    $params = [
      "year" => I("get.year"),
      "month" => I("get.month"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleMonthByWarehousePdf($params);
  }

  /**
   * 销售月报表(按仓库汇总) - 生成Excel文件
   */
  public function saleMonthByWarehouseExcel()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_WAREHOUSE)) {
      die("没有权限");
    }

    $params = [
      "year" => I("get.year"),
      "month" => I("get.month"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleMonthByWarehouseExcel($params);
  }

  /**
   * 销售月报表(按业务员汇总)
   */
  public function saleMonthByBizuser()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_BIZUSER)) {
      $this->initVar();

      $this->assign("title", "销售月报表(按业务员汇总)");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/saleMonthByBizuser");
    }
  }

  /**
   * 销售月报表(按业务员汇总) - 查询数据
   */
  public function saleMonthByBizuserQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_BIZUSER)) {
        die("没有权限");
      }

      $params = [
        "year" => I("post.year"),
        "month" => I("post.month"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleMonthByBizuserQueryData($params));
    }
  }

  /**
   * 销售月报表(按业务员汇总) - 查询汇总数据
   */
  public function saleMonthByBizuserSummaryQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_BIZUSER)) {
        die("没有权限");
      }

      $params = [
        "year" => I("post.year"),
        "month" => I("post.month")
      ];

      $rs = new SaleReportService();

      $this->ajaxReturn($rs->saleMonthByBizuserSummaryQueryData($params));
    }
  }

  /**
   * 销售月报表(按业务员汇总) - 生成打印页面
   */
  public function genSaleMonthByBizuserPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_BIZUSER)) {
        die("没有权限");
      }

      $params = [
        "year" => I("post.year"),
        "month" => I("post.month"),
        "limit" => I("post.limit"),
        "sort" => I("post.sort")
      ];

      $service = new SaleReportService();
      $data = $service->getSaleMonthByBizuserDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 销售月报表(按业务员汇总) - 生成PDF文件
   */
  public function saleMonthByBizuserPdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_BIZUSER)) {
      die("没有权限");
    }

    $params = [
      "year" => I("get.year"),
      "month" => I("get.month"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleMonthByBizuserPdf($params);
  }

  /**
   * 销售月报表(按业务员汇总) - 生成Excel文件
   */
  public function saleMonthByBizuserExcel()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SALE_MONTH_BY_BIZUSER)) {
      die("没有权限");
    }

    $params = [
      "year" => I("get.year"),
      "month" => I("get.month"),
      "limit" => I("get.limit"),
      "sort" => I("get.sort")
    ];

    $service = new SaleReportService();
    $service->saleMonthByBizuserExcel($params);
  }

  /**
   * 安全库存明细表
   */
  public function safetyInventory()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::REPORT_SAFETY_INVENTORY)) {
      $this->initVar();

      $this->assign("title", "安全库存明细表");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/safetyInventory");
    }
  }

  /**
   * 安全库存明细表 - 查询数据
   */
  public function safetyInventoryQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SAFETY_INVENTORY)) {
        die("没有权限");
      }

      $params = [
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];

      $is = new InventoryReportService();

      $this->ajaxReturn($is->safetyInventoryQueryData($params));
    }
  }

  /**
   * 安全库存明细表 - 生成打印页面
   */
  public function genSafetyInventoryPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_SAFETY_INVENTORY)) {
        die("没有权限");
      }

      $params = [
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];

      $service = new InventoryReportService();

      $data = $service->getSafetyInventoryDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 安全库存明细表 - 生成PDF文件
   */
  public function safetyInventoryPdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SAFETY_INVENTORY)) {
      die("没有权限");
    }

    $params = [
      "limit" => I("get.limit")
    ];

    $service = new InventoryReportService();
    $service->safetyInventoryPdf($params);
  }

  /**
   * 安全库存明细表 - 生成Excel文件
   */
  public function safetyInventoryExcel()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_SAFETY_INVENTORY)) {
      die("没有权限");
    }

    $params = [
      "limit" => I("get.limit")
    ];

    $service = new InventoryReportService();
    $service->safetyInventoryExcel($params);
  }

  /**
   * 应收账款账龄分析表
   */
  public function receivablesAge()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::REPORT_RECEIVABLES_AGE)) {
      $this->initVar();

      $this->assign("title", "应收账款账龄分析表");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/receivablesAge");
    }
  }

  /**
   * 应收账款账龄分析表 - 数据查询
   */
  public function receivablesAgeQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_RECEIVABLES_AGE)) {
        die("没有权限");
      }

      $params = [
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];

      $rs = new ReceivablesReportService();

      $this->ajaxReturn($rs->receivablesAgeQueryData($params));
    }
  }

  /**
   * 应收账款账龄分析表 - 当期汇总数据查询
   */
  public function receivablesSummaryQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_RECEIVABLES_AGE)) {
        die("没有权限");
      }

      $rs = new ReceivablesReportService();

      $this->ajaxReturn($rs->receivablesSummaryQueryData());
    }
  }

  /**
   * 应收账款账龄分析表 - 生成打印页面
   */
  public function genReceivablesAgePrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_RECEIVABLES_AGE)) {
        die("没有权限");
      }

      $params = [
        "limit" => I("post.limit")
      ];

      $service = new ReceivablesReportService();

      $data = $service->getReceivablesAgeDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 应收账款账龄分析表 - 生成PDF文件
   */
  public function receivablesAgePdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_RECEIVABLES_AGE)) {
      die("没有权限");
    }

    $params = [
      "limit" => I("get.limit")
    ];

    $service = new ReceivablesReportService();
    $service->receivablesAgePdf($params);
  }

  /**
   * 应收账款账龄分析表 - 生成Excel文件
   */
  public function receivablesAgeExcel()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_RECEIVABLES_AGE)) {
      die("没有权限");
    }

    $params = [
      "limit" => I("get.limit")
    ];

    $service = new ReceivablesReportService();
    $service->receivablesAgeExcel($params);
  }

  /**
   * 应付账款账龄分析表
   */
  public function payablesAge()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::REPORT_PAYABLES_AGE)) {
      $this->initVar();

      $this->assign("title", "应付账款账龄分析表");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/payablesAge");
    }
  }

  /**
   * 应付账款账龄分析表 - 数据查询
   */
  public function payablesAgeQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_PAYABLES_AGE)) {
        die("没有权限");
      }

      $params = [
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];

      $ps = new PayablesReportService();

      $this->ajaxReturn($ps->payablesAgeQueryData($params));
    }
  }

  /**
   * 应付账款账龄分析表 - 当期汇总数据查询
   */
  public function payablesSummaryQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_PAYABLES_AGE)) {
        die("没有权限");
      }

      $ps = new PayablesReportService();

      $this->ajaxReturn($ps->payablesSummaryQueryData());
    }
  }

  /**
   * 应付账款账龄分析表 - 生成打印页面
   */
  public function genPayablesAgePrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_PAYABLES_AGE)) {
        die("没有权限");
      }

      $params = [
        "limit" => I("post.limit")
      ];

      $service = new PayablesReportService();

      $data = $service->getPayablesAgeDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 应付账款账龄分析表 - 生成PDF文件
   */
  public function payablesAgePdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_PAYABLES_AGE)) {
      die("没有权限");
    }

    $params = [
      "limit" => I("get.limit")
    ];

    $service = new PayablesReportService();
    $service->payablesAgePdf($params);
  }

  /**
   * 应付账款账龄分析表 - 生成Excel文件
   */
  public function payablesAgeExcel()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_PAYABLES_AGE)) {
      die("没有权限");
    }

    $params = [
      "limit" => I("get.limit")
    ];

    $service = new PayablesReportService();
    $service->payablesAgeExcel($params);
  }

  /**
   * 库存超上限明细表
   */
  public function inventoryUpper()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::REPORT_INVENTORY_UPPER)) {
      $this->initVar();

      $this->assign("title", "库存超上限明细表");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/inventoryUpper");
    }
  }

  /**
   * 库存超上限明细表 - 查询数据
   */
  public function inventoryUpperQueryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_INVENTORY_UPPER)) {
        die("没有权限");
      }

      $params = [
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];

      $is = new InventoryReportService();

      $this->ajaxReturn($is->inventoryUpperQueryData($params));
    }
  }

  /**
   * 库存超上限明细表 - 生成打印页面
   */
  public function genInventoryUpperPrintPage()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::REPORT_INVENTORY_UPPER)) {
        die("没有权限");
      }

      $params = [
        "limit" => I("post.limit")
      ];

      $service = new InventoryReportService();

      $data = $service->getInventoryUpperDataForLodopPrint($params);
      $this->assign("data", $data);
      $this->display();
    }
  }

  /**
   * 库存超上限明细表 - 生成PDF文件
   */
  public function inventoryUpperPdf()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_INVENTORY_UPPER)) {
      die("没有权限");
    }

    $params = [
      "limit" => I("get.limit")
    ];

    $service = new InventoryReportService();
    $service->inventoryUpperPdf($params);
  }

  /**
   * 库存超上限明细表 - 生成Excel文件
   */
  public function inventoryUpperExcel()
  {
    $us = new UserService();

    if (!$us->hasPermission(FIdConst::REPORT_INVENTORY_UPPER)) {
      die("没有权限");
    }

    $params = [
      "limit" => I("get.limit")
    ];

    $service = new InventoryReportService();
    $service->inventoryUpperExcel($params);
  }

  /**
   * 采购入库明细表
   */
  public function purchaseDetail()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::PURCHASE_DETAIL_REPORT)) {
      $this->initVar();

      $this->assign("title", "采购入库明细表");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/purchaseDetail");
    }
  }

  /**
   * 采购入库明细表 - 查询数据
   */
  public function purchaseDetailQueryData()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PURCHASE_DETAIL_REPORT)) {
        die("没有权限");
      }

      $params = [
        "supplierId" => I("post.supplierId"),
        "warehouseId" => I("post.warehouseId"),
        "fromDT" => I("post.fromDT"),
        "toDT" => I("post.toDT"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];

      $service = new PurchaseReportService();

      $this->ajaxReturn($service->purchaseDetailQueryData($params));
    }
  }

  /**
   * 采购入库明细表 - 导出Excel
   */
  public function purchaseDetailExcel()
  {
    $us = new UserService();
    if (!$us->hasPermission(FIdConst::PURCHASE_DETAIL_REPORT)) {
      die("没有权限");
    }

    $params = [
      "limit" => I("get.limit"),
      "warehouseId" => I("get.warehouseId"),
      "supplierId" => I("get.supplierId"),
      "fromDT" => I("get.fromDT"),
      "toDT" => I("get.toDT")
    ];

    $service = new PurchaseReportService();
    $service->purchaseDetailExcel($params);
  }

  /**
   * 采购入库明细表 - 导出PDF
   */
  public function purchaseDetailPdf()
  {
    $us = new UserService();
    if (!$us->hasPermission(FIdConst::PURCHASE_DETAIL_REPORT)) {
      die("没有权限");
    }

    $params = [
      "limit" => I("get.limit"),
      "warehouseId" => I("get.warehouseId"),
      "supplierId" => I("get.supplierId"),
      "fromDT" => I("get.fromDT"),
      "toDT" => I("get.toDT")
    ];

    $service = new PurchaseReportService();
    $service->purchaseDetailPdf($params);
  }

  /**
   * 销售出库明细表
   */
  public function saleDetail()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::SALE_DETAIL_REPORT)) {
      $this->initVar();

      $this->assign("title", "销售出库明细表");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Report/saleDetail");
    }
  }

  /**
   * 销售出库明细表 - 查询数据
   */
  public function saleDetailQueryData()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::SALE_DETAIL_REPORT)) {
        die("没有权限");
      }

      $params = [
        "customerId" => I("post.customerId"),
        "warehouseId" => I("post.warehouseId"),
        "fromDT" => I("post.fromDT"),
        "toDT" => I("post.toDT"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];

      $service = new SaleReportService();

      $this->ajaxReturn($service->saleDetailQueryData($params));
    }
  }

  /**
   * 销售出库明细表 - 导出Excel
   */
  public function saleDetailExcel()
  {
    $us = new UserService();
    if (!$us->hasPermission(FIdConst::SALE_DETAIL_REPORT)) {
      die("没有权限");
    }

    $params = [
      "limit" => I("get.limit"),
      "warehouseId" => I("get.warehouseId"),
      "customerId" => I("get.customerId"),
      "fromDT" => I("get.fromDT"),
      "toDT" => I("get.toDT")
    ];

    $service = new SaleReportService();
    $service->saleDetailExcel($params);
  }

  /**
   * 销售出库明细表 - 导出PDF
   */
  public function saleDetailPdf()
  {
    $us = new UserService();
    if (!$us->hasPermission(FIdConst::SALE_DETAIL_REPORT)) {
      die("没有权限");
    }

    $params = [
      "limit" => I("get.limit"),
      "warehouseId" => I("get.warehouseId"),
      "customerId" => I("get.customerId"),
      "fromDT" => I("get.fromDT"),
      "toDT" => I("get.toDT")
    ];

    $service = new SaleReportService();
    $service->saleDetailPdf($params);
  }
}
