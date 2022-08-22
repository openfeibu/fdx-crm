/**
 * 采购入库单 - 新增或编辑界面
 *
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
PCL.define("PSI.PurchaseRej.PREditForm", {
  extend: "PSI.AFX.BaseDialogForm",
  config: {
    viewPrice: true
  },

  mixins: ["PSI.Mix.GoodsPrice"],

  initComponent: function () {
    var me = this;
    me.__readOnly = false;
    var entity = me.getEntity();
    me.adding = entity == null;

    var title = entity == null ? "新建采购退货出库单" : "编辑采购退货出库单";
    title = me.formatTitle(title);

    PCL.apply(me, {
      header: {
        title: title,
        height: 40
      },
      maximized: true,
      width: 1000,
      height: 600,
      layout: "border",
      defaultFocus: "editSupplier",
      tbar: [{
        xtype: "displayfield",
        value: "条码录入",
        id: "displayFieldBarcode"
      }, {
        xtype: "textfield",
        cls: "PSI-toolbox",
        id: "editBarcode",
        listeners: {
          specialkey: {
            fn: me.onEditBarcodeKeydown,
            scope: me
          }
        }

      }, " ", {
        text: "保存",
        id: "buttonSave",
        //iconCls: "PSI-button-ok",
        handler: me.onOK,
        scope: me
      }, "-", {
        text: "取消",
        id: "buttonCancel",
        handler: function () {
          if (me.__readonly) {
            me.close();
            return;
          }

          me.confirm("请确认是否取消当前操作？", function () {
            me.close();
          });
        },
        scope: me
      }, "->", /*{
       text: "表单通用操作指南",
       iconCls: "PSI-help",
       handler: function () {
       me.focus();
       window.open(me.URL("Home/Help/index?t=commBill"));
       }
       }, "-", /*{
       fieldLabel: "快捷访问",
       labelSeparator: "",
       margin: "5 5 5 0",
       cls: "PSI-toolbox",
       labelAlign: "right",
       labelWidth: 50,
       emptyText: "双击此处弹出选择框",
       xtype: "psi_mainmenushortcutfield"
       }*/],
      items: [{
        region: "center",
        layout: "fit",
        border: 0,
        bodyPadding: 10,
        items: [me.getGoodsGrid()]
      }, {
        region: "north",
        id: "editForm",
        layout: {
          type: "table",
          columns: 4
        },
        height: 90,
        bodyPadding: 10,
        border: 0,
        items: [{
          xtype: "hidden",
          id: "hiddenId",
          name: "id",
          value: entity == null ? null : entity
            .get("id")
        }, {
          id: "editRef",
          labelWidth: 60,
          labelAlign: "right",
          labelSeparator: "",
          fieldLabel: "单号",
          xtype: "displayfield",
          value: me.toFieldNoteText("保存后自动生成")
        }, {
          id: "editBizDT",
          fieldLabel: "业务日期",
          labelWidth: 60,
          labelAlign: "right",
          labelSeparator: "",
          allowBlank: false,
          blankText: "没有输入业务日期",
          beforeLabelTextTpl: PSI.Const.REQUIRED,
          xtype: "datefield",
          format: "Y-m-d",
          value: new Date(),
          name: "bizDT",
          listeners: {
            specialkey: {
              fn: me.onEditBizDTSpecialKey,
              scope: me
            }
          }
        }, {
          id: "editSupplier",
          colspan: 2,
          width: 430,
          labelWidth: 60,
          labelAlign: "right",
          labelSeparator: "",
          xtype: "psi_supplierfield",
          fieldLabel: "供应商",
          allowBlank: false,
          blankText: "没有输入供应商",
          beforeLabelTextTpl: PSI.Const.REQUIRED,
          listeners: {
            specialkey: {
              fn: me.onEditSupplierSpecialKey,
              scope: me
            }
          },
          showAddButton: true
        }, {
          id: "editWarehouse",
          labelWidth: 60,
          labelAlign: "right",
          labelSeparator: "",
          fieldLabel: "入库仓库",
          xtype: "psi_warehousefield",
          fid: "2001",
          allowBlank: false,
          blankText: "没有输入入库仓库",
          beforeLabelTextTpl: PSI.Const.REQUIRED,
          listeners: {
            specialkey: {
              fn: me.onEditWarehouseSpecialKey,
              scope: me
            }
          }
        }, {
          id: "editBizUser",
          labelWidth: 60,
          labelAlign: "right",
          labelSeparator: "",
          fieldLabel: "业务员",
          xtype: "psi_userfield",
          allowBlank: false,
          blankText: "没有输入业务员",
          beforeLabelTextTpl: PSI.Const.REQUIRED,
          listeners: {
            specialkey: {
              fn: me.onEditBizUserSpecialKey,
              scope: me
            }
          }
        }, {
          id: "editReceivingType",
          labelWidth: 60,
          width: 430,
          labelAlign: "right",
          labelSeparator: "",
          fieldLabel: "付款方式",
          xtype: "combo",
          queryMode: "local",
          editable: false,
          valueField: "id",
          store: PCL.create("PCL.data.ArrayStore", {
            fields: ["id", "text"],
            data: [["0", "冲销 记应付账款"],
              ["1", "现金付款"],
              /*["2", "预付款"]*/]
          }),
          value: "0",
          listeners: {
            specialkey: {
              fn: me.oneditReceivingTypeSpecialKey,
              scope: me
            }
          }
        }, {
          id: "editBillMemo",
          labelWidth: 60,
          labelAlign: "right",
          labelSeparator: "",
          fieldLabel: "备注",
          xtype: "textfield",
          //colspan: 4,
          width: 860,
          listeners: {
            specialkey: {
              fn: me.onEditBillMemoSpecialKey,
              scope: me
            }
          }
        }]
      }],
      listeners: {
        show: {
          fn: me.onWndShow,
          scope: me
        },
        close: {
          fn: me.onWndClose,
          scope: me
        }
      }
    });

    me.callParent(arguments);

    me.editRef = PCL.getCmp("editRef");
    me.editBizDT = PCL.getCmp("editBizDT");
    me.editSupplier = PCL.getCmp("editSupplier");
    me.editWarehouse = PCL.getCmp("editWarehouse");
    me.editBizUser = PCL.getCmp("editBizUser");
    me.editReceivingType = PCL.getCmp("editReceivingType");
    me.editExpand = PCL.getCmp("editExpand");
    me.editBillMemo = PCL.getCmp("editBillMemo");

    me.editHiddenId = PCL.getCmp("hiddenId");

    me.columnActionDelete = PCL.getCmp("columnActionDelete");
    me.columnActionAdd = PCL.getCmp("columnActionAdd");
    me.columnActionAppend = PCL.getCmp("columnActionAppend");
    me.editBarcode = PCL.getCmp("editBarcode");

    me.columnGoodsCode = PCL.getCmp("columnGoodsCode");
    //me.columnGoodsPrice = PCL.getCmp("columnGoodsPrice");
    //me.columnGoodsMoney = PCL.getCmp("columnGoodsMoney");

    me.buttonSave = PCL.getCmp("buttonSave");
    me.buttonCancel = PCL.getCmp("buttonCancel");

    me.displayFieldBarcode = PCL.getCmp("displayFieldBarcode");
  },

  onWindowBeforeUnload: function (e) {
    return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
  },

  onWndClose: function () {
    // 加上这个调用是为了解决 #IMQB2 - https://gitee.com/crm8000/PSI/issues/IMQB2
    // 这个只是目前的临时应急方法，实现的太丑陋了
    PCL.WindowManager.hideAll();

    PCL.get(window).un('beforeunload', this.onWindowBeforeUnload);
  },

  onWndShow: function () {
    PCL.get(window).on('beforeunload', this.onWindowBeforeUnload);

    var me = this;

    var el = me.getEl() || PCL.getBody();
    el.mask(PSI.Const.LOADING);
    me.ajax({
      url: me.URL("Home/PurchaseRej/prBillInfo"),
      params: {
        id: me.editHiddenId.getValue(),
      },
      callback: function (options, success, response) {
        el.unmask();

        if (success) {
          var data = me.decodeJSON(response.responseText);

          me.columnGoodsCode.setEditor({
            xtype: "psi_goods_with_purchaseprice_field",
            parentCmp: me,
            //showAddButton: me.getShowAddGoodsButton() == "1",
            supplierIdFunc: me.__supplierIdFunc,
            supplierIdScope: me,
            selType: "checkboxmodel",
          });
          // if (me.getViewPrice()) {
          //   me.columnGoodsPrice.setEditor({
          //     xtype: "numberfield",
          //     hideTrigger: true
          //   });
          //   me.columnGoodsMoney.setEditor({
          //     xtype: "numberfield",
          //     hideTrigger: true
          //   });
          // }

          if (data.ref) {
            me.editRef.setValue(me.toFieldNoteText(data.ref));

            me.editSupplier.setIdValue(data.supplierId);
            me.editSupplier.setValue(data.supplierName);

            me.editBillMemo.setValue(data.billMemo);

          }
          me.editWarehouse.setIdValue(data.warehouseId);
          me.editWarehouse.setValue(data.warehouseName);

          me.editBizUser.setIdValue(data.bizUserId);
          me.editBizUser.setValue(data.bizUserName);
          if (data.bizDT) {
            me.editBizDT.setValue(data.bizDT);
          }
          if (data.receivingType) {
            me.editReceivingType.setValue(data.receivingType);
          }

          me.__billId = data.pwbillId;

          var store = me.getGoodsGrid().getStore();
          store.removeAll();
          if (data.items) {
            store.add(data.items);
          }
          if (store.getCount() == 0) {
            store.add({});
          }

          if (data.billStatus && data.billStatus != 0) {
            me.setBillReadonly();
          }

        }
      }
    });
  },

  onOK: function () {
    var me = this;
    PCL.getBody().mask("正在保存中...");
    var r = {
      url: me.URL("Home/PurchaseRej/editPRBill"),
      params: {
        adding: me.adding ? "1" : "0",
        jsonStr: me.getSaveData()
      },
      callback: function (options, success, response) {
        PCL.getBody().unmask();

        if (success) {
          var data = me.decodeJSON(response.responseText);
          if (data.success) {
            me.close();
            var pf = me.getParentForm();
            if (pf) {
              pf.refreshMainGrid(data.id);
            }

            me.tip("成功保存数据");
          } else {
            me.showInfo(data.msg);
          }
        }
      }
    };
    me.ajax(r);
  },

  onEditBizDTSpecialKey: function (field, e) {
    var me = this;

    if (e.getKey() == e.ENTER) {
      me.editSupplier.focus();
    }
  },

  onEditSupplierSpecialKey: function (field, e) {
    var me = this;

    if (e.getKey() == e.ENTER) {
      me.editWarehouse.focus();
    }
  },

  onEditWarehouseSpecialKey: function (field, e) {
    var me = this;

    if (e.getKey() == e.ENTER) {
      me.editBizUser.focus();
    }
  },

  onEditBizUserSpecialKey: function (field, e) {
    var me = this;

    if (me.__readonly) {
      return;
    }

    if (e.getKey() == e.ENTER) {
      me.editReceivingType.focus();
    }
  },

  onEditBillMemoSpecialKey: function (field, e) {
    var me = this;

    if (me.__readonly) {
      return;
    }

    if (e.getKey() == e.ENTER) {
      var store = me.getGoodsGrid().getStore();
      if (store.getCount() == 0) {
        store.add({});
      }
      me.getGoodsGrid().focus();
      me.__cellEditing.startEdit(0, 1);
    }
  },

  oneditReceivingTypeSpecialKey: function (field, e) {
    var me = this;

    if (me.__readonly) {
      return;
    }

    if (e.getKey() == e.ENTER) {
      me.editExpand.focus();
    }
  },

  onEditExpandSpecialKey: function (field, e) {
    var me = this;

    if (me.__readonly) {
      return;
    }

    if (e.getKey() == e.ENTER) {
      me.editBillMemo.focus();
    }
  },

  getGoodsGrid: function () {
    var me = this;
    if (me.__goodsGrid) {
      return me.__goodsGrid;
    }
    var modelName = "PSIPRBillDetail_EditForm";
    PCL.define(modelName, {
      extend: "PCL.data.Model",
      fields: ["id", "goodsId", "goodsCode", "goodsName",
        "goodsSpec", "unitName", "goodsCount",
        "goodsMoney", "goodsPrice", "rejCount", "rejPrice",
        {
          name: "rejMoney",
          type: "float"
        }, "memo", "rejPriceWithTax", {
          name: "rejMoneyWithTax",
          type: "float"
        }, "goodsMoneyWithTax", "goodsPriceWithTax", "taxRate"]
    });
    var store = PCL.create("PCL.data.Store", {
      autoLoad: false,
      model: modelName,
      data: []
    });

    me.__cellEditing = PCL.create("PSI.UX.CellEditing", {
      clicksToEdit: 1,
      listeners: {
        edit: {
          fn: me.cellEditingAfterEdit,
          scope: me
        }
      }
    });

    me.__goodsGrid = PCL.create("PCL.grid.Panel", {
      cls: "PSI-EF",
      viewConfig: {
        enableTextSelection: true,
        markDirty: !me.adding
      },
      features: [{
        ftype: "summary"
      }],
      plugins: [me.__cellEditing],
      columnLines: true,
      columns: {
        defaults: {
          menuDisabled: true,
          sortable: false,
          draggable: false
        },
        items: [{
          xtype: "rownumberer",
          text: "#",
          width: 30
        }, {
          header: "物料编码",
          dataIndex: "goodsCode",
          id: "columnGoodsCode"
        }, {
          header: "品名/规格型号",
          dataIndex: "goodsName",
          width: 330,
          renderer: function (value, metaData, record) {
            return record.get("goodsName") + " " + record.get("goodsSpec");
          }
        }, {
          header: "退货数量",
          dataIndex: "rejCount",
          menuDisabled: true,
          sortable: false,
          draggable: false,
          align: "right",
          width: 80,
          editor: {
            xtype: "numberfield",
            allowDecimals: PSI.Const.GC_DEC_NUMBER > 0,
            decimalPrecision: PSI.Const.GC_DEC_NUMBER,
            minValue: 0,
            hideTrigger: true
          }
        }, {
          header: "单位",
          dataIndex: "unitName",
          menuDisabled: true,
          sortable: false,
          draggable: false,
          width: 50,
          align: "center"
        }, {
          header: "退货单价(含税)", hidden: true, //隐藏退货单价(含税)
          dataIndex: "rejPriceWithTax",
          menuDisabled: true,
          sortable: false,
          draggable: false,
          align: "right",
          xtype: "numbercolumn",
          width: 100,
          editor: {
            xtype: "numberfield",
            hideTrigger: true
          },
          summaryRenderer: function () {
            return "金额合计";
          }
        }, {
          header: "退货金额(含税)", hidden: true, //隐藏退货金额(含税)
          dataIndex: "rejMoneyWithTax",
          menuDisabled: true,
          sortable: false,
          draggable: false,
          align: "right",
          xtype: "numbercolumn",
          width: 100,
          editor: {
            xtype: "numberfield",
            hideTrigger: true
          },
          summaryType: "sum"
        }, {
          header: "退货单价", //退货单价(不含税)
          dataIndex: "rejPrice",
          id: "rejPrice",
          menuDisabled: true,
          sortable: false,
          draggable: false,
          align: "right",
          xtype: "numbercolumn",
          width: 110,
          editor: {
            xtype: "numberfield",
            hideTrigger: true
          },
          hidden: !me.getViewPrice()
        }, {
          header: "退货金额", //退货金额(不含税)
          dataIndex: "rejMoney",
          menuDisabled: true,
          sortable: false,
          draggable: false,
          align: "right",
          xtype: "numbercolumn",
          width: 110,
          editor: {
            xtype: "numberfield",
            hideTrigger: true
          },
          summaryType: "sum",
          hidden: !me.getViewPrice()
        }, {
          header: "税率(%)", hidden: true, //隐藏税率
          dataIndex: "taxRate",
          menuDisabled: true,
          sortable: false,
          draggable: false,
          align: "right",
          xtype: "numbercolumn",
          format: "#",
          width: 60
        }, /*{
          header: "原采购数量",
          dataIndex: "goodsCount",
          menuDisabled: true,
          sortable: false,
          draggable: false,
          align: "right",
          width: 80
        }, {
          header: "原采购单价", //原采购单价(不含税)
          dataIndex: "goodsPrice",
          menuDisabled: true,
          sortable: false,
          draggable: false,
          align: "right",
          xtype: "numbercolumn",
          width: 120
        }, {
          header: "原采购金额", //原采购金额(不含税)
          dataIndex: "goodsMoney",
          menuDisabled: true,
          sortable: false,
          draggable: false,
          align: "right",
          xtype: "numbercolumn",
          width: 120
        }, {
          header: "原采购单价(含税)", hidden: true, //隐藏原采购单价(含税)
          dataIndex: "goodsPriceWithTax",
          menuDisabled: true,
          sortable: false,
          draggable: false,
          align: "right",
          xtype: "numbercolumn",
          width: 110
        }, {
          header: "原采购金额(含税)", hidden: true, //隐藏原采购金额(含税)
          dataIndex: "goodsMoneyWithTax",
          menuDisabled: true,
          sortable: false,
          draggable: false,
          align: "right",
          xtype: "numbercolumn",
          width: 110
        },*/ {
          header: "备注",
          dataIndex: "memo",
          editor: {
            xtype: "textfield"
          }
        }, {
          header: "",
          id: "columnActionDelete",
          align: "center",
          width: 40,
          xtype: "actioncolumn",
          items: [{
            icon: me.URL("Public/Images/icons/delete.png"),
            tooltip: "删除当前记录",
            handler: function (grid, row) {
              var store = grid.getStore();
              store.remove(store.getAt(row));
              if (store.getCount() == 0) {
                store.add({});
              }
            },
            scope: me
          }]
        }
          , {
            header: "",
            id: "columnActionAdd",
            align: "center",
            width: 40,
            xtype: "actioncolumn",
            items: [{
              icon: me.URL("Public/Images/icons/insert.png"),
              tooltip: "在当前记录之前插入新记录",
              handler: function (grid, row) {
                var store = grid.getStore();
                store.insert(row, [{}]);
              },
              scope: me
            }]
          }, {
            header: "",
            id: "columnActionAppend",
            align: "center",
            width: 40,
            xtype: "actioncolumn",
            items: [{
              icon: me
                .URL("Public/Images/icons/add.png"),
              tooltip: "在当前记录之后新增记录",
              handler: function (grid, row) {
                var store = grid.getStore();
                store.insert(row + 1, [{}]);
              },
              scope: me
            }]
          }
        ]
      },
      store: store,
      listeners: {
        cellclick: function () {
          return !me.__readonly;
        }
      }
    });

    return me.__goodsGrid;
  },

  // xtype:psi_goods_with_purchaseprice_field中回调本方法
  // 参见PSI.Goods.GoodsWithPurchaseFieldField的onOK方法
  __setGoodsInfo: function (data) {
    var me = this;
    var item = me.getGoodsGrid().getSelectionModel().getSelection();
    var selectStore = me.getGoodsGrid().getStore();

    if (item == null) {
      return;
    }else if(data.length != 1){
      var selectData = [];
      data.forEach(v => {
        selectData.push({
          "goodsId":v.id,
          "goodsCode":v.code,
          "goodsName":v.name,
          "unitName":v.unitName,
          "goodsSpec":v.spec,
          "taxRate":v.taxRate,
          "rejPrice": v.purchasePrice,
          "goodsPrice": v.purchasePrice,
        });
      })
      if(item[0].data.goodsId.length == 0){
        console.log(1)
        selectStore.remove(item)
      }
      selectStore.add(selectData);
    }else{

      var goods = item[0];
      var dataInfo = data[0];

      goods.set("goodsId", dataInfo.id);
      goods.set("goodsCode", dataInfo.code);
      goods.set("goodsName", dataInfo.name);
      goods.set("unitName", dataInfo.unitName);
      goods.set("goodsSpec", dataInfo.spec);
      goods.set("taxRate", dataInfo.taxRate);
      goods.set("rejPrice", dataInfo.purchasePrice);
      // 设置建议采购价
      goods.set("goodsPrice", dataInfo.purchasePrice);

      me.calcMoney(goods);
    }
  },

  cellEditingAfterEdit: function (editor, e) {
    var me = this;

    var fieldName = e.field;
    var goods = e.record;
    var oldValue = e.originalValue;

    if (fieldName == "rejMoney") {
      if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
        me.calcPrice(goods);
      }

      var store = me.getGoodsGrid().getStore();
      e.rowIdx += 1;
      me.getGoodsGrid().getSelectionModel().select(e.rowIdx);
      me.__cellEditing.startEdit(e.rowIdx, 1);
    } else if (fieldName == "rejCount") {
      if (goods.get(fieldName) != oldValue) {
        me.calcMoney(goods);
      }
    } else if (fieldName == "rejPrice") {
      if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
        me.calcMoney(goods);
      }
    } else if (fieldName == "rejPriceWithTax") {
      if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
        me.calcMoney2(goods);
      }
    } else if (fieldName == "rejMoneyWithTax") {
      if (goods.get(fieldName) != (new Number(oldValue)).toFixed(2)) {
        me.calcPrice2(goods);
      }
    }
  },
