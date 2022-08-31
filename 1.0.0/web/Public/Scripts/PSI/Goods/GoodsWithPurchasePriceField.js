/**
 * 自定义字段 - 物料字段，显示建议采购价
 * 
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
PCL.define("PSI.Goods.GoodsWithPurchaseFieldField", {
  extend: "PCL.form.field.Trigger",
  alias: "widget.psi_goods_with_purchaseprice_field",

  config: {
    parentCmp: null,
    showAddButton: false,
    supplierIdFunc: null,
    supplierIdScope: null,
    selType:"rowmodel",
  },

  /**
   * 初始化组件
   */
  initComponent: function () {
    var me = this;

    me.enableKeyEvents = true;

    me.callParent(arguments);

    me.on("keydown", function (field, e) {
      if (e.getKey() == e.BACKSPACE) {
        field.setValue(null);
        e.preventDefault();
        return false;
      }

      if (e.getKey() != e.ENTER && !e.isSpecialKey(e.getKey())) {
        me.onTriggerClick(e);
      }
    });

    me.on({
      render: function (p) {
        p.getEl().on("dblclick", function () {
          me.onTriggerClick();
        });
      },
      single: true
    });
  },

  /**
   * 单击下拉按钮
   */
  onTriggerClick: function (e) {
    var me = this;
    var modelName = "PSI_Goods_GoodsWithPurchaseFieldField_PSIGoodsField";
    PCL.define(modelName, {
      extend: "PCL.data.Model",
      fields: ["id", "code", "name", "spec", "unitName",
        "purchasePrice", "memo", "taxRate", "taxRateType"]
    });

    var store = PCL.create("PCL.data.Store", {
      model: modelName,
      autoLoad: false,
      data: [],
      pageSize: 20,
      proxy: {
        type: "ajax",
        actionMethods: {
          read: "POST"
        },
        url: PSI.Const.BASE_URL
        + "Home/Goods/queryDataWithPurchasePrice",
        reader: {
          root: 'goodsList',
          totalProperty: 'totalCount'
        }
      }
    });
    store.on("beforeload", function () {
      var supplierId = null;
      var supplierIdFunc = me.getSupplierIdFunc();
      if (supplierIdFunc) {
        supplierId = supplierIdFunc.apply(me.getSupplierIdScope());
      }
      store.proxy.extraParams = {
        supplierId: supplierId,
        queryKey: editName.getValue(),
      };
    });

    var lookupGrid = PCL.create("PCL.grid.Panel", {
      cls: "PSI-Lookup",
      bbar: ["->", {
        id: "pagingFieldToolbar",
        border: 0,
        xtype: "pagingtoolbar",
        store: store
      }, "-", {
        xtype: "displayfield",
        value: "每页显示"
      }, {
        id: "comboFieldCountPerPage",
        xtype: "combobox",
        editable: false,
        width: 60,
        store: PCL.create("PCL.data.ArrayStore", {
          fields: ["text"],
          data: [["20"], ["50"], ["100"], ["300"],
            ["1000"]]
        }),
        value: 20,
        listeners: {
          change: {
            fn: function () {
              store.pageSize = PCL.getCmp("comboFieldCountPerPage").getValue();
              store.currentPage = 1;
              PCL.getCmp("pagingFieldToolbar").doRefresh();
            },
            scope: me
          }
        }
      }, {
        xtype: "displayfield",
        value: "条记录"
      }],
      columnLines: true,
      selType: me.getSelType(), //"checkboxmodel"
      border: 1,
      store: store,
      viewConfig: {
        enableTextSelection: true
      },
      columns: [{
        header: "物料编码",
        dataIndex: "code",
        menuDisabled: true,
        width: 70
      }, {
        header: "品名/规格型号",
        dataIndex: "name",
        menuDisabled: true,
        flex: 1,
        renderer: function (value, metaData, record) {
          return record.get("name") + " " + record.get("spec");
        }
      }, {
        header: "单位",
        dataIndex: "unitName",
        menuDisabled: true,
        width: 60
      }, {
        header: "建议采购价", hidden: true,
        dataIndex: "purchasePrice",
        menuDisabled: true,
        align: "right",
        xtype: "numbercolumn"
      }, {
        header: "备注",
        dataIndex: "memo",
        menuDisabled: true,
        width: 300
      }]
    });
    me.lookupGrid = lookupGrid;
    me.lookupGrid.on("itemdblclick", me.onOK, me);

    var buttons = [{
      xtype: "container",
      html: `
        <div class="PSI-lookup-note">
          输入编码、品名拼音字头、规格型号拼音字头可以过滤查询；
          ↑ ↓ 键改变当前选择项 ；回车键返回
        </div>
        `
    }, "->"];
    if (me.getShowAddButton()) {
      buttons.push({
        text: "新建物料",
        handler: me.onAddGoods,
        scope: me
      });
    }
    buttons.push({
      text: "确定",
      handler: me.onOK,
      scope: me
    }, {
      text: "取消",
      handler: function () {
        wnd.close();
      }
    });

    var wnd = PCL.create("PCL.window.Window", {
      title: "选择 - 物料",
      width: 950,
      height: 300,
      layout: "border",
      header: false,
      border: 0,
      items: [{
        region: "center",
        id: "PSI_Goods_GoodsWithPurchaseFieldField_Form",
        xtype: "panel",
        layout: "fit",
        border: 0,
        items: [lookupGrid]
      }, {
        xtype: "panel",
        region: "south",
        height: 30,
        layout: "fit",
        border: 0,
        items: [{
          xtype: "form",
          layout: "form",
          bodyPadding: 5,
          bodyCls: "PSI-Field",
          items: [{
            id: "__editGoods",
            xtype: "textfield",
            labelWidth: 0,
            labelAlign: "right",
            labelSeparator: ""
          }
          ]
        }]
      }],
      buttons: buttons
    });

    wnd.on("close", function () {
      me.focus();
    });
    wnd.on("deactivate", function () {
      wnd.close();
    });
    me.wnd = wnd;
    me.goodsForm = PCL.getCmp("PSI_Goods_GoodsWithPurchaseFieldField_Form");
    var editName = PCL.getCmp("__editGoods");
    /*
    editName.on("change", function () {


    }, me);
*/
    editName.on("specialkey", function (field, e) {
      if (e.getKey() == e.ENTER) {
        me.onOK();
      } else if (e.getKey() == e.UP) {
        var m = me.lookupGrid.getSelectionModel();
        var store = me.lookupGrid.getStore();
        var index = 0;
        for (var i = 0; i < store.getCount(); i++) {
          if (m.isSelected(i)) {
            index = i;
          }
        }
        index--;
        if (index < 0) {
          index = 0;
        }
        m.select(index);
        e.preventDefault();
        editName.focus();
      } else if (e.getKey() == e.DOWN) {
        var m = me.lookupGrid.getSelectionModel();
        var store = me.lookupGrid.getStore();
        var index = 0;
        for (var i = 0; i < store.getCount(); i++) {
          if (m.isSelected(i)) {
            index = i;
          }
        }
        index++;
        if (index > store.getCount() - 1) {
          index = store.getCount() - 1;
        }
        m.select(index);
        e.preventDefault();
        editName.focus();
      }
    }, me);

    me.wnd.on("show", function () {
      editName.focus();
      //editName.fireEvent("change");

      store.load();
    }, me);
    wnd.showBy(me);

    flag = true;
    Ext.EventManager.addListener("__editGoods", 'compositionstart', function(){
      flag = false;
      console.log(flag)
    });
    Ext.EventManager.addListener("__editGoods", 'compositionend', function(){
      flag = true;
      console.log(flag)
    });

    Ext.EventManager.addListener("__editGoods", 'input', PSI.CustomerCommon.debounce_f(function() {
      if (flag) {
        /*
        const f = me.goodsForm;
        const el = f.getEl();
        el && el.mask("数据加载中...");
        store.load({
          callback: function(records, operation, success) {
            el.unmask();
          }
        });
        */
        store.currentPage = 1;
        store.pageSize = PCL.getCmp("comboFieldCountPerPage").getValue();
        PCL.getCmp("pagingFieldToolbar").doRefresh();
        //store.load();
      }
    }, 500, false));
  },

  onOK: function () {
    var me = this;
    var grid = me.lookupGrid;
    var item = grid.getSelectionModel().getSelection();
    if (item == null ) {

      return;
    }else if (item.length != 1) {
      //多选
      var data = [];
      item.forEach(v => {
        data.push(v.getData());
      })
      me.wnd.close();
      me.focus();

      if (me.getParentCmp() && me.getParentCmp().__setGoodsInfo) {
        me.getParentCmp().__setGoodsInfo(data)
      }
    }else{
      //单选
      var data = [item[0].getData()];

      me.wnd.close();
      me.focus();
      me.setValue(item[0].getData().code);
      me.focus();

      if (me.getParentCmp() && me.getParentCmp().__setGoodsInfo) {
        me.getParentCmp().__setGoodsInfo(data)
      }
    }

  },

  onAddGoods: function () {
    var form = PCL.create("PSI.Goods.GoodsgoodsForm");

    form.show();
  }
});
