<script language="JavaScript">
function check(name){
	if($('input[name=check_'+name+']').attr('checked')) {
		$("input[name^='"+name+"'][type='checkbox']").attr('checked', true);   
	} else {
		$("input[name^='"+name+"'][type='checkbox']").attr('checked', false);  
	}
}

function cleanModules(){
	if(confirm('Очистить выбранные модули?')){
		
		return true;
	}
	return false;
}
</script>

<h1>Удаление модулей из системы</h1>
<form method="POST" action="/<?php echo LANGUAGE_URL; ?>action/admin/sdk/module_delete/">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
<table width="100%" border="0" class="vertical">
	<tr valign="top">
		<td width="40%">
			<?php
			reset($this->vars['/delete_1/'][$__key]);
			while(list($_delete_1_key,) = each($this->vars['/delete_1/'][$__key])):
			?>
			<input type="checkbox" name="modules[]" value="<?php echo $this->vars['/delete_1/'][$__key][$_delete_1_key]['id']; ?>" id="del_<?php echo $this->vars['/delete_1/'][$__key][$_delete_1_key]['id']; ?>"><label for="del_<?php echo $this->vars['/delete_1/'][$__key][$_delete_1_key]['id']; ?>"><?php echo $this->vars['/delete_1/'][$__key][$_delete_1_key]['name']; ?> (<?php echo $this->vars['/delete_1/'][$__key][$_delete_1_key]['description']; ?>)</label><br>
			<?php 
			endwhile;
			?>
		</td>
		<td>
			<?php
			reset($this->vars['/delete_2/'][$__key]);
			while(list($_delete_2_key,) = each($this->vars['/delete_2/'][$__key])):
			?>
			<input type="checkbox" name="modules[]" value="<?php echo $this->vars['/delete_2/'][$__key][$_delete_2_key]['id']; ?>" id="del_<?php echo $this->vars['/delete_2/'][$__key][$_delete_2_key]['id']; ?>"><label for="del_<?php echo $this->vars['/delete_2/'][$__key][$_delete_2_key]['id']; ?>"><?php echo $this->vars['/delete_2/'][$__key][$_delete_2_key]['name']; ?> (<?php echo $this->vars['/delete_2/'][$__key][$_delete_2_key]['description']; ?>)</label><br>
			<?php 
			endwhile;
			?>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<input type="submit" value="Удалить" onclick="return confirm('Удалить выбранные модули?');">&nbsp;
			<input id="check_modules" type="checkbox" name="check_modules" onclick="check('modules');"><label for="check_modules">Выделить все</label>	
		</td>
	</tr>
</table>
</form>



<h1>Удаление лишних данных</h1>
<form method="POST" action="/<?php echo LANGUAGE_URL; ?>action/admin/sdk/module_delete_extras/">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
<table width="100%" border="0" class="vertical">
	<tr valign="top">
		<td width="40%">	
		<?php if($this->vars['show_dirs']): ?>
			<h3>Директории</h3>
			<div>
				<input type="checkbox" name="delete[]" value="static" id="static"><label for="static">/static/ <?php echo $this->vars['static_size']; ?> Кб</label><br>
				<input type="checkbox" name="delete[]" value="import" id="import"><label for="import">/system/import/ <?php echo $this->vars['import_size']; ?> Кб</label><br>
				<input type="checkbox" name="delete[]" value="tmp" id="tmp"><label for="tmp">Временные файлы: <?php echo $this->vars['tmp_size']; ?> Кб</label><br>
				<input type="checkbox" name="delete[]" value="cvs" id="cvs"><label for="cvs">История изменений: <?php echo $this->vars['cvs_size']; ?> Кб</label><br>
				<input type="checkbox" name="delete[]" value="logs" id="logs"><label for="logs">Логи и отчёты: <?php echo $this->vars['logs_size']; ?> Кб</label><br>
				<input type="checkbox" name="delete[]" value="cache" id="cache"><label for="cache">Директория с закешированными файлами: <?php echo $this->vars['cache']; ?> Кб</label><br/>
				<input type="checkbox" name="delete[]" value="auth" id="auth"><label for="auth">Статистика авторизаций: - </label><br/>
				<input type="checkbox" name="delete[]" value="mailq" id="mailq"><label for="mailq">Очередь почтовых сообщений: -</label>
			</div>
		<?php endif; ?>
		</td>
		<td>
		<?php if($this->vars['show_tables']): ?>
			<h3>Таблицы</h3> 
			<div>
				<?php
			reset($this->vars['/table/'][$__key]);
			while(list($_table_key,) = each($this->vars['/table/'][$__key])):
			?>
				<input type="checkbox" name="tables[]" value="<?php echo $this->vars['/table/'][$__key][$_table_key]['table_name']; ?>.<?php echo $this->vars['/table/'][$__key][$_table_key]['table_type']; ?>" id="<?php echo $this->vars['/table/'][$__key][$_table_key]['table_type']; ?><?php echo $this->vars['/table/'][$__key][$_table_key]['table_name']; ?>"><label for="<?php echo $this->vars['/table/'][$__key][$_table_key]['table_type']; ?><?php echo $this->vars['/table/'][$__key][$_table_key]['table_name']; ?>"><?php echo $this->vars['/table/'][$__key][$_table_key]['table_name']; ?> [<?php echo $this->vars['/table/'][$__key][$_table_key]['table_type']; ?>]</label><br>
				<?php 
			endwhile;
			?>
			</div>
		<?php endif; ?>
		</td>
	</tr> 
	<tr>
		<td></td>
		<td>
			<input type="submit" value="Удалить" onclick="return confirm('Удалить выбранные данные?');">&nbsp;
			<input id="check_delete" type="checkbox" name="check_delete" onclick="check('delete');"><label for="check_delete">Выделить все</label>	
		</td>
	</tr>
