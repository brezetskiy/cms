<script>
	
	function checkSelect(){
		if($('input[name=checkselect]').attr('checked')) {
			$("input[name^='table_select'][type='checkbox']").attr('checked', true);   
		} else {
			$("input[name^='table_select'][type='checkbox']").attr('checked', false);  
		}
	}
	
	function checkUpdate(){
		if($('input[name=checkupdate]').attr('checked')) {
			$("input[name^='table_update'][type='checkbox']").attr('checked', true);   
		} else {
			$("input[name^='table_update'][type='checkbox']").attr('checked', false);  
		}
	}

	function checkEvent(){
		if($('input[name=checkevent]').attr('checked')) {
			$("input[name^='event'][type='checkbox']").attr('checked', true);   
		} else {
			$("input[name^='event'][type='checkbox']").attr('checked', false);  
		}
	}
	
	function checkView(){
		if($('input[name=checkview]').attr('checked')) {
			$("input[name^='view'][type='checkbox']").attr('checked', true);   
		} else {
			$("input[name^='view'][type='checkbox']").attr('checked', false);  
		}
	}
	
</script>

<h1><?php echo $this->vars['action']; ?></h1>
<a href="../?module_id=<?php echo $this->vars['module_id']; ?>"><img border="0" src="/design/cms/img/button/up.gif"> Вернуться к списку привилегий</a>

<form action="/<?php echo LANGUAGE_URL; ?>action/admin/auth/actions/" method="POST" onsubmit="AjaxRequest.form('form', 'Сохраняем...', {});return false;" id="form">
<input type="hidden" name="action_id" value="<?php echo $this->vars['action_id']; ?>">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">

<?php if($this->vars['show_change']>0): ?>
	<h1>Таблицы</h1>
	<?php
			reset($this->vars['/table/'][$__key]);
			while(list($_table_key,) = each($this->vars['/table/'][$__key])):
			?>
		<?php if(!empty($this->vars['/table/'][$__key][$_table_key]['db_name'])): ?>
			<b><?php echo $this->vars['/table/'][$__key][$_table_key]['db_name']; ?></b><br>
		<?php endif; ?>
		<input type="checkbox" name="table_select[]" value="<?php echo $this->vars['/table/'][$__key][$_table_key]['id']; ?>" <?php echo $this->vars['/table/'][$__key][$_table_key]['checked_select']; ?> id="select_<?php echo $this->vars['/table/'][$__key][$_table_key]['id']; ?>"><label for="select_<?php echo $this->vars['/table/'][$__key][$_table_key]['id']; ?>">[select]</label>
		<input type="checkbox" name="table_update[]" value="<?php echo $this->vars['/table/'][$__key][$_table_key]['id']; ?>" <?php echo $this->vars['/table/'][$__key][$_table_key]['checked_update']; ?> id="update_<?php echo $this->vars['/table/'][$__key][$_table_key]['id']; ?>"><label for="update_<?php echo $this->vars['/table/'][$__key][$_table_key]['id']; ?>">[update]</label>
		<?php echo $this->vars['/table/'][$__key][$_table_key]['name']; ?><br>
		<span class="comment">SELECT: <?php echo $this->vars['/table/'][$__key][$_table_key]['select_actions']; ?></span><br>
		<span class="comment">UPDATE: <?php echo $this->vars['/table/'][$__key][$_table_key]['update_actions']; ?></span><br>
	<?php 
			endwhile;
			?>
	<input id="checkselect" type="checkbox" name="checkselect" onclick="checkSelect();"><label for="checkselect">Все select</label>
	<input id="checkupdate" type="checkbox" name="checkupdate" onclick="checkUpdate();"><label for="checkupdate">Все update</label>
<?php endif; ?>

<?php if($this->vars['show_event']>0): ?>
	<h1>События</h1>
	<?php
			reset($this->vars['/event/'][$__key]);
			while(list($_event_key,) = each($this->vars['/event/'][$__key])):
			?>
		<input type="checkbox" name="event[]" value="<?php echo $this->vars['/event/'][$__key][$_event_key]['id']; ?>" <?php echo $this->vars['/event/'][$__key][$_event_key]['checked']; ?> id="event_<?php echo $this->vars['/event/'][$__key][$_event_key]['id']; ?>"><label for="event_<?php echo $this->vars['/event/'][$__key][$_event_key]['id']; ?>"><?php echo $this->vars['/event/'][$__key][$_event_key]['name']; ?></label><br><span class="comment"><?php echo $this->vars['/event/'][$__key][$_event_key]['actions']; ?></span><br>
	<?php 
			endwhile;
			?>
	<input id="checkevent" type="checkbox" name="checkevent" onclick="checkEvent();"><label for="checkevent">Все события</label>
<?php endif; ?>


<?php if($this->vars['show_view']): ?>
	<h1>Доступ к разделам</h1>
	<?php echo $this->vars['view']; ?>
	<?php
			reset($this->vars['/structure/'][$__key]);
			while(list($_structure_key,) = each($this->vars['/structure/'][$__key])):
			?>
	<?php echo $this->vars['/structure/'][$__key][$_structure_key]['name']; ?> [<?php echo $this->vars['/structure/'][$__key][$_structure_key]['url']; ?>]<br>
	<?php 
			endwhile;
			?>
	<input id="checkview" type="checkbox" name="checkview" onclick="checkView();"><label for="checkview">Все разделы</label>
<?php endif; ?>

<br/>
<br/>
<input type="submit" value="Сохранить изменения">


</form>