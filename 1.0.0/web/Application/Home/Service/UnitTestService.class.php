<?php

namespace Home\Service;

/**
 * 单元测试 Service
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class UnitTestService extends PSIBaseExService
{

  /**
   * 通过环境变量来控制是否可以进行单元测试
   *
   * @return bool true 可以进行单元测试
   */
  public function canUnitTest()
  {
    return getenv("PSI_UnitTest") === "1";
  }
}
