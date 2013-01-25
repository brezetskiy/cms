<?php if(AJAX_LOADER == 0): ?>
	<HTML>
	<head>
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; CHARSET=Windows-1251">
		<title>Внешний ключ</title>
		<LINK href="/design/cms/css/toc.css" type="text/css" rel="stylesheet">
		<script language="JavaScript" src="/js/shared/global.js"></script>
		<script language="JavaScript" type="text/javascript" src="/js/shared/jshttprequest.js"></script>
		<script language="JavaScript">
		var expanded = new Array();
		function expand(id, table_id, current_table_id, open_id, field_name) {
			if (expanded[current_table_id+'_'+id] == undefined) {
				expanded[current_table_id+'_'+id] = 1;
			} else if (expanded[current_table_id+'_'+id] == 1) {
				expanded[current_table_id+'_'+id] = 0;
				document.getElementById('div_'+current_table_id+'_'+id).style.display='none';
				document.getElementById('img_'+current_table_id+'_'+id).src='/design/cms/img/js/toc/c.gif';
				return;
			} else if (expanded[current_table_id+'_'+id] == 0) {
				expanded[current_table_id+'_'+id] = 1;
				document.getElementById('div_'+current_table_id+'_'+id).style.display='block';
				document.getElementById('img_'+current_table_id+'_'+id).src='/design/cms/img/js/toc/o.gif';
				return;
			} else if (expanded[current_table_id+'_'+id] == -1) {
				return;
			}
			document.getElementById('div_'+current_table_id+'_'+id).style.display='block';
			document.getElementById('div_'+current_table_id+'_'+id).innerHTML = '<span style="color:gray;">Подождите, идет загрузка...</span>';
			var req = new JsHttpRequest();
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					document.getElementById('div_'+current_table_id+'_'+id).innerHTML = req.responseText;
					if (req.responseJS && req.responseJS.id) {
						expand(req.responseJS.id, req.responseJS.table_id, req.responseJS.current_table_id, req.responseJS.open_id, req.responseJS.field_name);
					}
					if (req.responseText.length == 0) {
						document.getElementById('img_'+current_table_id+'_'+id).src='/design/cms/img/js/toc/n.gif';
						document.getElementById('div_'+current_table_id+'_'+id).style.display='none';
						expanded[current_table_id+'_'+id] = -1;
					} else {
						document.getElementById('img_'+current_table_id+'_'+id).src='/design/cms/img/js/toc/o.gif';
					}
				}
			}
			req.caching = true;
			req.open('POST', '/tools/cms/admin/ext_select.php', true);
			req.send({ 'open_id':open_id, 'field_name':field_name, 'id':id, 'table_id':table_id, 'table_refferer':current_table_id });
		}
		// Используется для раскрытия дерева на первой странице
		function init() {
			<?php echo $this->vars['expand_list']; ?>
		}
		// Установка значения
		function setLink(id, name) {
			window.opener.document.getElementById('<?php echo FIELD_NAME; ?>').value = id;
			window.opener.document.getElementById('<?php echo FIELD_NAME; ?>_text').value = name;
			window.close();
		}
		// Удаление значения
		function delLink() {
			window.opener.document.getElementById('<?php echo FIELD_NAME; ?>').value = 0;
			window.opener.document.getElementById('<?php echo FIELD_NAME; ?>_text').value = "";
			window.close();
		}
		</script>
	</head>
	<body onload="init();" on1selectstart="javascript:return false;" onKeyPress="EnterEsc(event);" onCo1ntextMenu="return false;" >
	<a onclick="delLink();" href="javascript:void(0);">Корень сервера / удалить ссылку</a>
	<br>
<?php endif; ?>
<?php
			reset($this->vars['/node/'][$__key]);
			while(list($_node_key,) = each($this->vars['/node/'][$__key])):
			?>
	<?php if($this->vars['/node/'][$__key][$_node_key]['childs'] == 1): ?>
		<a hidefocus id="a_<?php echo CURRENT_TABLE_ID; ?>_<?php echo $this->vars['/node/'][$__key][$_node_key]['id']; ?>" onclick="expand('<?php echo $this->vars['/node/'][$__key][$_node_key]['id']; ?>', '<?php echo TABLE_ID; ?>', '<?php echo CURRENT_TABLE_ID; ?>', '<?php echo OPEN_ID; ?>', '<?php echo FIELD_NAME; ?>' );" href="javascript:void(0);"><img align="absmiddle" src="/design/cms/img/js/toc/c.gif" id="img_<?php echo CURRENT_TABLE_ID; ?>_<?php echo $this->vars['/node/'][$__key][$_node_key]['id']; ?>" <?php echo $this->vars['/node/'][$__key][$_node_key]['class']; ?>>
	<?php else: ?>
		<img align="absmiddle" src="/design/cms/img/js/toc/n.gif">
	<?php endif; ?>
	<?php if(CURRENT_TABLE_ID == TABLE_ID): ?>
		</a><a onclick="setLink('<?php echo $this->vars['/node/'][$__key][$_node_key]['id']; ?>', '<?php echo $this->vars['/node/'][$__key][$_node_key]['name_filtered']; ?>');" href="javascript:void(0);" <?php echo $this->vars['/node/'][$__key][$_node_key]['class']; ?>><?php echo $this->vars['/node/'][$__key][$_node_key]['name']; ?></a>
	<?php else: ?>
		<?php echo $this->vars['/node/'][$__key][$_node_key]['name']; ?></a>
	<?php endif; ?>
	<br>
	<div id="div_<?php echo CURRENT_TABLE_ID; ?>_<?php echo $this->vars['/node/'][$__key][$_node_key]['id']; ?>" style="padding-left:20px;display:none;"></div>
<?php 
			endwhile;
			?>
<?php if(AJAX_LOADER == 0): ?>
	</body>
	</HTML>
<?php endif; ?>