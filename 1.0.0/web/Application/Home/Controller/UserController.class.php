<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\UserService;
use Home\Service\BizConfigService;
use Home\Service\IPService;

/**
 * 用户管理Controller
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class UserController extends PSIBaseController
{

  /**
   * 用户管理-主页面
   */
  public function index()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::USR_MANAGEMENT)) {
      $this->initVar();

      $this->assign("title", "用户管理");

      // 按钮权限：新增组织机构
      $this->assign("pAddOrg", $us->hasPermission(FIdConst::USER_MANAGEMENT_ADD_ORG) ? 1 : 0);
      // 按钮权限：编辑组织机构
      $this->assign("pEditOrg", $us->hasPermission(FIdConst::USER_MANAGEMENT_EDIT_ORG) ? 1 : 0);
      // 按钮权限：删除组织机构
      $this->assign("pDeleteOrg", $us->hasPermission(FIdConst::USER_MANAGEMENT_DELETE_ORG) ? 1 : 0);
      // 按钮权限：新增用户
      $this->assign("pAddUser", $us->hasPermission(FIdConst::USER_MANAGEMENT_ADD_USER) ? 1 : 0);
      // 按钮权限：编辑用户
      $this->assign("pEditUser", $us->hasPermission(FIdConst::USER_MANAGEMENT_EDIT_USER) ? 1 : 0);
      // 按钮权限：删除用户
      $this->assign("pDeleteUser", $us->hasPermission(FIdConst::USER_MANAGEMENT_DELETE_USER) ? 1 : 0);
      // 按钮权限：修改用户密码
      $this->assign("pChangePassword", $us->hasPermission(FIdConst::USER_MANAGEMENT_CHANGE_USER_PASSWORD) ? 1 : 0);

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/User/index");
    }
  }

  /**
   * 登录页面
   * 
   * web\Application\Home\View\User\login.html
   */
  public function login()
  {
    if (session("loginUserId")) {
      // 已经登录了，就返回首页
      redirect(__ROOT__);
    }

    $this->initVar();

    $bcs = new BizConfigService();
    $productionName = $bcs->getProductionName();
    $productionNameFull = $productionName;
    if ($productionName == "PSI低代码应用平台") {
      $productionNameFull = "{$productionName}基于开源技术，提供人、财、物、产、供、销、存一体化的企业管理全面解决方案";
    }

    $this->assign("productionName", $productionName);
    $this->assign("productionNameFull", $productionNameFull);

    $this->assign("title", "登录");

    $this->assign("returnPage", I("get.returnPage"));

    // footer中版权显示里面的当前年份
    $this->assign("year", date("Y"));

    $us = new UserService();
    // 部署环境为演示版的时候，页面右下显示的提示信息
    $this->assign("demoInfo", $us->getDemoLoginInfo());

    $this->display();
  }
	public function logout()
	{
		session(null);
		redirect(__ROOT__);
	}
  /**
   * 页面：修改我的密码
   * 
   * web\Application\Home\View\User\changeMyPassword.html
   */
  public function changeMyPassword()
  {
    $us = new UserService();

    if ($us->hasPermission(FIdConst::CHANGE_MY_PASSWORD)) {
      $this->initVar();

      $this->assign("loginUserId", $us->getLoginUserId());
      $this->assign("loginName", $us->getLoginName());
      $this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
      $this->assign("title", "修改我的密码");

      $this->display();
    } else {
      $this->gotoLoginPage("/Home/User/changeMyPassword");
    }
  }

  /**
   * 修改我的密码，POST方法
   * 
   * JS: web\Public\Scripts\PSI\User\ChangeMyPasswordForm.js
   */
  public function changeMyPasswordPOST()
  {
    if (IS_POST) {
      $us = new UserService();

      $loginUserId = $us->getLoginUserId();
      if (!$loginUserId) {
        die("当前用户没有登录");
      }

      // 每个登录用户都能修改自己的密码，就不需要额外的权限判断了

      $params = [
        // 当前登录的用户Id
        "userId" => I("post.userId"),
        // 旧密码
        "oldPassword" => I("post.oldPassword"),
        // 新密码
        "newPassword" => I("post.newPassword")
      ];

      $result = $us->changeMyPassword($params);
      $this->ajaxReturn($result);
    }
  }

  /**
   * 用户登录
   * 
   * JS: web\Application\Home\View\User\login.html
   */
  public function loginPOST()
  {
    if (IS_POST) {
      $ip = get_client_ip();
      // $ipFrom: 把IP转为文字，
      // 其主要用途是在业务日志中记录IP的时候，也记录下IP对应地区
      $ipFrom = (new IPService())->toRegion($ip);

      session("PSI_login_user_ip", $ip);
      session("PSI_login_user_ip_from", $ipFrom);

      $params = [
        // 登录名
        "loginName" => I("post.loginName"),
        // 登录密码
        "password" => I("post.password")
      ];

      $us = new UserService();
      $this->ajaxReturn($us->doLogin($params));
    }
  }

  /**
   * 获得组织机构树
   * 
   * JS:
   * web\Public\Scripts\PSI\User\MainForm.js
   * web\Public\Scripts\PSI\User\OrgEditor.js
   * web\Public\Scripts\PSI\User\OrgField.js
   * web\Public\Scripts\PSI\User\ParentOrgEditor.js
   */
  public function allOrgs()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::USR_MANAGEMENT)) {
        die("没有权限");
      }

      $params = [
        "loginName" => I("post.queryLoginName"),
        "name" => I("post.queryName"),
        "enabled" => I("post.enabled")
      ];

      $us = new UserService();
      $data = $us->allOrgs($params);

      $this->ajaxReturn($data);
    }
  }

  /**
   * 获得组织机构下的用户列表
   * 
   * JS: web\Public\Scripts\PSI\User\MainForm.js
   */
  public function users()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::USR_MANAGEMENT)) {
        die("没有权限");
      }

      $us = new UserService();
      $params = [
        "orgId" => I("post.orgId"),
        "start" => I("post.start"),
        "limit" => I("post.limit"),
        "loginName" => I("post.queryLoginName"),
        "name" => I("post.queryName"),
        "enabled" => I("post.enabled")
      ];

      $this->ajaxReturn($us->users($params));
    }
  }

  /**
   * 新建或编辑组织结构
   * 
   * JS: web\Public\Scripts\PSI\User\MainForm.js
   */
  public function editOrg()
  {
    if (IS_POST) {
      $us = new UserService();
      $id = I("post.id");
      $name = I("post.name");
      $parentId = I("post.parentId");
      $orgCode = strtoupper(I("post.orgCode"));
      $orgType = I("post.orgType");

      if ($id) {
        // 编辑组织机构
        if (!$us->hasPermission(FIdConst::USER_MANAGEMENT_EDIT_ORG)) {
          die("没有权限");
        }
      } else {
        // 新增组织机构
        if (!$us->hasPermission(FIdConst::USER_MANAGEMENT_ADD_ORG)) {
          die("没有权限");
        }
      }

      $result = $us->editOrg($id, $name, $parentId, $orgCode, $orgType);

      $this->ajaxReturn($result);
    }
  }

  /**
   * 获得组织机构的名称
   * 
   * JS: web\Public\Scripts\PSI\User\OrgEditForm.js 
   */
  public function orgParentName()
  {
    if (IS_POST) {
      $us = new UserService();

      $p1 = $us->hasPermission(FIdConst::USER_MANAGEMENT_ADD_ORG);
      $p2 = $us->hasPermission(FIdConst::USER_MANAGEMENT_EDIT_ORG);
      if (!($p1 || $p2)) {
        die("没有权限");
      }

      // orgId
      $id = I("post.id");
      $data = $us->orgParentName($id);

      $this->ajaxReturn($data);
    }
  }

  /**
   * 删除组织机构
   */
  public function deleteOrg()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::USER_MANAGEMENT_DELETE_ORG)) {
        die("没有权限");
      }

      // orgId
      $id = I("post.id");
      $data = $us->deleteOrg($id);

      $this->ajaxReturn($data);
    }
  }

  /**
   * 新增或编辑用户
   */
  public function editUser()
  {
    if (IS_POST) {
      $us = new UserService();

      if (I("post.id")) {
        // 编辑用户
        if (!$us->hasPermission(FIdConst::USER_MANAGEMENT_EDIT_USER)) {
          die("没有权限");
        }
      } else {
        // 新增用户
        if (!$us->hasPermission(FIdConst::USER_MANAGEMENT_ADD_USER)) {
          die("没有权限");
        }
      }

      $params = [
        "id" => I("post.id"),
        "loginName" => I("post.loginName"),
        "name" => I("post.name"),
        "orgCode" => strtoupper(I("post.orgCode")),
        "orgId" => I("post.orgId"),
	      "roleId" => I("post.roleId"),
        "enabled" => I("post.enabled"),
        "gender" => I("post.gender"),
        "birthday" => I("post.birthday"),
        "idCardNumber" => I("post.idCardNumber"),
        "tel" => I("post.tel"),
        "tel02" => I("post.tel02"),
        "address" => I("post.address")
      ];

      $result = $us->editUser($params);

      $this->ajaxReturn($result);
    }
  }

  /**
   * 删除用户
   */
  public function deleteUser()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::USER_MANAGEMENT_DELETE_USER)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id")
      ];

      $result = $us->deleteUser($params);

      $this->ajaxReturn($result);
    }
  }

  /**
   * 修改用户的密码
   */
  public function changePassword()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::USER_MANAGEMENT_CHANGE_USER_PASSWORD)) {
        die("没有权限");
      }

      $params = [
        "id" => I("post.id"),
        "password" => I("post.password")
      ];

      $result = $us->changePassword($params);

      $this->ajaxReturn($result);
    }
  }

  /**
   * 用户自定义字段，查询数据
   */
  public function queryData()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::USER_MANAGEMENT_USERS)) {
        die("没有权限");
      }

      $queryKey = I("post.queryKey");
      $us = new UserService();
      $this->ajaxReturn($us->queryData($queryKey));
    }
  }

  /**
   * 根据数据域返回可用的组织机构
   * 在 OrgWithDataOrgField.js 中使用
   */
  public function orgWithDataOrg()
  {
    if (IS_POST) {
      $us = new UserService();

      if (!$us->hasPermission(FIdConst::USER_MANAGEMENT_ORGS)) {
        die("没有权限");
      }

      $us = new UserService();
      $this->ajaxReturn($us->orgWithDataOrg());
    }
  }

  /**
   * 选择数据域自定义字段， 查询数据
   * 在 SelectUserDataOrg.js中使用
   */
  public function queryUserDataOrg()
  {
    if (IS_POST) {
      // TODO 需要重新考虑这里的权限控制
      $queryKey = I("post.queryKey");
      $us = new UserService();
      $this->ajaxReturn($us->queryUserDataOrg($queryKey));
    }
  }

  /**
   * 获得某个用户的完整信息
   * 
   * JS: web\Public\Scripts\PSI\User\UserEditForm.js
   */
  public function userInfo()
  {
    if (IS_POST) {
      $us = new UserService();
      if (!$us->hasPermission(FIdConst::USR_MANAGEMENT)) {
        die("没有权限");
      }

      $params = [
        // 用户id
        "id" => I("post.id")
      ];
      $us = new UserService();
      $this->ajaxReturn($us->userInfo($params));
    }
  }
}
