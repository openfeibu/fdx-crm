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
    /**
     * 防抖函数（常用于input框搜索情况）
     * @param {*} func
     * @param {*} delay
     * @param {*} immediate
     * @returns
     */
    debounce_f(func, delay, immediate = true) {
      let timer = null
      return function(args) {
        let _this = this
        if (timer) {
          clearTimeout(timer)
        }
        if (immediate) {
          let now = !timer
          timer = setTimeout(() => {
            timer = null
          }, delay)
          now && func.call(_this, args)
        } else {
          timer = setTimeout(() => {
            timer = null
            func.call(_this, args)
          }, delay)
        }
      }
    },


    /**
     * 节流函数（常用于onresize, onmouseover情况）
     * @param {*} func
     * @param {*} delay
     * @param {*} immediate
     * @returns
     */
    throttle(func, delay, immediate = true) {
      let timer = null
      return function (args) {
        let _this = this
        if (!timer) {
          if (immediate) {
            func.call(_this, args)
            timer = setTimeout(() => {
              timer = null
            }, delay)
          } else {
            timer = setTimeout(() => {
              func.call(_this, args)
              timer = null
            }, delay)
          }
        }
      }
    },
  }
});
