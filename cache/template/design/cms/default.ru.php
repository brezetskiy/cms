<html>
<head>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
	<meta http-equiv="imagetoolbar" content="no"> 
	<TITLE><?php echo $this->vars['title']; ?></TITLE>
	<base href="<?php echo HTTP_SCHEME; ?>://<?php echo CMS_HOST; ?><?php echo $this->global_vars['base_url']; ?>">
   	<link rel="stylesheet" href="/design/cms/css/admin.css" type="text/css">
   	<link rel="stylesheet" href="/design/cms/css/cms_view.css" type="text/css">
   	<link rel="stylesheet" href="/design/cms/css/scw.css" type="text/css">
   	<link rel="stylesheet" href="/design/cms/css/cms_gallery.css" type="text/css">
   	<link rel="stylesheet" href="/css/gallery/coolgallery.css" type="text/css">
   	<link rel="stylesheet" href="/design/cms/css/jquery.jgrowl.css" type="text/css">
   	<!--<link rel="stylesheet" href="/extras/jquery/lightbox/jquery.lightbox.css" type="text/css">-->
   	
 	<!--<link rel="stylesheet" href="/extras/jquery/css/chosen/style.css" />-->
 	
   	<?php
			reset($this->vars['/css/'][$__key]);
			while(list($_css_key,) = each($this->vars['/css/'][$__key])):
			?>
   		<link rel="stylesheet" href="<?php echo $this->vars['/css/'][$__key][$_css_key]['url']; ?>" type="text/css">
   	<?php 
			endwhile;
			?>
   	
   	<script language="JavaScript" type="text/javascript" src="/design/cms/js/cms.js"></script>
	<script language="JavaScript" type="text/javascript" src="/design/cms/js/ua.js"></script>
	<script language="JavaScript" type="text/javascript" src="/design/cms/js/ftiens4.js"></script>
	<script language="JavaScript" type="text/javascript" src="/design/cms/js/filter.js"></script>
	
   	
	<script language="JavaScript" type="text/javascript" src="/js/shared/jshttprequest.js"></script>
	<script language="JavaScript" type="text/javascript" src="/js/shared/scw.js"></script>
	
	<!--<script language="JavaScript" type="text/javascript" src="/extras/fusioncharts/chart.js"></script> -->
	<script language="JavaScript" type="text/javascript" src="/extras/jquery/jquery.min.js"></script>
	<!--<script language="JavaScript" type="text/javascript" src="/extras/jquery/lightbox/jquery.lightbox.js"></script>-->
	<script language="JavaScript" type="text/javascript" src="/extras/jquery/jqModal.js"></script>
	<script language="JavaScript" type="text/javascript" src="/extras/jquery/jquery.jgrowl.js"></script>
	<script language="JavaScript" type="text/javascript" src="/extras/jquery/jquery.tablednd.js"></script>
	<!--<script language="JavaScript" type="text/javascript" src="/extras/jquery/effects.core.js"></script>-->
	<!--<script language="JavaScript" type="text/javascript" src="/extras/jquery/effects.slide.js"></script>-->
	<script language="JavaScript" type="text/javascript" src="/extras/jquery/jquery.idtabs.min.js"></script>
	<script language="JavaScript" type="text/javascript" src="/extras/jquery/jquery.chosen.min.js"></script>
	
	<script language="JavaScript" type="text/javascript" src="/js/shared/global.js"></script>
	<script type="text/javascript" src="/extras/ckeditor/ckeditor.js"></script>
	
	<!-- DELTA СООБЩЕНИЯ -->
	<?php if(CMS_USE_DELTA_MESSAGE): ?> 
		<link rel="stylesheet" type="text/css" href="/css/cms/message.css" />
		<script type="text/javascript" src="/js/cms/message.js"></script>
		<script type="text/javascript" src="/extras/jquery/jquery.blockUI.js"></script>
	<?php endif; ?>
	
	<script language="JavaScript" type="text/javascript">
		<?php
			reset($this->vars['/onload_var/'][$__key]);
			while(list($_onload_var_key,) = each($this->vars['/onload_var/'][$__key])):
			?>
			<?php echo $this->vars['/onload_var/'][$__key][$_onload_var_key]['function']; ?>
		<?php 
			endwhile;
			?>
		
		$(function() {
			Hotkey.Init();
			FormFocus();
			
			for(var i = 0; i < document.links.length; i++){
				document.links.hidefocus = true;
			}
			
			// используется для открытия нужных разделов в полях типа ext_multiple
			<?php
			reset($this->vars['/onload/'][$__key]);
			while(list($_onload_key,) = each($this->vars['/onload/'][$__key])):
			?>
				<?php echo $this->vars['/onload/'][$__key][$_onload_key]['function']; ?> 
			<?php 
			endwhile;
			?>
			
                        //var
                        var start_tr = 0;
			$('.cms_view').tableDnD({
                            'dragHandle':'move', 
                            'onDrop': function (table, row) { 
                                if ( $(table).find('tbody tr').index($(row)) != start_tr)
                                    AjaxRequest.send($(table).parents('form.cms_view_form:first').attr('id'), '/action/admin/cms/table_sort/', 'Сохранение', true, {})
                            }, 
                            'onDragStart': function(table, row) {start_tr = $(table).find('tbody tr').index($(row));}   
                        });
			cmsView.init();
		  	
			$(".chzn-select").chosen({allow_single_deselect:true});   
		});
	</script>
	
	
