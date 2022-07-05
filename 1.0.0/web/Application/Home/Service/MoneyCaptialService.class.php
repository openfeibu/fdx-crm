<?php

namespace Home\Service;

require __DIR__ . '/../Common/Money/Money.php';

/**
 * 金额数字转中文大写Service
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class MoneyCaptialService
{

  public function toCaptial($m)
  {
    return (new \Capital\Money($m))->toCapital();
  }
}
