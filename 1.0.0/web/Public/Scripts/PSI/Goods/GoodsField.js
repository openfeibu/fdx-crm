/**
 * 自定义字段 - 物料字段
 * 
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
PCL.define("PSI.Goods.GoodsField", {
  extend: "PCL.form.field.Trigger",
  alias: "widget.psi_goodsfield",

  config: {
    parentCmp: null,
    showAddButton: false,
    showModal: false,
    // 显示库存量（与warehouseEdit一起使用）
    showInvCnt: false,
    // 获得关联的仓库
    warehouseEditName: null,
    selType:"rowmodel",
  },

  /**
   * 初始化组件
   */
  initComponent: function () {
    var me = this;
	
    if(typeof(goods_field_id)=='undefined')
    {
      goods_field_id = 1;
    }else{
      goods_field_id++;
    }

    console.log(goods_field_id);
    me.enableKeyEvents = true;

    me.callParent(arguments);

    me.on("keydown", function (field, e) {
      if (e.getKey() == e.BACKSPACE) {
        field.setValue(null);
        me.clearIdValue();
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

  onTriggerClick: function (e) {
    var me = this;

    var editWarehouse = PCL.getCmp(me.getWarehouseEditName());
    var warehouseId = null;
    if (editWarehouse) {
      warehouseId = editWarehouse.getIdValue();
    }

    var modelName = "PSIGoodsField";
    PCL.define(modelName, {
      extend: "PCL.data.Model",
      fields: ["id", "code", "name", "spec", "unitName",
        "taxRate", "invCnt"]
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
        + "Home/Goods/queryData",
        reader: {
          root: 'goodsList',
          totalProperty: 'totalCount'
        }
      }
    });

    store.on("beforeload", function () {
      store.proxy.extraParams = {
        warehouseId: warehouseId,
        queryKey: editName.getValue(),
      };
    });
    var pagingFieldToolbar_id = 'pagingFieldToolbar_'+goods_field_id;
    var comboFieldCountPerPage_id = 'comboFieldCountPerPage'+goods_field_id;
    var lookupGrid = PCL.create("PCL.grid.Panel", {
      cls: "PSI-Lookup",
      bbar: ["->", {
        id: pagingFieldToolbar_id,
        border: 0,
        xtype: "pagingtoolbar",
        store: store
      }, "-", {
        xtype: "displayfield",
        value: "每页显示"
      }, {
        id: comboFieldCountPerPage_id,
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
              store.pageSize = PCL.getCmp(comboFieldCountPerPage_id).getValue();
              store.currentPage = 1;
              PCL.getCmp(pagingFieldToolbar_id).doRefresh();
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
        header: "当前库存",
        dataIndex: "invCnt",
        menuDisabled: true,
        align: "right",
        hidden: !me.getShowInvCnt() || warehouseId == null,
        width: 80
      }, {
        header: "单位",
        dataIndex: "unitName",
        menuDisabled: true,
        width: 60
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
      header: false,
      modal: me.getShowModal(),
      border: 0,
      width: 850,
      height: 300,
      layout: "border",
      items: [{
        region: "center",
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
          }]
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

    var editName = PCL.getCmp("__editGoods");

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
        store.currentPage = 1;
        store.pageSize = PCL.getCmp(comboFieldCountPerPage_id).getValue();
        PCL.getCmp(pagingFieldToolbar_id).doRefresh();
      }
    }, 500, false));
  },

  onOK: function () {
    var me = this;
    var grid = me.lookupGrid;
    var item = grid.getSelectionModel().getSelection();

    if (item == null || item.length == 0) {
      me.wnd.close();
      me.focus();
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
      me.setIdValue(item[0].getData().id);

      if (me.getParentCmp() && me.getParentCmp().__setGoodsInfo) {
        me.getParentCmp().__setGoodsInfo(data)
      }
    }
  },

  setIdValue: function (id) {
    this.__idValue = id;
  },

  getIdValue: function () {
    return this.__idValue;
  },

  clearIdValue: function () {
    this.setValue(null);
    this.__idValue = null;
  },

  onAddGoods: function () {
    var form = PCL.create("PSI.Goods.GoodsEditForm");

    form.show();
  }
});
