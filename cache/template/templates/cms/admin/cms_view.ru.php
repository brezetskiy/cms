<script>

$(document.body).click(function(e) {
	var id = e.target.id;
	var is_window_inside = 0;  
	
	jQuery.each($("#filter_<?php echo $this->global_vars['instance_number']; ?>_select").find('*'), function(){
		if(id != '' && id == $(this).attr('id')){ 
			is_window_inside = 1;
		}
	});
	
	if(id == 'filter_<?php echo $this->global_vars['instance_number']; ?>_select_button') is_window_inside = 1;
	if(id == 'filter_<?php echo $this->global_vars['instance_number']; ?>_select_button_image') is_window_inside = 1;
	
	if(is_window_inside == 0) $("#filter_<?php echo $this->global_vars['instance_number']; ?>_select").hide();
});

</script>


<?php if($this->vars['show_title']): ?><H2><?php echo $this->vars['table_title']; ?></H2><?php endif; ?>


<!-- Фильтр -->
<div id="filter_form_<?php echo $this->global_vars['instance_number']; ?>" style="display:<?php echo $this->vars['show_filter']; ?>;">
	<br>
	
	<form method="POST" action="/action/admin/cms/table_filter/" id="filter_<?php echo $this->global_vars['instance_number']; ?>">
		<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
		<input type="hidden" name="_language" value="<?php echo LANGUAGE_CURRENT; ?>">
		<input type="hidden" name="instance_number" value="<?php echo $this->global_vars['instance_number']; ?>">
		<input type="hidden" name="structure_id" value="<?php echo CMS_STRUCTURE_ID; ?>" id="structure_id">
		<input type="hidden" name="table_id" value="<?php echo $this->global_vars['table']['id']; ?>" id="table_id">  
		<input  type="submit" name="update" value="Apply" style="position: absolute; height: 0px; width: 0px; border: none; padding: 0px;" hidefocus="true" tabindex="-1"/>
		
		<table class="filter-form" width="100%" cellpadding="0" cellspacing="0">
			<?php
			reset($this->vars['/filter_table/'][$__key]);
			while(list($_filter_table_key,) = each($this->vars['/filter_table/'][$__key])):
			?>
				<tr id="filter_<?php echo $this->global_vars['instance_number']; ?>_title_<?php echo $this->vars['/filter_table/'][$__key][$_filter_table_key]['id']; ?>" class="filter-table-title" style="<?php if(!$this->vars['/filter_table/'][$__key][$_filter_table_key]['show_as_dominant'] && !$this->vars['/filter_table/'][$__key][$_filter_table_key]['show_as_checked']): ?>display:none;<?php endif; ?>">
					<th colspan="3" ondblclick="document.location.href='/Admin/CMS/DB/Tables/Fields/?table_id=<?php echo $this->vars['/filter_table/'][$__key][$_filter_table_key]['table_id']; ?>'"><?php echo $this->vars['/filter_table/'][$__key][$_filter_table_key]['title']; ?></th>
				</tr>
				
				<?php
			reset($this->vars['/filter_table/filter_field/'][$_filter_table_key]);
			while(list($_filter_table_filter_field_key,) = each($this->vars['/filter_table/filter_field/'][$_filter_table_key])):
			?> 
					<tr id="filter_<?php echo $this->global_vars['instance_number']; ?>_row_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['table_id']; ?>_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" class="filter-row" style="<?php if(!$this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['show_as_dominant'] && !$this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['show_as_checked']): ?>display:none;<?php endif; ?>">
						<td width="30%" class="title <?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['class']; ?>"><span title="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['table_name']; ?>.<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['field_name']; ?>"><?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['title']; ?></span><?php if(!empty($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['field_language'])): ?><img src="/design/cms/img/language/<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['field_language']; ?>.gif" border="0" hspace="5"><?php endif; ?>:</td>
						<td width="65%">
							<input type="hidden" id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_condition" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[condition]" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['condition']; ?>">
							  
							<?php if($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['cms_type'] == 'checkbox_set' || $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['cms_type'] == 'radio' || $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['data_type'] == 'enum'): ?>
								<input type="hidden" id="filter_input_type_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" value="list">
							
								<select style="width:325px;" id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_list" class="tree filter-wide chzn-select" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[0]" onchange="cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>')">
									<option value=""></option>
									<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['options'],'selected'=>$this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['value_1'])); ?>
								</select>
								 
							<?php elseif($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['cms_type'] == 'fk_list' || $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['cms_type'] == 'fk_tree' || $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['cms_type'] == 'fk_cascade' || $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['cms_type'] == 'fk_nn_list' || $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['cms_type'] == 'fk_nn_tree'): ?>
								<input type="hidden" id="filter_input_type_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" value="list">
								
								<select id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_list" class="tree filter-wide chzn-select" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[0]<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['multiple_1']; ?>" onchange="cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>')" <?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['multiple_2']; ?>>
									<option value=""></option>
									<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['options'],'selected'=>$this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['value_1'])); ?>
									<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['tree']; ?>
								</select>
								
							<?php elseif($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['pilot_type']=='date'): ?>
								<input type="hidden" id="filter_input_type_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" value="int">
								
								<input type="text" class="date filter-short" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[0]" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['value_1']; ?>" onclick="scwShow(scwID('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_from'),event);cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');" id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_from">
								<div id="to_input_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>" style="display:<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['display']; ?>;"> - <input type="text" class="date filter-short" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[1]" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['value_2']; ?>" onclick="scwShow(scwID('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_to'),event);cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>', 'between');" id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_to"></div>
								
								<?php if($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['condition'] == 'between'): ?>
									<a id="to_switcher_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>" href="javascript:void(0);" onclick="cmsView.filterBetween('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');" title="Равно"><img src="/design/cms/img/filter/gray_spacer_active.png" align="absmiddle" border="0"></a>
								<?php else: ?>
									<a id="to_switcher_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>" href="javascript:void(0);" onclick="cmsView.filterBetween('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');" title="Внутри интервала"><img src="/design/cms/img/filter/gray_spacer.png" align="absmiddle" border="0"></a>
								<?php endif; ?>
							<?php elseif($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['cms_type']=='ajax_select'): ?>
								<input type="hidden" id="filter_input_type_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" value="ajax_list">
								<input type="hidden" id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[0]" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['value_1']; ?>">
								 
								<input name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['uniq_id']; ?>" type="text" id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_text" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['text_value']; ?>" maxLength="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['max_length']; ?>" size="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['size']; ?>" onblur="cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');AjaxSelect.blur('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');" onfocus="AjaxSelect.focus('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');" onkeydown="AjaxSelect.keyDown('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');" onkeyup="AjaxSelect.keyUp(<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['table_id']; ?>, '<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['field_name']; ?>', '<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');" class="filter-wide">
								<input type="checkbox" disabled id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_fixed" <?php if(!empty($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['text_value'])): ?>checked<?php endif; ?>>
								<div class="ajax_select_hint" id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_hint" onmouseover="AjaxSelect.cancelBlur();" onmouseout="AjaxSelect.restoreBlur();"></div>
								
							<?php elseif($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['cms_type'] == 'checkbox'): ?>
								<input type="hidden" id="filter_input_type_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" value="checkbox">
								
								<input type="hidden" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[dummie]" value="1">
								<input id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_checkbox" type="checkbox" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[0]" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['checkbox_value']; ?>" <?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['checked']; ?> onclick="cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>')">
							
							<?php elseif($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['cms_type']=='fk_ext_cascade' || $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['cms_type'] == 'fk_ext_tree'): ?>
								<input type="hidden" id="filter_input_type_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" value="text">
								
								<input type="text" id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_text" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['text_value']; ?>" size="50" onkeydown="return ignoreKey();" onclick="cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');CenterWindow('/tools/cms/admin/ext_select.php?field_name=<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>&table_id=<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['fk_table_id']; ?>&open_id='+document.getElementById('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>').value, 'tree', 400, 500, 1, 1);" class="filter-wide" style="width:90%;">
								<a href="javascript:void(0);" onclick="cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');CenterWindow('/tools/cms/admin/ext_select.php?field_name=<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>&table_id=<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['fk_table_id']; ?>&open_id='+document.getElementById('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>').value, 'tree', 400, 500, 1, 1);">
									<img src="/design/cms/img/ui/structure_link.png" width="16" height="16" border="0" align="absmiddle" onmouseover="this.style.background='EAF3FB';" onmouseout="this.style.background='';" style="margin: -2px 0px 0px 2px;"">
								</a>
								<input type="hidden" id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[0]" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['value_1']; ?>" size="50"><br> 
								
							<?php elseif($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['cms_type']=='fk_ext_list'): ?>
								<input type="hidden" id="filter_input_type_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" value="text">
								
								<input type="text" id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_text" value="<?php echo TemplateUDF::escape(array('type'=>'htmlspecialchars','text'=>$this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['text_value'])); ?>" size="50" onkeydown="return ignoreKey();" onclick="cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');CenterWindow('/tools/cms/admin/ext_list.php?field_name=<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>&table_id=<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['fk_table_id']; ?>&open_id='+document.getElementById('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>').value, 'tree', 800, 600, 1, 1);" class="filter-wide" style="width:90%;">
								<a href="javascript:void(0);" onclick="cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');CenterWindow('/tools/cms/admin/ext_list.php?field_name=<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>&table_id=<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['fk_table_id']; ?>&open_id='+document.getElementById('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>').value, 'tree', 800, 600, 1, 1);">
									<img src="/design/cms/img/ui/structure_link.png" width="16" height="16" border="0" align="absmiddle" onmouseover="this.style.background='EAF3FB';" onmouseout="this.style.background='';" style="margin: -2px 0px 0px 2px;">
								</a>
								<input type="hidden" id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[0]" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['value_1']; ?>" size="50"><br>
								
							<?php elseif($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['pilot_type']=='int' || $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['pilot_type']=='decimal'): ?>
								<input type="hidden" id="filter_input_type_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" value="int">
								 
								<input type="text" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[0]" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['value_1']; ?>" onkeyup="cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>')" class="filter-short"  id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_from">
								<div id="to_input_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>" style="display:<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['display']; ?>;"> - <input type="text" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[1]" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['value_2']; ?>" onclick="cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>', 'between');" id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_to" class="filter-short"></div>
								
								<?php if($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['condition'] == 'between'): ?>
									<a id="to_switcher_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>" href="javascript:void(0);" onclick="cmsView.filterBetween('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');" title="Равно"><img src="/design/cms/img/filter/gray_spacer_active.png" align="absmiddle" border="0"></a>
								<?php else: ?>
									<a id="to_switcher_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>" href="javascript:void(0);" onclick="cmsView.filterBetween('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>');" title="Внутри интервала"><img src="/design/cms/img/filter/gray_spacer.png" align="absmiddle" border="0"></a>
								<?php endif; ?> 
							<?php else: ?> 
								<input type="hidden" id="filter_input_type_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" value="text">
								<input id="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>_text" type="text" name="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['name']; ?>[0]" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['value_1']; ?>" onkeyup="cmsView.filterActivate('<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['id']; ?>', 'like')" class="filter-wide">
								
							<?php endif; ?> 
						</td>
						<td width="5%" align="center">
							<?php if(!$this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['show_as_dominant']): ?> 
							<a href="javascript:void(0);" onclick="cms_filter_field_hide('<?php echo $this->global_vars['instance_number']; ?>', <?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>);" title="Убрать поле из фильтра">
								<img src="/design/cms/img/filter/box_minus.png" align="absmiddle" border="0" alt="Скрыть" onmouseover="this.src='/design/cms/img/filter/box_minus_active.png'" onmouseout="this.src='/design/cms/img/filter/box_minus.png'">
							</a>
							<?php else: ?>
								&nbsp;
							<?php endif; ?>
						</td>
					</tr>  
				<?php 
			endwhile;
			?>
			<?php 
			endwhile;
			?>  
			
			<tr class="filter-control">
				<td class="filter-control-row" colspan="3">
					<div class="filter-separator-section" >&nbsp;</div>
					
					<?php if(!empty($this->global_vars['fields_select_box_total_exists'])): ?>
						<a href="javascript:void(0);" onclick="cms_filter_fields_show('<?php echo $this->global_vars['instance_number']; ?>');" class="button"><img src="/design/cms/img/filter/green_bottom.png" align="absmiddle" border="0" title="Отобразить все доступные фильтры"></a>
						<a href="javascript:void(0);" onclick="cms_filter_fields_hide('<?php echo $this->global_vars['instance_number']; ?>');" class="button"><img src="/design/cms/img/filter/green_top.png" align="absmiddle" border="0" title="Скрыть все фильтры"></a> 
						
						<div class="filter-separator-row">&nbsp;</div>
							 
						<a href="javascript:void(0);" id="filter_<?php echo $this->global_vars['instance_number']; ?>_select_button" onclick="cms_filter_select_box('<?php echo $this->global_vars['instance_number']; ?>');" class="button" title="Добавить условия поиска">
							&nbsp; <img id="filter_<?php echo $this->global_vars['instance_number']; ?>_select_button_image" src="/design/cms/img/filter/green_plus.png" align="absmiddle" border="0" style="margin-right:3px; ">
							Добавить условие поиска &nbsp; 
						</a>     
						<div id="filter_<?php echo $this->global_vars['instance_number']; ?>_select" class="filter-select-box">
						<?php
			reset($this->vars['/filter_table/'][$__key]);
			while(list($_filter_table_key,) = each($this->vars['/filter_table/'][$__key])):
			?>
							<?php
			reset($this->vars['/filter_table/filter_field/'][$_filter_table_key]);
			while(list($_filter_table_filter_field_key,) = each($this->vars['/filter_table/filter_field/'][$_filter_table_key])):
			?>
								<?php if(!$this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['show_as_dominant']): ?>  
									<?php if(!empty($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['table_title'])): ?>
										<div id="filter_<?php echo $this->global_vars['instance_number']; ?>_select_table_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" class="table"><?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['table_title']; ?></div>
									<?php endif; ?>   
									
									<label id="filter_<?php echo $this->global_vars['instance_number']; ?>_select_label_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" for="filter_<?php echo $this->global_vars['instance_number']; ?>_select_input_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>">
										<div id="filter_<?php echo $this->global_vars['instance_number']; ?>_select_button_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" class="filter-select-list" onclick="cms_filter_field_switch('<?php echo $this->global_vars['instance_number']; ?>');"> 
											<div id="filter_<?php echo $this->global_vars['instance_number']; ?>_select_unit_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" class="checkbox">
												<input id="filter_<?php echo $this->global_vars['instance_number']; ?>_select_input_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" type="checkbox" value="<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['table_id']; ?>_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" <?php if($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['show_as_checked']): ?>checked<?php endif; ?>>
											</div>   
											<div id="filter_<?php echo $this->global_vars['instance_number']; ?>_select_title_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" class="title"><?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['title']; ?></div>
											<div id="filter_<?php echo $this->global_vars['instance_number']; ?>_select_devider_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" style="clear:both;"></div>
										</div>
									</label>   
								<?php elseif($this->global_vars['fields_select_box_table_exists'] && !empty($this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['table_title'])): ?>
									<div id="filter_<?php echo $this->global_vars['instance_number']; ?>_select_table_<?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['index']; ?>" class="table"><?php echo $this->vars['/filter_table/filter_field/'][$_filter_table_key][$_filter_table_filter_field_key]['table_title']; ?></div>
								<?php endif; ?>
							<?php 
			endwhile;
			?>
						<?php 
			endwhile;
			?>
						</div>
						
						<div class="filter-separator-row">&nbsp;</div>
					<?php else: ?>
						<span class="comment">Дополнительные поля фильтрации не обнаружены</span>
						<div class="filter-separator-row">&nbsp;</div>	
					<?php endif; ?>
					
					<a href="javascript:void(0);" onclick="$('#filter_<?php echo $this->global_vars['instance_number']; ?>').submit();" class="button">
						&nbsp; <img src="/design/cms/img/filter/search.png" align="absmiddle" border="0"> Поиск &nbsp; 
					</a>
					
					<div style="float:right;"> 
						<a href="javascript:void(0);" onclick="cmsView.showFilter(<?php echo $this->global_vars['instance_number']; ?>);" class="button" title="Свернуть фильтр"><img src="/design/cms/img/filter/roll_down.png" align="absmiddle" border="0"></a>
						<a href="javascript:void(0);" onclick="document.location.href='/action/admin/cms/table_filter_clean/?instance_number=<?php echo $this->global_vars['instance_number']; ?>&structure_id=<?php echo CMS_STRUCTURE_ID; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>';" class="button" title="Отменить фильтр"><img src="/design/cms/img/icons/del.gif" align="absmiddle" border="0"></a>
					</div>
				</td>
			</tr> 
		</table>
		
	</form>