</table>
</form>



<h1>Удаление пользоваталей</h1>
<form method="POST" action="/<?php echo LANGUAGE_URL; ?>action/admin/sdk/module_delete_user/">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
<table width="100%" border="0" class="vertical">
	<tr valign="top">
		<td  width="40%">
			<?php
			reset($this->vars['/group_1/'][$__key]);
			while(list($_group_1_key,) = each($this->vars['/group_1/'][$__key])):
			?>
			<input type="checkbox" name="users[]" value="<?php echo $this->vars['/group_1/'][$__key][$_group_1_key]['id']; ?>" id="users_<?php echo $this->vars['/group_1/'][$__key][$_group_1_key]['id']; ?>"><label for="users_<?php echo $this->vars['/group_1/'][$__key][$_group_1_key]['id']; ?>"><?php echo $this->vars['/group_1/'][$__key][$_group_1_key]['group_name']; ?><sup><?php echo $this->vars['/group_1/'][$__key][$_group_1_key]['user_count']; ?></sup></label><br>
			<?php 
			endwhile;
			?>
		</td>
		<td>
			<?php
			reset($this->vars['/group_2/'][$__key]);
			while(list($_group_2_key,) = each($this->vars['/group_2/'][$__key])):
			?>
			<input type="checkbox" name="users[]" value="<?php echo $this->vars['/group_2/'][$__key][$_group_2_key]['id']; ?>" id="users_<?php echo $this->vars['/group_2/'][$__key][$_group_2_key]['id']; ?>"><label for="users_<?php echo $this->vars['/group_2/'][$__key][$_group_2_key]['id']; ?>"><?php echo $this->vars['/group_2/'][$__key][$_group_2_key]['group_name']; ?><sup><?php echo $this->vars['/group_2/'][$__key][$_group_2_key]['user_count']; ?></sup></label><br>
			<?php 
			endwhile;
			?>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
	<input type="submit" value="Удалить" onclick="return confirm('Удалить пользователей из выбранных групп?');">
	<input id="check_users" type="checkbox" name="check_users" onclick="check('users');"><label for="check_users">Выделить все</label>	
		</td>
	</tr>
</table>
</form>	


<h1>Удаление сайтов</h1>
<form method="POST" action="/<?php echo LANGUAGE_URL; ?>action/admin/sdk/module_delete_site/">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
<table width="100%" border="0" class="vertical">
	<tr valign="top">
		<td  width="40%">
			<?php
			reset($this->vars['/site_1/'][$__key]);
			while(list($_site_1_key,) = each($this->vars['/site_1/'][$__key])):
			?>
			<input type="checkbox" name="site[]" value="<?php echo $this->vars['/site_1/'][$__key][$_site_1_key]['id']; ?>" id="site_<?php echo $this->vars['/site_1/'][$__key][$_site_1_key]['id']; ?>"><label for="site_<?php echo $this->vars['/site_1/'][$__key][$_site_1_key]['id']; ?>"><?php echo $this->vars['/site_1/'][$__key][$_site_1_key]['url']; ?></label><br>
			<?php 
			endwhile;
			?>
		</td>
		<td>
			<?php
			reset($this->vars['/site_2/'][$__key]);
			while(list($_site_2_key,) = each($this->vars['/site_2/'][$__key])):
			?>
			<input type="checkbox" name="site[]" value="<?php echo $this->vars['/site_2/'][$__key][$_site_2_key]['id']; ?>" id="site_<?php echo $this->vars['/site_2/'][$__key][$_site_2_key]['id']; ?>"><label for="site_<?php echo $this->vars['/site_2/'][$__key][$_site_2_key]['id']; ?>"><?php echo $this->vars['/site_2/'][$__key][$_site_2_key]['url']; ?></label><br>
			<?php 
			endwhile;
			?>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
	<input type="submit" value="Удалить" onclick="return confirm('Удалить выбранные сайты?');">
	<input id="check_site" type="checkbox" name="check_site" onclick="check('site');"><label for="check_site">Выделить все</label>	
		</td>
	</tr>
