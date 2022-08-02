/**
 * 客户资料 - 新增或编辑界面
 * 
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
PCL.define("PSI.Customer.CustomerMergeForm", {
  extend: "PSI.AFX.Form.EditForm",

  initComponent: function () {
    var me = this;
    var buttons = [];
    var entity = me.getEntity();
    buttons.push({
      text: "提交",
      formBind: true,
      //iconCls: "PSI-button-ok",
      handler: function () {
        me.onOK();
      },
      scope: me
    }, {
      text: "取消",
      handler: function () {
        me.close();
      },
      scope: me
    });

    var categoryStore = null;
    if (me.getParentForm()) {
      categoryStore = me.getParentForm().categoryGrid.getStore();
    }

    var t = "合并客户 '"+entity.get("name")+"' 资料，合并后该客户数据清零";
    var logoHtml = me.genLogoHtml(entity, t);

    const width1 = 800;
    const width2 = 395;
    PCL.apply(me, {
      header: {
        title: me.formatTitle(PSI.Const.PROD_NAME),
        height: 40
      },
      width: 850,
      height: 320,
      layout: "border",
      items: [{
        region: "north",
        border: 0,
        height: 70,
        html: logoHtml
      }, {
        region: "center",
        border: 0,
        id: "PSI_Customer_CustomerMergeForm_mergeForm",
        xtype: "form",
        layout: {
          type: "table",
          columns: 2,
          tableAttrs: PSI.Const.TABLE_LAYOUT_SMALL,
        },
        height: "100%",
        bodyPadding: 5,
        defaultType: 'textfield',
        fieldDefaults: {
          labelWidth: 100,
          labelAlign: "right",
          labelSeparator: "",
          msgTarget: 'side'
        },
        items: [{
          xtype: "hidden",
          name: "id",
          value: entity == null ? null : entity
            .get("id")
        }, {
          id: "toCustomer",
          xtype: "psi_customerfield",
          fieldLabel: "数据迁移至该客户",
          showAddButton: true,
          allowBlank: false,
          labelWidth: 60,
          labelAlign: "right",
          labelSeparator: "",
          colspan: 2,
          width: 430,
          blankText: "没有输入客户",
          beforeLabelTextTpl: PSI.Const.REQUIRED,
          listeners: {
            specialkey: {
              fn: me.onEditSpecialKey,
              scope: me
            }
          },
          callbackFunc: me.__setCustomerExtData
        }, {
          id: "toCustomerId",
          xtype: "hiddenfield",
          name: "toId"
        }],
        buttons: buttons
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

    me.editForm = PCL.getCmp("PSI_Customer_CustomerMergeForm_mergeForm");
    me.toCustomer = PCL.getCmp("toCustomer");
    me.toCustomerId = PCL.getCmp("toCustomerId");

  },

  onWindowBeforeUnload: function (e) {
    return (window.event.returnValue = e.returnValue = '确认离开当前页面？');
  },

  onWndShow: function () {
    var me = this;

    PCL.get(window).on('beforeunload', me.onWindowBeforeUnload);

  },

  onWndClose: function () {
    var me = this;

    PCL.get(window).un('beforeunload', me.onWindowBeforeUnload);

    if (me.__lastId) {
      if (me.getParentForm()) {
        me.getParentForm().freshCustomerGrid(me.__lastId);
      }
    }
  },

  onOK: function () {
    var me = this;
    var entity = me.getEntity();


    if(entity.get("id") == me.toCustomer.getIdValue())
    {
      PSI.MsgBox.showInfo("合并客户不能选择同一个");
      return;
    }
    me.toCustomerId.setValue(me.toCustomer.getIdValue());
    var f = me.editForm;
    var el = f.getEl();
    el.mask(PSI.Const.SAVING);
    f.submit({
      url: me.URL("Home/Customer/mergeCustomer"),
      method: "POST",
      success: function (form, action) {
        el.unmask();
        me.__lastId = action.result.id;
        PSI.MsgBox.tip("数据合并成功");
        me.focus();

        me.close();

      },
      failure: function (form, action) {
        el.unmask();
        PSI.MsgBox.showInfo(action.result.msg, function () {
          me.editCode.focus();
        });
      }
    });
  },
  onEditSpecialKey: function (field, e) {

  },
// xtype:psi_customerfield回调本方法
  // 参见PSI.Customer.CustomerField的onOK方法
  __setCustomerExtData: function (data) {

  }

});