</div>


<?php if($this->vars['show_rows_limit']): ?>
	<!-- Форма, в которой указывается количество строк на странице -->
	<div style="float:right;">
		<form action="/action/admin/cms/table_rows/" method="POST">
		<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
		<input type="hidden" name="_language" value="<?php echo LANGUAGE_CURRENT; ?>">
		<input type="hidden" name="structure_id" value="<?php echo CMS_STRUCTURE_ID; ?>">
		<input type="hidden" name="table_id" value="<?php echo $this->global_vars['table']['id']; ?>">
		Показывать рядов: <input type="text" name="rows_per_page" value="<?php echo $this->global_vars['rows_per_page']; ?>" size=2>
		</form>
	</div>
<?php endif; ?>


<!-- Путь к таблице -->
<?php if($this->vars['show_path'] == true): ?>
	<?php
			reset($this->vars['/path/'][$__key]);
			while(list($_path_key,) = each($this->vars['/path/'][$__key])):
			?><a href="<?php echo $this->vars['/path/'][$__key][$_path_key]['url']; ?>"><?php echo $this->vars['/path/'][$__key][$_path_key]['name']; ?></a> <img src="/design/cms/img/ui/selector.gif" width="6" height="9" alt=""> <?php 
			endwhile;
			?> <?php echo $this->vars['path_current']; ?>
