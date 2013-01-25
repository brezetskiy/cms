
<link rel="stylesheet" type="text/css" href="/css/user/oid/box.css" />
<script type="text/javascript" src="/js/user/oid/box.js"></script> 

<?php if(!empty($_SESSION['oid_widget_active']) && $_SESSION['oid_widget_active'] == 'global_'.$this->vars['action'].'_form' && (!empty($_SESSION['oid_clarify_auto']) || !empty($_SESSION['oid_clarify_manual']))): ?>
	<script> oid_widget__box__start('global_<?php echo $this->vars['action']; ?>_form', 'box', '<?php echo $_SESSION['oid_widget']['return_path']; ?>', '<?php echo $this->vars['action']; ?>'); </script>
<?php endif; ?>	  
   
<a href="javascript:void(0);" onclick="oid_widget__box__start('global_<?php echo $this->vars['action']; ?>_form', 'box', '<?php echo CURRENT_URL_FORM; ?>', '<?php echo $this->vars['action']; ?>');" title="Войдите, как пользователь Google, ВКонтакте, Facebook, Yandex и т.д."><img src="/img/user/oid/google.png" border="0" align="absmiddle"></a>
<div id="oid_widget__box__content"></div> 


