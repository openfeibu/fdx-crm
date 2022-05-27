<?php

namespace Home\DAO\Common;

use Home\DAO\PSIBaseExDAO;

/**
 * 通用库存 DAO
 *
 * @author 艾格林门信息服务（大连）有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class InventoryDAO extends PSIBaseExDAO {

	/**
	 * 入库操作 - 修改库存账
	 *
	 * @param array $item
	 */
	public function inAction($item) {
		$warehouseId = $item["warehouseId"];
		if (! $warehouseId) {
			return $this->badParam("warehouseId");
		}
		$goodsId = $item["goodsId"];
		if (! $goodsId) {
			return $this->badParam("goodsId");
		}

		// TODO
		return $this->todo();
	}

	/**
	 * 出库操作 - 修改库存账
	 *
	 * @param array $item
	 */
	public function outAction($item) {
		// TODO
		return $this->todo();
	}
}
