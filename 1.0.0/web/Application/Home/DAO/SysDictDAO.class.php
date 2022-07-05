<?php

namespace Home\DAO;

/**
 * 系统数据字典DAO
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class SysDictDAO extends PSIBaseExDAO
{

  /**
   * 系统数据字典分类列表
   */
  public function categoryList()
  {
    $db = $this->db;

    $sql = "select id, code, name
            from t_dict_table_category
            order by code";
    $data = $db->query($sql);

    $result = [];
    foreach ($data as $v) {
      $result[] = [
        "id" => $v["id"],
        "code" => $v["code"],
        "name" => $v["name"]
      ];
    }

    return $result;
  }

  /**
   * 某个分类下的数据字典
   */
  public function sysDictList($params)
  {
    $db = $this->db;

    // 数据字典分类id
    $categoryId = $params["categoryId"];

    $sql = "select id, code, name, table_name, memo
            from t_dict_table_md
            where category_id = '%s' 
            order by code";
    $data = $db->query($sql, $categoryId);

    $result = [];

    foreach ($data as $v) {
      $result[] = [
        "id" => $v["id"],
        "code" => $v["code"],
        "name" => $v["name"],
        "tableName" => $v["table_name"],
        "memo" => $v["memo"]
      ];
    }

    return $result;
  }

  /**
   * 查询某个码表的数据
   */
  public function dictDataList($params)
  {
    $db = $this->db;

    // 数据字典元数据id
    $id = $params["id"];

    $sql = "select table_name from t_dict_table_md where id = '%s' ";
    $data = $db->query($sql, $id);
    if (!$data) {
      return $this->emptyResult();
    }

    $tableName = $data[0]["table_name"];

    $sql = "select id, code, code_int, name, memo
            from %s 
            order by show_order";

    $data = $db->query($sql, $tableName);

    $result = [];

    foreach ($data as $v) {
      $result[] = [
        "id" => $v["id"],
        "code" => $v["code"],
        "codeInt" => $v["code_int"],
        "name" => $v["name"],
        "memo" => $v["memo"]
      ];
    }

    return $result;
  }

  /**
   * 自定义字段 - 查询数据
   */
  public function queryDataForSysDictField($params)
  {
    $db = $this->db;

    $tableName = $params["tableName"];

    // 检查tableName在系统数据字典中是否存在
    // 1. 防SQL注入攻击
    // 2. 防查询非系统数据字典的表
    $sql = "select count(*) as cnt from t_dict_table_md where table_name = '%s' ";
    $data = $db->query($sql, $tableName);
    $cnt = $data[0]["cnt"];
    if ($cnt != 1) {
      die("没有权限");
    }

    $queryKey = $params["queryKey"];
    $sql = "select code, name, memo from {$tableName} 
            where code like '%s'
            order by show_order";
    $queryParams = [];
    $queryParams[] = "%{$queryKey}%";
    $data = $db->query($sql, $queryParams);

    $result = [];
    foreach ($data as $v) {
      $result[] = [
        "id" => $v["code"],
        "name" => $v["name"],
        "memo" => $v["memo"],
      ];
    }

    return $result;
  }
}
