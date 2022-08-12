/**
 * 应收账款 - 主界面
 * 
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
PCL.define("PSI.Funds.RvMainForm", {
  extend: "PSI.AFX.BaseMainExForm",
  config: {
    pReceivingPay: "",
  },
  initComponent: function () {
    var me = this;

    PCL.define("PSICACategory", {
      extend: "PCL.data.Model",
      fields: ["id", "name"]
    });

    PCL.apply(me, {
      tbar: [{
        xtype: "displayfield",
        margin: "5 0 0 0",
        value: "往来单位："
      }, {
        cls: "PSI-toolbox",
        xtype: "combo",
        id: "comboCA",
        queryMode: "local",
        editable: false,
        valueField: "id",
        store: PCL.create("PCL.data.ArrayStore", {
          fields: ["id", "text"],
          data: [["customer", "客户"],
          ["supplier", "供应商"]]
        }),
        value: "customer",
        listeners: {
          select: {
            fn: me.onComboCASelect,
            scope: me
          }
        }
      }, {
        xtype: "displayfield",
        margin: "5 0 0 0",
        value: "分类"
      }, {
        cls: "PSI-toolbox",
        xtype: "combobox",
        id: "comboCategory",
        queryMode: "local",
        editable: false,
        valueField: "id",
        displayField: "name",
        store: PCL.create("PCL.data.Store", {
          model: "PSICACategory",
          autoLoad: false,
          data: []
        })
      }, {
        id: "editQueryLabel",
        margin: "5 0 0 0",
        xtype: "displayfield",
        value: "客户 "
      }, {
        cls: "PSI-toolbox",
        id: "editCustomerQuery",
        xtype: "psi_customerfield",
        width: 200,
        showModal: true
      }, {
        cls: "PSI-toolbox",
        id: "editSupplierQuery",
        xtype: "psi_supplierfield",
        hidden: true,
        width: 200,
        showModal: true
      }, {
        xtype: "checkbox",
        boxLabel: "只显示有未收的记录",
        inputValue: "1",
        id: "editQueryHasBalance",
        margin: "5 0 0 0",
        listeners: {
          change: {
            fn: function () {
              me.onQuery();
            },
            scoep: me
          }
        }
      }, " ", "-", {
        text: "查询",
        //iconCls: "PSI-button-refresh",,
        handler: me.onQuery,
        scope: me
      }, {
        text: "清空查询条件",
        handler: me.onClearQuery,
        scope: me
      }, "-", {
        text: "关闭",
        handler: function () {
          me.closeWindow();
        }
      }],
      layout: "border",
      border: 0,
      items: [{
        region: "north",
        height: 2,
        border: 0,
      }, {
        region: "center",
        layout: "fit",
        border: 0,
        items: [me.getRvGrid()]
      }, {
        region: "south",
        layout: "border",
        border: 0,
        split: true,
        height: "50%",
        items: [{
          region: "center",
          border: 0,
          layout: "fit",
          items: [me.getRvDetailGrid()]
        }, {
          region: "east",
          layout: "fit",
          border: 0,
          width: "40%",
          split: true,
          items: [me.getRvRecordGrid()]
        }]
      }]
    });

    me.callParent(arguments);

    me.onComboCASelect();
  },

  getRvGrid: function () {
    var me = this;
    if (me.__rvGrid) {
      return me.__rvGrid;
    }

    PCL.define("PSIRv", {
      extend: "PCL.data.Model",
      fields: ["id", "caId", "code", "name", "recordStatus", "rvMoney",
        "actMoney", "balanceMoney"]
    });

    var store = PCL.create("PCL.data.Store", {
      model: "PSIRv",
      pageSize: 20,
      proxy: {
        type: "ajax",
        actionMethods: {
          read: "POST"
        },
        url: PSI.Const.BASE_URL + "Home/Funds/rvList",
        reader: {
          root: 'dataList',
          totalProperty: 'totalCount'
        }
      },
      autoLoad: false,
      data: []
    });

    store.on("beforeload", function () {
      PCL.apply(store.proxy.extraParams, {
        caType: PCL.getCmp("comboCA").getValue(),
        categoryId: PCL.getCmp("comboCategory").getValue(),
        customerId: PCL.getCmp("editCustomerQuery").getIdValue(),
        supplierId: PCL.getCmp("editSupplierQuery").getIdValue(),
        hasBalance: PCL.getCmp("editQueryHasBalance").getValue() ? 1 : 0
      });
    });

    me.__rvGrid = PCL.create("PCL.grid.Panel", {
      cls: "PSI-FC",
      viewConfig: {
        enableTextSelection: true
      },
      bbar: ["->", {
        xtype: "pagingtoolbar",
        border: 0,
        store: store
      }],
      columnLines: true,
      columns: [{
        header: "编码",
        dataIndex: "code",
        menuDisabled: true,
        sortable: false
      }, {
        header: "名称",
        dataIndex: "name",
        menuDisabled: true,
        sortable: false,
        width: 300,
        renderer(value, metaData, record) {
          return PSI.CustomerCommon.recordStatusHtml(record.get("recordStatus"),value);
        }
      }, {
        header: "应收金额",
        dataIndex: "rvMoney",
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
        width: 160
      }, {
        header: "已收金额",
        dataIndex: "actMoney",
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
        width: 160
      }, {
        header: "未收金额",
        dataIndex: "balanceMoney",
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn",
        width: 160
      }],
      store: store,
      listeners: {
        select: {
          fn: me.onRvGridSelect,
          scope: me
        }
      }
    });

    return me.__rvGrid;
  },

  getRvParam: function () {
    var item = this.getRvGrid().getSelectionModel().getSelection();
    if (item == null || item.length != 1) {
      return null;
    }

    var rv = item[0];
    return rv.get("caId");
  },

  onRvGridSelect: function () {
    var me = this;

    this.getRvRecordGrid().getStore().removeAll();
    this.getRvRecordGrid().setTitle(me.formatGridHeaderTitle("收款记录"));

    this.getRvDetailGrid().getStore().loadPage(1);
  },

  getRvDetailGrid: function () {
    var me = this;
    if (me.__rvDetailGrid) {
      return me.__rvDetailGrid;
    }

    PCL.define("PSIRvDetail", {
      extend: "PCL.data.Model",
      fields: ["id", "rvMoney", "actMoney", "balanceMoney",
        "refType", "refNumber", "bizDT", "dateCreated"]
    });

    var store = PCL.create("PCL.data.Store", {
      model: "PSIRvDetail",
      pageSize: 20,
      proxy: {
        type: "ajax",
        actionMethods: {
          read: "POST"
        },
        url: PSI.Const.BASE_URL + "Home/Funds/rvDetailList",
        reader: {
          root: 'dataList',
          totalProperty: 'totalCount'
        }
      },
      autoLoad: false,
      data: []
    });

    store.on("beforeload", function () {
      PCL.apply(store.proxy.extraParams, {
        caType: PCL.getCmp("comboCA").getValue(),
        caId: me.getRvParam()
      });
    });

    me.__rvDetailGrid = PCL.create("PCL.grid.Panel", {
      cls: "PSI-HL",
      viewConfig: {
        enableTextSelection: true,
        getRowClass:function(record,index,p,ds) {
          var cls = 'white-row';
          if(record.data.balanceMoney == 0)
          {
            cls = 'x-grid-record-green';
          }else if(record.data.balanceMoney < 0){
            cls = 'x-grid-record-orange';
          }
          return cls;
        }
      },
      header: {
        height: 30,
        title: me.formatGridHeaderTitle("业务单据")
      },
      bbar: ["->", {
        xtype: "pagingtoolbar",
        border: 0,
        store: store
      }],
      columnLines: true,
      selType: "checkboxmodel",
      tbar: [{
        text: "单据生成pdf",
        iconCls: "PSI-button-pdf",
        handler: me.onPDF,
        id: "buttonPDF",
        scope: me
      },{
        text: "单据生成Excel",
        iconCls: "PSI-button-excel",
        handler: me.onExcel,
        id: "buttonExcel",
        scope: me
      }],
      columns: [{
        header: "业务类型",
        dataIndex: "refType",
        menuDisabled: true,
        sortable: false,
        width: 120
      }, {
        header: "单号",
        dataIndex: "refNumber",
        menuDisabled: true,
        sortable: false,
        width: 120,
        renderer: function (value, md, record) {
          if (record.get("refType") == "应收账款期初建账") {
            return value;
          }

          return "<a href='"
            + PSI.Const.BASE_URL
            + "Home/Bill/viewIndex?fid=2004&refType="
            + encodeURIComponent(record
              .get("refType"))
            + "&ref="
            + encodeURIComponent(record
              .get("refNumber"))
            + "' target='_blank'>" + value
            + "</a>";
        }
      }, {
        header: "业务日期",
        dataIndex: "bizDT",
        menuDisabled: true,
        sortable: false
      }, {
        header: "应收金额",
        dataIndex: "rvMoney",
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn"
      }, {
        header: "已收金额",
        dataIndex: "actMoney",
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn"
      }, {
        header: "未收金额",
        dataIndex: "balanceMoney",
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn"
      }, {
        header: "创建时间",
        dataIndex: "dateCreated",
        menuDisabled: true,
        sortable: false,
        width: 140
      }],
      store: store,
      listeners: {
        select: {
          fn: me.onRvDetailGridSelect,
          scope: me
        }
      },
    });

    return me.__rvDetailGrid;
  },

  onRvDetailGridSelect: function () {
    var me = this;

    var grid = this.getRvRecordGrid();
    var item = this.getRvDetailGrid().getSelectionModel().getSelection();
    if (item == null || item.length != 1) {
      grid.setTitle(me.formatGridHeaderTitle("收款记录"));
      return null;
    }

    var rvDetail = item[0];

    grid.setTitle(me.formatGridHeaderTitle(rvDetail.get("refType")
      + " - 单号: " + rvDetail.get("refNumber") + " 的收款记录"));
    grid.getStore().loadPage(1);
  },

  getRvRecordGrid: function () {
    var me = this;
    if (me.__rvRecordGrid) {
      return me.__rvRecordGrid;
    }

    PCL.define("PSIRvRecord", {
      extend: "PCL.data.Model",
      fields: ["id", "actMoney", "bizDate", "bizUserName",
        "inputUserName", "dateCreated", "remark"]
    });

    var store = PCL.create("PCL.data.Store", {
      model: "PSIRvRecord",
      pageSize: 20,
      proxy: {
        type: "ajax",
        actionMethods: {
          read: "POST"
        },
        url: PSI.Const.BASE_URL + "Home/Funds/rvRecordList",
        reader: {
          root: 'dataList',
          totalProperty: 'totalCount'
        }
      },
      autoLoad: false,
      data: []
    });

    store.on("beforeload", function () {
      var rvDetail
      var item = me.getRvDetailGrid().getSelectionModel()
        .getSelection();
      if (item == null || item.length != 1) {
        rvDetail = null;
      } else {
        rvDetail = item[0];
      }

      PCL.apply(store.proxy.extraParams, {
        refType: rvDetail == null ? null : rvDetail.get("refType"),
        refNumber: rvDetail == null ? null : rvDetail.get("refNumber")
      });
    });

    me.__rvRecordGrid = PCL.create("PCL.grid.Panel", {
      cls: "PSI-HL",
      viewConfig: {
        enableTextSelection: true
      },
      header: {
        height: 30,
        title: me.formatGridHeaderTitle("收款记录")
      },
      tbar: [{
        text: "录入收款记录",
        iconCls: "PSI-button-add-record",
        handler: me.onAddRvRecord,
        scope: me,
        hidden: me.getPReceivingPay() != '1', true : false,
      }],
      bbar: ["->", {
        xtype: "pagingtoolbar",
        border: 0,
        store: store
      }],
      columnLines: true,
      columns: [{
        header: "收款日期",
        dataIndex: "bizDate",
        menuDisabled: true,
        sortable: false,
        width: 80
      }, {
        header: "收款金额",
        dataIndex: "actMoney",
        menuDisabled: true,
        sortable: false,
        align: "right",
        xtype: "numbercolumn"
      }, {
        header: "收款人",
        dataIndex: "bizUserName",
        menuDisabled: true,
        sortable: false,
        width: 80
      }, {
        header: "录入时间",
        dataIndex: "dateCreated",
        menuDisabled: true,
        sortable: false,
        width: 140
      }, {
        header: "录入人",
        dataIndex: "inputUserName",
        menuDisabled: true,
        sortable: false,
        width: 80
      }, {
        header: "备注",
        dataIndex: "remark",
        menuDisabled: true,
        sortable: false,
        width: 150
      }],
      store: store
    });

    return me.__rvRecordGrid;
  },

  onComboCASelect: function () {
    var me = this;

    var caType = PCL.getCmp("comboCA").getValue();
    if (caType == "customer") {
      PCL.getCmp("editQueryLabel").setValue("客户");
      PCL.getCmp("editCustomerQuery").setVisible(true);
      PCL.getCmp("editSupplierQuery").setVisible(false);
    } else {
      PCL.getCmp("editQueryLabel").setValue("供应商");
      PCL.getCmp("editCustomerQuery").setVisible(false);
      PCL.getCmp("editSupplierQuery").setVisible(true);
    }

    me.getRvGrid().getStore().removeAll();
    me.getRvDetailGrid().getStore().removeAll();
    me.getRvRecordGrid().getStore().removeAll();

    var el = PCL.getBody();
    el.mask(PSI.Const.LOADING);
    PCL.Ajax.request({
      url: PSI.Const.BASE_URL + "Home/Funds/rvCategoryList",
      params: {
        id: PCL.getCmp("comboCA").getValue()
      },
      method: "POST",
      callback: function (options, success, response) {
        var combo = PCL.getCmp("comboCategory");
        var store = combo.getStore();

        store.removeAll();

        if (success) {
          var data = PCL.JSON.decode(response.responseText);
          store.add(data);

          if (store.getCount() > 0) {
            combo.setValue(store.getAt(0).get("id"))
          }
        }

        el.unmask();
      }
    });
  },

  onQuery: function () {
    var me = this;
    me.getRvDetailGrid().getStore().removeAll();
    me.getRvRecordGrid().getStore().removeAll();
    me.getRvRecordGrid().setTitle(me.formatGridHeaderTitle("收款记录"));

    me.getRvGrid().getStore().loadPage(1);
  },

  onAddRvRecord: function () {
    var me = this;
    var item = me.getRvDetailGrid().getSelectionModel().getSelection();
    if (item == null || item.length != 1) {
      PSI.MsgBox.showInfo("请选择要做收款记录的业务单据");
      return;
    }

    var rvDetail = item[0];
    var form = PCL.create("PSI.Funds.RvRecordEditForm", {
      parentForm: me,
      rvDetail: rvDetail
    })
    form.show();
  },

  refreshRvInfo: function () {
    var me = this;
    var item = me.getRvGrid().getSelectionModel().getSelection();
    if (item == null || item.length != 1) {
      return;
    }
    var rv = item[0];

    PCL.Ajax.request({
      url: PSI.Const.BASE_URL + "Home/Funds/refreshRvInfo",
      method: "POST",
      params: {
        id: rv.get("id")
      },
      callback: function (options, success, response) {
        if (success) {
          var data = PCL.JSON.decode(response.responseText);
          rv.set("actMoney", data.actMoney);
          rv.set("balanceMoney", data.balanceMoney)
          me.getRvGrid().getStore().commitChanges();
        }
      }

    });
  },

  refreshRvDetailInfo: function () {
    var me = this;
    var item = me.getRvDetailGrid().getSelectionModel().getSelection();
    if (item == null || item.length != 1) {
      return;
    }
    var rvDetail = item[0];

    PCL.Ajax.request({
      url: PSI.Const.BASE_URL + "Home/Funds/refreshRvDetailInfo",
      method: "POST",
      params: {
        id: rvDetail.get("id")
      },
      callback: function (options, success, response) {
        if (success) {
          var data = PCL.JSON.decode(response.responseText);
          rvDetail.set("actMoney", data.actMoney);
          rvDetail.set("balanceMoney", data.balanceMoney)
          me.getRvDetailGrid().getStore().commitChanges();
        }
      }

    });
  },
  onPDF: function () {
    var me = this;
    const grid = me.__rvDetailGrid;

    const items = grid.getSelectionModel().getSelection();
    if (items == null || items.length == 0) {
      me.showInfo("请选择要生成pdf的单据");
      return;
    }
    var ids = '';
    items.forEach(items => {
      ids += items.get('id') + ',';
    })
    var url = PSI.Const.BASE_URL + "Home/Funds/rvPdf";
    var oForm = document.createElement("form");　　　　//表单提交
    oForm.method="post";
    oForm.target="_blank";
    oForm.action=url; 　　　　//action路径

    var hasitemsids_input = document.createElement("input");
    hasitemsids_input.type="hidden";
    hasitemsids_input.name="ids";
    hasitemsids_input.value=ids;  　　　　//待传参数
    oForm.appendChild(hasitemsids_input);
    document.body.appendChild(oForm);

    oForm.submit();
  },
  onExcel: function () {
    var me = this;
    const grid = me.__rvDetailGrid;

    const items = grid.getSelectionModel().getSelection();
    if (items == null || items.length == 0) {
      me.showInfo("请选择要生成Excel的单据");
      return;
    }
    var ids = '';
    items.forEach(items => {
      ids += items.get('id') + ',';
    })
    var url = PSI.Const.BASE_URL + "Home/Funds/rvExcel";
    var oForm = document.createElement("form");　　　　//表单提交
    oForm.method="post";
    oForm.target="_blank";
    oForm.action=url; 　　　　//action路径

    var hasitemsids_input = document.createElement("input");
    hasitemsids_input.type="hidden";
    hasitemsids_input.name="ids";
    hasitemsids_input.value=ids;  　　　　//待传参数
    oForm.appendChild(hasitemsids_input);
    document.body.appendChild(oForm);

    oForm.submit();
  },
  onClearQuery: function () {
    var me = this;

    PCL.getCmp("editCustomerQuery").clearIdValue();
    PCL.getCmp("editSupplierQuery").clearIdValue();
    me.onQuery();
  }
});