// 因为退货数量或不含税单价变化
  calcMoney: function (goods) {
    if (!goods) {
      return;
    }

    var rejCount = goods.get("rejCount");
    var rejPrice = goods.get("rejPrice");
    var taxRate = goods.get("taxRate") / 100;

    goods.set("goodsPrice", rejPrice);
    goods.set("rejMoney", rejCount * rejPrice);
    rejPriceWithTax = rejPrice * (1 + taxRate);
    goods.set("rejPriceWithTax", rejPriceWithTax);
    goods.set("rejMoneyWithTax", rejCount * rejPriceWithTax);
  },

  // 因为含税单价变化
  calcMoney2: function (goods) {
    if (!goods) {
      return;
    }

    var rejCount = goods.get("rejCount");
    var rejPriceWithTax = goods.get("rejPriceWithTax");
    var taxRate = goods.get("taxRate") / 100;

    goods.set("goodsPrice", rejPrice);
    goods.set("rejMoneyWithTax", rejCount * rejPriceWithTax);
    rejPrice = rejPriceWithTax / (1 + taxRate);
    goods.set("rejPrice", rejPrice);
    goods.set("rejMoney", rejCount * rejPrice);
  },

  // 因不含税金额变化
  calcPrice: function (goods) {
    if (!goods) {
      return;
    }
    var rejCount = goods.get("rejCount");
    var rejMoney = goods.get("rejMoney");
    var taxRate = goods.get("taxRate") / 100;
    var rejMoneyWithTax = rejMoney * (1 + taxRate);
    goods.set("rejMoneyWithTax", rejMoneyWithTax);
    if (rejCount && rejCount != 0) {
      goods.set("rejPrice", rejMoney / rejCount);
      goods.set("rejPriceWithTax", rejMoneyWithTax / rejCount);
    }
  },

  // 因含税金额变化
  calcPrice2: function (goods) {
    if (!goods) {
      return;
    }
    var rejCount = goods.get("rejCount");
    var rejMoneyWithTax = goods.get("rejMoneyWithTax");
    var taxRate = goods.get("taxRate") / 100;

    var rejMoney = rejMoneyWithTax / (1 + taxRate);
    goods.set("rejMoney", rejMoney);
    if (rejCount && rejCount != 0) {
      goods.set("rejPrice", rejMoney / rejCount);
      goods.set("rejPriceWithTax", rejMoneyWithTax / rejCount);
    }
  },
  getSaveData: function () {
    var me = this;

    var result = {
      id: me.editHiddenId.getValue(),
      bizDT: PCL.Date.format(me.editBizDT.getValue(), "Y-m-d"),
      supplierId: me.editSupplier.getIdValue(),
      warehouseId: me.editWarehouse.getIdValue(),
      bizUserId: me.editBizUser.getIdValue(),
      receivingType: me.editReceivingType.getValue(),
      billMemo: me.editBillMemo.getValue(),
      viewPrice: me.getViewPrice() ? "1" : "0",
      items: []
    };

    var store = me.getGoodsGrid().getStore();
    for (var i = 0; i < store.getCount(); i++) {
      var item = store.getAt(i);
      result.items.push({
        id: item.get("id"),
        goodsId: item.get("goodsId"),
        goodsCount: item.get("goodsCount"),
        goodsPrice: item.get("goodsPrice"),
        rejCount: item.get("rejCount"),
        rejPrice: item.get("rejPrice"),
        rejMoney: item.get("rejMoney"),
        memo: item.get("memo"),
        taxRate: item.get("taxRate"),
        rejPriceWithTax: item.get("rejPriceWithTax"),
        rejMoneyWithTax: item.get("rejMoneyWithTax"),
        goodsPriceWithTax: item.get("goodsPriceWithTax"),
        goodsMoneyWithTax: item.get("goodsMoneyWithTax")
      });
    }

    return PCL.JSON.encode(result);
  },

  setBillReadonly: function () {
    var me = this;
    me.__readonly = true;
    me.setTitle("<span style='font-size:160%;'>查看采购退货出库单</span>");
    me.buttonSave.setDisabled(true);
    me.buttonCancel.setText("关闭");
    me.editBizDT.setReadOnly(true);
    me.editSupplier.setReadOnly(true);
    me.editWarehouse.setReadOnly(true);
    me.editBizUser.setReadOnly(true);
    me.editReceivingType.setReadOnly(true);
    //me.editExpand.setReadOnly(true);
    me.editBillMemo.setReadOnly(true);
    me.columnActionDelete.hide();
    me.columnActionAdd.hide();
    me.columnActionAppend.hide();
    me.displayFieldBarcode.setDisabled(true);
    me.editBarcode.setDisabled(true);
  },

  onEditBarcodeKeydown: function (field, e) {
    if (e.getKey() == e.ENTER) {
      var me = this;

      var el = PCL.getBody();
      el.mask("查询中...");
      var r = {
        url: me.URL("Home/Goods/queryGoodsInfoByBarcodeForPW"),
        params: {
          barcode: field.getValue()
        },
        callback: function (options, success, response) {
          el.unmask();

          if (success) {
            var data = me.decodeJSON(response.responseText);
            if (data.success) {
              var goods = {
                goodsId: data.id,
                goodsCode: data.code,
                goodsName: data.name,
                goodsSpec: data.spec,
                unitName: data.unitName,
                goodsCount: 1,
                goodsPrice: data.purchasePrice,
                goodsMoney: data.purchasePrice,
                taxRate: data.taxRate
              };
              me.addGoodsByBarCode(goods);
              var edit = me.editBarcode;
              edit.setValue(null);
              edit.focus();
            } else {
              var edit = me.editBarcode;
              edit.setValue(null);
              me.showInfo(data.msg, function () {
                edit.focus();
              });
            }
          } else {
            me.showInfo("网络错误");
          }
        }
      };
      me.ajax(r);
    }
  },

  addGoodsByBarCode: function (goods) {
    if (!goods) {
      return;
    }

    var me = this;
    var store = me.getGoodsGrid().getStore();

    if (store.getCount() == 1) {
      var r = store.getAt(0);
      var id = r.get("goodsId");
      if (id == null || id == "") {
        store.removeAll();
      }
    }

    store.add(goods);
  },

  __supplierIdFunc: function () {
    var me = this;
    return me.editSupplier.getIdValue();
  }
});
