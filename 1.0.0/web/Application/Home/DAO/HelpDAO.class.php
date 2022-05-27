<?php

namespace Home\DAO;

/**
 * 指南 DAO
 *
 * @author 艾格林门信息服务（大连）有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class HelpDAO extends PSIBaseExDAO
{

  /**
   * 获得某个指南页面的导航信息
   */
  public function getNav($params)
  {
    $db = $this->db;

    $code = $params["code"];

    $sql = "select name, memo from t_sysdict_sln0000_help_nav where code = '%s' ";
    $data = $db->query($sql, $code);
    if ($data) {
      $v = $data[0];

      return [
        "url" => $v["name"],
        "log" => $v["memo"],
      ];
    } else {
      return null;
    }
  }
}
