<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
<html>

<head>
  <title><?php echo ($title); ?> - <?php echo ($productionName); ?></title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="<?php echo ($uri); ?>Public/Images/favicon.ico" rel="shortcut icon" type="image/x-icon" />

  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/UX/JSPatch.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>

  <script src="<?php echo ($uri); ?>Public/PCL/pcl.js" type="text/javascript"></script>
  <script src="<?php echo ($uri); ?>Public/PCL/ext-lang-zh_CN.js" type="text/javascript"></script>
  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/MsgBox.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/Const.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>

  <link href="<?php echo ($uri); ?>Public/PCL/resources/pcl.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo ($uri); ?>Public/Content/Site.css?dt=<?php echo ($dtFlag); ?>" rel="stylesheet" type="text/css" />
  <link href="<?php echo ($uri); ?>Public/Content/icons.css?dt=<?php echo ($dtFlag); ?>" rel="stylesheet" type="text/css" />

  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/App.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/AFX/BaseMainExForm.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/AFX/BaseMainForm.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/AFX/BaseDialogForm.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>

  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/UX/PickerOverride.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/UX/NumberOverride.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>

  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/MainMenu/ShortcutField.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>

  
  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/AFX/Mix/Common.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/AFX/Form/MainForm.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/AFX/Form/EditForm.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/AFX/MessageBox/ShowInfoForm.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
  <script src="<?php echo ($uri); ?>Public/Scripts/PSI/AFX/MessageBox/ConfirmForm.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>

</head>

<body style="background-image:url(<?php echo ($uri); ?>Public/Images/background.png)">
  <script>
    PSI.Const.BASE_URL = "<?php echo ($uri); ?>";

    PCL.tip.QuickTipManager.init();

    PSI.Const.MOT = "<?php echo ($mot); ?>";
    PSI.Const.GC_DEC_NUMBER = parseInt("<?php echo ($goodsCountDecNumber); ?>");
    PSI.Const.ENABLE_LODOP = "<?php echo ($enableLodop); ?>";
    PSI.Const.PROD_NAME = "<?php echo ($productionName); ?>";
  </script>

  


<script src="<?php echo ($uri); ?>Public/Scripts/PSI/WSP/WSPMainForm.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
<script src="<?php echo ($uri); ?>Public/Scripts/PSI/WSP/WSPEditForm.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>

<script src="<?php echo ($uri); ?>Public/Scripts/PSI/Warehouse/WarehouseField.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
<script src="<?php echo ($uri); ?>Public/Scripts/PSI/User/UserField.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
<script src="<?php echo ($uri); ?>Public/Scripts/PSI/Goods/GoodsFieldForBOM.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>
<script src="<?php echo ($uri); ?>Public/Scripts/PSI/Goods/GoodsField.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>

<script src="<?php echo ($uri); ?>Public/Scripts/PSI/UX/CellEditing.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script>

<?php if($enableLodop == 1): ?><script src="<?php echo ($uri); ?>Public/Lodop/LodopFuncs.js?dt=<?php echo ($dtFlag); ?>" type="text/javascript"></script><?php endif; ?>

<script>
  PCL.onReady(() => {
    const app = PCL.create("PSI.App", {
      userName: "<?php echo ($loginUserName); ?>",
      productionName: "<?php echo ($productionName); ?>",
      appHeaderInfo: {
        title: "<?php echo ($title); ?>"
      }
    });

    const permission = {
      add: "<?php echo ($pAdd); ?>",
      edit: "<?php echo ($pEdit); ?>",
      del: "<?php echo ($pDelete); ?>",
      commit: "<?php echo ($pCommit); ?>",
      genPDF: "<?php echo ($pGenPDF); ?>",
      print: "<?php echo ($pPrint); ?>"
    };

    app.add(PCL.create("PSI.WSP.WSPMainForm", {
      permission
    }));
  });
</script>

</body>

</html>