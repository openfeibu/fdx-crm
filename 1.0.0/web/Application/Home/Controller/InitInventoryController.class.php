<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\InitInventoryService;
use Home\Service\UserService;
use Home\Service\ImportService;

/**
 * 库存建账Controller
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class InitInventoryController extends PSIBaseController
{

  /**
   * 查询仓库列表
   */
  public function warehouseList()
  {

    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::INVENTORY_INIT)) {
        die("没有权限");
      }

      $is = new InitInventoryService();
      $this->ajaxReturn($is->warehouseList());
    }
  }

  /**
   * 获得建账信息列表
   */
  public function initInfoList()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::INVENTORY_INIT)) {
        die("没有权限");
      }

      $params = [
        "warehouseId" => I("post.warehouseId"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];
      $is = new InitInventoryService();
      $this->ajaxReturn($is->initInfoList($params));
    }
  }

  /**
   * 录入建账信息时候，获得商品分类列表
   */
  public function goodsCategoryList()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::INVENTORY_INIT)) {
        die("没有权限");
      }

      $is = new InitInventoryService();
      $this->ajaxReturn($is->goodsCategoryList());
    }
  }

  /**
   * 录入建账信息的时候，获得商品列表
   */
  public function goodsList()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::INVENTORY_INIT)) {
        die("没有权限");
      }

      $params = [
        "warehouseId" => I("post.warehouseId"),
        "categoryId" => I("post.categoryId"),
        "page" => I("post.page"),
        "start" => I("post.start"),
        "limit" => I("post.limit")
      ];
      $is = new InitInventoryService();
      $this->ajaxReturn($is->goodsList($params));
    }
  }

  /**
   * 提交建账信息
   */
  public function commitInitInventoryGoods()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::INVENTORY_INIT)) {
        die("没有权限");
      }

      $params = [
        "warehouseId" => I("post.warehouseId"),
        "goodsId" => I("post.goodsId"),
        "goodsCount" => I("post.goodsCount"),
        "goodsMoney" => I("post.goodsMoney")
      ];
      $is = new InitInventoryService();
      $this->ajaxReturn($is->commitInitInventoryGoods($params));
    }
  }

  /**
   * 标记完成建账
   */
  public function finish()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::INVENTORY_INIT)) {
        die("没有权限");
      }

      $params = [
        "warehouseId" => I("post.warehouseId")
      ];
      $is = new InitInventoryService();
      $this->ajaxReturn($is->finish($params));
    }
  }

  /**
   * 取消建账完成标记
   */
  public function cancel()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::INVENTORY_INIT)) {
        die("没有权限");
      }

      $params = [
        "warehouseId" => I("post.warehouseId")
      ];
      $is = new InitInventoryService();
      $this->ajaxReturn($is->cancel($params));
    }
  }
	/**
	 * 通过Excel导入物料
	 */
	public function import()
	{
		if (IS_POST) {
			$us = new UserService();
			if (!$us->hasPermission(FIdConst::INVENTORY_INIT)) {
				die("没有权限");
			}
			
			$upload = new \Think\Upload();
			
			// 允许上传的文件后缀
			$upload->exts = [
				"xlsx"
			];
			
			// 保存路径
			$upload->savePath = "/Inventory/";
			
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
					'warehouseId' => I("post.warehouseId"),
					"datafile" => $uploadFileFullPath,
					"ext" => $uploadFileExt
				];
				$ims = new ImportService();
				$this->ajaxReturn($ims->importInitInventorFromExcelFile($params));
			}
		}
	}
	
}
