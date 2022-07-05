<?php

namespace FEIBU0001\Service;

use FEIBU0001\DAO\ExpressDAO;
use Home\Service\PSIBaseExService;

/**
 * 基础数据仓库Service
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class ExpressService extends PSIBaseExService
{
  private $LOG_CATEGORY = "基础数据-仓库";
  
  
  public function queryData($queryKey, $fid)
  {
    if ($this->isNotOnline()) {
      return $this->emptyResult();
    }
    
    $params = [
      "loginUserId" => $this->getLoginUserId(),
      "queryKey" => $queryKey
    ];
    
    $dao = new ExpressDAO($this->db());
    return $dao->queryData($params);
  }

}
