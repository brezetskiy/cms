Или войдите, как пользователь &nbsp;

<link rel="stylesheet" type="text/css" href="/css/user/oid/context.css" />
<script type="text/javascript" src="/js/user/oid/context.js"></script> 

 
<div id="oid_widget__<?php echo $this->global_vars['oid_widget_uniq_name']; ?>" class="oid_widget">   

	<!-- Вывод иконок всех провайдеров (OAuth и OpenID) -->
	<?php
			reset($this->vars['/providers/'][$__key]);
			while(list($_providers_key,) = each($this->vars['/providers/'][$__key])):
			?>  
		<?php if($this->vars['/providers/'][$__key][$_providers_key]['openid_enable']): ?>
			<a rel="nofollow" href="javascript:void(0);" onclick="oid_widget__form_openid_open('<?php echo $this->global_vars['oid_widget_uniq_name']; ?>', 'context', <?php echo $this->vars['/providers/'][$__key][$_providers_key]['id']; ?>);"><img src="<?php echo $this->vars['/providers/'][$__key][$_providers_key]['icon']; ?>" title="<?php echo $this->vars['/providers/'][$__key][$_providers_key]['name']; ?>" border="0"/></a>
		<?php else: ?>	
			<a rel="nofollow" href="javascript:void(0);" onclick="oid_widget__context__cancel('<?php echo $this->global_vars['oid_widget_uniq_name']; ?>'); StableWindow('<?php echo $this->vars['/providers/'][$__key][$_providers_key]['dialog_url']; ?>', '<?php echo $this->vars['/providers/'][$__key][$_providers_key]['name']; ?>', 1);"><img src="<?php echo $this->vars['/providers/'][$__key][$_providers_key]['icon']; ?>" title="<?php echo $this->vars['/providers/'][$__key][$_providers_key]['name']; ?>" border="0"/></a>
		<?php endif; ?>
    <?php 
			endwhile;
			?>     
</div>	

