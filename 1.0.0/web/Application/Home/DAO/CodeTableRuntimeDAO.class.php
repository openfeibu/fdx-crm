<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 码表运行时DAO
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class CodeTableRuntimeDAO extends PSIBaseExDAO
{
  /**
   * 根据fid获得码表的元数据
   * 该元数据是模块级别的精简数据
   *
   * @param string $fid
   * @return array|NULL
   */
  public function getModuleMetaDataByFid($fid)
  {
    $db = $this->db;

    // TODO 这里有个疑问，取模块title，为什么是从菜单中取得？
    // 菜单标题和码表元数据中的标题，的确应该是一样的
    // 但是也有可能是误操作，被人为弄成不一样了
    // 如何选择？
    // ------------------------------------------------------
    // 能想到的一种应用场景：菜单标题是【物料-测试用途】，码表是【物料】
    // 这时候，从菜单中取值，有很不错的效果。
    // ------------------------------------------------------
    // 还有一种场景：码表没有挂接到菜单中，从浏览器直接输入fid就不应该出现该模块
    // 例如：物料分类+物料被组合成一个视图【物料管理】作为一个挂接菜单的模块
    // 这时候，物料分类和物料这两个码表就不应该可以通过浏览器直接访问到
    // 下面的代码，有避免这种场景的附带效果
    // TODO 所以结论是，应该先判断fid是否挂接了菜单，已经挂接菜单了，
    // 再取码表元数据中的标题。

    $sql = "select caption from t_menu_item_plus where fid = '%s' ";
    $data = $db->query($sql, $fid);
    if ($data) {
      $result = [];
      $result["title"] = $data[0]["caption"];
      return $result;
    } else {
      return null;
    }
  }

  /**
   * 查询码表元数据 - 运行界面用
   */
  public function getMetaDataForRuntime($params, $forBackend = false)
  {
    $db = $this->db;

    $fid = $params["fid"];

    $sql = "select id, name, table_name, enable_parent_id, edit_col_cnt, view_paging, 
              auto_code_length, input_company ";
    if ($forBackend) {
      $sql .= ",handler_class_name ";
    }
    $sql .=  " from t_code_table_md where fid = '%s' ";
    $data = $db->query($sql, $fid);
    if (!$data) {
      return null;
    }
    $v = $data[0];

    $id = $v["id"];

    $result = [
      "fid" => $fid,
      "tableName" => $v["table_name"],
      "name" => $v["name"],
      "treeView" => $v["enable_parent_id"] == 1,
      "editColCnt" => $v["edit_col_cnt"],
      "viewPaging" => $v["view_paging"],
      "autoCodeLength" => $v["auto_code_length"],
      "inputCompany" => $v["input_company"] == 2,
    ];
    if ($forBackend) {
      $result["handlerClassName"] = $v["handler_class_name"];
    }

    // 列
    $sql = "select caption, 
              db_field_name, db_field_type, db_field_length, db_field_decimal,
              sys_col, is_visible, width_in_view, must_input, value_from,
              value_from_table_name, value_from_col_name, editor_xtype, value_from_col_name_display,
              col_span, show_order_in_view, default_value, default_value_ext
            from t_code_table_cols_md
            where table_id = '%s' 
            order by show_order";
    $data = $db->query($sql, $id);
    $cols = [];
    foreach ($data as $v) {
      $isVisible = $v["is_visible"] == 1;
      $valueFrom = $v["value_from"];
      $valueFromTableName = $v["value_from_table_name"];
      $valueFromColName = $v["value_from_col_name"];
      $valueFromExtData = [];

      $col = [
        "caption" => $v["caption"],
        "fieldName" => $v["db_field_name"],
        "isVisible" => $isVisible,
        "widthInView" => $isVisible ? ($v["width_in_view"] ?? 100) : null,
        "mustInput" => $v["must_input"] == 2,
        "valueFrom" => $v["value_from"],
        "valueFromTableName" => $v["value_from_table_name"],
        "valueFromColName" => $v["value_from_col_name"],
        "valueFromColNameDisplay" => $v["value_from_col_name_display"],
        "isSysCol" => $v["sys_col"] == 1,
        "editorXtype" => $v["editor_xtype"],
        "colSpan" => $v["col_span"],
        "showOrderInView" => $v["show_order_in_view"],
        "defaultValue" => $v["default_value"],
        "defaultValueExt" => $v["default_value_ext"],
      ];

      if ($forBackend) {
        $col["fieldType"] = $v["db_field_type"];
      }

      if ($valueFrom == 2) {
        // 引用系统数据字典
        $sql = "select %s as col_1, name
                from %s
                order by show_order";
        $d = $db->query($sql, $valueFromColName, $valueFromTableName);
        foreach ($d as $item) {
          $valueFromExtData[] = [
            "{$valueFromColName}" => $item["col_1"],
            "name" => $item["name"]
          ];
        }
        $col["valueFromExtData"] = $valueFromExtData;
      } else if ($valueFrom == 3) {
        // 引用其他码表
        $sql = "select fid from t_code_table_md where table_name = '%s' ";
        $d = $db->query($sql, $valueFromTableName);
        if ($d) {
          $col["valueFromFid"] = $d[0]["fid"];
        }
      }

      $cols[] = $col;
    }
    $result["cols"] = $cols;

    $sql = "select caption, db_field_name, width_in_view
            from t_code_table_cols_md
            where table_id = '%s' and show_order_in_view >= 0
            order by show_order_in_view";
    $data = $db->query($sql, $id);
    $colsForView = [];
    foreach ($data as $v) {
      $colsForView[] = [
        "caption" => $v["caption"],
        "fieldName" => $v["db_field_name"],
        "widthInView" => $v["width_in_view"] ?? 100,
      ];
    }
    $result["colsForView"] = $colsForView;

    // 按钮
    $us = $params["userService"];
    $buttons = [];
    $sql = "select fid, caption, on_click_frontend
            from t_code_table_buttons
            where table_id = '%s'
            order by show_order";
    $data = $db->query($sql, $id);
    $lastCaption = "";
    foreach ($data as $v) {
      $caption = $v["caption"];
      $fid = $v["fid"];
      if ($caption != "-") {
        // 检查权限
        if ($us->hasPermission($fid)) {
          $buttons[] = [
            "fid" => $fid,
            "caption" => $caption,
            "onClick" => $v["on_click_frontend"],
          ];
          $lastCaption = $caption;
        }
      } else {
        // 当某个按钮没有权限的时候，容易出现前后都是分隔符的现象，会造成UI不美观
        if ($lastCaption != "-") {
          $buttons[] = [
            "caption" => "-"
          ];
          $lastCaption = $caption;
        }
      }
    }

    $result["buttons"] = $buttons;

    // 高级菜单下的按钮
    $advancedButtons = [];
    if ($us->hasPermission("{$fid}-edit-dataorg")) {
      $advancedButtons[] = [
        "caption" => "修改数据域",
        "onClick" => "_onEditDataOrg",
      ];
    }
    if ($us->hasPermission("{$fid}-edit-py")) {
      $advancedButtons[] = [
        "caption" => "修改助记码",
        "onClick" => "_onEditPY",
      ];
    }

    $result["advancedButtons"] = $advancedButtons;

    return $result;
  }

  /**
   * 自动生成码表记录的编码
   */
  private function autoCode($md)
  {
    $autoCodeLength = intval($md["autoCodeLength"]);
    if ($autoCodeLength < 0 || $autoCodeLength > 20) {
      return null;
    }

    $db = $this->db;

    $c = "1";

    $tableName = $md["tableName"];
    $sql = "select code from %s order by code desc limit 1";
    $data = $db->query($sql, $tableName);
    if ($data) {
      $code = $data[0]["code"];
      // 自动编码的时候，生成的编码都是数字流水号
      $next = intval($code) + 1;
      $c = "{$next}";
    }

    return str_pad($c, $autoCodeLength, "0", STR_PAD_LEFT);;
  }

  /**
   * 新增码表记录
   *
   * @param array $params
   * @return array|NULL
   */
  public function addRecord(&$params, $pyService)
  {
    $db = $this->db;
    $fid = $params["fid"];

    $dataOrg = $params["dataOrg"];
    $loginUserId = $params["loginUserId"];
    if ($this->loginUserIdNotExists($loginUserId)) {
      return $this->badParam("loginUserId");
    }
    if ($this->dataOrgNotExists($dataOrg)) {
      return $this->badParam("dataOrg");
    }
    $companyId = $params["companyId"];
    if ($this->companyIdNotExists($companyId)) {
      return $this->badParam("companyId");
    }

    $md = $this->getMetaDataForRuntime($params, true);

    if (!$md) {
      return $this->badParam("fid");
    }

    $codeTableName = $md["name"];

    // true: 层级数据
    $treeView = $md["treeView"];

    $autoCodeLength = intval($md["autoCodeLength"]);
    $code = $params["code"];
    if ($autoCodeLength > 0) {
      // 自动编码
      $code = $this->autoCode($md);
    }
    if (!$code) {
      return $this->bad("没有输入编码");
    }
    $name = $params["name"];
    $recordStatus = $params["record_status"];

    $id = $this->newId();

    $tableName = $md["tableName"];

    // 检查编码是否重复
    $sql = "select count(*) as cnt from %s where code = '%s' ";
    $queryParams = [];
    $queryParams[] = $tableName;
    $queryParams[] = $code;
    $data = $db->query($sql, $queryParams);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("编码为[$code]的记录已经存在");
    }

    $sql = "insert into %s (id, py, data_org, company_id, 
              date_created, create_user_id, code, name, 
              record_status";
    $sqlParams = [];
    $sqlParams[] = $tableName;

    foreach ($md["cols"] as $colMd) {
      if ($colMd["isSysCol"]) {
        continue;
      }

      // 非系统字段
      $fieldName = $colMd["fieldName"];

      $sql .= ", %s";
      $sqlParams[] = $fieldName;
    }

    if ($treeView) {
      $sql .= ", parent_id, full_name";
    }

    $sql .= ") values ('%s', '%s', '%s', '%s', 
						now(), '%s', '%s', '%s', 
						%d";
    $sqlParams[] = $id;
    $sqlParams[] = $pyService->toPY($name);
    $sqlParams[] = $dataOrg;
    $sqlParams[] = $companyId;
    $sqlParams[] = $loginUserId;
    $sqlParams[] = $code;
    $sqlParams[] = $name;
    $sqlParams[] = $recordStatus;

    foreach ($md["cols"] as $colMd) {
      if ($colMd["isSysCol"]) {
        continue;
      }

      // 非系统字段
      $fieldName = $colMd["fieldName"];
      $fieldType = $colMd["fieldType"];
      if ($fieldType == "int") {
        $sql .= ", %d";
      } else if ($fieldType == "decimal") {
        $sql .= ", %f";
      } else {
        $sql .= ", '%s'";
      }
      $sqlParams[] = $params[$fieldName];
    }

    if ($treeView) {
      $sql .= ", '%s', '%s'";
      $parentId = $params["parent_id"];
      $sqlParams[] = $parentId;
      $fullName = $name;

      $foundParentId = false;
      if ($parentId) {
        $q = [];
        $sqlQuery = "select full_name from %s where id = '%s' ";
        $q[] = $tableName;
        $q[] = $parentId;
        $d = $db->query($sqlQuery, $q);
        if ($d) {
          $foundParentId = true;
          $fullName = $d[0]["full_name"] . "\\" . $name;
        }
      }
      $sqlParams[] = $fullName;
    }
    $sql .= ")";

    $rc = $db->execute($sql, $sqlParams);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    if ($treeView && !$foundParentId) {
      // 保证顶级数据的parent_id值为null
      $q = [];
      $sql = "update %s set parent_id = null where id = '%s' ";
      $q[] = $tableName;
      $q[] = $id;
      $rc = $db->execute($sql, $q);
      if ($rc === false) {
        return $this->sqlError(__METHOD__, __LINE__);
      }
    }

    $handlerClassName = $md["handlerClassName"];
    if ($handlerClassName) {
      // 处理自定义业务逻辑
      if (!class_exists($handlerClassName)) {
        return $this->bad("后台业务逻辑类[{$handlerClassName}]不存在");
      }

      $hc = new $handlerClassName;
      if (!method_exists($hc, "afterAdd")) {
        return $this->bad("[{$handlerClassName}]没有方法afterAdd");
      }

      $rc = $hc->afterAdd($db, $fid, $id);
      if ($rc) {
        return $this->bad($rc);
      }
    }

    // 操作成功
    $params["id"] = $id;
    $params["log"] = "新增{$codeTableName} ：{$code}-{$name}";
    $params["logCategory"] = $codeTableName;
    return null;
  }

  /**
   * 编辑码表记录
   */
  public function updateRecord(&$params, $pyService)
  {
    $db = $this->db;
    $fid = $params["fid"];

    $loginUserId = $params["loginUserId"];
    if ($this->loginUserIdNotExists($loginUserId)) {
      return $this->badParam("loginUserId");
    }

    $md = $this->getMetaDataForRuntime($params, true);

    if (!$md) {
      return $this->badParam("fid");
    }

    $codeTableName = $md["name"];

    // true: 层级数据
    $treeView = $md["treeView"];

    $code = $params["code"];
    $name = $params["name"];
    $recordStatus = $params["record_status"];

    $id = $params["id"];

    $tableName = $md["tableName"];

    // 检查记录是否存在
    $sql = "select count(*) as cnt from %s where id = '%s' ";
    $data = $db->query($sql, $tableName, $id);
    $cnt = $data[0]["cnt"];
    if ($cnt == 0) {
      return $this->bad("要编辑的记录不存在");
    }

    // 检查编码是否重复
    $sql = "select count(*) as cnt from %s where code = '%s' and id <> '%s' ";
    $queryParams = [];
    $queryParams[] = $tableName;
    $queryParams[] = $code;
    $queryParams[] = $id;
    $data = $db->query($sql, $queryParams);
    $cnt = $data[0]["cnt"];
    if ($cnt > 0) {
      return $this->bad("编码为[$code]的记录已经存在");
    }

    if ($treeView) {
      // 检查上级id
      $parentId = $params["parent_id"];
      if ($parentId) {
        $sql = "select count(*) as cnt from %s where id = '%s' ";
        $data = $db->query($sql, $tableName, $parentId);
        if ($data) {
          // 检查上级id是否构成了循环引用
          if ($id == $parentId) {
            return $this->bad("不能把自身设置为上级");
          }

          while ($parentId) {
            $sql = "select parent_id from %s where id = '%s' ";
            $data = $db->query($sql, $tableName, $parentId);
            if ($data) {
              $parentId = $data[0]["parent_id"];
              if ($parentId == $id) {
                return $this->bad("不能选用当前记录的下级作为上级");
              }
            } else {
              $parentId = null;
            }
          }
        } else {
          // 传入的parent_id在数据库中不存在
          $params["parent_id"] = null;
        }
      }
    }

    $sql = "update %s 
            set code = '%s', name = '%s', update_dt = now(), update_user_id = '%s',
              record_status = %d, py = '%s' ";
    $queryParams = [];
    $queryParams[] = $tableName;
    $queryParams[] = $code;
    $queryParams[] = $name;
    $queryParams[] = $loginUserId;
    $queryParams[] = $recordStatus;
    $queryParams[] = $pyService->toPY($name);
    foreach ($md["cols"] as $colMd) {
      if ($colMd["isSysCol"]) {
        continue;
      }

      // 非系统字段
      $fieldName = $colMd["fieldName"];

      $sql .= ", %s = ";
      $queryParams[] = $fieldName;

      $fieldType = $colMd["fieldType"];
      if ($fieldType == "int") {
        $sql .= "%d";
      } else if ($fieldType == "decimal") {
        $sql .= "%f";
      } else {
        $sql .= "'%s'";
      }
      $queryParams[] = $params[$fieldName];
    }

    $sql .= " where id = '%s' ";
    $queryParams[] = $id;
    $rc = $db->execute($sql, $queryParams);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    if ($treeView) {
      $parentId = $params["parent_id"];
      if ($parentId) {
        $sql = "select full_name from %s where id = '%s' ";
        $data = $db->query($sql, $tableName, $parentId);
        if ($data) {
          $fullName = $data[0]["full_name"] . "\\" . $name;
          $sql = "update %s set parent_id = '%s', full_name = '%s' where id = '%s' ";
          $rc = $db->execute($sql, $tableName, $parentId, $fullName, $id);
          if ($rc === false) {
            return $this->sqlError(__METHOD__, __LINE__);
          }
        } else {
          $parentId = null;
        }
      }

      if ($parentId == null) {
        $sql = "update %s set parent_id = null, full_name = '%s' where id = '%s' ";
        $rc = $db->execute($sql, $tableName, $name, $id);
        if ($rc === false) {
          return $this->sqlError(__METHOD__, __LINE__);
        }
      }
    }

    $handlerClassName = $md["handlerClassName"];
    if ($handlerClassName) {
      // 处理自定义业务逻辑
      if (!class_exists($handlerClassName)) {
        return $this->bad("后台业务逻辑类[{$handlerClassName}]不存在");
      }

      $hc = new $handlerClassName;
      if (!method_exists($hc, "afterUpdate")) {
        return $this->bad("[{$handlerClassName}]没有方法afterUpdate");
      }

      $rc = $hc->afterUpdate($db, $fid, $id);
      if ($rc) {
        return $this->bad($rc);
      }
    }

    // 操作成功
    $code = $params["code"];
    $name = $params["name"];
    $params["id"] = $id;
    $params["log"] = "编辑{$codeTableName} ：{$code}-{$name}";
    $params["logCategory"] = $codeTableName;
    return null;
  }

  /**
   * 删除码表记录
   */
  public function deleteRecord(&$params)
  {
    $db = $this->db;
    $fid = $params["fid"];
    $md = $this->getMetaDataForRuntime($params, true);

    if (!$md) {
      return $this->badParam("fid");
    }

    $codeTableName = $md["name"];

    // true: 层级数据
    $treeView = $md["treeView"];

    $code = $params["code"];
    $name = $params["name"];
    $recordStatus = $params["record_status"];

    $id = $params["id"];

    $tableName = $md["tableName"];

    // 检查记录是否存在
    $sql = "select code, name from %s where id = '%s' ";
    $data = $db->query($sql, $tableName, $id);
    if (!$data) {
      return $this->bad("要删除的记录不存在");
    }
    $code = $data[0]["code"];
    $name = $data[0]["name"];

    if ($treeView) {
      // 检查是否有下级
      $sql = "select count(*) as cnt from %s where parent_id = '%s' ";
      $data = $db->query($sql, $tableName, $id);
      $cnt = $data[0]["cnt"];
      if ($cnt > 0) {
        return $this->bad("当前记录还有下级，不能删除");
      }
    }

    // 判断其他码表是否引用了当前记录
    // 这里处理的通过id引用来引用的场景，这类似处理数据库外键
    // 如果是通过其他字段的引用，需要通过handlerClassName来判断
    $sql = "select distinct t.name, t.table_name, c.db_field_name
            from t_code_table_md t, t_code_table_cols_md c
            where t.id = c.table_id and c.value_from = 3
              and c.value_from_table_name = '%s' 
              and c.value_from_col_name = 'id' ";
    $data = $db->query($sql, $tableName);
    foreach ($data as $v) {
      $foreignName = $v["name"];
      $foreignTableName = $v["table_name"];
      $foreignFieldName = $v["db_field_name"];
      $sql = "select count(*) as cnt from %s where %s = '%s'";
      $d = $db->query($sql, $foreignTableName, $foreignFieldName, $id);
      $cnt = $d[0]["cnt"];
      if ($cnt > 0) {
        return $this->bad("码表[{$foreignName}]中引用了当前记录，所以不能删除当前记录");
      }
    }

    // 表单主表是否有引用该码表记录
    $sql = "select distinct f.name, f.table_name, c.db_field_name
            from t_form_cols c, t_form f
            where c.value_from_table_name = '%s' and c.form_id = f.id";
    $data = $db->query($sql, $tableName);
    foreach ($data as $v) {
      $foreignName = $v["name"];
      $foreignTableName = $v["table_name"];
      $foreignFieldName = $v["db_field_name"];
      $sql = "select count(*) as cnt from %s where %s = '%s'";
      $d = $db->query($sql, $foreignTableName, $foreignFieldName, $id);
      $cnt = $d[0]["cnt"];
      if ($cnt > 0) {
        return $this->bad("表单[{$foreignName}]主表中引用了当前记录，所以不能删除当前记录");
      }
    }

    // 表单明细表列是否有引用该码表记录
    $sql = "select distinct f.name, f.table_name, c.db_field_name, fm.name as form_name
            from t_form_detail_cols c, t_form_detail f, t_form fm
            where c.value_from_table_name = '%s' and c.detail_id = f.id and f.form_id = fm.id";
    $data = $db->query($sql, $tableName);
    foreach ($data as $v) {
      $formName = $v["form_name"];
      $foreignName = $v["name"];
      $foreignTableName = $v["table_name"];
      $foreignFieldName = $v["db_field_name"];
      $sql = "select count(*) as cnt from %s where %s = '%s'";
      $d = $db->query($sql, $foreignTableName, $foreignFieldName, $id);
      $cnt = $d[0]["cnt"];
      if ($cnt > 0) {
        return $this->bad("表单[{$formName}]的[{$foreignName}]表中引用了当前记录，所以不能删除当前记录");
      }
    }

    $handlerClassName = $md["handlerClassName"];
    if ($handlerClassName) {
      // 处理自定义业务逻辑
      if (!class_exists($handlerClassName)) {
        return $this->bad("后台业务逻辑类[{$handlerClassName}]不存在");
      }

      $hc = new $handlerClassName;
      if (!method_exists($hc, "beforeDelete")) {
        return $this->bad("[{$handlerClassName}]没有方法beforeDelete");
      }

      $rc = $hc->beforeDelete($db, $fid, $id);
      if ($rc) {
        return $this->bad($rc);
      }
    }

    $sql = "delete from %s where id = '%s' ";
    $rc = $db->execute($sql, $tableName, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $params["log"] = "删除{$codeTableName}记录 ：{$code}-{$name}";
    $params["logCategory"] = $codeTableName;
    return null;
  }

  /**
   * 码表记录列表
   */
  public function codeTableRecordList($params)
  {
    $db = $this->db;

    $fid = $params["fid"];
    $loginUserId = $params["loginUserId"];
    if ($this->loginUserIdNotExists($loginUserId)) {
      return $this->emptyResult();
    }

    $md = $this->getMetaDataForRuntime($params, true);
    if (!$md) {
      return $this->emptyResult();
    }

    $tableName = $md["tableName"];

    $viewPaging = $md["viewPaging"] == 1;

    $sql = "select cr.id, cr.code, cr.name, u.name as create_user_name, 
            r.name as record_status, r.code_int as record_status_code_int";

    foreach ($md["cols"] as $colMd) {
      $showOrderInView = $colMd["showOrderInView"];

      // showOrderInView > 0的时候，在列表中都显示，不管该字段是否是系统字段
      // 常见的一个场景：数据域字段
      if ($colMd["isVisible"] || $showOrderInView > 0) {
        $sql .= ", cr." . $colMd["fieldName"];
      }
    }

    $sql .= " from %s cr, t_user u, t_sysdict_sln0000_ct_record_status r ";
    $queryParams = [];
    $queryParams[] = $tableName;

    $sql .= " where (cr.create_user_id = u.id) and (cr.record_status = r.code_int)";
    if ($md["inputCompany"] == 2) {
      // 启用多公司录入
      $companyId = $params["companyId"];
      $sql .= " and (cr.company_id = '%s') ";
      $queryParams[] = $companyId;
    }
    // 数据域
    $ds = new DataOrgDAO($db);
    $rs = $ds->buildSQL($fid, "cr", $loginUserId);
    if ($rs) {
      $sql .= " and " . $rs[0];
      $queryParams = array_merge($queryParams, $rs[1]);
    }

    $sql .= " order by cr.record_status desc, cr.code ";
    if ($viewPaging) {
      $start = $params["start"];
      $limit = $params["limit"];
      $sql .= " limit %d, %d ";
      $queryParams[] = $start;
      $queryParams[] = $limit;
    }

    $data = $db->query($sql, $queryParams);
    $result = [];
    foreach ($data as $v) {
      $item = [
        "id" => $v["id"],
        "code" => $v["code"],
        "name" => $v["name"],
        "record_status" => $v["record_status"],
        "record_status_code_int" => $v["record_status_code_int"],
      ];

      foreach ($md["cols"] as $colMd) {
        $showOrderInView = intval($colMd["showOrderInView"]);

        if ($colMd["isVisible"] || $showOrderInView > 0) {
          $fieldName = $colMd["fieldName"];

          $fieldType = $colMd["fieldType"];
          if ($fieldType == "datetime") {
            $item[$fieldName] = $this->toYMD($v[$fieldName]);
          } else {
            $item[$fieldName] = $v[$fieldName];
          }
        }

        $valueFrom = $colMd["valueFrom"];
        $valueFromTableName = $colMd["valueFromTableName"];
        $valueFromColName = $colMd["valueFromColName"];
        $valueFromColNameDisplay = $colMd["valueFromColNameDisplay"];
        if ($valueFrom == 2) {
          //系统数据字典
          $sql = "select {$valueFromColNameDisplay} as nv from {$valueFromTableName}
                  where {$valueFromColName} = '%s' ";
          $d = $db->query($sql, $v[$fieldName]);
          if ($d) {
            $item[$fieldName] = $d[0]["nv"];
          }
        } else if ($valueFrom == 3) {
          // 引用其他码表
          $sql = "select {$valueFromColNameDisplay} as nv from {$valueFromTableName}
                  where {$valueFromColName} = '%s' ";
          $d = $db->query($sql, $v[$fieldName]);
          if ($d) {
            $item[$fieldName] = $d[0]["nv"];
          } else {
            $item[$fieldName] = null;
          }
        }
      }

      $result[] = $item;
    }

    $cnt = 0;
    if ($viewPaging) {
      $sql = "select count(*) as cnt from %s cr where (1 =1 ) ";
      $queryParams = [];
      $queryParams[] = $tableName;

      // 数据域
      $ds = new DataOrgDAO($db);
      $rs = $ds->buildSQL($fid, "cr", $loginUserId);
      if ($rs) {
        $sql .= " and " . $rs[0];
        $queryParams = array_merge($queryParams, $rs[1]);
      }
      $data = $db->query($sql, $queryParams);
      $cnt = $data[0]["cnt"];
    } else {
      $cnt = count($result);
    }
    return ["dataList" => $result, "totalCount" => $cnt];
  }

  private function codeTableRecordListForTreeViewInternal($db, $id, $md, $rs)
  {
    $tableName = $md["tableName"];

    $sql = "select cr.id, cr.code, cr.name, cr.full_name, u.name as create_user_name, 
              r.name as record_status, cr.data_org, cr.py";

    foreach ($md["cols"] as $colMd) {
      if ($colMd["isSysCol"]) {
        continue;
      }

      if ($colMd["isVisible"]) {
        $sql .= ", cr." . $colMd["fieldName"];
      }
    }

    $sql .= " from %s cr, t_user u, t_sysdict_sln0000_ct_record_status r ";
    $queryParams = [];
    $queryParams[] = $tableName;

    $sql .= " where (cr.parent_id = '%s') and (cr.create_user_id = u.id) and (cr.record_status = r.code_int)";
    $queryParams[] = $id;
    // 数据域
    if ($rs) {
      $sql .= " and " . $rs[0];
      $queryParams = array_merge($queryParams, $rs[1]);
    }

    $sql .= " order by code";

    $data = $db->query($sql, $queryParams);
    $result = [];
    foreach ($data as $v) {
      // 递归调用自身
      $children = $this->codeTableRecordListForTreeViewInternal($db, $v["id"], $md, $rs);
      $v["children"] = $children;
      $v["leaf"] = count($children) == 0;
      $v["expanded"] = true;
      $v["iconCls"] = "PSI-CodeTable-DataCategory";

      $result[] = $v;
    }

    return $result;
  }

  /**
   * 码表记录 - 树状结构
   */
  public function codeTableRecordListForTreeView($params)
  {
    $db = $this->db;

    $fid = $params["fid"];
    $loginUserId = $params["loginUserId"];
    if ($this->loginUserIdNotExists($loginUserId)) {
      return $this->emptyResult();
    }
    $md = $this->getMetaDataForRuntime($params);
    if (!$md) {
      return $this->emptyResult();
    }

    $tableName = $md["tableName"];

    $sql = "select cr.id, cr.code, cr.name, cr.full_name, u.name as create_user_name, 
              r.name as record_status, cr.data_org, cr.py";

    foreach ($md["cols"] as $colMd) {
      if ($colMd["isSysCol"]) {
        continue;
      }

      if ($colMd["isVisible"]) {
        $sql .= ", cr." . $colMd["fieldName"];
      }
    }

    $sql .= " from %s cr, t_user u, t_sysdict_sln0000_ct_record_status r ";
    $queryParams = [];
    $queryParams[] = $tableName;

    $sql .= " where (cr.parent_id is null) and (cr.create_user_id = u.id) and (cr.record_status = r.code_int)";
    if ($md["inputCompany"] == 2) {
      // 启用多公司录入
      $companyId = $params["companyId"];
      $sql .= " and (cr.company_id = '%s') ";
      $queryParams[] = $companyId;
    }

    // 数据域
    $ds = new DataOrgDAO($db);
    $rs = $ds->buildSQL($fid, "cr", $loginUserId);
    if ($rs) {
      $sql .= " and " . $rs[0];
      $queryParams = array_merge($queryParams, $rs[1]);
    }

    $sql .= " order by code";

    $data = $db->query($sql, $queryParams);
    $result = [];
    foreach ($data as $v) {
      $children = $this->codeTableRecordListForTreeViewInternal($db, $v["id"], $md, $rs);
      $v["children"] = $children;
      $v["leaf"] = count($children) == 0;
      $v["expanded"] = true;
      $v["iconCls"] = "PSI-CodeTable-DataCategory";

      $result[] = $v;
    }

    return $result;
  }

  /**
   * 查询码表记录的详情
   */
  public function recordInfo($params)
  {
    $db = $this->db;
    $id = $params["id"];


    $md = $this->getMetaDataForRuntime($params, true);
    if (!$md) {
      // 传入了错误的fid值
      return $this->emptyResult();
    }

    // 新建 - begin
    if (!$id) {
      // 新建时候，处理各个字段的默认值

      $result = [];

      foreach ($md["cols"] as $colMd) {
        $fieldName = $colMd["fieldName"];

        $defaultValue = $colMd["defaultValue"];
        if ($defaultValue == 200) {
          // 固定值
          $defaultValueExt = $colMd["defaultValueExt"];
          $vf = $colMd["valueFrom"];
          if ($vf == 2 || $vf == 3) {
            // 2 - 系统数据字典 3 - 码表

            // $defaultValueExt中保存的是键值，需要通过关联表查询到显示值
            $valueFromTableName = $colMd["valueFromTableName"];
            $valueFromColName = $colMd["valueFromColName"];
            $valueFromColNameDisplay = $colMd["valueFromColNameDisplay"];
            $sql = "select {$valueFromColNameDisplay} as nd from {$valueFromTableName}
                    where {$valueFromColName} = '%s' ";
            $d = $db->query($sql, $defaultValueExt);
            if ($d) {
              $result[$fieldName . "_display_value"] = $d[0]["nd"];
            }
          }

          $result[$fieldName] = $defaultValueExt;
        } else if ($defaultValue == 300) {
          // 宏
          // TODO
        } else if ($defaultValue == 400) {
          // 后台PHP
          // TODO
        }
      }

      return $result;
    }
    // 新建 - end

    // 下面是编辑时候的业务逻辑

    $tableName = $md["tableName"];
    $treeView = $md["treeView"];
    $sql = "select cr.code, cr.name, cr.record_status";
    if ($treeView) {
      $sql .= ",parent_id";
    }

    foreach ($md["cols"] as $colMd) {
      if ($colMd["isSysCol"]) {
        continue;
      }

      if ($colMd["isVisible"]) {
        $sql .= ", cr." . $colMd["fieldName"];
      }
    }

    $sql .= " from %s cr 
              where (cr.id = '%s')";
    $queryParams = [];
    $queryParams[] = $tableName;
    $queryParams[] = $id;

    $data = $db->query($sql, $queryParams);
    if (!$data) {
      return $this->emptyResult();
    }

    $result = $data[0];
    foreach ($md["cols"] as $colMd) {
      if ($colMd["isSysCol"]) {
        continue;
      }

      if ($colMd["isVisible"]) {
        if ($colMd["fieldType"] == "datetime") {
          $fieldName = $colMd["fieldName"];
          $result[$fieldName] = $this->toYMD($result[$fieldName]);
        }
      }
    }

    // 层级数据
    if ($treeView) {
      $parentId = $data[0]["parent_id"];
      if ($parentId) {
        $sql = "select full_name from %s where id = '%s' ";
        $d = $db->query($sql, $tableName, $parentId);
        if ($d) {
          $result["parent_id_value"] = $d[0]["full_name"];
        }
      }
    }

    foreach ($md["cols"] as $colMd) {
      if ($colMd["valueFrom"] == 3) {
        // 码表
        $valueFromTableName = $colMd["valueFromTableName"];
        $valueFromColName = $colMd["valueFromColName"];
        $valueFromColNameDisplay = $colMd["valueFromColNameDisplay"];
        $sql = "select {$valueFromColNameDisplay} as nd from {$valueFromTableName}
                where {$valueFromColName} = '%s' ";
        $d = $db->query($sql, $result[$colMd["fieldName"]]);
        if ($d) {
          $result[$colMd["fieldName"] . "_display_value"] = $d[0]["nd"];
        }
      } else if ($colMd["valueFrom"] == 2) {
        // 系统数据字典
        $valueFromTableName = $colMd["valueFromTableName"];
        $valueFromColName = $colMd["valueFromColName"];
        $valueFromColNameDisplay = $colMd["valueFromColNameDisplay"];
        $sql = "select {$valueFromColNameDisplay} as nd from {$valueFromTableName}
                where {$valueFromColName} = '%s' ";
        $d = $db->query($sql, $result[$colMd["fieldName"]]);
        if ($d) {
          $result[$colMd["fieldName"] . "_display_value"] = $d[0]["nd"];
        }
      }
    }

    return $result;
  }

  /**
   * 码表记录引用字段 - 查询数据
   */
  public function queryDataForRecordRef($params)
  {
    $db = $this->db;
    $fid = $params["fid"];
    $queryKey = $params["queryKey"];
    $loginUserId = $params["loginUserId"];

    $sql = "select table_name from t_code_table_md where fid = '%s' ";
    $data = $db->query($sql, $fid);

    if (!$data) {
      return $this->emptyResult();
    }
    $tableName = $data[0]["table_name"];

    $sql = "select id, code, name from {$tableName} ct
            where (code like '%s' or name like '%s' or py like '%s') and (record_status = 1000) ";
    $queryParams = [];
    $queryParams[] = "%{$queryKey}%";
    $queryParams[] = "%{$queryKey}%";
    $queryParams[] = "%{$queryKey}%";
    $ds = new DataOrgDAO($db);
    $rs = $ds->buildSQL("{$fid}-dataorg", "ct", $loginUserId);
    if ($rs) {
      $sql .= " and " . $rs[0];
      $queryParams = array_merge($queryParams, $rs[1]);
    }
    $sql .= " order by code limit 20 ";

    $data = $db->query($sql, $queryParams);

    $result = [];
    foreach ($data as $v) {
      $result[] = [
        "id" => $v["id"],
        "code" => $v["code"],
        "name" => $v["name"],
      ];
    }
    return $result;
  }

  /**
   * 保存列视图布局
   */
  public function saveColViewLayout(&$params)
  {
    $db = $this->db;

    $fid = $params["fid"];
    $json = $params["json"];
    $layout = json_decode(html_entity_decode($json), true);
    if (!$layout) {
      return $this->badParam("json");
    }

    $sql = "select id, name
            from t_code_table_md
            where fid = '%s' ";
    $data = $db->query($sql, $fid);
    if (!$data) {
      return $this->badParam("fid");
    }
    $v = $data[0];
    $codeTableName = $v["name"];
    $id = $v["id"];

    foreach ($layout as $i => $v) {
      $sql = "update t_code_table_cols_md
              set show_order_in_view = %d, width_in_view = %d
              where table_id = '%s' and db_field_name = '%s' ";
      $rc = $db->execute($sql, $i, $v["width"], $id, $v["dataIndex"]);
      if ($rc === false) {
        return $this->sqlError(__METHOD__, __LINE__);
      }
    }

    // 操作成功
    $params["name"] = $codeTableName;
    return null;
  }

  /**
   * 获得码表的全部记录，用于导出Excel
   */
  public function recordListForExcel(&$params)
  {
    $db = $this->db;
    $fid = $params["fid"];

    $loginUserId = $params["loginUserId"];
    if ($this->loginUserIdNotExists($loginUserId)) {
      return [
        "error" => $this->badParam("loginUserId")
      ];
    }

    $md = $this->getMetaDataForRuntime($params, true);

    if (!$md) {
      return [
        "error" => $this->badParam("fid")
      ];
    }

    $codeTableName = $md["name"];

    $cols = [];

    foreach ($md["colsForView"] as $col) {
      $cols[] = [
        "caption" => $col["caption"],
        "fieldName" => $col["fieldName"],
      ];
    }

    $tableName = $md["tableName"];
    $sql = "select cr.id, cr.code, cr.name, u.name as create_user_name, 
            r.name as record_status, r.code_int as record_status_code_int";

    foreach ($md["cols"] as $colMd) {
      $showOrderInView = $colMd["showOrderInView"];

      // showOrderInView > 0的时候，在列表中都显示，不管该字段是否是系统字段
      // 常见的一个场景：数据域字段
      if ($colMd["isVisible"] || $showOrderInView > 0) {
        $sql .= ", cr." . $colMd["fieldName"];
      }
    }
    $sql .= " from %s cr, t_user u, t_sysdict_sln0000_ct_record_status r ";
    $queryParams = [];
    $queryParams[] = $tableName;
    $sql .= " where (cr.create_user_id = u.id) and (cr.record_status = r.code_int)";
    // 数据域
    $ds = new DataOrgDAO($db);
    $rs = $ds->buildSQL($fid, "cr", $loginUserId);
    if ($rs) {
      $sql .= " and " . $rs[0];
      $queryParams = array_merge($queryParams, $rs[1]);
    }

    $sql .= " order by cr.record_status desc, cr.code ";
    $data = $db->query($sql, $queryParams);
    $result = [];
    foreach ($data as $v) {
      $item = [
        "code" => $v["code"],
        "name" => $v["name"],
        "record_status" => $v["record_status"],
      ];

      foreach ($md["cols"] as $colMd) {
        $showOrderInView = intval($colMd["showOrderInView"]);

        if ($colMd["isVisible"] || $showOrderInView > 0) {
          $fieldName = $colMd["fieldName"];

          $fieldType = $colMd["fieldType"];
          if ($fieldType == "datetime") {
            $item[$fieldName] = $this->toYMD($v[$fieldName]);
          } else {
            $item[$fieldName] = $v[$fieldName];
          }
        }

        $valueFrom = $colMd["valueFrom"];
        $valueFromTableName = $colMd["valueFromTableName"];
        $valueFromColName = $colMd["valueFromColName"];
        $valueFromColNameDisplay = $colMd["valueFromColNameDisplay"];
        if ($valueFrom == 2) {
          //系统数据字典
          $sql = "select {$valueFromColNameDisplay} as nv from {$valueFromTableName}
                  where {$valueFromColName} = '%s' ";
          $d = $db->query($sql, $v[$fieldName]);
          if ($d) {
            $item[$fieldName] = $d[0]["nv"];
          }
        } else if ($valueFrom == 3) {
          // 引用其他码表
          $sql = "select {$valueFromColNameDisplay} as nv from {$valueFromTableName}
                  where {$valueFromColName} = '%s' ";
          $d = $db->query($sql, $v[$fieldName]);
          if ($d) {
            $item[$fieldName] = $d[0]["nv"];
          } else {
            $item[$fieldName] = null;
          }
        }
      }

      $result[] = $item;
    }

    $params["log"] = "把码表{$codeTableName}的数据导出为Excel文件";
    $params["logCategory"] = $codeTableName;

    return [
      "error" => null,
      "dataList" => $result,
      "cols" => $cols,
    ];
  }

  /**
   * 修改数据域
   */
  public function editDataOrg(&$params)
  {
    $db = $this->db;
    $id = $params["id"];
    $newDataOrg = $params["dataOrg"];
    $md = $this->getMetaDataForRuntime($params, true);

    if (!$md) {
      return $this->badParam("fid");
    }

    $codeTableName = $md["name"];

    $tableName = $md["tableName"];
    // 检查记录是否存在
    $sql = "select code, name, data_org from %s where id = '%s' ";
    $data = $db->query($sql, $tableName, $id);
    if (!$data) {
      return $this->bad("要修改数据域的记录不存在");
    }
    $code = $data[0]["code"];
    $name = $data[0]["name"];
    $oldDataOrg = $data[0]["data_org"];

    if ($newDataOrg == $oldDataOrg) {
      return $this->bad("数据域没有发生变动，不需要进行修改操作");
    }

    $sql = "update %s
            set data_org = '%s'
            where id = '%s' ";
    $rc = $db->execute($sql, $tableName, $newDataOrg, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $params["log"] = "把{$codeTableName}[{$code}-{$name}]的数据域从{$oldDataOrg}修改为{$newDataOrg}";
    $params["logCategory"] = $codeTableName;
    return null;
  }

  /**
   * 修改数据域
   */
  public function editPy(&$params)
  {
    $db = $this->db;
    $id = $params["id"];
    $newPy = strtoupper(trim($params["py"]));
    $md = $this->getMetaDataForRuntime($params, true);

    if (!$md) {
      return $this->badParam("fid");
    }

    $codeTableName = $md["name"];

    $tableName = $md["tableName"];
    // 检查记录是否存在
    $sql = "select code, name, py from %s where id = '%s' ";
    $data = $db->query($sql, $tableName, $id);
    if (!$data) {
      return $this->bad("要修改助记码的记录不存在");
    }
    $code = $data[0]["code"];
    $name = $data[0]["name"];
    $oldPy = $data[0]["py"];

    if ($newPy == $oldPy) {
      return $this->bad("助记码没有发生变动，不需要进行修改操作");
    }

    $sql = "update %s
            set py = '%s'
            where id = '%s' ";
    $rc = $db->execute($sql, $tableName, $newPy, $id);
    if ($rc === false) {
      return $this->sqlError(__METHOD__, __LINE__);
    }

    // 操作成功
    $params["log"] = "把{$codeTableName}[{$code}-{$name}]的助记码从{$oldPy}修改为{$newPy}";
    $params["logCategory"] = $codeTableName;
    return null;
  }
}
