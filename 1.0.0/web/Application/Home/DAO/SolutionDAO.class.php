<?php

namespace Home\DAO;

/**
 * 解决方案DAO
 *
 * @author 艾格林门信息服务（大连）有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class SolutionDAO extends PSIBaseExDAO
{

  /**
   * 解决方案列表
   */
  public function solutionList()
  {
    $db = $this->db;

    $sql = "select id, code, name, is_default
            from t_solution
            order by code";
    $data = $db->query($sql);

    $result = [];
    foreach ($data as $v) {
      $result[] = [
        "id" => $v["id"],
        "code" => $v["code"],
        "name" => $v["name"],
        "isDefault" => $v["is_default"] == 1 ? "▲" : "",
      ];
    }

    return $result;
  }

  private function checkParams($params)
  {
    $code = trim($params["code"]);
    $name = trim($params["name"]);

    if (!$code) {
      return $this->bad("没有录入解决方案编码");
    }
    if (strlen($code) > 20) {
      return $this->bad("解决方案编码长度不能超过20位");
    }

    if (!$name) {
      return $this->bad("没有录入解决方案名称");
    }
    if (strlen($code) > 50) {
      return $this->bad("解决方案名称长度不能超过50位");
    }

    // 没有错误
    return null;
  }

  /**
   * 新建解决方案
   */
  public function addSolution(&$params)
  {
    $rc = $this->checkParams($params);
    if ($rc) {
      return $rc;
    }

    $db = $this->db;
    $code = strtoupper(trim($params["code"]));
    $name = trim($params["name"]);

    // 检查编码是否存在
    $sql = "select count(*) as cnt from t_solution where code = '%s' ";
    $data = $db->query($sql, $code);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("编码[{$code}]已经存在");
    }
    // 检查名称是否已经存在
    $sql = "select count(*) as cnt from t_solution where name = '%s' ";
    $data = $db->query($sql, $name);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("解决方案[{$name}]已经存在");
    }

    $id = $this->newId();
    $sql = "insert into t_solution(id, code, name)
            values ('%s', '%s', '%s')";
    $rc = $db->execute($sql, $id, $code, $name);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $params["id"] = $id;
    return null;
  }

  /**
   * 编辑解决方案
   */
  public function updateSolution(&$params)
  {
    $rc = $this->checkParams($params);
    if ($rc) {
      return $rc;
    }

    $id = $params["id"];
    $solution = $this->getSolutionById($id);
    if (!$solution) {
      return $this->bad("要编辑的解决方案不存在");
    }
    $oldCode = $solution["code"];

    $db = $this->db;
    $code = strtoupper(trim($params["code"]));
    $name = trim($params["name"]);

    // 检查编码是否存在
    $sql = "select count(*) as cnt from t_solution where code = '%s' and id <> '%s' ";
    $data = $db->query($sql, $code, $id);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("编码[{$code}]已经存在");
    }
    // 检查名称是否已经存在
    $sql = "select count(*) as cnt from t_solution where name = '%s' and id <> '%s' ";
    $data = $db->query($sql, $name, $id);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("解决方案[{$name}]已经存在");
    }

    if ($oldCode != $code) {
      // 解接方案的编码在应用后就不能修改，因为其他模块中存储的都是解决方案的编码

      // 检查码表分类中的应用情况
      $sql = "select count(*) as cnt from t_code_table_category where sln_code = '%s' ";
      $data = $db->query($sql, $oldCode);
      $cnt = $data[0]["cnt"];
      if ($cnt > 0) {
        return $this->bad("本解决方案的原编码[{$oldCode}]已经在码表中应用了，就不能再修改其编码了");
      }

      // TODO 还需要检查自定义表单等模块中的应用情况
    }

    // 执行更新操作
    $sql = "update t_solution 
            set code = '%s', name = '%s'
            where id = '%s' ";
    $rc = $db->execute($sql, $code, $name, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    return null;
  }

  /**
   * 删除解决方案
   */
  public function deleteSolution(&$params)
  {
    $db = $this->db;

    // 解决方案id
    $id = $params["id"];

    $solution = $this->getSolutionById($id);
    if (!$solution) {
      return $this->bad("要删除的解决方案不存在");
    }

    $code = $solution["code"];
    $name = $solution["name"];
    $log = "删除解决方案：名称：{$name}，编码：{$code}";

    // 检查码表分类中是否使用了该解决方案
    $sql = "select count(*) as cnt from t_code_table_category where sln_code = '%s' ";
    $data = $db->query($sql, $code);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("不能删除解决方案[{$name}]，因为本解决方案其下还有码表分类");
    }

    // TODO : 还需要判断自定义表单等模块中的使用情况

    // 删除操作
    $sql = "delete from t_solution where id = '%s' ";
    $rc = $db->execute($sql, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $params["log"] = $log;
    return null;
  }

  public function getSolutionById($id)
  {
    $db = $this->db;

    $sql = "select code, name from t_solution where id = '%s' ";
    $data = $db->query($sql, $id);
    if ($data) {
      $v = $data[0];
      return [
        "id" => $id,
        "code" => $v["code"],
        "name" => $v["name"],
      ];
    } else {
      return null;
    }
  }

  /**
   * 获得某个解决方案的详情
   */
  public function solutionInfo($params)
  {
    $id = $params["id"];
    $solution = $this->getSolutionById($id);
    if ($solution) {
      return $solution;
    } else {
      return $this->emptyResult();
    }
  }

  /**
   * 设置默认解决方案
   */
  public function setDefault(&$params)
  {
    $db = $this->db;

    // 解决方案id
    $id = $params["id"];

    $solution = $this->getSolutionById($id);
    if (!$solution) {
      return $this->bad("解决方案不存在");
    }

    $code = $solution["code"];
    $name = $solution["name"];
    $log = "把解决方案[{$code} - {$name}]设置为默认解决方案";

    $sql = "update t_solution
            set is_default = 1
            where id = '%s' ";
    $rc = $db->execute($sql, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 把其他的解决方案设置为非默认解决方案
    $sql = "update t_solution
            set is_default = 0
            where id <> '%s' ";
    $rc = $db->execute($sql, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $params["log"] = $log;
    return null;
  }
}
