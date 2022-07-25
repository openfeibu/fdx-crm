Ext.define('Ext.Home.MainForm.treesLeftStore', {
    extend:"Ext.data.TreeStore",
    alias: 'store.treesLeftStore',
 
    proxy: {
        type: 'ajax',
        url: 'app/data/data.json'
    },
    root: {
        expanded: true
    },
    autoLoad: true
});