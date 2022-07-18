/**
 * 首页
 * 
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
PCL.define("PSI.Home.MainForm", {
  extend: "PCL.panel.Panel",

  config: {
    pSale: "",
    pInventory: "",
    pPurchase: "",
    pMoney: "",
    pSaleBrief: "",
    pSaleCntBrief: "",
    pSaleTop: "",
    productionName: "PSI"
  },

  border: 0,
  bodyPadding: 5,

  initComponent: function () {
    var me = this;

    var items = [];
    var PSaleBriefItems = [];
    if (me.getPSaleBrief() == "1") {
      PSaleBriefItems.push(me.getSaleBriefPortal());
    }
    if (me.getPSaleCntBrief() == "1") {
      PSaleBriefItems.push(me.getSaleCntBriefPortal());
    }
    if(PSaleBriefItems.length>0)
    {
      items.push({
        width: "100%",
        layout: "hbox",
        border: 0,
        items: PSaleBriefItems
      });
    }
    /*
    //销售简要统计（当日，当月，当年，全部）；
    if (me.getPSaleBrief() == "1") {
      items.push({
        width: "50%",
        layout: "hbox",
        border: 0,
        items: [me.getSaleBriefPortal()]
      });
    }
    //出库量简要统计（当日，当月，当年，全部）；
    if (me.getPSaleCntBrief() == "1") {
      items.push({
        width: "50%",
        layout: "hbox",
        border: 0,
        items: [me.getSaleCntBriefPortal()]
      });
    }
    */
    // 销售看板
    if (me.getPSale() == "1") {
      items.push({
        width: "100%",
        layout: "hbox",
        border: 0,
        hidden: me.getPSale() != "1",
        items: [me.getSalePortal1(), me.getSalePortal2()]
      });
    }
    // 产品销量前10
    if (me.getPSaleTop() == "1") {
      items.push({
        width: "100%",
        layout: "hbox",
        border: 0,
        items: [me.getSaleTopPortal()]
      });
    }
    // 采购看板
    if (me.getPPurchase() == "1") {
      items.push({
        width: "100%",
        layout: "hbox",
        border: 0,
        items: [me.getPurchasePortal1(),
        me.getPurchasePortal2()]
      });
    }
    // 库存看板
    if (me.getPInventory() == "1") {
      items.push({
        width: "100%",
        layout: "hbox",
        border: 0,
        items: [me.getInventoryPortal()]
      });
    }
    // 资金看板
    if (me.getPMoney() == "1") {
      items.push({
        width: "100%",
        layout: "hbox",
        border: 0,
        items: [me.getMoneyPortal()]
      });
    }

    // 如果上述看板都没有权限，则显示默认信息
    if (items.length == 0) {
      items.push({
        width: "100%",
        layout: "hbox",
        border: 0,
        items: [me.getInfoPortal()]
      });
    }
    PCL.apply(me, {
      layout: "vbox",
      autoScroll: true,
      items: items
    });

    me.callParent(arguments);

    if (me.getPSale() == "1") {
      me.querySaleData("this_month");
    }
    if (me.getPSaleBrief() == "1") {
      me.querySaleBriefData();
    }
    if (me.getPSaleCntBrief() == "1") {
      me.querySaleCntBriefData();
    }
    if (me.getPPurchase() == "1") {
      me.queryPurchaseData("this_month");
    }

    if (me.getPInventory() == "1") {
      me.queryInventoryData();
    }

    if (me.getPMoney() == "1") {
      me.queryMoneyData();
    }
    if (me.getPSaleTop() == "1") {
      me.querySaleTopData();
    }
  },

  getSaleGrid: function () {
    var me = this;
    if (me.__saleGrid) {
      return me.__saleGrid;
    }

    var modelName = "PSIModel.PSI.Home.PortalSale";
    PCL.define(modelName, {
      extend: "PCL.data.Model",
      fields: ["month", "saleMoney", "profit", "rate"]
    });

    me.__saleGrid = PCL.create("PCL.grid.Panel", {
      cls: "PSI-KB",
      viewConfig: {
        enableTextSelection: true
      },
      columnLines: true,
      border: 0,
      columns: [{
        header: "月份/日",
        dataIndex: "month",
        width: '20%',
        menuDisabled: true,
		align: "center",
        sortable: false
      }, {
        header: "销售额(不含税)",
        dataIndex: "saleMoney",
        width: '80%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn"
      }/*, {
        header: "毛利",
        dataIndex: "profit",
        width: '30%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn"
      }, {
        header: "毛利率",
        dataIndex: "rate",
        width: '20%',
        menuDisabled: true,
        sortable: false,
        align: "right"
      }*/],
      store: PCL.create("PCL.data.Store", {
        model: modelName,
        autoLoad: false,
        data: []
      })
    });

    return me.__saleGrid;
  },

  getPurchaseGrid: function () {
    var me = this;
    if (me.__purchaseGrid) {
      return me.__purchaseGrid;
    }

    var modelName = "PSIModel.PSI.Home.PortalPurchase";
    PCL.define(modelName, {
      extend: "PCL.data.Model",
      fields: ["month", "purchaseMoney"]
    });

    me.__purchaseGrid = PCL.create("PCL.grid.Panel", {
      cls: "PSI-KB",
      viewConfig: {
        enableTextSelection: true
      },
      columnLines: true,
      border: 0,
      columns: [{
        header: "月份/日",
        dataIndex: "month",
        width: '30%',
        menuDisabled: true,
        sortable: false,
        align: "center",
      }, {
        header: "采购额",
        dataIndex: "purchaseMoney",
        width: '70%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn"
      }],
      store: PCL.create("PCL.data.Store", {
        model: modelName,
        autoLoad: false,
        data: []
      })
    });

    return me.__purchaseGrid;
  },

  getInventoryGrid: function () {
    var me = this;
    if (me.__inventoryGrid) {
      return me.__inventoryGrid;
    }

    var modelName = "PSIModel.PSI.Home.PortalInventory";
    PCL.define(modelName, {
      extend: "PCL.data.Model",
      fields: ["warehouseName", { name: "inventoryMoney", type: "float" },
        { name: "siCount", type: "float" },
        { name: "iuCount", type: "float" }]
    });

    me.__inventoryGrid = PCL.create("PCL.grid.Panel", {
      cls: "PSI-KB",
      viewConfig: {
        enableTextSelection: true
      },
      features: [{
        ftype: "summary",
        dock: "bottom"
      }],
      columnLines: true,
      border: 0,
      columns: [{
        header: "仓库",
        dataIndex: "warehouseName",
        width: '49.5%',
        menuDisabled: true,
        sortable: false,
        align: "center",
        summaryRenderer: function () {
          return "合计";
        }
      }, {
        header: "存货金额",
        dataIndex: "inventoryMoney",
        width: '49.5%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
        summaryType: "sum"
      }
      /*, {
        header: "低于安全库存物料种类数",
        dataIndex: "siCount",
        width: 180,
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
        format: "0",
        renderer: function (value) {
          return value > 0
            ? "<span style='color:red'>"
            + value + "</span>"
            : value;
        },
        summaryType: "sum"
      }, {
        header: "超过库存上限的物料种类数",
        dataIndex: "iuCount",
        width: 180,
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
        format: "0",
        renderer: function (value) {
          return value > 0
            ? "<span style='color:red'>"
            + value + "</span>"
            : value;
        },
        summaryType: "sum"
      }*/
      ],
      store: PCL.create("PCL.data.Store", {
        model: modelName,
        autoLoad: false,
        data: []
      })
    });

    return me.__inventoryGrid;
  },

  getSaleBriefGrid: function () {
    var me = this;
    if (me.__saleBriefGrid) {
      return me.__saleBriefGrid;
    }

    var modelName = "PSIModel.PSI.Home.PortalSaleBrief";
    PCL.define(modelName, {
      extend: "PCL.data.Model",
      fields: [{ name: "todaySaleMoney", type: "float" },
        { name: "thisMonthSaleMoney", type: "float" },
        { name: "thisYearSaleMoney", type: "float" },
        { name: "allSaleMoney", type: "float" }]
    });

    me.__saleBriefGrid = PCL.create("PCL.grid.Panel", {
      cls: "PSI-KB",
      viewConfig: {
        enableTextSelection: true
      },
      features: [{
        ftype: "summary",
        //dock: "bottom"
      }],
      columnLines: true,
      border: 0,
      columns: [{
        header: "当日",
        dataIndex: "todaySaleMoney",
        width: '20%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
      }, {
        header: "当月",
        dataIndex: "thisMonthSaleMoney",
        width: '20%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
      }, {
        header: "今年",
        dataIndex: "thisYearSaleMoney",
        width: '30%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
      }, {
        header: "总销售额",
        dataIndex: "thisYearSaleMoney",
        width: '30%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
      }],
      store: PCL.create("PCL.data.Store", {
        model: modelName,
        autoLoad: false,
        data: []
      })
    });

    return me.__saleBriefGrid;
  },
  getSaleCntBriefGrid:function () {
    var me = this;
    if (me.__saleCntBriefGrid) {
      return me.__saleCntBriefGrid;
    }

    var modelName = "PSIModel.PSI.Home.PortalSaleCntBrief";
    PCL.define(modelName, {
      extend: "PCL.data.Model",
      fields: [{ name: "todaySaleCnt", type: "float" },
        { name: "thisMonthSaleCnt", type: "float" },
        { name: "thisYearSaleCnt", type: "float" },
        { name: "allSaleCnt", type: "float" }]
    });

    me.__saleCntBriefGrid = PCL.create("PCL.grid.Panel", {
      cls: "PSI-KB",
      viewConfig: {
        enableTextSelection: true
      },
      features: [{
        ftype: "summary",
        //dock: "bottom"
      }],
      columnLines: true,
      border: 0,
      columns: [{
        header: "当日",
        dataIndex: "todaySaleCnt",
        width: '20%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
        format: "0",
      }, {
        header: "当月",
        dataIndex: "thisMonthSaleCnt",
        width: '20%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
        format: "0",
      }, {
        header: "今年",
        dataIndex: "thisYearSaleCnt",
        width: '30%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
        format: "0",
      }, {
        header: "总销售出库量",
        dataIndex: "thisYearSaleCnt",
        width: '30%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
        format: "0",
      }],
      store: PCL.create("PCL.data.Store", {
        model: modelName,
        autoLoad: false,
        data: []
      })
    });

    return me.__saleCntBriefGrid;
  },
  getSaleTopGrid:function () {
    var me = this;
    if (me.__saleTopGrid) {
      return me.__saleTopGrid;
    }

    var modelName = "PSIModel.PSI.Home.PortalSaleTop";
    PCL.define(modelName, {
      extend: "PCL.data.Model",
      fields: ["goods_name", "cnt"]
    });

    me.__saleTopGrid = PCL.create("PCL.chart.Chart", {
      renderTo: Ext.getBody(),
      width: '100%',
      height: 300,
      animate: true,
      store: PCL.create("PCL.data.Store", {
        model: modelName,
        autoLoad: false,
        data: []
      }),
      //theme:'Blue',
      axes: [
        {
          type: "Numeric",
          position: 'left',
          fields: ['cnt'],
          label: {
            renderer: Ext.util.Format.numberRenderer('0,0')
          },
          title: '销售量',
          grid: true,
          minimum: 0
        },
        {
          type: 'Category',
          position: 'bottom',
          fields: ['goods_name'],
		  
          title: '产品'
        }
      ],
      series: [
        {
          type: 'column',
          axis: 'left',
          highlight: true,
          tips: {
            trackMouse: true,
            width: 140,
            height: 28,
            renderer: function(storeItem, item) {
              this.setTitle(storeItem.get('goods_name') + ': ' + parseInt(storeItem.get('cnt')) + '');
            },
          },
          label: {
            display: 'insideEnd',
            'text-anchor': 'middle',
            field: 'cnt',
            renderer: Ext.util.Format.numberRenderer('0'),
            //orientation: 'vertical',
            color: '#fff'
          },
		   
          xField: 'goods_name',
          yField: 'cnt'
        }
      ]
    });
    return me.__saleTopGrid;
  },
  getMoneyGrid: function () {
    var me = this;
    if (me.__moneyGrid) {
      return me.__moneyGrid;
    }

    var modelName = "PSIModel.PSI.Home.PortalMoney";
    PCL.define(modelName, {
      extend: "PCL.data.Model",
      fields: ["item", "balanceMoney", "money30", "money30to60",
        "money60to90", "money90"]
    });

    me.__moneyGrid = PCL.create("PCL.grid.Panel", {
      cls: "PSI-KB",
      viewConfig: {
        enableTextSelection: true
      },
      columnLines: true,
      border: 0,
      columns: [{
        header: "款项",
        dataIndex: "item",
        width: '10%',
        menuDisabled: true,
        sortable: false
      }, {
        header: "当期余额",
        dataIndex: "balanceMoney",
        width: '18%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn"
      }, {
        header: "账龄30天内",
        dataIndex: "money30",
        width: '18%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn"
      }, {
        header: "账龄30-60天",
        dataIndex: "money30to60",
        width: '18%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn"
      }, {
        header: "账龄60-90天",
        dataIndex: "money60to90",
        width: '18%',
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn"
      }, {
        header: "账龄大于90天",
        dataIndex: "money90",
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
        width: '18%'
      }],
      store: PCL.create("PCL.data.Store", {
        model: modelName,
        autoLoad: false,
        data: []
      })
    });

    return me.__moneyGrid;
  },

  getSalePortal1: function () {
    var me = this;
    return {
      flex: 1,
      width: "100%",
      height: 270,
      margin: "5",
      header: {
        title: "<span style='font-size:120%;font-weight:normal;'>销售数据图表</span>",
        iconCls: "PSI-portal-sale",
        height: 40
      },
      layout: "fit",
      tbar: [{
        xtype: "combo",
        queryMode: "local",
        editable: false,
        valueField: "id",
        labelWidth: 60,
        labelAlign: "right",
        labelSeparator: "",
        fieldLabel: "选择时间",
        margin: "5, 0, 0, 0",
        store: PCL.create("PCL.data.ArrayStore", {
          fields: ["id", "text"],
          data: [["this_month", "这个月"],["past_six_months", "近半年"], ["this_year", "今年"],
            ["last_year", "去年"]]
        }),
        value: "this_month",
        listeners: {
          change: function (e) {
            me.querySaleData(e.value);
          }
        }
      }],
      items: me.getSaleChart()
    };
  },

  getSalePortal2: function () {
    var me = this;
    return {
      flex: 1,
      width: "100%",
      height: 270,
      margin: "5",
      header: {
        title: "<span style='font-size:120%;font-weight:normal;'>销售数据</span>",
        iconCls: "PSI-portal-sale",
        height: 40
      },
      layout: "fit",
      items: me.getSaleGrid()
    };
  },
  getSalePortal3: function () {
    var me = this;
    return {
      flex: 1,
      width: "100%",
      height: 270,
      margin: "5",
      header: {
        title: "<span style='font-size:120%;font-weight:normal;'>销售统计</span>",
        iconCls: "PSI-portal-sale",
        height: 40
      },
      layout: "fit",
      items: me.getSalePortal3Grid()
    };
  },
  getPurchasePortal1: function () {
    var me = this;
    return {
      header: {
        title: "<span style='font-size:120%;font-weight:normal;'>采购数据图表</span>",
        iconCls: "PSI-portal-purchase",
        height: 40
      },
      flex: 1,
      width: "100%",
      height: 270,
      margin: "5",
      layout: "fit",
      tbar: [{
        xtype: "combo",
        queryMode: "local",
        editable: false,
        valueField: "id",
        labelWidth: 60,
        labelAlign: "right",
        labelSeparator: "",
        fieldLabel: "选择时间",
        margin: "5, 0, 0, 0",
        store: PCL.create("PCL.data.ArrayStore", {
          fields: ["id", "text"],
          data: [["this_month", "这个月"], ["past_six_months", "近半年"], ["this_year", "今年"],
            ["last_year", "去年"]]
        }),
        value: "this_month",
        listeners: {
          change: function (e) {
            me.queryPurchaseData(e.value);
          }
        }
      }],
      items: me.getPurchaseChart()
    };
  },

  getPurchasePortal2: function () {
    var me = this;
    return {
      header: {
        title: "<span style='font-size:120%;font-weight:normal;'>采购数据</span>",
        iconCls: "PSI-portal-purchase",
        height: 40
      },
      flex: 1,
      width: "100%",
      height: 270,
      margin: "5",
      layout: "fit",
      items: me.getPurchaseGrid()
    };
  },

  getInventoryPortal: function () {
    var me = this;
    return {
      header: {
        title: "<span style='font-size:120%;font-weight:normal;'>库存金额统计</span>",
        iconCls: "PSI-portal-inventory",
        height: 40
      },
      flex: 1,
      width: "100%",
      height: 270,
      margin: "5",
      layout: "fit",
      items: [me.getInventoryGrid()]
    };
  },
  getSaleBriefPortal: function () {
    var me = this;
    return {
      header: {
        title: "<span style='font-size:120%;font-weight:normal;'>销售统计</span>",
        iconCls: "PSI-portal-sale",
        height: 40
      },
      flex: 1,
      width: "50%",
      height: 120,
      margin: "5",
      //layout: "fit",
      items: [me.getSaleBriefGrid()]
    };
  },
  getSaleCntBriefPortal: function () {
    var me = this;
    return {
      header: {
        title: "<span style='font-size:120%;font-weight:normal;'>销售出库量统计</span>",
        iconCls: "PSI-portal-sale",
        height: 40
      },
      flex: 1,
      width: "50%",
      height: 120,
      margin: "5",
      //layout: "fit",
      items: [me.getSaleCntBriefGrid()]
    };
  },
  getSaleTopPortal:function () {
    var me = this;
    return {
      header: {
        title: "<span style='font-size:120%;font-weight:normal;'>产品销量前10排行榜</span>",
        iconCls: "PSI-portal-sale",
        height: 40
      },
      flex: 1,
      width: "100%",
      height: 350,
      margin: "5",
      //layout: "fit",
      items: [me.getSaleTopGrid()]
    };
  },

  getMoneyPortal: function () {
    var me = this;
    return {
      header: {
        title: "<span style='font-size:120%;font-weight:normal;'>财务统计</span>",
        iconCls: "PSI-portal-money",
        height: 40
      },
      flex: 1,
      width: "100%",
      height: 270,
      margin: "5",
      layout: "fit",
      items: [me.getMoneyGrid()]
    };
  },

  queryInventoryData: function () {
    var me = this;
    var grid = me.getInventoryGrid();
    var el = grid.getEl() || PCL.getBody();
    el.mask(PSI.Const.LOADING);
    PCL.Ajax.request({
      url: PSI.Const.BASE_URL + "Home/Portal/inventoryPortal",
      method: "POST",
      callback: function (options, success, response) {
        var store = grid.getStore();
        store.removeAll();

        if (success) {
          var data = PCL.JSON.decode(response.responseText);
          store.add(data);
        }

        el.unmask();
      }
    });
  },
  querySaleBriefData: function () {
    var me = this;
    var grid = me.getSaleBriefGrid();
    var el = grid.getEl() || PCL.getBody();
    el.mask(PSI.Const.LOADING);
    PCL.Ajax.request({
      url: PSI.Const.BASE_URL + "Home/Portal/saleBriefPortal",
      method: "POST",
      callback: function (options, success, response) {
        var store = grid.getStore();
        store.removeAll();

        if (success) {
          var data = PCL.JSON.decode(response.responseText);
          store.add(data);
        }

        el.unmask();
      }
    });
  },
  querySaleCntBriefData: function () {
    var me = this;
    var grid = me.getSaleCntBriefGrid();
    var el = grid.getEl() || PCL.getBody();
    el.mask(PSI.Const.LOADING);
    PCL.Ajax.request({
      url: PSI.Const.BASE_URL + "Home/Portal/saleCntBriefPortal",
      method: "POST",
      callback: function (options, success, response) {
        var store = grid.getStore();
        store.removeAll();

        if (success) {
          var data = PCL.JSON.decode(response.responseText);
          store.add(data);
        }

        el.unmask();
      }
    });
  },
  querySaleTopData:function () {
    var me = this;
    var grid = me.getSaleTopGrid();
    var el = grid.getEl() || PCL.getBody();
    el.mask(PSI.Const.LOADING);
    PCL.Ajax.request({
      url: PSI.Const.BASE_URL + "Home/Portal/saleTopPortal",
      method: "POST",
      callback: function (options, success, response) {
        var store = grid.getStore();
        store.removeAll();

        if (success) {
          var data = PCL.JSON.decode(response.responseText);
          store.add(data);
        }
        el.unmask();
      }
    });
  },
  querySaleData: function (type) {
    var me = this;
    var grid = me.getSaleGrid();
    var el = grid.getEl() || PCL.getBody();
    el.mask(PSI.Const.LOADING);
    PCL.Ajax.request({
      url: PSI.Const.BASE_URL + "Home/Portal/salePortal",
      params: {
        type: type,
      },
      method: "POST",
      callback: function (options, success, response) {
        var store = grid.getStore();
        store.removeAll();

        if (success) {
          var data = PCL.JSON.decode(response.responseText);
          store.add(data);

          me.copyDataFromSaleGrid(data);
        }

        el.unmask();
      }
    });
  },

  copyDataFromSaleGrid: function (data) {
    var me = this;
    var store = me.getSaleChart().getStore();
    store.removeAll();
    var len = data.length;
    for (var i = len - 1; i >= 0; i--) {
      var d = data[i];
      store.add({
        month: d.month,
        "不含税销售额": d.saleMoney,
        "毛利": d.profit
      });
    }
  },

  queryPurchaseData: function (type) {
    var me = this;
    var grid = me.getPurchaseGrid();
    var el = grid.getEl() || PCL.getBody();
    el.mask(PSI.Const.LOADING);
    PCL.Ajax.request({
      url: PSI.Const.BASE_URL + "Home/Portal/purchasePortal",
      params: {
        type: type,
      },
      method: "POST",
      callback: function (options, success, response) {
        var store = grid.getStore();
        store.removeAll();

        if (success) {
          var data = PCL.JSON.decode(response.responseText);
          store.add(data);
          me.copyDataFromPurchaseGrid(data);
        }

        el.unmask();
      }
    });
  },

  copyDataFromPurchaseGrid: function (data) {
    var me = this;
    var store = me.getPurchaseChart().getStore();
    store.removeAll();
    var len = data.length;
    for (var i = len - 1; i >= 0; i--) {
      var d = data[i];
      store.add(d);
    }
  },

  queryMoneyData: function () {
    var me = this;
    var grid = me.getMoneyGrid();
    var el = grid.getEl() || PCL.getBody();
    el.mask(PSI.Const.LOADING);
    PCL.Ajax.request({
      url: PSI.Const.BASE_URL + "Home/Portal/moneyPortal",
      method: "POST",
      callback: function (options, success, response) {
        var store = grid.getStore();
        store.removeAll();

        if (success) {
          var data = PCL.JSON.decode(response.responseText);
          store.add(data);
        }

        el.unmask();
      }
    });
  },

  getInfoPortal: function () {
    var me = this;
    return {
      border: 0,
      html: "<h1 style='color:#0050b3'>欢迎使用" + me.getProductionName() + "</h1>"
    }
  },

  getSaleChart: function () {
    var me = this;
    if (me.__saleChart) {
      return me.__saleChart;
    }

    var modelName = "PSIModel.PSI.Home.SaleChart";
    PCL.define(modelName, {
      extend: "PCL.data.Model",
      fields: ["不含税销售额", "毛利", "month"]
    });
    var store = PCL.create("PCL.data.Store", {
      model: modelName,
      data: []
    });

    me.__saleChart = PCL.create("PCL.chart.Chart", {
      theme: "Category1",
      animate: true,
      legend: {
        position: "top"
      },
      store: store,
      axes: [{
        title: "金额",
        type: "Numeric",
        position: "left",
        grid: true,
        fields: ["不含税销售额"/*, "毛利"*/],
        label: {
          renderer: function (v) {
            return me.formatMoney2(v);
          }
        }
      }, {
        type: "Category",
        position: "bottom",
        fields: ["month"]
      }],
      series: [{
        type: "line",
        xField: "month",
        yField: "不含税销售额",
        highlight: {
          size: 7,
          radius: 7
        },
        tips: {
          trackMouse: true,
          width: 120,
          height: 50,
          renderer: function (storeItem, item) {
            this.setTitle("不含税销售额");
            this.update(me
              .formatMoney(storeItem.get("不含税销售额")));
          }
        }
      }/*, {
        type: "line",
        xField: "month",
        yField: "毛利",
        highlight: {
          size: 7,
          radius: 7
        },
        highlight: {
          size: 7,
          radius: 7
        },
        tips: {
          trackMouse: true,
          width: 120,
          height: 50,
          renderer: function (storeItem, item) {
            this.setTitle("毛利");
            this
              .update(me.formatMoney(storeItem
                .get("毛利")));
          }
        }
      }*/]
    });
    return me.__saleChart;
  },

  getPurchaseChart: function () {
    var me = this;
    if (me.__purchaseChart) {
      return me.__purchaseChart;
    }

    var modelName = "PSIModel.PSI.Home.PurchaseChart";
    PCL.define(modelName, {
      extend: "PCL.data.Model",
      fields: ["purchaseMoney", "month"]
    });
    var store = PCL.create("PCL.data.Store", {
      model: modelName,
      data: []
    });

    me.__purchaseChart = PCL.create("PCL.chart.Chart", {
      theme: "Green",
      animate: true,
      store: store,
      axes: [{
        title: "采购金额",
        type: "Numeric",
        position: "left",
        grid: true,
        fields: ["purchaseMoney"],
        label: {
          renderer: function (v) {
            return me.formatMoney2(v);
          }
        }
      }, {
        type: "Category",
        position: "bottom",
        fields: ["month"]
      }],
      series: [{
        type: "line",
        xField: "month",
        yField: "purchaseMoney",
        highlight: {
          size: 7,
          radius: 7
        },
        tips: {
          trackMouse: true,
          width: 120,
          height: 50,
          renderer: function (storeItem, item) {
            this.setTitle("采购金额");
            this.update(me.formatMoney(storeItem
              .get("purchaseMoney")));
          }
        }
      }]
    });
    return me.__purchaseChart;
  },

  formatMoney: function (value) {
    var value = parseFloat(value);
    var format = "0,000.00";
    if (value >= 0) {
      return PCL.util.Format.number(value, format);
    } else {
      return "-" + PCL.util.Format.number(Math.abs(value), format);
    }
  },

  formatMoney2: function (value) {
    var value = parseFloat(value);
    var format = "0,000";
    if (value >= 0) {
      return PCL.util.Format.number(value, format);
    } else {
      return "-" + PCL.util.Format.number(Math.abs(value), format);
    }
  }
});
