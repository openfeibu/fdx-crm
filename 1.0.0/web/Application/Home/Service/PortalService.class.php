<?php

namespace Home\Service;

use Home\Common\FIdConst;

/**
 * Portal Service
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
class PortalService extends PSIBaseExService
{

  public function inventoryPortal()
  {
    if ($this->isNotOnline()) {
      return $this->emptyResult();
    }

    $result = [];

    $db = $this->db();
    $sql = "select id, name 
				from t_warehouse 
				where (inited = 1) and (enabled = 1) ";
    $queryParams = [];
    $ds = new DataOrgService();
    $rs = $ds->buildSQL(FIdConst::PORTAL_INVENTORY, "t_warehouse");
    if ($rs) {
      $sql .= " and " . $rs[0];
      $queryParams = $rs[1];
    }

    $sql .= " order by code ";
    $data = $db->query($sql, $queryParams);
    foreach ($data as $i => $v) {
      $result[$i]["warehouseName"] = $v["name"];
      $warehouseId = $v["id"];

      // 库存金额
      $sql = "select sum(balance_money) as balance_money 
					from t_inventory
					where warehouse_id = '%s' ";
      $d = $db->query($sql, $warehouseId);
      if ($d) {
        $m = $d[0]["balance_money"];
        $result[$i]["inventoryMoney"] = $m ? $m : 0;
      } else {
        $result[$i]["inventoryMoney"] = 0;
      }
      // 低于安全库存数量的商品种类
      $sql = "select count(*) as cnt
					from t_inventory i, t_goods_si s
					where i.goods_id = s.goods_id and i.warehouse_id = s.warehouse_id
						and s.safety_inventory > i.balance_count
						and i.warehouse_id = '%s' ";
      $d = $db->query($sql, $warehouseId);
      $result[$i]["siCount"] = $d[0]["cnt"];

      // 超过库存上限的商品种类
      $sql = "select count(*) as cnt
					from t_inventory i, t_goods_si s
					where i.goods_id = s.goods_id and i.warehouse_id = s.warehouse_id
						and s.inventory_upper < i.balance_count 
						and (s.inventory_upper <> 0 and s.inventory_upper is not null)
						and i.warehouse_id = '%s' ";
      $d = $db->query($sql, $warehouseId);
      $result[$i]["iuCount"] = $d[0]["cnt"];
    }

    return $result;
  }

  public function salePortal($params)
  {
    if ($this->isNotOnline()) {
      return $this->emptyResult();
    }
		
    $result = [];

    $db = $this->db();

    // 当月
    $sql = "select year(now()) as y, month(now())  as m, day(now()) as d";
    $data = $db->query($sql);
    $year = $data[0]["y"];
    $month = $data[0]["m"];
	  $day = $data[0]["d"];
		
	  $type = $params['type'];
	  $kind = '';
	  if(in_array($type,['past_six_months','this_year','last_year']))
	  {
	  	$kind = 'month';
	  }else{
		  $kind = 'day';
	  }
	  
	  if($type == 'past_six_months')
	  {
		  $i_length = 6;
	  }
	  if($type == 'this_year')
	  {
		  $i_length = $month;
	  }
	  if($type == 'last_year')
	  {
		  $i_length = 12;
		  $year = $year-1;
		  $month = 12;
	  }
	  if($type == 'this_month')
	  {
		  $i_length = $day;
	  }
    for ($i = 0; $i < $i_length; $i++) {
	    if ($month < 10) {
		    $result[$i]["month"] = "$year-0$month";
	    } else {
		    $result[$i]["month"] = "$year-$month";
	    }
	    if($kind == 'day')
	    {
	    	if($day<10)
		    {
			    $result[$i]["month"] .= "-0$day";
		    }else {
			    $result[$i]["month"] .= "-$day";
		    }
	    }
      $sql = "select sum(w.sale_money) as sale_money, sum(w.profit) as profit
              from t_ws_bill w
              where w.bill_status >= 1000
                and year(w.bizdt) = %d
                and month(w.bizdt) = %d";
	    
      $queryParams = [];
      $queryParams[] = $year;
      $queryParams[] = $month;
	
	    if($kind == 'day')
	    {
		    $sql .= " and day(w.bizdt) = %d";
		    $queryParams[] = $day;
	    }
	    
      $ds = new DataOrgService();
      $rs = $ds->buildSQL(FIdConst::PORTAL_SALE, "w");
      if ($rs) {
        $sql .= " and " . $rs[0];
        $queryParams = array_merge($queryParams, $rs[1]);
      }

      $data = $db->query($sql, $queryParams);
      $saleMoney = $data[0]["sale_money"];
      if (!$saleMoney) {
        $saleMoney = 0;
      }
      $profit = $data[0]["profit"];
      if (!$profit) {
        $profit = 0;
      }

      // 扣除退货
      $sql = "select sum(s.rejection_sale_money) as rej_sale_money,
                sum(s.profit) as rej_profit
              from t_sr_bill s
              where s.bill_status = 1000
                and year(s.bizdt) = %d
                and month(s.bizdt) = %d";
	    
	    
      $queryParams = [];
      $queryParams[] = $year;
      $queryParams[] = $month;
	
	    if($kind == 'day')
	    {
		    $sql .= " and day(s.bizdt) = %d";
		    $queryParams[] = $day;
	    }
	    
      $ds = new DataOrgService();
      $rs = $ds->buildSQL(FIdConst::PORTAL_SALE, "s");
      if ($rs) {
        $sql .= " and " . $rs[0];
        $queryParams = array_merge($queryParams, $rs[1]);
      }

      $data = $db->query($sql, $queryParams);
      $rejSaleMoney = $data[0]["rej_sale_money"];
      if (!$rejSaleMoney) {
        $rejSaleMoney = 0;
      }
      $rejProfit = $data[0]["rej_profit"];
      if (!$rejProfit) {
        $rejProfit = 0;
      }

      $saleMoney -= $rejSaleMoney;
      $profit += $rejProfit; // 这里是+号，因为$rejProfit是负数

      $result[$i]["saleMoney"] = $saleMoney;
      $result[$i]["profit"] = $profit;

      if ($saleMoney != 0) {
        $result[$i]["rate"] = sprintf("%0.2f", $profit / $saleMoney * 100) . "%";
      } else {
        $result[$i]["rate"] = "";
      }
	    if($kind == 'day')
	    {
	    	$day -= 1;
	    }else{
		    // 获得上个月
		    if ($month == 1) {
			    $month = 12;
			    $year -= 1;
		    } else {
			    $month -= 1;
		    }
	    }
      
    }

    return $result;
  }

  public function purchasePortal($params)
  {
    if ($this->isNotOnline()) {
      return $this->emptyResult();
    }

    $result = [];

    $db = $this->db();

    // 当月
	
	  // 当月
	  $sql = "select year(now()) as y, month(now())  as m, day(now()) as d";
	  $data = $db->query($sql);
	  $year = $data[0]["y"];
	  $month = $data[0]["m"];
	  $day = $data[0]["d"];
	
	  $type = $params['type'];
	  $kind = '';
	  if(in_array($type,['past_six_months','this_year','last_year']))
	  {
		  $kind = 'month';
	  }else{
		  $kind = 'day';
	  }
	
	  if($type == 'past_six_months')
	  {
		  $i_length = 6;
	  }
	  if($type == 'this_year')
	  {
		  $i_length = $month;
	  }
	  if($type == 'last_year')
	  {
		  $i_length = 12;
		  $year = $year-1;
		  $month = 12;
	  }
	  if($type == 'this_month')
	  {
		  $i_length = $day;
	  }

    for ($i = 0; $i < $i_length; $i++) {
	    if ($month < 10) {
		    $result[$i]["month"] = "$year-0$month";
	    } else {
		    $result[$i]["month"] = "$year-$month";
	    }
	    if($kind == 'day')
	    {
		    if($day<10)
		    {
			    $result[$i]["month"] .= "-0$day";
		    }else {
			    $result[$i]["month"] .= "-$day";
		    }
	    }
      $sql = "select sum(w.goods_money) as goods_money
					from t_pw_bill w
					where w.bill_status >= 1000
						and year(w.biz_dt) = %d
						and month(w.biz_dt) = %d";
      $queryParams = [];
      $queryParams[] = $year;
      $queryParams[] = $month;
	
	    if($kind == 'day')
	    {
		    $sql .= " and day(w.biz_dt) = %d";
		    $queryParams[] = $day;
	    }
	    
      $ds = new DataOrgService();
      $rs = $ds->buildSQL(FIdConst::PORTAL_PURCHASE, "w");
      if ($rs) {
        $sql .= " and " . $rs[0];
        $queryParams = array_merge($queryParams, $rs[1]);
      }

      $data = $db->query($sql, $queryParams);
      $goodsMoney = $data[0]["goods_money"];
      if (!$goodsMoney) {
        $goodsMoney = 0;
      }

      // 扣除退货
      $sql = "select sum(s.rejection_money) as rej_money
					from t_pr_bill s
					where s.bill_status = 1000
						and year(s.bizdt) = %d
						and month(s.bizdt) = %d";
      $queryParams = [];
      $queryParams[] = $year;
      $queryParams[] = $month;
	
	    if($kind == 'day')
	    {
		    $sql .= " and day(s.bizdt) = %d";
		    $queryParams[] = $day;
	    }
	    
      $ds = new DataOrgService();
      $rs = $ds->buildSQL(FIdConst::PORTAL_PURCHASE, "s");
      if ($rs) {
        $sql .= " and " . $rs[0];
        $queryParams = array_merge($queryParams, $rs[1]);
      }

      $data = $db->query($sql, $queryParams);
      $rejMoney = $data[0]["rej_money"];
      if (!$rejMoney) {
        $rejMoney = 0;
      }

      $goodsMoney -= $rejMoney;

      $result[$i]["purchaseMoney"] = $goodsMoney;
	
	    if($kind == 'day')
	    {
		    $day -= 1;
	    }else{
		    // 获得上个月
		    if ($month == 1) {
			    $month = 12;
			    $year -= 1;
		    } else {
			    $month -= 1;
		    }
	    }
    }

    return $result;
  }

  public function moneyPortal()
  {
    if ($this->isNotOnline()) {
      return $this->emptyResult();
    }

    $result = [];

    $db = $this->db();
    $us = new UserService();
    $companyId = $us->getCompanyId();

    // 应收账款
    $result[0]["item"] = "应收账款";
    $sql = "select sum(balance_money) as balance_money
				from t_receivables 
				where company_id = '%s' ";
    $data = $db->query($sql, $companyId);
    $balance = $data[0]["balance_money"];
    if (!$balance) {
      $balance = 0;
    }
    $result[0]["balanceMoney"] = $balance;

    // 账龄30天内
    $sql = "select sum(balance_money) as balance_money
				from t_receivables_detail
				where datediff(current_date(), biz_date) < 30
					and company_id = '%s' ";
    $data = $db->query($sql, $companyId);
    $balance = $data[0]["balance_money"];
    if (!$balance) {
      $balance = 0;
    }
    $result[0]["money30"] = $balance;

    // 账龄30-60天
    $sql = "select sum(balance_money) as balance_money
				from t_receivables_detail
				where datediff(current_date(), biz_date) <= 60
					and datediff(current_date(), biz_date) >= 30
					and company_id = '%s' ";
    $data = $db->query($sql, $companyId);
    $balance = $data[0]["balance_money"];
    if (!$balance) {
      $balance = 0;
    }
    $result[0]["money30to60"] = $balance;

    // 账龄60-90天
    $sql = "select sum(balance_money) as balance_money
				from t_receivables_detail
				where datediff(current_date(), biz_date) <= 90
					and datediff(current_date(), biz_date) > 60
					and company_id = '%s' ";
    $data = $db->query($sql, $companyId);
    $balance = $data[0]["balance_money"];
    if (!$balance) {
      $balance = 0;
    }
    $result[0]["money60to90"] = $balance;

    // 账龄大于90天
    $sql = "select sum(balance_money) as balance_money
				from t_receivables_detail
				where datediff(current_date(), biz_date) > 90
					and company_id = '%s' ";
    $data = $db->query($sql, $companyId);
    $balance = $data[0]["balance_money"];
    if (!$balance) {
      $balance = 0;
    }
    $result[0]["money90"] = $balance;

    // 应付账款
    $result[1]["item"] = "应付账款";
    $sql = "select sum(balance_money) as balance_money
				from t_payables 
				where company_id = '%s' ";
    $data = $db->query($sql, $companyId);
    $balance = $data[0]["balance_money"];
    if (!$balance) {
      $balance = 0;
    }
    $result[1]["balanceMoney"] = $balance;

    // 账龄30天内
    $sql = "select sum(balance_money) as balance_money
				from t_payables_detail
				where datediff(current_date(), biz_date) < 30
					and company_id = '%s' ";
    $data = $db->query($sql, $companyId);
    $balance = $data[0]["balance_money"];
    if (!$balance) {
      $balance = 0;
    }
    $result[1]["money30"] = $balance;

    // 账龄30-60天
    $sql = "select sum(balance_money) as balance_money
				from t_payables_detail
				where datediff(current_date(), biz_date) <= 60
					and datediff(current_date(), biz_date) >= 30
					and company_id = '%s' ";
    $data = $db->query($sql, $companyId);
    $balance = $data[0]["balance_money"];
    if (!$balance) {
      $balance = 0;
    }
    $result[1]["money30to60"] = $balance;

    // 账龄60-90天
    $sql = "select sum(balance_money) as balance_money
				from t_payables_detail
				where datediff(current_date(), biz_date) <= 90
					and datediff(current_date(), biz_date) > 60
					and company_id = '%s' ";
    $data = $db->query($sql, $companyId);
    $balance = $data[0]["balance_money"];
    if (!$balance) {
      $balance = 0;
    }
    $result[1]["money60to90"] = $balance;

    // 账龄大于90天
    $sql = "select sum(balance_money) as balance_money
				from t_payables_detail
				where datediff(current_date(), biz_date) > 90
					and company_id = '%s' ";
    $data = $db->query($sql, $companyId);
    $balance = $data[0]["balance_money"];
    if (!$balance) {
      $balance = 0;
    }
    $result[1]["money90"] = $balance;

    return $result;
  }
  public function saleBriefPortal()
  {
	  if ($this->isNotOnline()) {
		  return $this->emptyResult();
	  }
	  $result = [];
	
	  $result['todaySaleMoney'] = $this->saleBrief('today');
	  $result['thisMonthSaleMoney'] = $this->saleBrief('this_month');
	  $result['thisYearSaleMoney'] = $this->saleBrief('this_year');
	  $result['allSaleMoney'] = $this->saleBrief('all');
	  return $result;
  }
  public function saleBrief($type)
  {
	  $db = $this->db();
	  $sql = "select year(now()) as y, month(now())  as m, day(now()) as d";
	  $data = $db->query($sql);
	  $year = $data[0]["y"];
	  $month = $data[0]["m"];
	  $day = $data[0]["d"];
	  $queryParams = [];
	  $t_ws_bill_where = $t_sr_bill_where = '';
  	switch ($type)
	  {
		  case 'today':
			  $t_ws_bill_where = " and year(w.bizdt) = %d
						and month(w.bizdt) = %d
						and day(w.bizdt) = %d";
				$t_sr_bill_where = " and year(s.bizdt) = %d
                and month(s.bizdt) = %d 
                and day(s.bizdt) = %d";
			  $queryParams[] = $year;
			  $queryParams[] = $month;
			  $queryParams[] = $day;
		  	break;
		  case 'this_month':
			  $t_ws_bill_where = " and year(w.bizdt) = %d
						and month(w.bizdt) = %d";
			  $t_sr_bill_where = " and year(s.bizdt) = %d
                and month(s.bizdt) = %d";
			  $queryParams[] = $year;
			  $queryParams[] = $month;
			  break;
		  case 'this_year':
			  $t_ws_bill_where = " and year(w.bizdt) = %d";
			  $t_sr_bill_where = " and year(s.bizdt) = %d";
			  $queryParams[] = $year;
			  break;
		  case 'all':
		  	break;
	  }
	  $t_sr_bill_where_queryParams = $queryParams;
	  
	  $sql = "select sum(w.sale_money) as sale_money, sum(w.profit) as profit
              from t_ws_bill w
              where w.bill_status >= 1000 ".$t_ws_bill_where;
	  
	  $ds = new DataOrgService();
	  $rs = $ds->buildSQL(FIdConst::PORTAL_SALE_BRIEF, "w");
	  if ($rs) {
		  $sql .= " and " . $rs[0];
		  $queryParams = array_merge($queryParams, $rs[1]);
	  }
	
	  $data = $db->query($sql, $queryParams);
	  $saleMoney = $data[0]["sale_money"];
	  if (!$saleMoney) {
		  $saleMoney = 0;
	  }
	  
	  // 扣除退货
	  $sql = "select sum(s.rejection_sale_money) as rej_sale_money
              from t_sr_bill s
              where s.bill_status = 1000 ".$t_sr_bill_where;

	  $ds = new DataOrgService();
	  $rs = $ds->buildSQL(FIdConst::PORTAL_SALE_BRIEF, "s");
	  if ($rs) {
		  $sql .= " and " . $rs[0];
		  $t_sr_bill_where_queryParams = array_merge($t_sr_bill_where_queryParams, $rs[1]);
	  }
	
	  $data = $db->query($sql, $t_sr_bill_where_queryParams);
	  $rejSaleMoney = $data[0]["rej_sale_money"];
	  if (!$rejSaleMoney) {
		  $rejSaleMoney = 0;
	  }
	
	  $saleMoney -= $rejSaleMoney;
	  
	  return $saleMoney;
  }
	public function saleCntBriefPortal()
	{
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		$result = [];
		
		$result['todaySaleCnt'] = $this->saleCntBrief('today');
		$result['thisMonthSaleCnt'] = $this->saleCntBrief('this_month');
		$result['thisYearSaleCnt'] = $this->saleCntBrief('this_year');
		$result['allSaleCnt'] = $this->saleCntBrief('all');
		return $result;
	}
	public function saleCntBrief($type)
	{
		$db = $this->db();
		$sql = "select year(now()) as y, month(now())  as m, day(now()) as d";
		$data = $db->query($sql);
		$year = $data[0]["y"];
		$month = $data[0]["m"];
		$day = $data[0]["d"];
		$queryParams = [];
		$where = '';
		switch ($type)
		{
			case 'today':
				$where = " and year(ws.bizdt) = %d
						and month(ws.bizdt) = %d
						and day(ws.bizdt) = %d";
				$queryParams[] = $year;
				$queryParams[] = $month;
				$queryParams[] = $day;
				break;
			case 'this_month':
				$where = " and year(ws.bizdt) = %d
						and month(ws.bizdt) = %d";
				$queryParams[] = $year;
				$queryParams[] = $month;
				break;
			case 'this_year':
				$where = " and year(ws.bizdt) = %d";
				$queryParams[] = $year;
				break;
			case 'all':
				break;
		}
		
		$sql = "select sum(d.goods_count) as cnt
            from t_ws_bill ws, t_ws_bill_detail d, t_goods g, t_goods_unit u,
              t_customer c, t_warehouse w
            where (ws.id = d.wsbill_id) and (d.goods_id = g.id) 
              and (g.unit_id = u.id) and (ws.customer_id = c.id)
              and (ws.warehouse_id = w.id) ".$where;
		$ds = new DataOrgService();
		// 构建数据域SQL
		$rs = $ds->buildSQL(FIdConst::PORTAL_SALE_CNT_BRIEF, "ws");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		return $cnt;
	}
	public function saleTopPortal($limit=10)
	{
		$db = $this->db();
	
		$queryParams = [];
		$result = [];
		
		$sql = "select sum(d.goods_count) as cnt,g.name as goods_name, g.id as goods_id
            from t_ws_bill ws, t_ws_bill_detail d, t_goods g, t_goods_unit u,
              t_customer c, t_warehouse w
            where (ws.id = d.wsbill_id) and (d.goods_id = g.id) 
              and (g.unit_id = u.id) and (ws.customer_id = c.id)
              and (ws.warehouse_id = w.id) ";
		$ds = new DataOrgService();
		// 构建数据域SQL
		$rs = $ds->buildSQL(FIdConst::PORTAL_SALE_TOP, "ws");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		$sql .="group by g.id order by cnt desc,g.id desc limit $limit";
		$data = $db->query($sql, $queryParams);
		foreach ($data as $i => $v) {
			$result[$i]['goods_name'] = $v["goods_name"];
			$result[$i]['cnt'] = $v["cnt"];
		}
		return $result;
	}
}
