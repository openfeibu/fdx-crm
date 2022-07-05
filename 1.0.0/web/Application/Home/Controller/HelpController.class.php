<?php

namespace Home\Controller;

use Home\Service\BizlogService;
use Home\Service\HelpService;

/**
 * 实施指南导航Controller
 * 
 * 各个模块先访问本Controller的Action，然后由Action跳转到对于的指南HTML页面
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class HelpController extends PSIBaseController
{

  public function index()
  {
    $code = I("get.t");
    $params = [
      "code" => $code,
    ];
    $hs = new HelpService();
    $nav = $hs->getNav($params);
    if ($nav) {
      $url = $nav["url"];
      $log = $nav["log"];
      if ($log) {
        // 记录业务日志
        $bs = new BizlogService();
        $logCategory = $code == "fromShortcut" ? "快捷访问" : "实施指南";
        $bs->insertBizlog($log, $logCategory);
      }

      redirect($url);
    } else {
      // 或许是bug、或许是用户通过浏览器乱输入的
      // 最后的兜底处理，都视为从主菜单访问指南

      $bs = new BizlogService();
      $bs->insertBizlog("从主菜单访问实施指南页面 ");
      redirect("/help/index.html");
    }
  }
}
