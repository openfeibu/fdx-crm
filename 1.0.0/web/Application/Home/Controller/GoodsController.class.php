<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\GoodsService;
use Home\Service\ImportService;
use Home\Service\UserService;

/**
 * 物料Controller
 * 
 * 因为PSI最开始是做的商贸企业的进销存，所以只做了商品模块。
 * 随着PSI项目的拓展，逐步开始实现完整的ERP，就把商品模块升级为物料模块了。
 *
 * @author 艾格林门信息服务（大连）有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class GoodsController extends PSIBaseController
{

  /**
   * 物料主页面
   */
  public function index()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::GOODS)) {
      $this->initVar();

      $this->assign("title", "物料");

      // 按钮权限：新建物料分类
      $this->assign("pAddCategory", $us->hasPermission(FIdConst::GOODS_CATEGORY_ADD) ? 1 : 0);

      // 按钮权限：编辑物料分类
      $this->assign(
        "pEditCategory",
        $us->hasPermission(FIdConst::GOODS_CATEGORY_EDIT) ? 1 : 0
      );

      // 按钮权限：删除物料分类
      $this->assign(
        "pDeleteCategory",
        $us->hasPermission(FIdConst::GOODS_CATEGORY_DELETE) ? 1 : 0
      );

      // 按钮权限：新建物料
      $this->assign("pAddGoods", $us->hasPermission(FIdConst::GOODS_ADD) ? 1 : 0);

      // 按钮权限：编辑物料
      $this->assign("pEditGoods", $us->hasPermission(FIdConst::GOODS_EDIT) ? 1 : 0);

      // 按钮权限：删除物料
      $this->assign("pDeleteGoods", $us->hasPermission(FIdConst::GOODS_DELETE) ? 1 : 0);

      // 按钮权限：导入物料
      $this->assign("pImportGoods", $us->hasPermission(FIdConst::GOODS_IMPORT) ? 1 : 0);

      // 按钮权限：设置物料安全库存
      $this->assign("pGoodsSI", $us->hasPermission(FIdConst::GOODS_SI) ? 1 : 0);

      // 按钮权限：新增子件
      $this->assign("pAddBOM", $us->hasPermission(FIdConst::GOODS_BOM_ADD) ? 1 : 0);

      // 按钮权限：编辑子件
      $this->assign("pEditBOM", $us->hasPermission(FIdConst::GOODS_BOM_EDIT) ? 1 : 0);

      // 按钮权限：删除子件
      $this->assign("pDeleteBOM", $us->hasPermission(FIdConst::GOODS_BOM_DELETE) ? 1 : 0);

      // 按钮权限：设置商品价格体系
      $this->assign(
        "pPriceSystem",
        $us->hasPermission(FIdConst::PRICE_SYSTEM_SETTING_GOODS) ? 1 : 0
      );

      // 按钮权限：导出Excel
      $this->assign("pExcel", $us->hasPermission(FIdConst::GOODS_EXPORT_EXCEL) ? 1 : 0);

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Goods/index");
    }
  }

  /**
   * 物料计量单位主页面
   */
  public function unitIndex()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::GOODS_UNIT)) {
      $this->initVar();

      $this->assign("title", "物料计量单位");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Goods/unitIndex");
    }
  }

  /**
   * 获得所有的物料计量单位列表
   *
   * JS:PSI.Goods.UnitMainForm中调用本Action
   */
  public function allUnits()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::GOODS_UNIT)) {
        die("没有权限");
      }

      $gs = new GoodsService();
      $this->ajaxReturn($gs->allUnits());
    }
  }

  /**
   * 新增或编辑物料单位
   *
   * JS:PSI.Goods.UnitEditForm中调用本Action
   */
  public function editUnit()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::GOODS_UNIT)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "name" => I("post.name"),
        "code" => I("post.code"),
        "recordStatus" => I("post.recordStatus")
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->editUnit($params));
    }
  }

  /**
   * 删除物料计量单位
   *
   * JS:PSI.Goods.UnitMainForm中调用本Action
   */
  public function deleteUnit()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::GOODS_UNIT)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->deleteUnit($params));
    }
  }

  /**
   * 获得物料分类
   */
  public function allCategories()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::GOODS_CATEGORY)) {
        die("没有权限");
      }

      $gs = new GoodsService();
      $params = [
        "code" => I("post.code"),
        "name" => I("post.name"),
        "spec" => I("post.spec"),
        "barCode" => I("post.barCode"),
        "brandId" => I("post.brandId")
      ];
      $this->ajaxReturn($gs->allCategories($params));
    }
  }

  /**
   * 新增或编辑物料分类
   */
  public function editCategory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (I("post.id")) {
        // 编辑物料分类
        if (!$us->hasPermission(FIdConst::GOODS_CATEGORY_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新增物料分类
        if (!$us->hasPermission(FIdConst::GOODS_CATEGORY_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id"),
        "code" => strtoupper(I("post.code")),
        "name" => I("post.name"),
        "parentId" => I("post.parentId"),
        "taxRate" => I("post.taxRate"),
        "mType" => I("post.mType"),
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->editCategory($params));
    }
  }

  /**
   * 获得某个分类的信息
   */
  public function getCategoryInfo()
  {
    if (IS_POST) {
      $us = new UserService();
      if (I("post.id")) {
        // 编辑物料分类
        if (!$us->hasPermission(FIdConst::GOODS_CATEGORY_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新增物料分类
        if (!$us->hasPermission(FIdConst::GOODS_CATEGORY_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id")
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->getCategoryInfo($params));
    }
  }

  /**
   * 删除物料分类
   */
  public function deleteCategory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_CATEGORY_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->deleteCategory($params));
    }
  }

  /**
   * 获得物料列表
   */
  public function goodsList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS)) {
        die("没有权限");
      }

      $params = [
        "categoryId" => I("post.categoryId"),
        "code" => I("post.code"),
        "name" => I("post.name"),
        "spec" => I("post.spec"),
        "barCode" => I("post.barCode"),
        "brandId" => I("post.brandId"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->goodsList($params));
    }
  }

  /**
   * 新增或编辑物料
   */
  public function editGoods()
  {
    if (IS_POST) {
      $us = new UserService();
      if (I("post.id")) {
        // 编辑
        if (!$us->hasPermission(FIdConst::GOODS_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新建
        if (!$us->hasPermission(FIdConst::GOODS_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id"),
        "categoryId" => I("post.categoryId"),
        "code" => strtoupper(I("post.code")),
        "name" => I("post.name"),
        "spec" => I("post.spec"),
        "unitId" => I("post.unitId"),
        "salePrice" => I("post.salePrice"),
        "purchasePrice" => I("post.purchasePrice"),
        "barCode" => I("post.barCode"),
        "brandId" => I("post.brandId"),
        "memo" => I("post.memo"),
        "recordStatus" => I("post.recordStatus"),
        "taxRate" => I("post.taxRate"),
        "mType" => I("post.mType"),
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->editGoods($params));
    }
  }

  /**
   * 删除物料
   */
  public function deleteGoods()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->deleteGoods($params));
    }
  }

  /**
   * 物料自定义字段，查询数据
   */
  public function queryData()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_BILL)) {
        die("没有权限");
      }

      $queryKey = I("post.queryKey");
      $warehouseId = I("post.warehouseId");
      $gs = new GoodsService();
      $this->ajaxReturn($gs->queryData($queryKey, $warehouseId));
    }
  }

  /**
   * 物料自定义字段，查询数据 - 只显示有子件的物料，用于加工业务中
   */
  public function queryDataForBOM()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_BILL)) {
        die("没有权限");
      }

      $queryKey = I("post.queryKey");
      $gs = new GoodsService();
      $this->ajaxReturn($gs->queryDataForBOM($queryKey));
    }
  }

  /**
   * 物料自定义字段，查询数据
   */
  public function queryDataWithSalePrice()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_BILL)) {
        die("没有权限");
      }

      $queryKey = I("post.queryKey");
      $customerId = I("post.customerId");
      $warehouseId = I("post.warehouseId");
      $sumInv = I("post.sumInv");
      $gs = new GoodsService();
      $this->ajaxReturn($gs->queryDataWithSalePrice($queryKey, $customerId, $warehouseId, $sumInv));
    }
  }

  /**
   * 物料自定义字段，查询数据
   */
  public function queryDataWithPurchasePrice()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_BILL)) {
        die("没有权限");
      }

      $queryKey = I("post.queryKey");
      $supplierId = I("post.supplierId");
      $gs = new GoodsService();
      $this->ajaxReturn($gs->queryDataWithPurchasePrice($queryKey, $supplierId));
    }
  }

  /**
   * 查询某个物料的信息
   */
  public function goodsInfo()
  {
    if (IS_POST) {
      $us = new UserService();

      $id = I("post.id");
      if ($id) {
        // 编辑
        if (!$us->hasPermission(FIdConst::GOODS_EDIT)) {
          die("没有权限");
        }
      } else {
        // 新建
        if (!$us->hasPermission(FIdConst::GOODS_ADD)) {
          die("没有权限");
        }
      }

      $id = I("post.id");
      $categoryId = I("post.categoryId");
      $gs = new GoodsService();
      $data = $gs->getGoodsInfo($id, $categoryId);
      $this->ajaxReturn($data);
    }
  }

  /**
   * 获得物料的安全库存信息
   */
  public function goodsSafetyInventoryList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->goodsSafetyInventoryList($params));
    }
  }

  /**
   * 设置安全库存时候，查询信息
   */
  public function siInfo()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_SI)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->siInfo($params));
    }
  }

  /**
   * 设置安全库存
   */
  public function editSafetyInventory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_SI)) {
        die("没有权限");
      }

      $params = [
        "jsonStr" => I("post.jsonStr")
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->editSafetyInventory($params));
    }
  }

  /**
   * 根据条形码，查询商品信息, 销售出库单使用
   */
  public function queryGoodsInfoByBarcode()
  {
    if (IS_POST) {
      $us = new UserService();
      $f1 = $us->hasPermission(FIdConst::SALE_ORDER_ADD);
      $f2 = $us->hasPermission(FIdConst::SALE_ORDER_EDIT);
      if (!($f1 || $f2)) {
        die("没有权限");
      }

      $params = [
        "barcode" => I("post.barcode")
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->queryGoodsInfoByBarcode($params));
    }
  }

  /**
   * 根据条形码，查询物料信息, 采购入库单使用
   */
  public function queryGoodsInfoByBarcodeForPW()
  {
    if (IS_POST) {
      $us = new UserService();
      $f1 = $us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_ADD);
      $f2 = $us->hasPermission(FIdConst::PURCHASE_WAREHOUSE_EDIT);
      if (!($f1 || $f2)) {
        die("没有权限");
      }

      $params = [
        "barcode" => I("post.barcode")
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->queryGoodsInfoByBarcodeForPW($params));
    }
  }

  /**
   * 通过Excel导入物料
   */
  public function import()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_IMPORT)) {
        die("没有权限");
      }

      $upload = new \Think\Upload();

      // 允许上传的文件后缀
      $upload->exts = [
        "xlsx"
      ];

      // 保存路径
      $upload->savePath = "/Goods/";

      // 先上传文件
      $fileInfo = $upload->uploadOne($_FILES['data_file']);
      if (!$fileInfo) {
        $this->ajaxReturn(
          [
            "msg" => $upload->getError(),
            "success" => false
          ]
        );
      } else {
        $uploadFileFullPath = './Uploads' . $fileInfo['savepath'] . $fileInfo['savename']; // 获取上传到服务器文件路径
        $uploadFileExt = $fileInfo['ext']; // 上传文件扩展名

        $params = [
          "datafile" => $uploadFileFullPath,
          "ext" => $uploadFileExt
        ];
        $ims = new ImportService();
        $this->ajaxReturn($ims->importGoodsFromExcelFile($params));
      }
    }
  }

  /**
   * 获得所有的物料种类数
   */
  public function getTotalGoodsCount()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS)) {
        die("没有权限");
      }

      $params = [
        "code" => I("post.code"),
        "name" => I("post.name"),
        "spec" => I("post.spec"),
        "barCode" => I("post.barCode")
      ];

      $gs = new GoodsService();
      $this->ajaxReturn($gs->getTotalGoodsCount($params));
    }
  }

  /**
   * 物料品牌主页面
   */
  public function brandIndex()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::GOODS_BRAND)) {
      $this->initVar();

      $this->assign("title", "物料品牌");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Goods/brandIndex");
    }
  }

  /**
   * 获得所有的品牌
   */
  public function allBrands()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_BRAND)) {
        die("没有权限");
      }

      $gs = new GoodsService();
      $this->ajaxReturn($gs->allBrands());
    }
  }

  /**
   * 新增或编辑物料品牌
   */
  public function editBrand()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_BRAND)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "name" => I("post.name"),
        "parentId" => I("post.parentId"),
        "recordStatus" => I("post.recordStatus")
      ];

      $gs = new GoodsService();
      $this->ajaxReturn($gs->editBrand($params));
    }
  }

  /**
   * 获得某个品牌的上级品牌全称
   */
  public function brandParentName()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_BRAND)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $gs = new GoodsService();
      $this->ajaxReturn($gs->brandParentName($params));
    }
  }

  /**
   * 删除物料品牌
   */
  public function deleteBrand()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_BRAND)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $gs = new GoodsService();
      $this->ajaxReturn($gs->deleteBrand($params));
    }
  }

  /**
   * 某个物料的静态BOM
   */
  public function goodsBOMList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $gs = new GoodsService();
      $this->ajaxReturn($gs->goodsBOMList($params));
    }
  }

  /**
   * 新增或编辑静态BOM
   */
  public function editGoodsBOM()
  {
    if (IS_POST) {
      $us = new UserService();
      if (I("post.id")) {
        // 编辑
        if (!$us->hasPermission(FIdConst::GOODS_BOM_EDIT)) {
          die("没有权限");
        }
      } else {
        if (!$us->hasPermission(FIdConst::GOODS_BOM_ADD)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id"),
        "addBOM" => I("post.addBOM"),
        "subGoodsId" => I("post.subGoodsId"),
        "subGoodsCount" => I("post.subGoodsCount"),
        "costWeight" => I("post.costWeight")
      ];

      $gs = new GoodsService();
      $this->ajaxReturn($gs->editGoodsBOM($params));
    }
  }

  /**
   * 子件字段，查询数据
   */
  public function queryDataForSubGoods()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_BILL)) {
        die("没有权限");
      }

      $params = [
        "queryKey" => I("post.queryKey"),
        "parentGoodsId" => I("post.parentGoodsId")
      ];

      $gs = new GoodsService();
      $this->ajaxReturn($gs->queryDataForSubGoods($params));
    }
  }

  /**
   * 查询子件的详细信息
   */
  public function getSubGoodsInfo()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_BOM_EDIT)) {
        die("没有权限");
      }

      $params = [
        "goodsId" => I("post.goodsId"),
        "subGoodsId" => I("post.subGoodsId")
      ];

      $gs = new GoodsService();
      $this->ajaxReturn($gs->getSubGoodsInfo($params));
    }
  }

  /**
   * 删除物料构成中的子件
   */
  public function deleteGoodsBOM()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_BOM_DELETE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $gs = new GoodsService();
      $this->ajaxReturn($gs->deleteGoodsBOM($params));
    }
  }

  /**
   * 价格体系 - 主页面
   */
  public function psIndex()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::PRICE_SYSTEM)) {
      $this->initVar();

      $this->assign("title", "价格体系");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/Goods/psIndex");
    }
  }

  /**
   * 价格体系-价格列表
   */
  public function priceSystemList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PRICE_SYSTEM)) {
        die("没有权限");
      }

      $gs = new GoodsService();
      $this->ajaxReturn($gs->priceSystemList());
    }
  }

  /**
   * 新增或编辑价格体系中的价格
   */
  public function editPriceSystem()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PRICE_SYSTEM)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "name" => I("post.name"),
        "factor" => I("post.factor")
      ];
      $gs = new GoodsService();

      $this->ajaxReturn($gs->editPriceSystem($params));
    }
  }

  /**
   * 删除价格体系中的价格
   */
  public function deletePriceSystem()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PRICE_SYSTEM)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $gs = new GoodsService();

      $this->ajaxReturn($gs->deletePriceSystem($params));
    }
  }

  /**
   * 查询某个商品的所有价格体系里面的价格列表
   */
  public function goodsPriceSystemList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $gs = new GoodsService();
      $this->ajaxReturn($gs->goodsPriceSystemList($params));
    }
  }

  /**
   * 查询某个商品的价格体系中所有价格的值
   */
  public function goodsPriceSystemInfo()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PRICE_SYSTEM_SETTING_GOODS)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $gs = new GoodsService();
      $this->ajaxReturn($gs->goodsPriceSystemInfo($params));
    }
  }

  /**
   * 设置商品价格体系中的价格
   */
  public function editGoodsPriceSystem()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::PRICE_SYSTEM_SETTING_GOODS)) {
        die("没有权限");
      }

      $params = [
        "jsonStr" => I("post.jsonStr")
      ];
      $gs = new GoodsService();
      $this->ajaxReturn($gs->editGoodsPriceSystem($params));
    }
  }

  /**
   * 物料品牌自定义字段 - 查询数据
   */
  public function queryGoodsBrandData()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_BRAND)) {
        die("没有权限");
      }

      $queryKey = I("post.queryKey");
      $service = new GoodsService();
      $this->ajaxReturn($service->queryGoodsBrandData($queryKey));
    }
  }

  /**
   * 导出Excel
   */
  public function exportExcel()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::GOODS_EXPORT_EXCEL)) {
      $service = new GoodsService();
      $service->exportExcel();
    } else {
      die("没有导出Excel的权限");
    }
  }

  /**
   * 物料计量单位字段 - 查询数据
   * 
   * 在GoodsUnitField.js中调用
   */
  public function queryUnitData()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::GOODS_UNIT)) {
        die("没有权限");
      }

      $queryKey = I("post.queryKey");
      $service = new GoodsService();
      $this->ajaxReturn($service->queryUnitData($queryKey));
    }
  }
}