</head>
<body> 

	<div class="web_link">
		<a href="<?php echo HTTP_SCHEME; ?>://<?php echo CMS_HOST; ?>" title="Вернуться на главную страницу сайта" style="font-size:11px;">
			Вернуться на сайт &nbsp; 
			<img src="/design/cms/img/web.png" border="0" align="absmiddle" alt="На главную">
		</a>
	</div>

	<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%" class="main">
		<TBODY>
			<tr>
				<td class="menu">
					<img src="/img/shared/1x1.gif" width="205" height="1" alt="" border=0><br>
					<a href="http://www.treem"></a>
					<script language="JavaScript" type="text/javascript">
						USETEXTLINKS = 1
						STARTALLOPEN = 0
						USEFRAMES = 0
						USEICONS = 0
						WRAPTEXT = 0
						PERSERVESTATE = 1
						BUILDALL = 0
						ICONPATH = "/design/cms/img/js/tree/"
						
						foldersTree = gFld("<B><?php echo $_SESSION['auth']['login']; ?></B> [<a href=\"/<?php echo LANGUAGE_URL; ?>action/cms/logout/?_return_path=/\">Выход</a>]");
						foldersTree.xID = "100000000"
						Tree0 = foldersTree;
						foldersTree.xID = "0"
						
						<?php
			reset($this->vars['/menu_item/'][$__key]);
			while(list($_menu_item_key,) = each($this->vars['/menu_item/'][$__key])):
			?>
							Tree<?php echo $this->vars['/menu_item/'][$__key][$_menu_item_key]['id']; ?> = insFld(Tree<?php echo $this->vars['/menu_item/'][$__key][$_menu_item_key]['parent']; ?>, gFld("<?php echo $this->vars['/menu_item/'][$__key][$_menu_item_key]['name']; ?>", "<?php echo $this->vars['/menu_item/'][$__key][$_menu_item_key]['url']; ?>"));Tree<?php echo $this->vars['/menu_item/'][$__key][$_menu_item_key]['id']; ?>.xID = <?php echo $this->vars['/menu_item/'][$__key][$_menu_item_key]['id']; ?>;
						<?php 
			endwhile;
			?>
					</script>
					
					<span class=TreeviewSpanArea>
						<script language="JavaScript" type="text/javascript">initializeDocument();</script>
					</span>
					
					<noscript>
						Для просмотра сайта Вам необходимо иметь включенную поддержку JavaScript
					</noscript>
				</td>
				
				<td class="content">
					<?php
			reset($this->vars['/error/'][$__key]);
			while(list($_error_key,) = each($this->vars['/error/'][$__key])):
			?><div class="delta_error"><?php echo $this->vars['/error/'][$__key][$_error_key]['message']; ?></div><?php 
			endwhile;
			?>
					<?php
			reset($this->vars['/success/'][$__key]);
			while(list($_success_key,) = each($this->vars['/success/'][$__key])):
			?><div class="delta_success"><?php echo $this->vars['/success/'][$__key][$_success_key]['message']; ?></div><?php 
			endwhile;
			?>
					<?php
			reset($this->vars['/info/'][$__key]);
			while(list($_info_key,) = each($this->vars['/info/'][$__key])):
			?><div class="delta_info"><?php echo $this->vars['/info/'][$__key][$_info_key]['message']; ?></div><?php 
			endwhile;
			?>
					<?php
			reset($this->vars['/warning/'][$__key]);
			while(list($_warning_key,) = each($this->vars['/warning/'][$__key])):
			?><div class="delta_warning"><?php echo $this->vars['/warning/'][$__key][$_warning_key]['message']; ?></div><?php 
			endwhile;
			?>
					
					<?php
			reset($this->vars['/privileg/'][$__key]);
			while(list($_privileg_key,) = each($this->vars['/privileg/'][$__key])):
			?><div class="cms_privileg"><?php echo $this->vars['/privileg/'][$__key][$_privileg_key]['message']; ?></div><?php 
			endwhile;
			?>
					
					<?php echo $this->vars['content']; ?>
					
					<?php if($this->vars['show_responce_log']): ?>
						<p>
							<div class="context_help">
								<b>Системой выполнены действия:</b><br>
								
								<?php
			reset($this->vars['/responce_log/'][$__key]);
			while(list($_responce_log_key,) = each($this->vars['/responce_log/'][$__key])):
			?>
									<li><?php echo $this->vars['/responce_log/'][$__key][$_responce_log_key]['text']; ?><br>
								<?php 
			endwhile;
			?>
							</div>
						</p>
					<?php endif; ?>
					
				</td>
			</tr>
			<tr>
				<td class="menu" style="background-image:none;vertical-align:bottom;">
					<a target="_blank" href="http://www.delta-x.com.ua/"><img src="/design/cms/img/ui/delta_logo.gif" border="0"></a>
				</td>
				<td></td>
			</tr>
		</TBODY>
	</table>

	<div id="ajaxPreloader">Идёт обновление информации, подождите...</div>

	<?php
			reset($this->vars['/cross_domain_auth/'][$__key]);
			while(list($_cross_domain_auth_key,) = each($this->vars['/cross_domain_auth/'][$__key])):
			?>
		<iframe frameborder="0" height="0" width="0" scrolling="no" src="http://<?php echo $this->vars['/cross_domain_auth/'][$__key][$_cross_domain_auth_key]['site']; ?>/tools/auth/cross_domain_auth.php?key=<?php echo $this->vars['/cross_domain_auth/'][$__key][$_cross_domain_auth_key]['key']; ?>&p=<?php echo $this->vars['/cross_domain_auth/'][$__key][$_cross_domain_auth_key]['rnd']; ?>"></iframe>
	<?php 
			endwhile;
			?>
	
</body>
</html>