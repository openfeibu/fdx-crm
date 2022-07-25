Ext.define('Ext.Home.MainForm.region.treesLeft', {
    extend: 'Ext.tree.Panel',
    xtype:'treesLeft',
 
    title:'导航菜单',
 
    lines: false,
    rootVisible: false,
 
    store:Ext.create('Ext.Home.MainForm.treesLeftStore', {
        storeId: "treesLeftStoreId"
    }),
});