/**
 * 信息提示框
 * 
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
PCL.define("PSI.CustomerCommon", {
  statics: {
    recordStatusHtml(recordStatus, value) {
      if (parseInt(recordStatus) == 1000) {
        return value;
      } else {
        return `<span class="PSI-record-disabled">${value}</span>`;
      }
    },

  }
});
