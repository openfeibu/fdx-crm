/**
 * 关于窗体
 * 
 * @author 广州飞步信息科技有限公司
 * @copyright 2015 - present
 * @license GPL v3
 */
PCL.define("PSI.Home.MainForm", {
   extend: "PCL.window.Window",
 
	  config: {
		loginUserId: null,
		loginUserName: null,
		loginUserFullName: null
	  },

	  border: 0,
	  layout: "border",
	 

 /**
   * 构建URL的助手函数
   */
  URL(url) {
	  
    return PSI.Const.BASE_URL + url;
  },
  /**
   * @override
   */
  initComponent() {
    const me = this;

    const year = new Date().getFullYear();
    const c = `Copyright &copy; 2015-${year} 广州飞步信息科技有限公司, All Rights Reserved`;
	  PCL.Ajax.request({
      url: me.URL("Home/MainMenu/mainMenuItems"),
      method: "POST",
      callback(opt, success, response) {
        if (success) {
          const data = PCL.JSON.decode(response.responseText);
          
          console.log(data)
		  var html ='';
			data.forEach(function(v,k){
				html+= `<div class="home-nav" click='menuItemClick'>
							<div class="home-nav-t">`+v.caption+`</div>`;
				if(v.children.length != 0){
					html+=`<ul>`;
					v.children.forEach((v3) => {
						html+=`<li fid=`+v3.fid+` click='menuItemClick'><i class="`+v3.icon+`"></i>`;
						html+= v3.caption;
						html+=`</li>`
						
					  });
					
					
					html+=`</ul>`
				}
				html+=`</div>`
				
			})
			$(".x-panel-body ").html(html)
		  
        }

       
      },
      scope: me
    });
    
    },
   /**
   * 创建主菜单
   * 
   * @private 
   */
  createMainMenu(root) {
    const me = this;

    const menuItemClick = (item) => {
      const fid = item.fid;

      if (fid == "-9995") {
        me._vp.focus();
        window.open(me.URL("Home/Help/index"));
      } else if (fid === "-9999") {
        // 重新登录
        PSI.MsgBox.confirm(`请确认是否重新登录${PSI.Const.PROD_NAME} ?`, function () {
          location.replace(me.URL("Home/MainMenu/navigateTo/fid/-9999"));
        });
      } else {
        me._vp.focus();

        const url = me.URL(`Home/MainMenu/navigateTo/fid/${fid}`);
        if (PSI.Const.MOT == "0") {
          location.href = url;
        } else {
          window.open(url);
        }
      }
    };

    const mainMenu = [];

    const getIconCls = (fid) => {
      const isCodeTable = fid.substring(0, 2) == "ct";
      if (isCodeTable) {
        return "PSI-fid_todo";
      }

      // TODO 还需要处理LCAP其他模块
     
      return `PSI-fid${fid}`;
    };

    root.forEach((m1) => {
      const menuItem = PCL.create("PCL.menu.Menu", { plain: true, bodyCls: "PSI-App-MainMenu fb-bg" });
      m1.children.forEach((m2) => {
        if (m2.children.length === 0) {
          // 只有二级菜单
          if (m2.fid) {
            menuItem.add({
              text: m2.caption,
              fid: m2.fid,
              handler: menuItemClick,
              iconCls: m2.icon
            });
          }
        } else {
          const menuItem2 = PCL.create("PCL.menu.Menu", { plain: true, bodyCls: "PSI-App-MainMenu fb-bg" });

          menuItem.add({
            text: m2.caption,
            menu: menuItem2,
			iconCls: m2.icon
          });

          // 三级菜单
          m2.children.forEach((m3) => {
            menuItem2.add({
              text: m3.caption,
              fid: m3.fid,
              handler: menuItemClick,
              iconCls: m3.icon
            });
          });
        }
      });

      if (m1.children.length > 0) {
        mainMenu.push({
          text: m1.caption,
          menu: menuItem
        });
      }
    });

   

  }

});
