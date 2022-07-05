<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 码表DAO
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class CodeTableDAO extends PSIBaseExDAO
{

  /**
   * 码表分类列表
   */
  public function categoryList($params)
  {
    $db = $this->db;

    // 解决方案编码
    $slnCode = $params["slnCode"];

    $sql = "select id, code, name, is_system
            from t_code_table_category
            where sln_code = '%s'
            order by code";
    $data = $db->query($sql, $slnCode);

    $result = [];
    foreach ($data as $v) {
      $result[] = [
        "id" => $v["id"],
        "code" => $v["code"],
        "name" => $v["name"],
        "isSystem" => $v["is_system"],
        "isSystemCaption" => $v["is_system"] == 1 ? "▲" : "",
      ];
    }

    return $result;
  }

  /**
   * 新建码表分类
   *
   * @param array $params
   * @return null|array
   */
  public function addCodeTableCategory(&$params)
  {
    $db = $this->db;

    $code = $params["code"] ?? "";

    if (!$code) {
      return $this->bad("没有录入分类编码");
    }

    $code = strtoupper(trim($code));
    $name = trim($params["name"]);
    $slnCode = $params["slnCode"];

    // 检查解决方案是否存在
    $sql = "select name from t_solution where code = '%s' ";
    $data = $db->query($sql, $slnCode);
    if (!$data) {
      return $this->bad("解决方案不存在");
    }
    $slnName = $data[0]["name"];

    // 检查编码是否存在
    if ($code) {
      $sql = "select count(*) as cnt from t_code_table_category where code = '%s' ";
      $data = $db->query($sql, $code);
      $cnt = $data[0]["cnt"];
      if ($cnt) {
        return $this->bad("码表分类编码[{$code}]已经存在");
      }
    } else {
      $code = "";
    }

    // 检查分类名称是否存在
    $sql = "select count(*) as cnt from t_code_table_category where name = '%s' ";
    $data = $db->query($sql, $name);
    $cnt = $data[0]["cnt"];
    if ($cnt) {
      return $this->bad("码表分类[{$name}]已经存在");
    }

    $id = $this->newId();
    $sql = "insert into t_code_table_category (id, code, name, parent_id, sln_code)
            values ('%s', '%s', '%s', null, '%s')";

    $rc = $db->execute($sql, $id, $code, $name, $slnCode);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $log = "解决方案[{$slnCode}-{$slnName}] - 新建码表分类[{$name}]";
    $params["log"] = $log;
    $params["id"] = $id;
    return null;
  }

  /**
   * 编辑码表分类
   *
   * @param array $params
   */
  public function updateCodeTableCategory(&$params)
  {
    $db = $this->db;

    $id = $params["id"];
    $code = $params["code"] ?? "";
    if (!$code) {
      return $this->bad("没有录入分类编码");
    }

    $code = strtoupper($code);
    $name = $params["name"];

    $category = $this->getCodeTableCategoryById($id);
    if (!$category) {
      return $this->bad("要编辑的码表分类不存在");
    }
    $isSystem = $category["isSystem"];
    if ($isSystem == 1) {
      $n = $category["name"];
      return $this->bad("分类[{$n}]是系统固有分类，不能编辑");
    }
    $slnCode = $category["slnCode"];

    $sql = "select name from t_solution where code = '%s' ";
    $data = $db->query($sql, $slnCode);
    if (!$data) {
      return $this->bad("解决方案不存在");
    }
    $slnName = $data[0]["name"];

    // 检查编码是否存在
    if ($code) {
      $sql = "select count(*) as cnt from t_code_table_category 
              where code = '%s' and id <> '%s' ";
      $data = $db->query($sql, $code, $id);
      $cnt = $data[0]["cnt"];
      if ($cnt) {
        return $this->bad("码表分类编码[{$code}]已经存在");
      }
    } else {
      $code = "";
    }

    // 检查分类名称是否存在
    $sql = "select count(*) as cnt from t_code_table_category 
            where name = '%s' and id <> '%s' ";
    $data = $db->query($sql, $name, $id);
    $cnt = $data[0]["cnt"];
    if ($cnt) {
      return $this->bad("码表分类[{$name}]已经存在");
    }

    $sql = "update t_code_table_category
            set code = '%s', name = '%s'
            where id = '%s' ";

    $rc = $db->execute($sql, $code, $name, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $log = "解决方案[{$slnCode}-{$slnName}] - 编辑码表分类[{$name}]";
    $params["log"] = $log;
    return null;
  }

  public function getCodeTableCategoryById($id)
  {
    $db = $this->db;

    $sql = "select code, name, is_system, sln_code from t_code_table_category where id = '%s' ";
    $data = $db->query($sql, $id);
    if ($data) {
      return [
        "code" => $data[0]["code"],
        "name" => $data[0]["name"],
        "isSystem" => $data[0]["is_system"],
        "slnCode" => $data[0]["sln_code"],
      ];
    } else {
      return null;
    }
  }

  /**
   * 删除码表分类
   */
  public function deleteCodeTableCategory(&$params)
  {
    $db = $this->db;

    $id = $params["id"];

    $category = $this->getCodeTableCategoryById($id);
    if (!$category) {
      return $this->bad("要删除的码表分类不存在");
    }
    $name = $category["name"];
    $isSystem = $category["isSystem"];
    if ($isSystem == 1) {
      return $this->bad("分类[{$name}]是系统固有分类，不能删除");
    }
    $slnCode = $category["slnCode"];

    $sql = "select name from t_solution where code = '%s' ";
    $data = $db->query($sql, $slnCode);
    if (!$data) {
      return $this->bad("解决方案不存在");
    }
    $slnName = $data[0]["name"];

    // 查询该分类是否被使用了
    $sql = "select count(*) as cnt from t_code_table_md
            where category_id = '%s' ";
    $data = $db->query($sql, $id);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("码表分类[$name]下还有码表，不能删除");
    }

    $sql = "delete from t_code_table_category where id = '%s' ";
    $rc = $db->execute($sql, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $log = "解决方案[{$slnCode}-{$slnName}] - 删除码表分类[{$name}]";
    $params["log"] = $log;
    return null;
  }

  /**
   * 码表列表
   */
  public function codeTableList($params)
  {
    $db = $this->db;

    $categoryId = $params["categoryId"];

    $sql = "select id, code, name, table_name, memo, fid, md_version, is_fixed,
              enable_parent_id, handler_class_name, module_name, edit_col_cnt,
              view_paging, auto_code_length, input_company
            from t_code_table_md
            where category_id = '%s' 
            order by code, table_name";
    $data = $db->query($sql, $categoryId);

    $result = [];
    foreach ($data as $v) {
      $result[] = [
        "id" => $v["id"],
        "code" => $v["code"],
        "name" => $v["name"],
        "moduleName" => $v["module_name"],
        "tableName" => $v["table_name"],
        "fid" => $v["fid"],
        "memo" => $v["memo"],
        "mdVersion" => $v["md_version"],
        "isFixed" => $v["is_fixed"],
        "isFixedName" => $v["is_fixed"] == 1 ? "▲" : "",
        "viewPaging" => $v["view_paging"] == 1 ? "▲" : "",
        "enableParentId" => $v["enable_parent_id"],
        "handlerClassName" => $v["handler_class_name"],
        "editColCnt" => $v["edit_col_cnt"],
        "autoCodeLength" => $v["auto_code_length"] > 0 ? $v["auto_code_length"] : null,
        "inputCompany" => $v["input_company"],
      ];
    }
    return $result;
  }

  /**
   * 码表分类自定义字段 - 查询数据
   */
  public function queryDataForCategory($params)
  {
    $db = $this->db;

    $queryKey = $params["queryKey"] ?? "";
    $slnCode = $params["slnCode"];

    $sql = "select id, code, name
            from t_code_table_category
            where (sln_code = '%s') and (code like '%s' or name like '%s') ";
    $queryParams = [];
    $queryParams[] = $slnCode;
    $queryParams[] = "%{$queryKey}%";
    $queryParams[] = "%{$queryKey}%";

    $data = $db->query($sql, $queryParams);

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

  private function checkTableName($tableName, $slnCode)
  {
    $tableName = strtolower($tableName);

    $len = strlen($tableName);
    if ($len < 6) {
      return $this->bad("数据库表名长度不能小于6");
    }

    $c = ord($tableName[0]);
    $isABC = ord('a') <= $c && ord('z') >= $c;
    if (!$isABC) {
      return $this->bad("数据库表名需要以字符开头");
    }

    for ($i = 1; $i < $len; $i++) {
      $c = ord($tableName[0]);
      $isABC = ord('a') <= $c && ord('z') >= $c;
      $isNumber = ord('0') <= $c && ord('9') >= $c;
      $isOK = $isABC || $isNumber || ord('_') == $c;
      if (!$isOK) {
        $index = $i + 1;
        return $this->bad("数据库表名的第{$index}个字符非法");
      }
    }

    // 码表需要以t_ct开头
    $sn = strtolower($slnCode);
    $n = "t_{$sn}_ct_";
    if (!(substr($tableName, 0, strlen($n)) == $n)) {
      return $this->bad("数据库表名需要以 <span style='color:red'>{$n}</span> 开头");
    }

    // 表名正确
    return null;
  }

  /**
   * 检查字段名是否合法
   */
  private function checkFieldName($fieldName)
  {
    $c = ord($fieldName[0]);
    $isABC = ord('a') <= $c && ord('z') >= $c;
    if (!$isABC) {
      return $this->bad("数据库字段名需要以字符开头");
    }

    $len = strlen($fieldName);
    for ($i = 1; $i < $len; $i++) {
      $c = ord($fieldName[0]);
      $isABC = ord('a') <= $c && ord('z') >= $c;
      $isNumber = ord('0') <= $c && ord('9') >= $c;
      $isOK = $isABC || $isNumber || ord('_') == $c;
      if (!$isOK) {
        $index = $i + 1;
        return $this->bad("数据库字段名的第{$index}个字符非法");
      }
    }

    // 字段名正确
    return null;
  }

  /**
   * 返回码表的系统固有列
   *
   * @return array
   */
  private function getCodeTableSysCols()
  {
    $result = [];

    // id
    $result[] = [
      "caption" => "id",
      "fieldName" => "id",
      "fieldType" => "varchar",
      "fieldLength" => 255,
      "fieldDecimal" => 0,
      "valueFrom" => 99,
      "valueFromTableName" => "",
      "valueFromColName" => "",
      "valueFromColNameDisplay" => "",
      "mustInput" => 1,
      "showOrder" => -1000,
      "showOrderInView" => -1000,
      "sysCol" => 1,
      "isVisible" => 2,
      "widthInView" => 0,
      "editorXtype" => "textfield",
      "defaultValue" => 100,
      "defaultValueExt" => "",
    ];

    // code
    $result[] = [
      "caption" => "编码",
      "fieldName" => "code",
      "fieldType" => "varchar",
      "fieldLength" => 255,
      "fieldDecimal" => 0,
      "valueFrom" => 1,
      "valueFromTableName" => "",
      "valueFromColName" => "",
      "valueFromColNameDisplay" => "",
      "mustInput" => 2,
      "showOrder" => 0,
      "showOrderInView" => 0,
      "sysCol" => 1,
      "isVisible" => 1,
      "widthInView" => 120,
      "editorXtype" => "textfield",
      "defaultValue" => 100,
      "defaultValueExt" => "",
    ];

    // name
    $result[] = [
      "caption" => "名称",
      "fieldName" => "name",
      "fieldType" => "varchar",
      "fieldLength" => 255,
      "fieldDecimal" => 0,
      "valueFrom" => 1,
      "valueFromTableName" => "",
      "valueFromColName" => "",
      "valueFromColNameDisplay" => "",
      "mustInput" => 2,
      "showOrder" => 1,
      "showOrderInView" => 1,
      "sysCol" => 1,
      "isVisible" => 1,
      "widthInView" => 200,
      "editorXtype" => "textfield",
      "defaultValue" => 100,
      "defaultValueExt" => "",
    ];

    // 助记码
    $result[] = [
      "caption" => "助记码",
      "fieldName" => "py",
      "fieldType" => "varchar",
      "fieldLength" => 255,
      "fieldDecimal" => 0,
      "valueFrom" => 99,
      "valueFromTableName" => "",
      "valueFromColName" => "",
      "valueFromColNameDisplay" => "",
      "mustInput" => 1,
      "showOrder" => -900,
      "showOrderInView" => 4,
      "sysCol" => 1,
      "isVisible" => 2,
      "widthInView" => 200,
      "editorXtype" => "textfield",
      "defaultValue" => 100,
      "defaultValueExt" => "",
    ];

    // 数据域data_org
    $result[] = [
      "caption" => "数据域",
      "fieldName" => "data_org",
      "fieldType" => "varchar",
      "fieldLength" => 255,
      "fieldDecimal" => 0,
      "valueFrom" => 99,
      "valueFromTableName" => "",
      "valueFromColName" => "",
      "valueFromColNameDisplay" => "",
      "mustInput" => 1,
      "showOrder" => -800,
      "showOrderInView" => 3,
      "sysCol" => 1,
      "isVisible" => 2,
      "widthInView" => 150,
      "editorXtype" => "textfield",
      "defaultValue" => 100,
      "defaultValueExt" => "",
    ];

    // company_id
    $result[] = [
      "caption" => "公司",
      "fieldName" => "company_id",
      "fieldType" => "varchar",
      "fieldLength" => 255,
      "fieldDecimal" => 0,
      "valueFrom" => 99,
      "valueFromTableName" => "",
      "valueFromColName" => "",
      "valueFromColNameDisplay" => "",
      "mustInput" => 1,
      "showOrder" => -700,
      "showOrderInView" => -1000,
      "sysCol" => 1,
      "isVisible" => 2,
      "widthInView" => 0,
      "editorXtype" => "textfield",
      "defaultValue" => 100,
      "defaultValueExt" => "",
    ];

    // 记录创建时间
    $result[] = [
      "caption" => "记录创建时间",
      "fieldName" => "date_created",
      "fieldType" => "datetime",
      "fieldLength" => 0,
      "fieldDecimal" => 0,
      "valueFrom" => 99,
      "valueFromTableName" => "",
      "valueFromColName" => "",
      "valueFromColNameDisplay" => "",
      "mustInput" => 1,
      "showOrder" => -699,
      "showOrderInView" => -1000,
      "sysCol" => 1,
      "isVisible" => 2,
      "widthInView" => 0,
      "editorXtype" => "textfield",
      "defaultValue" => 100,
      "defaultValueExt" => "",
    ];

    // 记录创建人id
    $result[] = [
      "caption" => "记录创建人",
      "fieldName" => "create_user_id",
      "fieldType" => "varchar",
      "fieldLength" => 255,
      "fieldDecimal" => 0,
      "valueFrom" => 99,
      "valueFromTableName" => "",
      "valueFromColName" => "",
      "valueFromColNameDisplay" => "",
      "mustInput" => 1,
      "showOrder" => -698,
      "showOrderInView" => -1000,
      "sysCol" => 1,
      "isVisible" => 2,
      "widthInView" => 0,
      "editorXtype" => "textfield",
      "defaultValue" => 100,
      "defaultValueExt" => "",
    ];

    // 记录最后一次编辑时间
    $result[] = [
      "caption" => "最后编辑时间",
      "fieldName" => "update_dt",
      "fieldType" => "datetime",
      "fieldLength" => 0,
      "fieldDecimal" => 0,
      "valueFrom" => 99,
      "valueFromTableName" => "",
      "valueFromColName" => "",
      "valueFromColNameDisplay" => "",
      "mustInput" => 1,
      "showOrder" => -697,
      "showOrderInView" => -1000,
      "sysCol" => 1,
      "isVisible" => 2,
      "widthInView" => 0,
      "editorXtype" => "textfield",
      "defaultValue" => 100,
      "defaultValueExt" => "",
    ];

    // 记录最后一次编辑人id
    $result[] = [
      "caption" => "最后编辑人",
      "fieldName" => "update_user_id",
      "fieldType" => "varchar",
      "fieldLength" => 255,
      "fieldDecimal" => 0,
      "valueFrom" => 99,
      "valueFromTableName" => "",
      "valueFromColName" => "",
      "valueFromColNameDisplay" => "",
      "mustInput" => 1,
      "showOrder" => -696,
      "showOrderInView" => -1000,
      "sysCol" => 1,
      "isVisible" => 2,
      "widthInView" => 0,
      "editorXtype" => "textfield",
      "defaultValue" => 100,
      "defaultValueExt" => "",
    ];

    // 状态
    $result[] = [
      "caption" => "状态",
      "fieldName" => "record_status",
      "fieldType" => "int",
      "fieldLength" => 11,
      "fieldDecimal" => 0,
      "valueFrom" => 2,
      "valueFromTableName" => "t_sysdict_sln0000_ct_record_status",
      "valueFromColName" => "code_int",
      "valueFromColNameDisplay" => "name",
      "mustInput" => 2,
      "showOrder" => 2,
      "showOrderInView" => 2,
      "sysCol" => 1,
      "isVisible" => 1,
      "widthInView" => 80,
      "editorXtype" => "psi_sysdictfield",
      "defaultValue" => 200,
      "defaultValueExt" => "1000",
    ];

    return $result;
  }

  /**
   * 新建码表
   *
   * @param array $params
   * @return array|null
   */
  public function addCodeTable(&$params)
  {
    $db = $this->db;

    $slnCode = $params["slnCode"];
    // 检查解决方案是否存在
    $sql = "select name from t_solution where code = '%s' ";
    $data = $db->query($sql, $slnCode);
    if (!$data) {
      return $this->bad("解决方案不存在");
    }
    $slnName = $data[0]["name"];

    $categoryId = $params["categoryId"];
    if (!$this->getCodeTableCategoryById($categoryId)) {
      return $this->bad("码表分类不存在");
    }

    $code = strtoupper($params["code"] ?? "");
    if (!$code) {
      return $this->bad("没有录入码表编码");
    }
    $name = $params["name"];
    $moduleName = $params["moduleName"] ?? $name;
    $memo = $params["memo"] ?? "";
    $py = $params["py"];
    $tableName = strtolower($params["tableName"]);
    $enableParetnId = intval($params["enableParentId"] ?? 0);
    if ($enableParetnId != 0 && $enableParetnId != 1) {
      //$enableParentId只能取值0或1
      $enableParetnId = 0;
    }
    $handlerClassName = $params["handlerClassName"];

    $editColCnt = intval($params["editColCnt"]);
    if ($editColCnt < 1) {
      $editColCnt = 1;
    } else if ($editColCnt > 4) {
      return $this->bad("编辑布局列数不能超过4");
    }
    $autoCodeLength = intval($params["autoCodeLength"]);
    if ($autoCodeLength < 0) {
      $autoCodeLength = 0;
    }
    if ($autoCodeLength > 20) {
      return $this->bad("自动编码长度不能超过20");
    }

    $viewPaging = intval($params["viewPaging"]);
    if ($viewPaging != 1 && $viewPaging != 2) {
      $viewPaging = 2;
    }
    if ($enableParetnId == 1) {
      if ($viewPaging == 1) {
        return $this->bad("层级数据视图不能分页");
      }
    }

    $inputCompany = intval($params["inputCompany"]);

    if (!in_array($inputCompany, [1, 2])) {
      return $this->badParam("inputCompany");
    }

    // 检查编码是否已经存在
    if ($code) {
      $sql = "select count(*) as cnt from t_code_table_md
              where code = '%s' ";
      $data = $db->query($sql, $code);
      $cnt = $data[0]["cnt"];
      if ($cnt > 0) {
        return $this->bad("编码为[{$code}]的码表已经存在");
      }
    }

    // 检查名称是否已经存在
    $sql = "select count(*) as cnt from t_code_table_md
            where name = '%s' ";
    $data = $db->query($sql, $name);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("名称为[{$name}]的码表已经存在");
    }

    // 检查表名是否正确
    $rc = $this->checkTableName($tableName, $slnCode);
    if ($rc) {
      return $rc;
    }
    // 检查表名是否已经存在
    $sql = "select count(*) as cnt from t_code_table_md
					where table_name = '%s' ";
    $data = $db->query($sql, $tableName);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("表名为[{$tableName}]的码表已经存在");
    }
    // 检查数据库中是否已经存在该表了
    $dbUtilDAO = new DBUtilDAO($db);
    if ($dbUtilDAO->tableExists($tableName)) {
      return $this->bad("表[{$tableName}]已经在数据库中存在了");
    }

    if ($handlerClassName) {
      // 判断后台业务处理类是否已经存在
      if (!class_exists($handlerClassName)) {
        return $this->bad("后台业务逻辑处理类[{$handlerClassName}]不存在");
      }
    }

    $id = $this->newId();
    $fid = "ct" . date("YmdHis");

    $sql = "insert into t_code_table_md (id, category_id, code, name, table_name, py, memo, fid,
              enable_parent_id, handler_class_name, module_name, edit_col_cnt, view_paging,
              auto_code_length, sln_code, input_company)
            values ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
              %d, '%s', '%s', %d, %d,
              %d, '%s', %d)";
    $rc = $db->execute(
      $sql,
      $id,
      $categoryId,
      $code,
      $name,
      $tableName,
      $py,
      $memo,
      $fid,
      $enableParetnId,
      $handlerClassName,
      $moduleName,
      $editColCnt,
      $viewPaging,
      $autoCodeLength,
      $slnCode,
      $inputCompany,
    );
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 码表标准列
    $cols = $this->getCodeTableSysCols();
    if ($enableParetnId == 1) {
      // 启用层级数据，则创建新列parent_id和full_name
      $cols[] = [
        "caption" => "上级",
        "fieldName" => "parent_id",
        "fieldType" => "varchar",
        "fieldLength" => 255,
        "fieldDecimal" => 0,
        "valueFrom" => 4,
        "valueFromTableName" => $tableName,
        "valueFromColName" => "id",
        "valueFromColNameDisplay" => "full_name",
        "mustInput" => 0,
        "showOrder" => 4,
        "showOrderInView" => -1000,
        "sysCol" => 1,
        "isVisible" => 1,
        "widthInView" => 0,
        "editorXtype" => "psi_codetable_parentidfield",
        "defaultValue" => 100,
        "defaultValueExt" => "",
      ];

      $cols[] = [
        "caption" => "全名",
        "fieldName" => "full_name",
        "fieldType" => "varchar",
        "fieldLength" => 1000,
        "fieldDecimal" => 0,
        "valueFrom" => 99,
        "valueFromTableName" => "",
        "valueFromColName" => "",
        "valueFromColNameDisplay" => "",
        "mustInput" => 0,
        "showOrder" => -1000,
        "showOrderInView" => 3,
        "sysCol" => 1,
        "isVisible" => 2,
        "widthInView" => 300,
        "editorXtype" => "textfield",
        "defaultValue" => 100,
        "defaultValueExt" => "",
      ];
    }

    foreach ($cols as $v) {
      $sql = "insert into t_code_table_cols_md (id, table_id,
                caption, db_field_name, db_field_type, db_field_length,
                db_field_decimal, show_order, value_from, value_from_table_name,
                value_from_col_name, must_input, sys_col, is_visible, width_in_view,
                show_order_in_view, editor_xtype, value_from_col_name_display,
                default_value, default_value_ext)
              values ('%s', '%s',
                '%s', '%s', '%s', %d,
                %d, %d, %d, '%s',
                '%s', %d, %d, %d, %d,
                %d, '%s', '%s',
                %d, '%s')";
      $rc = $db->execute(
        $sql,
        $this->newId(),
        $id,
        $v["caption"],
        $v["fieldName"],
        $v["fieldType"],
        $v["fieldLength"],
        $v["fieldDecimal"],
        $v["showOrder"],
        $v["valueFrom"],
        $v["valueFromTableName"],
        $v["valueFromColName"],
        $v["mustInput"],
        $v["sysCol"],
        $v["isVisible"],
        $v["widthInView"],
        $v["showOrderInView"],
        $v["editorXtype"],
        $v["valueFromColNameDisplay"],
        $v["defaultValue"],
        $v["defaultValueExt"]
      );
      if ($rc === false) {
        return $this->sqlError(__METHOD__, __LINE__);
      }
    }

    // fid: t_fid_plus
    $sql = "insert into t_fid_plus (fid, name, py, memo) values ('%s', '%s', '%s', '')";
    $rc = $db->execute($sql, $fid, $name, $py);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 权限: t_permission_plus
    $sql = "insert into t_permission_plus (id, fid, name, note, category, py, show_order)
            values ('%s', '%s', '%s', '%s', '%s','%s', %d)";
    $rc = $db->execute($sql, $fid, $fid, $moduleName . " - 模块权限", "通过菜单进入{$moduleName}模块的权限", $moduleName, "", 100);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 按钮
    $defaultButtons = $this->getDefaultButtons($fid, $name);
    foreach ($defaultButtons as $v) {
      $sql = "insert into t_code_table_buttons(id, table_id, caption, fid, on_click_frontend, show_order)
              values ('%s', '%s', '%s', '%s', '%s', %d)";
      $rc = $db->execute($sql, $this->newId(), $id, $v["caption"], $v["fid"], $v["onClick"], $v["showOrder"]);
      if ($rc === false) {
        return $this->sqlError(__METHOD__, __LINE__);
      }

      if ($v["fid"] != $fid) {
        // 按钮权限
        $sql = "insert into t_permission_plus (id, parent_fid, fid, name, note, category, py, show_order)
                values ('%s', '%s', '%s', '%s', '%s', '%s','%s', %d)";
        $buttonCaption = $v["caption"];
        $rc = $db->execute(
          $sql,
          $v["fid"],
          $fid,
          $v["fid"],
          "{$moduleName} - 按钮权限 - {$buttonCaption}",
          "{$moduleName}模块中按钮[{$buttonCaption}]的启用权限",
          $moduleName,
          "",
          200 + intval($v["showOrder"])
        );
        if ($rc === false) {
          return $this->sqlError(__METHOD__, __LINE__);
        }
      }
    }

    // 工具栏【高级】菜单下的按钮权限
    $cnt = count($defaultButtons);
    // 修改数据域
    $showOrder = $cnt + 1;
    $showOrder += 200;
    $caption = "修改数据域";
    $sql = "insert into t_permission_plus (id, parent_fid, fid, name, note, category, py, show_order)
            values ('%s', '%s', '%s', '%s', '%s', '%s','%s', %d)";
    $f = "{$fid}-edit-dataorg";
    $rc = $db->execute(
      $sql,
      $f,
      $fid,
      $f,
      "{$moduleName} - 按钮权限 - {$caption}",
      "{$moduleName}模块中按钮[{$caption}]的启用权限",
      $moduleName,
      "",
      $showOrder
    );
    // 修改助记码
    $showOrder++;
    $caption = "修改助记码";
    $sql = "insert into t_permission_plus (id, parent_fid, fid, name, note, category, py, show_order)
            values ('%s', '%s', '%s', '%s', '%s', '%s','%s', %d)";
    $f = "{$fid}-edit-py";
    $rc = $db->execute(
      $sql,
      $f,
      $fid,
      $f,
      "{$moduleName} - 按钮权限 - {$caption}",
      "{$moduleName}模块中按钮[{$caption}]的启用权限",
      $moduleName,
      "",
      $showOrder
    );

    // 码表数据域权限
    $sql = "insert into t_permission_plus (id, parent_fid, fid, name, note, category, py, show_order)
            values ('%s', '%s', '%s', '%s', '%s', '%s','%s', %d)";
    $buttonCaption = $v["caption"];
    $fidData  = $fid . "-dataorg";
    $rc = $db->execute(
      $sql,
      $fidData,
      $fid,
      $fidData,
      "{$moduleName} - 数据域权限 - {$name}在业务单据中的使用权限",
      "{$name}在业务单据中的使用权限",
      $moduleName,
      "",
      300
    );
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $log = "解决方案[{$slnCode}-{$slnName}] - 新建码表[{$name}]";
    $params["log"] = $log;
    $params["id"] = $id;
    return null;
  }

  /**
   * 创建码表在数据库中对应的物理表
   * 
   * 因为MySQL的DDL是自动提交事务，所以这段代码需要在创建码表的元数据提交后再执行
   */
  public function createCodeTableInDb($params)
  {
    $db = $this->db;

    // tableName在之前的创建元数据的方法中已经做了校验了，
    // 这里就不需要再检查是否是正确的表名了
    $tableName = strtolower($params["tableName"]);

    $enableParetnId = intval($params["enableParentId"] ?? 0);

    $cols = $this->getCodeTableSysCols();
    if ($enableParetnId == 1) {
      // 启用层级数据，则创建新列parent_id和full_name
      $cols[] = [
        "caption" => "上级",
        "fieldName" => "parent_id",
        "fieldType" => "varchar",
        "fieldLength" => 255,
        "fieldDecimal" => 0,
        "valueFrom" => 4,
        "valueFromTableName" => $tableName,
        "valueFromColName" => "id",
        "valueFromColNameDisplay" => "full_name",
        "mustInput" => 0,
        "showOrder" => 4,
        "showOrderInView" => -1000,
        "sysCol" => 1,
        "isVisible" => 1,
        "widthInView" => 0,
        "editorXtype" => "psi_codetable_parentidfield",
        "defaultValue" => 100,
        "defaultValueExt" => "",
      ];

      $cols[] = [
        "caption" => "全名",
        "fieldName" => "full_name",
        "fieldType" => "varchar",
        "fieldLength" => 1000,
        "fieldDecimal" => 0,
        "valueFrom" => 99,
        "valueFromTableName" => "",
        "valueFromColName" => "",
        "valueFromColNameDisplay" => "",
        "mustInput" => 0,
        "showOrder" => -1000,
        "showOrderInView" => 3,
        "sysCol" => 1,
        "isVisible" => 2,
        "widthInView" => 300,
        "editorXtype" => "textfield",
        "defaultValue" => 100,
        "defaultValueExt" => "",
      ];
    }

    $sql = $this->buildCreateDDL($tableName, $cols);
    $rc = $db->execute($sql);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    return null;
  }

  private function buildCreateDDL($tableName, $cols)
  {
    $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (\n";
    foreach ($cols as $v) {
      $fieldName = $v["fieldName"];
      $fieldType = $v["fieldType"];
      $fieldLength = $v["fieldLength"];
      $fieldDecimal = $v["fieldDecimal"];
      $mustInput = $v["mustInput"];
      $sysCol = $v["sysCol"];

      $type = $fieldType;

      if ($fieldType == "varchar") {
        $type .= "({$fieldLength})";
      } else if ($fieldType == "decimal") {
        $type .= "(19, {$fieldDecimal})";
      } else if ($fieldType == "int") {
        $type .= "(11)";
      }

      $sql .= "  `{$fieldName}` {$type} ";

      if ($sysCol == 1) {
        // 系统字段除了update_dt、update_user_id和parent_id之外，都不允许为NULL
        if (in_array($fieldName, ["update_dt", "update_user_id", "parent_id"])) {
          $sql .= " DEFAULT NULL";
        } else {
          $sql .= " NOT NULL";
        }
      } else {
        // 非系统字段
        if ($mustInput == 1) {
          $sql .= " NOT NULL";
        } else {
          $sql .= " DEFAULT NULL";
        }
      }

      $sql .= ",\n";
    }
    $sql .= "  PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\n";

    return $sql;
  }

  /**
   * 编辑码表主表元数据
   *
   * @param array $params
   */
  public function updateCodeTable(&$params)
  {
    $db = $this->db;

    // 码表id
    $id = $params["id"];
    $code = strtoupper($params["code"]) ?? "";
    if (!$code) {
      return $this->bad("没有录入码表编码");
    }

    $name = $params["name"];
    $moduleName = $params["moduleName"];
    $categoryId = $params["categoryId"];
    $handlerClassName = $params["handlerClassName"];
    $memo = $params["memo"] ?? "";
    $py = $params["py"];

    $t = $this->isPSISystemCodeTable($id);
    if ($t) {
      return $this->bad("{$t}的元数据不允许修改");
    }

    $editColCnt = intval($params["editColCnt"]);
    if ($editColCnt < 1) {
      $editColCnt = 1;
    } else if ($editColCnt > 4) {
      return $this->bad("编辑布局列数不能超过4");
    }
    $autoCodeLength = intval($params["autoCodeLength"]);
    if ($autoCodeLength < 0) {
      $autoCodeLength = 0;
    }
    if ($autoCodeLength > 20) {
      return $this->bad("自动编码长度不能超过20");
    }

    $viewPaging = intval($params["viewPaging"]);
    if ($viewPaging != 1 && $viewPaging != 2) {
      $viewPaging = 2;
    }

    if (!$this->getCodeTableCategoryById($categoryId)) {
      return $this->bad("码表分类不存在");
    }

    $codeTable = $this->getCodeTableById($id);
    if (!$codeTable) {
      return $this->bad("要编辑的码表不存在");
    }
    $fid = $codeTable["fid"];
    $enableParentId = $codeTable["enableParentId"];
    if ($enableParentId == 1) {
      if ($viewPaging == 1) {
        return $this->bad("层级数据视图不能分页");
      }
    }

    $inputCompany = intval($params["inputCompany"]);

    if (!in_array($inputCompany, [1, 2])) {
      return $this->badParam("inputCompany");
    }

    $slnCode = $codeTable["slnCode"];
    // 检查解决方案是否存在
    $sql = "select name from t_solution where code = '%s' ";
    $data = $db->query($sql, $slnCode);
    if (!$data) {
      return $this->bad("解决方案不存在");
    }
    $slnName = $data[0]["name"];

    if ($handlerClassName) {
      // 判断后台业务处理类是否已经存在
      if (!class_exists($handlerClassName)) {
        return $this->bad("后台业务逻辑处理类[{$handlerClassName}]不存在");
      }
    }

    $sql = "update t_code_table_md
            set code = '%s', name = '%s', module_name = '%s',
              category_id = '%s', memo = '%s',
              handler_class_name = '%s',
              md_version = md_version + 1,
              edit_col_cnt = %d, view_paging = %d,
              auto_code_length = %d, input_company = %d
            where id = '%s' ";
    $rc = $db->execute(
      $sql,
      $code,
      $name,
      $moduleName,
      $categoryId,
      $memo,
      $handlerClassName,
      $editColCnt,
      $viewPaging,
      $autoCodeLength,
      $inputCompany,
      $id
    );
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // fid
    $sql = "update t_fid_plus
              set name = '%s', py = '%s'
            where fid = '%s'";
    $rc = $db->execute($sql, $moduleName, $py, $fid);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 权限
    $sql = "update t_permission_plus
              set category = '%s', name = '%s',
                note = '%s', py = '%s' 
            where fid = '%s' ";
    $rc = $db->execute($sql, $moduleName, $moduleName . " - 模块权限", "通过菜单进入{$moduleName}模块的权限", $py, $fid);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 按钮及权限
    $sql = "select id, fid
            from t_code_table_buttons
            where table_id = '%s'
            order by show_order";
    $data = $db->query($sql, $id);
    foreach ($data as $v) {
      $fidButton = $v["fid"];
      $idButton = $v["id"];
      $buttonCaption = "";

      if ($fidButton == $fid) {
        // 不需要处理额外处理权限的按钮，其权限是进入模块的权限
        continue;
      } else
      if ($fidButton == $fid . "-add") {
        $buttonCaption = "新建{$name}";
      } else if ($fidButton == $fid . "-update") {
        $buttonCaption = "编辑{$name}";
      } else if ($fidButton == $fid . "-delete") {
        $buttonCaption = "删除{$name}";
      } else if ($fidButton == $fid . "-excel") {
        $buttonCaption = "导出Excel ";
      } else {
        return $this->badParam("fidButton - {$fidButton}");
      }

      // 按钮
      $sql = "update t_code_table_buttons
              set caption = '%s'
              where id = '%s'";
      $rc = $db->execute($sql, $buttonCaption, $idButton);
      if ($rc === false) {
        return $this->sqlError(__METHOD__, __LINE__);
      }

      // 权限
      $sql = "update t_permission_plus
              set category = '%s', name = '%s',
                note = '%s' 
              where parent_fid = '%s' and fid = '%s'";
      $rc = $db->execute(
        $sql,
        $moduleName,
        "{$moduleName} - 按钮权限 - {$buttonCaption}",
        "{$moduleName}模块中按钮[{$buttonCaption}]的启用权限",
        $fid,
        $fidButton
      );
      if ($rc === false) {
        return $this->sqlError(__METHOD__, __LINE__);
      }
    }

    // 工具栏【高级】菜单下的按钮权限
    // 修改数据域
    $buttonCaption = "修改数据域";
    $fidButton = "{$fid}-edit-dataorg";
    $sql = "update t_permission_plus
            set category = '%s', name = '%s',
              note = '%s' 
            where parent_fid = '%s' and fid = '%s'";
    $rc = $db->execute(
      $sql,
      $moduleName,
      "{$moduleName} - 按钮权限 - {$buttonCaption}",
      "{$moduleName}模块中按钮[{$buttonCaption}]的启用权限",
      $fid,
      $fidButton
    );
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }
    // 修改助记码
    $buttonCaption = "修改助记码";
    $fidButton = "{$fid}-edit-py";
    $sql = "update t_permission_plus
            set category = '%s', name = '%s',
              note = '%s' 
            where parent_fid = '%s' and fid = '%s'";
    $rc = $db->execute(
      $sql,
      $moduleName,
      "{$moduleName} - 按钮权限 - {$buttonCaption}",
      "{$moduleName}模块中按钮[{$buttonCaption}]的启用权限",
      $fid,
      $fidButton
    );
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }


    // 数据域权限
    $sql = "update t_permission_plus
            set category = '%s', name = '%s',
              note = '%s' 
            where parent_fid = '%s' and fid = '%s'";
    $rc = $db->execute(
      $sql,
      $moduleName,
      "{$moduleName} - 数据域权限 - {$name}在业务单据中的使用权限",
      "{$name}在业务单据中的使用权限",
      $fid,
      $fid . "-dataorg"
    );
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $log = "解决方案[{$slnCode}-{$slnName}] - 编辑码表[{$name}]的元数据";
    $params["log"] = $log;
    return null;
  }

  private function valueFromCodeToName($valueFrom)
  {
    $db = $this->db;

    $sql = "select name from t_sysdict_sln0000_ct_value_from where code = '%s' ";
    $data = $db->query($sql, $valueFrom);
    if ($data) {
      return $data[0]["name"];
    } else {
      return "[未定义]";
    }
  }

  private function editorXtypeCodeToName($code)
  {
    $db = $this->db;

    $sql = "select name from t_sysdict_sln0000_ct_editor_xtype where code = '%s' ";
    $data = $db->query($sql, $code);
    if ($data) {
      return $data[0]["name"];
    } else {
      return "[未定义]";
    }
  }

  private function isVisibleCodeToName($code)
  {
    $db = $this->db;

    $sql = "select name from t_sysdict_sln0000_ct_field_visible where code = '%s' ";
    $data = $db->query($sql, $code);
    if ($data) {
      return $data[0]["name"];
    } else {
      return "[未定义]";
    }
  }

  private function mustInputCodeToName($code)
  {
    $db = $this->db;

    $sql = "select name from t_sysdict_sln0000_ct_must_input where code = '%s' ";
    $data = $db->query($sql, $code);
    if ($data) {
      return $data[0]["name"];
    } else {
      return "[未定义]";
    }
  }

  private function defaultValueCodeToName($code, $queryFromDB =  false)
  {
    if ($queryFromDB) {
      $db = $this->db;
      $sql = "select name from t_sysdict_sln0000_ct_field_default_value where code = '%s' ";
      $data = $db->query($sql, $code);
      if ($data) {
        return $data[0]["name"];
      } else {
        return "[未定义]";
      }
    }


    switch ($code) {
      default:
      case 100:
        return "";
      case 200:
        return "固定值";
      case 300:
        return "宏";
      case 400:
        return "后台代码";
        return "";
    }
  }

  /**
   * 某个码表的列
   */
  public function codeTableColsList($params)
  {
    $db = $this->db;

    // 码表id
    $id = $params["id"];

    $sql = "select id, caption, db_field_name, db_field_type, db_field_length,
              db_field_decimal, show_order, value_from, value_from_table_name,
              value_from_col_name, must_input, sys_col, is_visible, width_in_view,
              note, show_order_in_view, editor_xtype, value_from_col_name_display,
              col_span, default_value, default_value_ext
            from t_code_table_cols_md
            where table_id = '%s' 
            order by show_order";
    $data = $db->query($sql, $id);

    $result = [];
    foreach ($data as $v) {
      $isVisible = $v["is_visible"] == 1;
      $result[] = [
        "id" => $v["id"],
        "caption" => $v["caption"],
        "fieldName" => $v["db_field_name"],
        "fieldType" => $v["db_field_type"],
        "fieldLength" => $v["db_field_type"] == "datetime" ? null : $v["db_field_length"],
        "fieldDecimal" => $v["db_field_type"] == "decimal" ? $v["db_field_decimal"] : null,
        "showOrder" => $v["show_order"],
        "valueFrom" => $this->valueFromCodeToName($v["value_from"]),
        "valueFromTableName" => $v["value_from_table_name"],
        "valueFromColName" => $v["value_from_col_name"],
        "valueFromColNameDisplay" => $v["value_from_col_name_display"],
        "mustInput" => $v["must_input"] == 2 ? "必录项" : "",
        "sysCol" => $v["sys_col"] == 1 ? "▲" : "",
        "sysColRawValue" => $v["sys_col"],
        "isVisible" => $isVisible ? "可见" : "不可见",
        "widthInView" => $isVisible ? ($v["width_in_view"] ?? 100) : null,
        "note" => $v["note"],
        "showOrderInView" => $v["show_order_in_view"],
        "editorXtype" => $this->editorXtypeCodeToName($v["editor_xtype"]),
        "colSpan" => $v["col_span"],
        "defaultValue" => $this->defaultValueCodeToName($v["default_value"]),
        "defaultValueExt" => $v["default_value_ext"],
      ];
    }

    return $result;
  }

  public function getCodeTableById($id)
  {
    $db = $this->db;

    $sql = "select code, name, fid, is_fixed, table_name, enable_parent_id, sln_code 
            from t_code_table_md where id = '%s' ";
    $data = $db->query($sql, $id);
    if ($data) {
      return [
        "code" => $data[0]["code"],
        "name" => $data[0]["name"],
        "fid" => $data[0]["fid"],
        "isFixed" => $data[0]["is_fixed"],
        "tableName" => $data[0]["table_name"],
        "enableParentId" => $data[0]["enable_parent_id"],
        "slnCode" => $data[0]["sln_code"],
      ];
    } else {
      return null;
    }
  }

  /**
   * 删除码表
   */
  public function deleteCodeTable(&$params)
  {
    $db = $this->db;

    // 码表id
    $id = $params["id"];

    $codeTable = $this->getCodeTableById($id);
    if (!$codeTable) {
      return $this->bad("要删除的码表不存在");
    }
    $name = $codeTable["name"];
    $fid = $codeTable["fid"];
    $isFixed = $codeTable["isFixed"];
    if ($isFixed == 1) {
      return $this->bad("码表[$name]是系统固有码表，不能删除");
    }
    $tableName = $codeTable["tableName"];
    $slnCode = $codeTable["slnCode"];
    // 检查解决方案是否存在
    $sql = "select name from t_solution where code = '%s' ";
    $data = $db->query($sql, $slnCode);
    if (!$data) {
      return $this->bad("解决方案不存在");
    }
    $slnName = $data[0]["name"];

    // 检查fid是否在菜单中使用了
    $sql = "select count(*) as cnt from t_menu_item where fid = '%s' ";
    $data = $db->query($sql, $fid);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("当前码表已经挂接在主菜单中了<br/>在菜单项没有从主菜单中删除之前，码表也不能删除");
    }
    $sql = "select count(*) as cnt from t_menu_item_plus where fid = '%s' ";
    $data = $db->query($sql, $fid);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("当前码表已经挂接在主菜单中了<br/>在菜单项没有从主菜单中删除之前，码表也不能删除");
    }

    // 码表有了数据，也不能删除元数据
    // 这样限制，是为了在系统上线后，减少误删元数据的情形发生
    $sql = "select count(*) as cnt from {$tableName}";
    $data = $db->query($sql);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("当前码表已经有数据，这时不能删除元数据");
    }

    // 列
    $sql = "delete from t_code_table_cols_md where table_id = '%s' ";
    $rc = $db->execute($sql, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 主表
    $sql = "delete from t_code_table_md where id = '%s' ";
    $rc = $db->execute($sql, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // fid
    $sql = "delete from t_fid_plus where fid = '%s' ";
    $rc = $db->execute($sql, $fid);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 权限
    $sql = "delete from t_permission_plus where fid = '%s' ";
    $rc = $db->execute($sql, $fid);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 删除按钮权限
    $sql = "delete from t_permission_plus where parent_fid = '%s' ";
    $rc = $db->execute($sql, $fid);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 删除按钮
    $sql = "delete from t_code_table_buttons where table_id = '%s' ";
    $rc = $db->execute($sql, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $log = "解决方案[{$slnCode}-{$slnName}] - 删除码表[{$name}]的元数据";
    $params["log"] = $log;
    return null;
  }

  /**
   * 返回码表的默认按钮
   */
  private function getDefaultButtons($fid, $name)
  {
    $result = [];

    // 新建
    $result[] = [
      "caption" => "新建{$name}",
      "showOrder" => 1,
      "fid" => "{$fid}-add",
      "onClick" => "_onAddCodeTableRecord",
    ];
    // 编辑
    $result[] = [
      "caption" => "编辑{$name}",
      "showOrder" => 2,
      "fid" => "{$fid}-update",
      "onClick" => "_onEditCodeTableRecord",
    ];
    //删除
    $result[] = [
      "caption" => "删除{$name}",
      "showOrder" => 3,
      "fid" => "{$fid}-delete",
      "onClick" => "_onDeleteCodeTableRecord",
    ];

    // 分隔符
    $result[] = [
      "caption" => "-",
      "showOrder" => 4,
      "fid" => $fid,
      "onClick" => "",
    ];

    // 导出Excel
    $result[] = [
      "caption" => "导出Excel",
      "showOrder" => 5,
      "fid" => "{$fid}-excel",
      "onClick" => "_onExcelCodeTableRecord",
    ];

    // 分隔符
    $result[] = [
      "caption" => "-",
      "showOrder" => 6,
      "fid" => $fid,
      "onClick" => "",
    ];

    // 刷新
    $result[] = [
      "caption" => "刷新",
      "showOrder" => 7,
      "fid" => $fid,
      "onClick" => "_onRefreshCodeTableRecord",
    ];

    return $result;
  }

  private function enableParentCodeToName($code)
  {
    switch ($code) {
      case 0:
        return "否";
      case 1:
        return "是";
      default:
        return "否";
    }
  }

  private function viewPagingCodeToName($code)
  {
    return $code == 1 ? "分页" : "不分页";
  }

  private function inputCompanyCodeToName($code)
  {
    $db = $this->db;

    $sql = "select name from t_sysdict_sln0000_ct_input_company where code = '%s' ";
    $data = $db->query($sql, $code);
    if ($data) {
      return $data[0]["name"];
    } else {
      return "[未定义]";
    }
  }

  /**
   * 查询码表主表元数据
   */
  public function codeTableInfo($params)
  {
    $db = $this->db;

    // 码表id
    $id = $params["id"];

    $sql = "select c.name as category_name, d.code, d.name,
              d.table_name, d.category_id, d.memo, d.enable_parent_id,
              d.handler_class_name, d.module_name, d.edit_col_cnt,
              d.view_paging, d.auto_code_length, d.input_company
            from t_code_table_md d, t_code_table_category c
            where d.id = '%s' and d.category_id = c.id ";
    $data = $db->query($sql, $id);
    if ($data) {
      $v = $data[0];
      return [
        "code" => $v["code"],
        "name" => $v["name"],
        "moduleName" => $v["module_name"],
        "tableName" => $v["table_name"],
        "categoryId" => $v["category_id"],
        "categoryName" => $v["category_name"],
        "enableParentId" => $v["enable_parent_id"],
        "enableParentName" => $this->enableParentCodeToName($v["enable_parent_id"]),
        "handlerClassName" => $v["handler_class_name"],
        "memo" => $v["memo"],
        "editColCnt" => $v["edit_col_cnt"],
        "viewPaging" => $v["view_paging"],
        "viewPagingDisplay" => $this->viewPagingCodeToName($v["view_paging"]),
        "autoCodeLength" => $v["auto_code_length"],
        "inputCompany" => $v["input_company"],
        "inputCompanyDisplay" => $this->inputCompanyCodeToName($v["input_company"]),
      ];
    } else {
      return $this->emptyResult();
    }
  }

  /**
   * 新建码表列 - 写入元数据
   *
   * @param array $params
   * @return null|array
   */
  public function addCodeTableCol(&$params)
  {
    $db = $this->db;

    $codeTableId = $params["codeTableId"];
    $caption = $params["caption"];
    $fieldName = strtolower($params["fieldName"]);
    $fieldType = strtolower($params["fieldType"]);
    $fieldLength = $params["fieldLength"];
    $fieldDecimal = $params["fieldDecimal"];
    $valueFrom = intval($params["valueFrom"]);
    $valueFromTableName = $params["valueFromTableName"];
    $valueFromColName = $params["valueFromColName"];
    $valueFromColNameDisplay = $params["valueFromColNameDisplay"];
    $mustInput = $params["mustInput"];
    $widthInView = $params["widthInView"];
    $showOrder = $params["showOrder"];
    $showOrderInView = $params["showOrderInView"];
    $isVisible = $params["isVisible"];
    $editorXtype = $params["editorXtype"];
    $memo = $params["memo"];
    $colSpan = intval($params["colSpan"]);
    if ($colSpan < 1) {
      $colSpan = 1;
    }
    $defaultValue = intval($params["defaultValue"]);
    $defaultValueExt = $params["defaultValueExt"];
    if (!in_array($defaultValue, [100, 200, 300, 400])) {
      $defaultValue = 100;
      $defaultValueExt = "";
    }

    $t = $this->isPSISystemCodeTable($codeTableId);
    if ($t) {
      return $this->bad("{$t}不允许新建字段");
    }

    // 检查码表是否存在
    $codeTable = $this->getCodeTableById($codeTableId);
    if (!$codeTable) {
      return $this->bad("要新建列的码表不存在");
    }
    $tableName = $codeTable["tableName"];
    $codeTableName = $codeTable["name"];

    // 检查字段名是否合法
    $rc = $this->checkFieldName($fieldName);
    if ($rc) {
      return $rc;
    }

    // 检查数据库中码表是否已经存在该字段了
    $dbUtilDAO = new DBUtilDAO($db);
    if ($dbUtilDAO->columnExists($tableName, $fieldName)) {
      return $this->bad("在表[{$tableName}]中已经存在字段[{$fieldName}]了");
    }

    if ($fieldName == "record_status_code_int") {
      return $this->bad("[record_status_code_int]是系统保留的字段名，不能使用这个字段名");
    }

    // 检查字段类型
    if ($fieldType == "varchar") {
      $fieldLength = intval($fieldLength);
      if ($fieldLength <= 0) {
        $fieldLength = 255;
      }
    } else if ($fieldType == "int") {
      $fieldLength = 11;
    } else if ($fieldType == "decimal") {
      $fieldLength = 19;
      $fieldDecimal = intval($fieldDecimal);
      if ($fieldDecimal < 0) {
        $fieldDecimal = 0;
      }
      if ($fieldDecimal > 8) {
        return $this->bad("字段类型[decimal]的小数位数不能超过8位");
      }
    } else if ($fieldType == "datetime") {
      //占位
    } else {
      return $this->bad("字段类型[{$fieldType}]目前还不支持");
    }

    if ($valueFrom < 1 || $valueFrom > 5) {
      return $this->bad("字段值来源没有正确设置");
    }
    if ($valueFrom == 2) {
      // 引用系统数据字典
      // 检查系统数据字典是否存在
      $sql = "select count(*) as cnt from t_dict_table_md where table_name = '%s' ";
      $data = $db->query($sql, $valueFromTableName);
      $cnt = $data[0]["cnt"];
      if ($cnt == 0) {
        return $this->bad("系统数据字典[{$valueFromTableName}]的元数据不存在");
      }
      if (!$dbUtilDAO->tableExists($valueFromTableName)) {
        return $this->bad("系统数据字典[{$valueFromTableName}]在数据库中不存在");
      }
      if (!$dbUtilDAO->columnExists($valueFromTableName, $valueFromColName)) {
        return $this->bad("系统数据字典[{$valueFromTableName}]中不存在列[{$valueFromColName}]");
      }
      if (!$valueFromColNameDisplay) {
        // 没有设置显示字段，就默认取关联字段
        $valueFromColNameDisplay = $valueFromColName;
      } else {
        if (!$dbUtilDAO->columnExists($valueFromTableName, $valueFromColNameDisplay)) {
          return $this->bad("系统数据字典[{$valueFromTableName}]中不存在列[{$valueFromColNameDisplay}]");
        }
      }
    }
    if ($valueFrom == 3) {
      // 引用其他码表
      if ($tableName == $valueFromTableName) {
        // TODO 这个限制是否合理，待定
        return $this->bad("字段值来源不能引用自身");
      }

      // 检查码表是否存在
      $sql = "select id from t_code_table_md where table_name = '%s' ";
      $data = $db->query($sql, $valueFromTableName);
      if (!$data) {
        return $this->bad("码表[{$valueFromTableName}]的元数据不存在");
      }
      $valueFromTableId = $data[0]["id"];
      $sql = "select count(*) as cnt from t_code_table_cols_md where table_id = '%s' and db_field_name = '%s' ";
      $data = $db->query($sql, $valueFromTableId, $valueFromColName);
      $cnt = $data[0]["cnt"];
      if ($cnt == 0) {
        return $this->bad("码表[$valueFromTableName]的列[{$valueFromColName}]的元数据不存在");
      }
      if (!$dbUtilDAO->tableExists($valueFromTableName)) {
        return $this->bad("码表[{$valueFromTableName}]在数据库中不存在");
      }
      if (!$dbUtilDAO->columnExists($valueFromTableName, $valueFromColName)) {
        return $this->bad("码表[{$valueFromTableName}]中不存在列[{$valueFromColName}]");
      }
      if (!$valueFromColNameDisplay) {
        // 没有设置显示字段，就默认取关联字段
        $valueFromColNameDisplay = $valueFromColName;
      } else {
        if (!$dbUtilDAO->columnExists($valueFromTableName, $valueFromColNameDisplay)) {
          return $this->bad("码表[{$valueFromTableName}]中不存在列[{$valueFromColNameDisplay}]");
        }
      }
    }
    if ($valueFrom == 4) {
      // 引用自身
      $valueFromTableName = $tableName;
      $sql = "select count(*) as cnt from t_code_table_cols_md where table_id = '%s' and db_field_name = '%s' ";
      $data = $db->query($sql, $codeTableId, $valueFromColName);
      $cnt = $data[0]["cnt"];
      if ($cnt == 0) {
        return $this->bad("码表[$valueFromTableName]的列[{$valueFromColName}]的元数据不存在");
      }
      if (!$dbUtilDAO->columnExists($valueFromTableName, $valueFromColName)) {
        return $this->bad("码表[{$valueFromTableName}]中不存在列[{$valueFromColName}]");
      }
      if (!$valueFromColNameDisplay) {
        // 没有设置显示字段，就默认取关联字段
        $valueFromColNameDisplay = $valueFromColName;
      } else {
        if (!$dbUtilDAO->columnExists($valueFromTableName, $valueFromColNameDisplay)) {
          return $this->bad("码表[{$valueFromTableName}]中不存在列[{$valueFromColNameDisplay}]");
        }
      }
    }

    // 写入字段元数据
    $id = $this->newId();
    $sql = "insert into t_code_table_cols_md (id, table_id, caption, 
              db_field_name, db_field_type, db_field_length, db_field_decimal,
              show_order, value_from, value_from_table_name, value_from_col_name,
              must_input, sys_col, is_visible, width_in_view, note, 
              show_order_in_view, editor_xtype, value_from_col_name_display,
              col_span, default_value, default_value_ext)
            values ('%s', '%s', '%s',
              '%s', '%s', %d, %d,
              %d, %d, '%s', '%s',
              %d, %d, %d, %d, '%s',
              %d, '%s', '%s',
              %d, %d, '%s')";
    $rc = $db->execute(
      $sql,
      $id,
      $codeTableId,
      $caption,
      $fieldName,
      $fieldType,
      $fieldLength,
      $fieldDecimal,
      $showOrder,
      $valueFrom,
      $valueFromTableName,
      $valueFromColName,
      $mustInput,
      2,
      $isVisible,
      $widthInView,
      $memo,
      $showOrderInView,
      $editorXtype,
      $valueFromColNameDisplay,
      $colSpan,
      $defaultValue,
      $defaultValueExt
    );
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    //操作成功
    $params["log"] = "新建码表[{$codeTableName}]列 ：{$caption}";
    $params["id"] = $id;
    return null;
  }

  /**
   * 新建码表列 - 创建实际列
   *
   * @param array $params
   * @return null|array
   */
  public function addCodeTableColInDb($params)
  {
    $db = $this->db;

    $codeTableId = $params["codeTableId"];
    // 检查码表是否存在
    $codeTable = $this->getCodeTableById($codeTableId);
    if (!$codeTable) {
      return $this->bad("要新建列的码表不存在");
    }
    $tableName = $codeTable["tableName"];

    // 在创建元数据的方法中保证下列参数正确
    $fieldName = strtolower($params["fieldName"]);
    $fieldType = strtolower($params["fieldType"]);
    $fieldLength = $params["fieldLength"];
    $fieldDecimal = $params["fieldDecimal"];

    // 在数据库表中创建字段
    $sql = "alter table {$tableName} add {$fieldName} ";
    if ($fieldType == "varchar") {
      $sql .= " varchar({$fieldLength}) ";
    } else if ($fieldType == "int") {
      $sql .= " int(11) ";
    } else if ($fieldType == "decimal") {
      $sql .= " decimal({$fieldLength}, {$fieldDecimal}) ";
    } else if ($fieldType == "datetime") {
      $sql .= " datetime ";
    } else {
      return $this->bad("字段类型[{$fieldType}]还不支持");
    }
    $sql .= " default null;";
    $rc = $db->execute($sql);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    //操作成功
    return null;
  }

  /**
   * 编辑码表列 - 编辑元数据
   */
  public function updateCodeTableCol(&$params)
  {
    $db = $this->db;
    $id = $params["id"];
    $codeTableId = $params["codeTableId"];
    $codeTable = $this->getCodeTableById($codeTableId);
    if (!$codeTable) {
      return $this->badParam("codeTableId");
    }
    $codeTableName = $codeTable["name"];

    $t = $this->isPSISystemCodeTable($codeTableId);
    if ($t) {
      return $this->bad("{$t}不允许编辑字段");
    }

    // 检查码表列元数据是否存在
    $sql = "select sys_col from t_code_table_cols_md 
            where id = '%s' ";
    $data = $db->query($sql, $id);
    if (!$data) {
      return $this->bad("要编辑的码表列的元数据不存在");
    }

    $sysCol = $data[0]["sys_col"];

    $caption = $params["caption"];
    $widthInView = $params["widthInView"];
    $showOrder = $params["showOrder"];
    $showOrderInView = $params["showOrderInView"];
    $note = $params["memo"];
    $colSpan = intval($params["colSpan"]);
    if ($colSpan < 1) {
      $colSpan = 1;
    }
    $editorXtype = $params["editorXtype"];

    $isVisible = intval($params["isVisible"]);
    if (!in_array($isVisible, [1, 2])) {
      return $this->badParam("isVisible");
    }

    $defaultValue = intval($params["defaultValue"]);
    $defaultValueExt = $params["defaultValueExt"];
    if (!in_array($defaultValue, [100, 200, 300, 400])) {
      $defaultValue = 100;
      $defaultValueExt = "";
    }

    if ($sysCol == 1) {
      // 系统列
      $sql = "update t_code_table_cols_md
              set width_in_view = %d, show_order = %d,
                show_order_in_view = %d, note = '%s', col_span = %d
              where id = '%s' ";
      $rc = $db->execute(
        $sql,
        $widthInView,
        $showOrder,
        $showOrderInView,
        $note,
        $colSpan,
        $id
      );
      if ($rc === false) {
        return $this->sqlError(__METHOD__, __LINE__);
      }
    } else {
      $mustInput = intval($params["mustInput"]);
      if (!($mustInput == 1 || $mustInput == 2)) {
        return $this->bad("没有正确选择必须录入的属性值");
      }
      // 用户自定义列
      $sql = "update t_code_table_cols_md
              set caption = '%s', width_in_view = %d, show_order = %d,
                show_order_in_view = %d, note = '%s', col_span = %d,
                must_input = %d, editor_xtype = '%s', is_visible = %d,
                default_value = %d, default_value_ext = '%s'
              where id = '%s' ";
      $rc = $db->execute(
        $sql,
        $caption,
        $widthInView,
        $showOrder,
        $showOrderInView,
        $note,
        $colSpan,
        $mustInput,
        $editorXtype,
        $isVisible,
        $defaultValue,
        $defaultValueExt,
        $id
      );
      if ($rc === false) {
        return $this->sqlError(__METHOD__, __LINE__);
      }
    }

    // 操作成功
    $params["log"] = "编辑码表[{$codeTableName}]的列[{$caption}]的元数据";
    return null;
  }

  /**
   * 码表某列的详细信息
   */
  public function codeTableColInfo($params)
  {
    $db = $this->db;
    // $tableId = $params["tableId"];

    // col id
    $id = $params["id"];

    $result = [];

    if ($id) {
      // 编辑
      $sql = "select caption, db_field_name, db_field_type, db_field_length,
                db_field_decimal, note, show_order, value_from, 
                value_from_table_name, value_from_col_name, value_from_col_name_display,
                must_input, sys_col, is_visible, width_in_view, show_order_in_view,
                editor_xtype, col_span, default_value, default_value_ext
              from t_code_table_cols_md
              where id = '%s' ";
      $data = $db->query($sql, $id);
      if ($data) {
        $v = $data[0];
        $result["col"] = [
          "caption" => $v["caption"],
          "fieldName" => $v["db_field_name"],
          "fieldType" => $v["db_field_type"],
          "fieldLength" => $v["db_field_length"],
          "fieldDecimal" => $v["db_field_decimal"],
          "memo" => $v["note"],
          "showOrder" => $v["show_order"],
          "valueFrom" => $v["value_from"],
          "valueFromDisplay" => $this->valueFromCodeToName($v["value_from"]),
          "valueFromTableName" => $v["value_from_table_name"],
          "valueFromColName" => $v["value_from_col_name"],
          "valueFromColNameDisplay" => $v["value_from_col_name_display"],
          "mustInput" => $v["must_input"],
          "mustInputDisplay" => $this->mustInputCodeToName($v["must_input"]),
          "sysCol" => $v["sys_col"],
          "isVisible" => $v["is_visible"],
          "isVisibleDisplay" => $this->isVisibleCodeToName($v["is_visible"]),
          "widthInView" => $v["width_in_view"],
          "showOrderInView" => $v["show_order_in_view"],
          "editorXtype" => $v["editor_xtype"],
          "editorXtypeDisplay" => $this->editorXtypeCodeToName($v["editor_xtype"]),
          "colSpan" => $v["col_span"],
          "defaultValue" => $v["default_value"],
          "defaultValueDisplay" => $this->defaultValueCodeToName($v["default_value"], true),
          "defaultValueExt" => $v["default_value_ext"],
        ];
      }
    }
    return $result;
  }

  /**
   * 删除码表列
   */
  public function deleteCodeTableCol(&$params)
  {
    $db = $this->db;
    // 码表id
    $tableId = $params["tableId"];
    // 列id
    $id = $params["id"];

    $sql = "select table_name, name from t_code_table_md where id = '%s' ";
    $data = $db->query($sql, $tableId);
    if (!$data) {
      return $this->bad("码表不存在");
    }
    $tableName = $data[0]["table_name"];
    $name = $data[0]["name"];

    $sql = "select caption, sys_col, db_field_name 
            from t_code_table_cols_md 
            where id = '%s' and table_id = '%s' ";
    $data = $db->query($sql, $id, $tableId);
    if (!$data) {
      return $this->bad("要删除的列不存在");
    }
    $caption = $data[0]["caption"];
    $sysCol = $data[0]["sys_col"];
    if ($sysCol == 1) {
      return $this->bad("列[{$caption}]是系统固有列，不能删除");
    }
    $colName = $data[0]["db_field_name"];

    // 检查该字段是否被其他码表使用
    $sql = "select t.name, c.caption
            from t_code_table_md t, t_code_table_cols_md c
            where t.id = c.table_id and c.value_from = 3
              and c.value_from_table_name = '%s'
              and c.value_from_col_name = '%s' ";
    $data = $db->query($sql, $tableName, $colName);
    if ($data) {
      $n = $data[0]["name"];
      $c = $data[0]["caption"];
      return $this->bad("码表[{$n}]的列[{$c}]引用当前字段，所以不能删除当前字段");
    }

    // 执行删除操作
    $sql = "delete from t_code_table_cols_md where id = '%s' and table_id = '%s' ";
    $rc = $db->execute($sql, $id, $tableId);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $params["log"] = "删除码表[{$name}]列[{$caption}]的元数据";
    return null;
  }

  /**
   * 查询是否是固有码表，例如：t_org、t_user等
   * 
   * @param $id 码表的id
   * 
   * @return null:非固有码表。非null，则是对于的码表数据库名称,例如：t_org
   */
  private function isPSISystemCodeTable($id)
  {
    $list = [
      ["id" => "AFB52688-851E-11EA-B071-E86A641ED142", "tableName" => "t_org"],
      ["id" => "1C7AE1C9-85CC-11EA-A819-E86A641ED142", "tableName" => "t_user"],
      ["id" => "49F3F27F-8607-11EA-A0E2-E86A641ED142", "tableName" => "t_warehouse"],
      ["id" => "C68DBABE-860B-11EA-A0E2-E86A641ED142", "tableName" => "t_goods_category"],
      ["id" => "8BD2B19E-8623-11EA-B463-E86A641ED142", "tableName" => "t_goods_unit"],
      ["id" => "D7D9D328-8834-11EA-8C36-E86A641ED142", "tableName" => "t_goods_brand"],
    ];

    foreach ($list as $v) {
      if ($v["id"] == $id) {
        return $v["tableName"];
      }
    }

    // 不是固有码表
    return null;
  }

  /**
   * 把码表转化为系统固有码表
   */
  public function convertCodeTable(&$params)
  {
    $db = $this->db;
    $id = $params["id"];

    $codeTable = $this->getCodeTableById($id);
    if (!$codeTable) {
      return $this->bad("要操作的表单不存在");
    }
    $isFixed = $codeTable["isFixed"];
    $codeTableName = $codeTable["name"];

    if ($isFixed == 1) {
      return $this->bad("[{$codeTableName}]已经是系统固有码表了，不需要再次转换");
    }

    $sql = "update t_code_table_md
            set is_fixed = 1
            where id = '%s' ";
    $rc = $db->execute($sql, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    $sql = "update t_code_table_cols_md
            set sys_col = 1
            where table_id = '%s' ";
    $rc = $db->execute($sql, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $params["name"] = $codeTableName;
    return null;
  }

  /**
   * 查询码表编辑界面字段的显示次序
   */
  public function queryCodeTableEditColShowOrder($params)
  {
    $db = $this->db;

    $id = $params["id"];

    $sql = "select caption, db_field_name
            from t_code_table_cols_md
            where table_id = '%s' and is_visible = 1 
            order by show_order";
    $data = $db->query($sql, $id);
    $result = [];
    foreach ($data as $v) {
      $result[] = [
        "caption" => $v["caption"],
        "dataIndex" => $v["db_field_name"],
      ];
    }
    return $result;
  }

  /**
   * 保存编辑界面字段显示次序
   */
  public function saveColEditShowOrder(&$params)
  {
    $db = $this->db;

    $id = $params["id"];
    $json = $params["json"];
    $layout = json_decode(html_entity_decode($json), true);
    if (!$layout) {
      return $this->badParam("json");
    }

    $sql = "select name
            from t_code_table_md
            where id = '%s' ";
    $data = $db->query($sql, $id);
    if (!$data) {
      return $this->bad("码表不存在");
    }
    $v = $data[0];
    $codeTableName = $v["name"];

    foreach ($layout as $i => $v) {
      $sql = "update t_code_table_cols_md
              set show_order = %d
              where table_id = '%s' and db_field_name = '%s' ";
      $rc = $db->execute($sql, $i, $id, $v["dataIndex"]);
      if ($rc === false) {
        return $this->sqlError(__METHOD__, __LINE__);
      }
    }

    // 操作成功
    $params["name"] = $codeTableName;
    return null;
  }

  /**
   * 码表生成SQL
   */
  public function codeTableGenSQL($params)
  {
    $id = $params["id"];

    return $this->genMetaDataSQL($id);
  }

  /**
   * 生成一个码表的元数据的SQL
   * 
   * @param $id 码表id
   */
  private function genMetaDataSQL($id)
  {
    $db = $this->db;
    $sql = "select code, name, table_name, category_id, memo,
              py, fid, md_version, is_fixed, enable_parent_id, handler_class_name,
              module_name, edit_col_cnt, view_paging, input_company
            from t_code_table_md 
            where id = '%s' ";
    $data = $db->query($sql, $id);
    if (!$data) {
      return $this->bad("码表不存在");
    }
    $v = $data[0];

    // t_code_table_md
    $code = $v["code"];
    $name = $v["name"];
    $tableName = $v["table_name"];
    $categoryId = $v["category_id"];
    $memo = $v["memo"];
    $py = $v["py"];
    $fid = $v["fid"];
    $mdVersion = $v["md_version"];
    $isFixed = $v["is_fixed"];
    $enableParentId = $v["enable_parent_id"];
    $handlerClassName = $v["handler_class_name"];
    $moduleName = $v["module_name"];
    $editColCnt = $v["edit_col_cnt"];
    $viewPaging = $v["view_paging"];
    $inputCompany = $v["input_company"];
    $lineSep = "# " . str_repeat("-", 78) . "\n";
    $result = $lineSep;
    $result .= "# 码表：{$name}\n";
    $result .= "# 元数据\n";
    $result .= $lineSep;
    $result .= "DELETE FROM `t_code_table_md` where `id` = '{$id}';\n";
    $result .= "INSERT INTO `t_code_table_md` (`id`, `code`, `name`, `table_name`, `category_id`, `memo`,";
    $result .= " `py`, `fid`, `md_version`, `is_fixed`, `enable_parent_id`, `handler_class_name`,";
    $result .= " `module_name`, `edit_col_cnt`, `view_paging`, `input_company`)\n";
    $result .= "VALUES ('{$id}', '{$code}', '{$name}', '{$tableName}', '{$categoryId}', '{$memo}',";
    $result .= " '{$py}', '{$fid}', $mdVersion, $isFixed, $enableParentId, '{$handlerClassName}',";
    $result .= " '{$moduleName}', $editColCnt, $viewPaging, $inputCompany);\n\n";

    // t_code_table_cols_md
    $result .= "DELETE FROM `t_code_table_cols_md` where `table_id` = '{$id}';\n";
    $sql = "select id, caption, db_field_name, db_field_type, db_field_length, db_field_decimal,
              show_order, value_from, value_from_table_name, value_from_col_name,
              value_from_col_name_display, must_input, sys_col, is_visible, width_in_view,
              note, show_order_in_view, editor_xtype, col_span, default_value, default_value_ext
            from t_code_table_cols_md
            where table_id = '%s'";
    $data = $db->query($sql, $id);
    $cnt = count($data);
    if ($cnt > 0) {
      $result .= "INSERT INTO `t_code_table_cols_md` (`id`, `table_id`, `caption`, `db_field_name`,";
      $result .= " `db_field_type`, `db_field_length`, `db_field_decimal`, `show_order`, `value_from`,";
      $result .= " `value_from_table_name`, `value_from_col_name`, `value_from_col_name_display`,";
      $result .= " `must_input`, `sys_col`, `is_visible`, `width_in_view`, `note`, `show_order_in_view`,";
      $result .= " `editor_xtype`, `col_span`, `default_value`, `default_value_ext`) VALUES\n";
    }
    foreach ($data as $i => $v) {
      $colId = $v["id"];
      $caption = $v["caption"];
      $fieldName = $v["db_field_name"];
      $fieldType = $v["db_field_type"];
      $fieldLength = $v["db_field_length"];
      $fieldDecimal = $v["db_field_decimal"];
      $showOrder = $v["show_order"];
      $valueFrom = $v["value_from"];
      $valueFromTableName = $v["value_from_table_name"];
      $valueFromColName = $v["value_from_col_name"];
      $valueFromColNameDisplay = $v["value_from_col_name_display"];
      $mustInput = $v["must_input"];
      $sysCol = $v["sys_col"];
      $isVisible = $v["is_visible"];
      $widthInView = $v["width_in_view"];
      $note = $v["note"];
      $showOrderInView = $v["show_order_in_view"];
      $editorXtype = $v["editor_xtype"];
      $colSpan = $v["col_span"];
      $defaultValue = $v["default_value"];
      $defaultValueExt = $v["default_value_ext"];

      $result .= "('{$colId}', '{$id}', '{$caption}', '{$fieldName}',";
      $result .= " '{$fieldType}', {$fieldLength}, {$fieldDecimal}, {$showOrder}, {$valueFrom},";
      $result .= " '{$valueFromTableName}', '{$valueFromColName}', '{$valueFromColNameDisplay}',";
      $result .= " {$mustInput}, {$sysCol}, {$isVisible}, {$widthInView}, '{$note}', {$showOrderInView},";
      $result .= " '{$editorXtype}', {$colSpan}, {$defaultValue}, '{$defaultValueExt}')";
      $result .= $i < $cnt - 1 ? "," : ";";
      $result .= "\n";
    }
    $result .= "\n";

    // t_code_table_buttons
    $result .= "DELETE FROM `t_code_table_buttons` where `table_id` = '{$id}';\n";
    $sql = "select id, caption, fid, on_click_frontend, on_click_backend, show_order
            from t_code_table_buttons
            where table_id = '%s' ";
    $data = $db->query($sql, $id);
    $cnt = count($data);
    if ($cnt > 0) {
      $result .= "INSERT INTO `t_code_table_buttons` (`id`, `table_id`, `caption`, `fid`,";
      $result .= " `on_click_frontend`, `on_click_backend`, `show_order`) VALUES\n";
    }
    foreach ($data as $i => $v) {
      $btnId = $v["id"];
      $caption = $v["caption"];
      $btnFid = $v["fid"];
      $onClickFrontend = $v["on_click_frontend"];
      $onClickBackend = $v["on_click_backend"];
      $showOrder = $v["show_order"];
      $result .= "('{$btnId}', '{$id}', '{$caption}', '{$btnFid}',";
      $result .= " '{$onClickFrontend}', '{$onClickBackend}', $showOrder)";
      $result .= $i < $cnt - 1 ? "," : ";";
      $result .= "\n";
    }
    $result .= "\n";

    // t_fid_plus
    $result .= "DELETE FROM `t_fid_plus` where `fid` = '{$fid}';\n";
    $sql = "select name ,py, memo from t_fid_plus where fid = '%s' ";
    $data = $db->query($sql, $fid);
    if ($data) {
      $v = $data[0];
      $name = $v["name"];
      $py = $v["py"];
      $memo = $v["memo"];
      $result .= "INSERT INTO `t_fid_plus` (`fid`, `name`, `py`, `memo`) VALUES\n";
      $result .= "('{$fid}', '{$name}', '{$py}', '{$memo}');\n";
    }
    $result .= "\n";

    // t_permission_plus
    $result .= "DELETE FROM `t_permission_plus` where fid = '{$fid}' or parent_fid = '{$fid}';\n";

    $sql = "select id, fid, name, note, category, py, show_order, parent_fid
            from t_permission_plus
            where fid = '%s' or parent_fid = '%s'
            order by show_order";
    $data = $db->query($sql, $fid, $fid);
    $cnt = count($data);
    if ($cnt > 0) {
      $result .= "INSERT INTO `t_permission_plus` (`id`, `fid`, `name`, `note`, `category`,";
      $result .= " `py`, `show_order`, `parent_fid`) VALUES\n";
    }
    foreach ($data as $i => $v) {
      $permissionId = $v["id"];
      $permissionFid = $v["fid"];
      $name = $v["name"];
      $note = $v["note"];
      $category = $v["category"];
      $py = $v["py"];
      $showOrder = $v["show_order"];
      $parentFid = $v["parent_fid"];
      $result .= "('{$permissionId}', '{$permissionFid}', '{$name}', '{$note}', '{$category}',";
      $result .= " '{$py}', $showOrder,";
      if ($parentFid) {
        $result .= " '{$parentFid}'";
      } else {
        $result .= " null";
      }
      $result .= ")";
      $result .= $i < $cnt - 1 ? "," : ";";
      $result .= "\n";
    }

    return ["sql" => $result, "success" => true];
  }

  /**
   * 生成一个码表的CREATE 语句
   * 
   * @param $id 码表id
   */
  private function genCreateTableSQL($id)
  {
    $db = $this->db;
    $sql = "select table_name, name
            from t_code_table_md 
            where id = '%s' ";
    $data = $db->query($sql, $id);
    if (!$data) {
      return $this->bad("码表不存在");
    }
    $name = $data[0]["name"];
    $tableName = $data[0]["table_name"];

    $lineSep = "# " . str_repeat("-", 78) . "\n";
    $result = $lineSep;
    $result .= "# 码表：{$name}\n";
    $result .= "# CREATE DDL\n";
    $result .= $lineSep;

    $sql = "select db_field_name, db_field_type, db_field_length,
              db_field_decimal, must_input, sys_col
            from t_code_table_cols_md
            where table_id = '%s'
            order by show_order";
    $dataCols = $db->query($sql, $id);
    $cols = [];
    foreach ($dataCols as $v) {
      $cols[] = [
        "fieldName" => $v["db_field_name"],
        "fieldType" => $v["db_field_type"],
        "fieldLength" => $v["db_field_length"],
        "fieldDecimal" => $v["db_field_decimal"],
        "mustInput" => $v["must_input"],
        "sysCol" => $v["sys_col"],
      ];
    }
    $result .= $this->buildCreateDDL($tableName, $cols);

    return ["sql" => $result, "success" => true];
  }

  /**
   * 解决方案的全部码表生成SQL
   */
  public function codeTableSolutionGenSQL($params)
  {
    $db = $this->db;
    $slnCode = $params["slnCode"];

    // 检查解决方案是否存在
    $sql = "select name from t_solution where code = '%s' ";
    $data = $db->query($sql, $slnCode);
    if (!$data) {
      return $this->bad("解决方案不存在");
    }

    // 码表分类
    $sql = "select id, code, name, is_system 
            from t_code_table_category
            where sln_code = '%s' 
            order by code";
    $dataCategory = $db->query($sql, $slnCode);
    if (!$dataCategory) {
      return $this->bad("解决方案下还没有码表");
    }

    $lineSep = "# " . str_repeat("-", 78) . "\n";
    $result = $lineSep;
    $result .= "# 清空码表分类\n";
    $result .= $lineSep;
    $result .= "DELETE FROM t_code_table_category WHERE sln_code = '{$slnCode}';\n\n";
    $result .= $lineSep;
    $result .= "# 码表分类\n";
    $result .= $lineSep;
    foreach ($dataCategory as $category) {
      $categoryId = $category["id"];
      $categoryCode = $category["code"];
      $categoryIsSystem = $category["is_system"];
      $categoryName = $category["name"];
      $result .= "INSERT INTO t_code_table_category(id, code, name, parent_id, is_system, sln_code)\n";
      $result .= "VALUES ('{$categoryId}', '{$categoryCode}', '{$categoryName}', NULL, {$categoryIsSystem}, '$slnCode');\n";
    }

    foreach ($dataCategory as $category) {
      $categoryId = $category["id"];

      // 码表分类下的码表
      $sql = "select id
              from t_code_table_md
              where category_id = '%s'
              order by code ";
      $dataCodeTable = $db->query($sql, $categoryId);
      foreach ($dataCodeTable as $codeTable) {
        $result .= "\n";

        $codeTableId = $codeTable["id"];
        $md = $this->genMetaDataSQL($codeTableId);
        if ($md["success"]) {
          $result .= $md["sql"];
        } else {
          $result .= $md["msg"] . "\n";
        }

        $result .= "\n";
        $ddl = $this->genCreateTableSQL($codeTableId);
        if ($ddl["success"]) {
          $result .= $ddl["sql"];
        } else {
          $result .= $ddl["msg"] . "\n";
        }
      }
    }

    return ["sql" => $result, "success" => true];
  }

  /**
   * 查询解决方案列表
   */
  public function querySolutionList()
  {
    $db = $this->db;

    $sql = "select code, name, is_default from t_solution order by code";
    $data = $db->query($sql);

    $defaultCode = "";

    $list = [];
    foreach ($data as $v) {
      if ($v["is_default"] == 1) {
        $defaultCode = $v["code"];
      }

      $list[] = [
        "code" => $v["code"],
        "name" => $v["code"] . " - " . $v["name"],
      ];
    }

    return [
      "list" => $list,
      "defaultCode" => $defaultCode,
    ];
  }

  /**
   * 选择值来源的引用列 - 查询表
   */
  public function queryTablesForColRef($params)
  {
    $db = $this->db;

    $result = [];
    $valueFrom = $params["valueFrom"];
    $tableName = $params["tableName"];
    $searchKey = $params["searchKey"];
    if ($valueFrom == 2) {
      // 引用系统数据字典
      $sql = "select name, table_name from t_dict_table_md";
      $queryParams = [];
      if ($searchKey) {
        $sql .= " where table_name like '%s' ";
        $queryParams[] = "%{$searchKey}%";
      }
      $sql .= " order by table_name";
      $data = $db->query($sql, $queryParams);
      foreach ($data as $v) {
        $result[] = [
          "name" => $v["table_name"],
          "caption" => $v["name"],
        ];
      }
    } else if ($valueFrom == 3) {
      // 引用其他码表
      $sql = "select name, table_name from t_code_table_md
              where table_name <> '%s' ";
      $queryParams = [];
      $queryParams[] = $tableName;
      if ($searchKey) {
        $sql .= " and table_name like '%s' ";
        $queryParams[] = "%{$searchKey}%";
      }
      $sql .= " order by table_name";
      $data = $db->query($sql, $queryParams);
      foreach ($data as $v) {
        $result[] = [
          "name" => $v["table_name"],
          "caption" => $v["name"],
        ];
      }
    }
    return $result;
  }

  /**
   * 选择值来源的引用列 - 查询列
   */
  public function queryColsForColRef($params)
  {
    $db = $this->db;
    $result = [];

    $valueFrom = $params["valueFrom"];

    if ($valueFrom == 2) {
      // 系统数据字典
      $result[] = [
        "dbFieldName" => "code",
        "caption" => "编码 - 字符类型",
      ];
      $result[] = [
        "dbFieldName" => "code_int",
        "caption" => "编码 - 整数类型",
      ];
      $result[] = [
        "dbFieldName" => "name",
        "caption" => "值",
      ];
    } else if ($valueFrom == 3 || $valueFrom == 4) {
      // 码表
      $tableName = $params["tableName"];

      $sql = "select caption, db_field_name
              from t_code_table_md t, t_code_table_cols_md c
              where t.id = c.table_id and t.table_name = '%s' 
              order by db_field_name";
      $data = $db->query($sql, $tableName);
      foreach ($data as $v) {
        $result[] = [
          "dbFieldName" => $v["db_field_name"],
          "caption" => $v["caption"],
        ];
      }
    }

    return $result;
  }
}