<?php endif; ?>


<form id="form_<?php echo $this->global_vars['instance_number']; ?>" action="/action/admin/cms/table_update/" method="POST" class="cms_view_form">
	<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
	<input type="hidden" name="_language" value="<?php echo LANGUAGE_CURRENT; ?>">
	<input type="hidden" name="_current_url" value="<?php echo CMS_STRUCTURE_URL; ?>">
	<input type="hidden" name="_table_language" value="<?php echo $this->vars['table_language']; ?>">
	<input type="hidden" name="_table_id" value="<?php echo $this->global_vars['table']['id']; ?>">
	<?php
			reset($this->vars['/hidden_field/'][$__key]);
			while(list($_hidden_field_key,) = each($this->vars['/hidden_field/'][$__key])):
			?>
		<input type="hidden" name="<?php echo $this->vars['/hidden_field/'][$__key][$_hidden_field_key]['name']; ?>" value="<?php echo $this->vars['/hidden_field/'][$__key][$_hidden_field_key]['value']; ?>">
	<?php 
			endwhile;
			?>
	
	<!-- События -->
	<?php if($this->vars['event_counter'] > 0): ?>
		<div style="float:left;">
			<img src="/design/cms/img/event/arrow.gif" width="14" height="21" border="0" alt="" style="margin-left:20px;">
			<?php
			reset($this->vars['/event_button/'][$__key]);
			while(list($_event_button_key,) = each($this->vars['/event_button/'][$__key])):
			?>
				<a title="<?php echo $this->vars['/event_button/'][$__key][$_event_button_key]['alt']; ?>" href="javascript:void(0);" onclick="if(!cmsView.click(this, '<?php echo $this->vars['/event_button/'][$__key][$_event_button_key]['alert']; ?>')) return false; <?php echo $this->vars['/event_button/'][$__key][$_event_button_key]['event']; ?>"><img 
				id="<?php echo $this->global_vars['table']['id']; ?>_<?php echo $this->vars['/event_button/'][$__key][$_event_button_key]['name']; ?>" class="event" longdesc="<?php echo $this->vars['/event_button/'][$__key][$_event_button_key]['image']; ?>" lang="<?php echo $this->vars['/event_button/'][$__key][$_event_button_key]['image_over']; ?>" alt="<?php echo $this->vars['/event_button/'][$__key][$_event_button_key]['alt']; ?>" src="<?php echo $this->vars['/event_button/'][$__key][$_event_button_key]['image']; ?>" 
				rel="<?php echo $this->vars['/event_button/'][$__key][$_event_button_key]['select_none']; ?><?php echo $this->vars['/event_button/'][$__key][$_event_button_key]['select_one']; ?><?php echo $this->vars['/event_button/'][$__key][$_event_button_key]['select_few']; ?>"></a>
			<?php 
			endwhile;
			?>
		</div>
	<?php endif; ?>
	
	<!-- Переключение языков -->
	<div style="float:right;padding-right:10px;">
		<?php
			reset($this->vars['/table_language/'][$__key]);
			while(list($_table_language_key,) = each($this->vars['/table_language/'][$__key])):
			?>
		<a href="<?php echo $this->vars['/table_language/'][$__key][$_table_language_key]['url']; ?>"><img <?php echo $this->vars['/table_language/'][$__key][$_table_language_key]['class']; ?> src="/design/cms/img/language/<?php echo $this->vars['/table_language/'][$__key][$_table_language_key]['language']; ?>.gif" border="0" hspace="5"></a>
		<?php 
			endwhile;
			?>
	</div>
	<br clear="all">
	<table border="0" class="cms_view" cellspacing="2">
		<thead>
			<tr>
				<?php
			reset($this->vars['/th1/'][$__key]);
			while(list($_th1_key,) = each($this->vars['/th1/'][$__key])):
			?>
				<td colspan="<?php echo $this->vars['/th1/'][$__key][$_th1_key]['colspan']; ?>" rowspan="<?php echo $this->vars['/th1/'][$__key][$_th1_key]['rowspan']; ?>" width="<?php echo $this->vars['/th1/'][$__key][$_th1_key]['width']; ?>"><?php echo $this->vars['/th1/'][$__key][$_th1_key]['title']; ?></td>
				<?php 
			endwhile;
			?>
			</tr>
			<?php if($this->vars['merged_columns'] > 0): ?>
			<tr>
				<?php
			reset($this->vars['/th2/'][$__key]);
			while(list($_th2_key,) = each($this->vars['/th2/'][$__key])):
			?>
				<td width="<?php echo $this->vars['/th2/'][$__key][$_th2_key]['width']; ?>"><?php echo $this->vars['/th2/'][$__key][$_th2_key]['title']; ?></td>
				<?php 
			endwhile;
			?>
			</tr>
			<?php endif; ?>
		</thead>
		<tbody>
		<?php if($this->vars['show_parent_link']): ?>
			<tr style="cursor:pointer;cursor:hand;" onclick="document.location.href='<?php echo $this->vars['parent_link']; ?>&_event_insert_id=<?php echo $this->global_vars['return_id']; ?>&_event_table_id=<?php echo $this->global_vars['parent_table_id']; ?>'" class="nodrop nodrag odd" onmouseover="this.className='nodrop nodrag over';" onmouseout="this.className='nodrop nodrag odd';">
				<?php
			reset($this->vars['/parent_cell/'][$__key]);
			while(list($_parent_cell_key,) = each($this->vars['/parent_cell/'][$__key])):
			?>
				<td align="<?php echo $this->vars['/parent_cell/'][$__key][$_parent_cell_key]['align']; ?>"><img src="/design/cms/img/button/up.gif" border="0" alt="На уровень вверх" width="16" height="16"></td>
				<?php 
			endwhile;
			?>
			</tr>
		<?php elseif($this->vars['total_rows'] == 0): ?>
			<tr>
				<td class="no_content" colspan="<?php echo $this->global_vars['total_columns']; ?>">Нет данных для отображения.</td>
			</tr>
		<?php endif; ?>
		<?php echo $this->vars['grid']; ?>
		</tbody>
		<tfoot>
		<tr class="bottom">
			<td colspan="<?php echo $this->global_vars['total_columns']; ?>">
				<table width="100%" border="0" cellpadding="0" cellspacing="1">
				<tr>
					<td width="25%"><?php echo $this->vars['from']; ?> - <?php echo $this->vars['to']; ?> из <?php echo $this->vars['out_of']; ?></td>
					<td width="50%">
					<?php if($this->vars['total_rows'] > $this->global_vars['rows_per_page']): ?>
						<?php if($this->global_vars['current_page'] == 0): ?>
							<img class="disabled" align="middle" src="/design/cms/img/button/first.gif" border="0" alt="Первая">
							<img class="disabled" align="middle" src="/design/cms/img/button/previous.gif" border="0" alt="Предыдущая">
						<?php else: ?>
							<a accesskey="38" href="<?php echo $this->vars['page_link']['first']; ?>"><img align="middle" src="/design/cms/img/button/first.gif" border="0" alt="Первая [Ctrl] + [вверх]"></a>
							<a accesskey="37" href="<?php echo $this->vars['page_link']['previous']; ?>"><img align="middle" src="/design/cms/img/button/previous.gif" border="0" alt="Предыдущая [Ctrl] + [<-]"></a>
						<?php endif; ?>
						Страница:
						<select style="font-family:Verdana;font-size:10px;" size="1" name="_tb_start_<?php echo $this->global_vars['table']['id']; ?>" onchange="location.href='?<?php echo $this->global_vars['get_vars']; ?>&_tb_start_<?php echo $this->global_vars['table']['id']; ?>=' + this.value;">
						<?php echo TemplateUDF::html_options(array('options'=>$this->vars['pages_list'],'selected'=>$this->global_vars['current_page'])); ?>
						</select> из <?php echo $this->vars['total_pages'] + 1; ?>
						<?php if($this->global_vars['current_page'] == $this->vars['total_pages']): ?>
							<img class="disabled" align="middle" src="/design/cms/img/button/next.gif" border="0" alt="Следующая">
							<img class="disabled" align="middle" src="/design/cms/img/button/last.gif" border="0" alt="Последняя">
						<?php else: ?>
							<a accesskey="39" href="<?php echo $this->vars['page_link']['next']; ?>"><img align="middle" src="/design/cms/img/button/next.gif" border="0" alt="Следующая  [Ctrl] + [->]"></a>
							<a accesskey="40" href="<?php echo $this->vars['page_link']['last']; ?>"><img align="middle" src="/design/cms/img/button/last.gif" border="0" alt="Последняя [Ctrl] + [вниз]"></a>
						<?php endif; ?>
					<?php endif; ?>
					</td>
					<?php if($this->vars['show_update_button'] > 0): ?>
						<td width="25%"><input name="_event[cms/table_update][]" id="save_sort" type="image" src="/design/cms/img/event/table/save_changes.gif" width="143" height="21" alt="Сохранить изменения"></td>
					<?php else: ?>
						<td width="25%">&nbsp;</td>
					<?php endif; ?>
				</tr>
				</table>
			</td>
			</tr>
		</tfoot>
	</table>
</form>
