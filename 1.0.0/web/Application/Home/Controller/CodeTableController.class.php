<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\UserService;
use Home\Service\CodeTableService;
use Home\Service\PinyinService;

/**
 * 码表设置Controller
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class CodeTableController extends PSIBaseController
{

  /**
   * 码表设置 - 主页面
   * 
   * web\Application\Home\View\CodeTable\index.html
   */
  public function index()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::CODE_TABLE)) {
      $this->initVar();

      $this->assign("title", "码表设置");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/CodeTable/index");
    }
  }

  /**
   * 查询解决方案列表
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\MainForm.js
   */
  public function querySolutionList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $service = new CodeTableService();
      $this->ajaxReturn($service->querySolutionList());
    }
  }

  /**
   * 码表分类列表
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\MainForm.js
   */
  public function categoryList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        // 解决方案编码
        "slnCode" => I("post.slnCode")
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->categoryList($params));
    }
  }

  /**
   * 新增或编辑码表分类
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\CategoryEditForm.js
   */
  public function editCodeTableCategory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "code" => I("post.code"),
        "name" => I("post.name"),
        "slnCode" => I("post.slnCode"),
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->editCodeTableCategory($params));
    }
  }

  /**
   * 删除码表分类
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\MainForm.js
   */
  public function deleteCodeTableCategory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        // 码表分类id
        "id" => I("post.id")
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->deleteCodeTableCategory($params));
    }
  }

  /**
   * 码表列表
   * 
   * JS：web\Public\Scripts\PSI\CodeTable\MainForm.js
   */
  public function codeTableList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        "categoryId" => I("post.categoryId")
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->codeTableList($params));
    }
  }

  /**
   * 码表分类自定义字段 - 查询数据
   * 
   * JS：web\Public\Scripts\PSI\CodeTable\CodeTableCategoryField.js
   */
  public function queryDataForCategory()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        "queryKey" => I("post.queryKey"),
        "slnCode" => I("post.slnCode"),
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->queryDataForCategory($params));
    }
  }

  /**
   * 新增或编辑码表
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\CodeTableEditForm.js
   */
  public function editCodeTable()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $py = new PinyinService();

      $params = [
        "id" => I("post.id"),
        "slnCode" => I("post.slnCode"),
        "categoryId" => I("post.categoryId"),
        "code" => I("post.code"),
        "name" => I("post.name"),
        "moduleName" => I("post.moduleName"),
        "tableName" => I("post.tableName"),
        "enableParentId" => I("post.enableParentId"),
        "handlerClassName" => I("post.handlerClassName"),
        "memo" => I("post.memo"),
        "py" => $py->toPY(I("post.moduleName")),
        "editColCnt" => I("post.editColCnt"),
        "viewPaging" => I("post.viewPaging"),
        "autoCodeLength" => I("post.autoCodeLength"),
        "inputCompany" => I("post.inputCompany"),
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->editCodeTable($params));
    }
  }

  /**
   * 某个码表的列
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\MainForm.js
   */
  public function codeTableColsList()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];
      $service = new CodeTableService();
      $this->ajaxReturn($service->codeTableColsList($params));
    }
  }

  /**
   * 新增或编辑码表列
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\CodeTableColEditForm.js
   */
  public function editCodeTableCol()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "codeTableId" => I("post.codeTableId"),
        "caption" => I("post.caption"),
        "fieldName" => I("post.fieldName"),
        "fieldType" => I("post.fieldType"),
        "fieldLength" => I("post.fieldLength"),
        "fieldDecimal" => I("post.fieldDecimal"),
        "valueFrom" => I("post.valueFrom"),
        "valueFromTableName" => I("post.valueFromTableName"),
        "valueFromColName" => I("post.valueFromColName"),
        "valueFromColNameDisplay" => I("post.valueFromColNameDisplay"),
        "mustInput" => I("post.mustInput"),
        "widthInView" => I("post.widthInView"),
        "showOrder" => I("post.showOrder"),
        "showOrderInView" => I("post.showOrderInView"),
        "isVisible" => I("post.isVisible"),
        "editorXtype" => I("post.editorXtype"),
        "memo" => I("post.memo"),
        "colSpan" => I("post.colSpan"),
        "defaultValue" => I("post.defaultValue"),
        "defaultValueExt" => I("post.defaultValueExt"),
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->editCodeTableCol($params));
    }
  }

  /**
   * 删除码表列
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\MainForm.js
   */
  public function deleteCodeTableCol()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        "tableId" => I("post.tableId"),
        "id" => I("post.id")
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->deleteCodeTableCol($params));
    }
  }

  /**
   * 删除码表
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\MainForm.js
   */
  public function deleteCodeTable()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->deleteCodeTable($params));
    }
  }

  /**
   * 查询码表主表元数据
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\CodeTableEditForm.js
   */
  public function codeTableInfo()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        // 码表id
        "id" => I("post.id")
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->codeTableInfo($params));
    }
  }

  /**
   * 码表某列的详细信息
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\CodeTableColEditForm.js
   */
  public function codeTableColInfo()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        "tableId" => I("post.tableId"),
        // id: 列id
        "id" => I("post.id")
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->codeTableColInfo($params));
    }
  }

  /**
   * 把码表转化为系统固有码表
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\MainForm.js
   */
  public function convertCodeTable()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->convertCodeTable($params));
    }
  }

  /**
   * 查询码表编辑界面字段的显示次序
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\CodeTableEditColShowOrderForm.js
   */
  public function queryCodeTableEditColShowOrder()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = ["id" => I("post.tableId")];

      $service = new CodeTableService();
      $this->ajaxReturn($service->queryCodeTableEditColShowOrder($params));
    }
  }

  /**
   * 保存编辑界面字段显示次序
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\CodeTableEditColShowOrderForm.js
   */
  public function saveColEditShowOrder()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "json" => I("post.json")
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->saveColEditShowOrder($params));
    }
  }

  /**
   * 码表生成SQL
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\CodeTableGenSQLForm.js
   */
  public function codeTableGenSQL()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->codeTableGenSQL($params));
    }
  }

  /**
   * 解决方案的全部码表生成SQL
   * 
   * JS: web\Public\Scripts\PSI\CodeTable\CodeTableSolutionGenSQLForm.js
   */
  public function codeTableSolutionGenSQL()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        // 解决方案编码
        "slnCode" => I("post.slnCode"),
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->codeTableSolutionGenSQL($params));
    }
  }

  /**
   * 选择值来源的引用列 - 查询表
   * 
   * JS：web\Public\Scripts\PSI\CodeTable\SelectColRefForm.js
   */
  public function queryTablesForColRef()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        // 值来源，见：t_sysdict_sln0000_ct_value_from
        "valueFrom" => I("post.valueFrom"),
        // 当前码表的数据库表名
        "tableName" => I("post.tableName"),
        // 过滤查询键值
        "searchKey" => I("post.searchKey"),
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->queryTablesForColRef($params));
    }
  }

  /**
   * 选择值来源的引用列 - 查询列
   * 
   * JS：web\Public\Scripts\PSI\CodeTable\SelectColRefForm.js
   */
  public function queryColsForColRef()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::CODE_TABLE)) {
        die("没有权限");
      }

      $params = [
        // 值来源，见：t_sysdict_sln0000_ct_value_from
        "valueFrom" => I("post.valueFrom"),
        // 数据库表名
        "tableName" => I("post.tableName"),
      ];

      $service = new CodeTableService();
      $this->ajaxReturn($service->queryColsForColRef($params));
    }
  }
}
