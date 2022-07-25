/**
 * confirm窗体
 * 
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
PCL.define("PSI.AFX.MessageBox.ConfirmContentForm", {
  extend: "PCL.window.Window",
  config: {
    fn: null,
    msg: "",
  },

  modal: true,
  closable: false,
  width: 600,
  layout: "fit",

  /**
   * @override
   */
  initComponent() {
    const me = this;

    PCL.apply(me, {
      header: {
        title: `<span style='font-size:160%'>${PSI.Const.PROD_NAME}</span>`,
        height: 50
      },
      height: 220,
      width: 600,
      layout: {
        type: "table",
        columns: 1,
        tableAttrs: PSI.Const.TABLE_LAYOUT_SMALL,
      },
      items: [{
        border: 0,
        xtype: "container",
        margin: "0 0 0 10",
        html: `
              <h2 style='color:#843fa1;padding-left:5px;padding-right:10px'>${me.getMsg()}</h2>
              `
      },{
        border: 0,
        id: "confirmContent",
        xtype: "textfield",
        fieldLabel: "理由",
        margin: "0 0 0 10",
        width: 400,
      }],
      buttons: [{
        id: "PSI_AFX_MessageBox_ConfirmForm_buttonOK",
        text: "确认",
        handler: me._onOK,
        scope: me
        
      }, {
        id: "PSI_AFX_MessageBox_ConfirmForm_buttonCancel",
        text: "取消",
        handler: me._onCancel,
        scope: me,
      }],
      listeners: {
        show: {
          fn: me._onWndShow,
          scope: me
        }
      }
    });

    me.callParent(arguments);
  },

  /**
   * @private
   */
  _onWndShow() {
    PCL.getCmp("PSI_AFX_MessageBox_ConfirmForm_buttonCancel").focus();
  },

  /**
   * @private
   */
  _onOK() {
    const me = this;

    me.close();

    const fn = me.getFn();
    if (fn) {
      fn();
    }
  },

  /**
   * @private
   */
  _onCancel() {
    const me = this;
    me.close();
  }
});