</table>
</form>	


<h1>Удаление дизайнов</h1>
<form method="POST" action="/<?php echo LANGUAGE_URL; ?>action/admin/sdk/module_delete_template/">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
<table width="100%" border="0" class="vertical">
	<tr valign="top">
		<td  width="40%">
			<?php
			reset($this->vars['/template_1/'][$__key]);
			while(list($_template_1_key,) = each($this->vars['/template_1/'][$__key])):
			?>
			<input type="checkbox" name="template[]" value="<?php echo $this->vars['/template_1/'][$__key][$_template_1_key]['id']; ?>" id="template_<?php echo $this->vars['/template_1/'][$__key][$_template_1_key]['id']; ?>"><label for="template_<?php echo $this->vars['/template_1/'][$__key][$_template_1_key]['id']; ?>"><?php echo $this->vars['/template_1/'][$__key][$_template_1_key]['name']; ?></label><br>
			<?php 
			endwhile;
			?>
		</td>
		<td>
			<?php
			reset($this->vars['/template_2/'][$__key]);
			while(list($_template_2_key,) = each($this->vars['/template_2/'][$__key])):
			?>
			<input type="checkbox" name="template[]" value="<?php echo $this->vars['/template_2/'][$__key][$_template_2_key]['id']; ?>" id="template_<?php echo $this->vars['/template_2/'][$__key][$_template_2_key]['id']; ?>"><label for="template_<?php echo $this->vars['/template_2/'][$__key][$_template_2_key]['id']; ?>"><?php echo $this->vars['/template_2/'][$__key][$_template_2_key]['name']; ?></label><br>
			<?php 
			endwhile;
			?>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
	<input type="submit" value="Удалить" onclick="return confirm('Удалить выбранные дизайны?');">
	<input id="check_template" type="checkbox" name="check_template" onclick="check('template');"><label for="check_template">Выделить все</label>	
		</td>
	</tr>
</table>
</form>	


<h1>Удаление новостей</h1>
<form method="POST" action="/<?php echo LANGUAGE_URL; ?>action/admin/sdk/module_delete_news/">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
<table width="100%" border="0" class="vertical">
	<tr valign="top">
		<td  width="40%">
			<?php
			reset($this->vars['/news_1/'][$__key]);
			while(list($_news_1_key,) = each($this->vars['/news_1/'][$__key])):
			?>
			<input type="checkbox" name="news[]" value="<?php echo $this->vars['/news_1/'][$__key][$_news_1_key]['id']; ?>" id="news_<?php echo $this->vars['/news_1/'][$__key][$_news_1_key]['id']; ?>"><label for="news_<?php echo $this->vars['/news_1/'][$__key][$_news_1_key]['id']; ?>"><?php echo $this->vars['/news_1/'][$__key][$_news_1_key]['name']; ?><sup><?php echo $this->vars['/news_1/'][$__key][$_news_1_key]['message_count']; ?></sup></label><br>
			<?php 
			endwhile;
			?>
		</td>
		<td>
			<?php
			reset($this->vars['/news_2/'][$__key]);
			while(list($_news_2_key,) = each($this->vars['/news_2/'][$__key])):
			?>
			<input type="checkbox" name="news[]" value="<?php echo $this->vars['/news_2/'][$__key][$_news_2_key]['id']; ?>" id="news_<?php echo $this->vars['/news_2/'][$__key][$_news_2_key]['id']; ?>"><label for="news_<?php echo $this->vars['/news_2/'][$__key][$_news_2_key]['id']; ?>"><?php echo $this->vars['/news_2/'][$__key][$_news_2_key]['name']; ?><sup><?php echo $this->vars['/news_2/'][$__key][$_news_2_key]['message_count']; ?></sup></label><?php echo $this->vars['/news_2/'][$__key][$_news_2_key]['name']; ?></label><br>
			<?php 
			endwhile;
			?>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
	<input type="submit" value="Удалить" onclick="return confirm('Удалить выбранные новости?');">
	<input id="check_news" type="checkbox" name="check_news" onclick="check('news');"><label for="check_news">Выделить все</label>	
		</td>
	</tr>
</table>
</form>	

