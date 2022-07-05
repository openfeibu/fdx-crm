<?php

namespace FEIBU0001\DAO;

use Home\Common\FIdConst;
use Home\DAO\PSIBaseDAO;
use Home\DAO\DataOrgDAO;
use Home\DAO\PSIBaseExDAO;
/**

 * 仓库 DAO
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class ExpressDAO extends PSIBaseExDAO
{
  
  /**
   * 通过仓库id查询仓库
   *
   * @param string $id
   * @return array|NULL
   */
  public function getExpressById($id)
  {
    $db = $this->db;
    $sql = "select code, name, data_org, express_code, freight
            from t_feibu0001_ct_express where id = '%s' ";
    $data = $db->query($sql, $id);
    
    if (!$data) {
      return null;
    }
    
    return [
      "code" => $data[0]["code"],
      "name" => $data[0]["name"],
      "dataOrg" => $data[0]["data_org"],
      "express_code" => $data[0]["express_code"],
      "freight" => $data[0]["freight"],
    ];
  }
  

  /**
   * 查询数据，用于仓库自定义字段
   *
   * @param array $params
   * @return array
   */
  public function queryData($params)
  {
    $db = $this->db;
    
    $loginUserId = $params["loginUserId"];
    if ($this->loginUserIdNotExists($loginUserId)) {
      return $this->emptyResult();
    }
    
    $queryKey = $params["queryKey"];
    if ($queryKey == null) {
      $queryKey = "";
    }
    
    $sql = "select id, code, name, freight from t_feibu0001_ct_express
            where (code like '%s' or name like '%s' or py like '%s')  ";
    $key = "%{$queryKey}%";
    $queryParams = [];
    $queryParams[] = $key;
    $queryParams[] = $key;
    $queryParams[] = $key;
    
    $ds = new DataOrgDAO($db);
    $rs = $ds->buildSQL("ct20220531145123-dataorg", "t_feibu0001_ct_express", $loginUserId);
    if ($rs) {
      $sql .= " and " . $rs[0];
      $queryParams = array_merge($queryParams, $rs[1]);
    }
    
    $sql .= " order by code";
    
    return $db->query($sql, $queryParams);
  }
}
