<h1>Поиск в структуре БД</h1>
<form action="/Admin/CMS/Development/DbStructureSearch/" method="POST">
<input type="hidden" name="go" value="true">
<table class="vertical" width="100%">
	<tr>
		<td class="title">Соединение:</td>
		<td>
			<select name="db_alias">
				<?php echo TemplateUDF::html_options(array('options'=>$this->vars['connections'],'selected'=>DB_ALIAS)); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="title">RegExp поиска:</td>
		<td>
			<input class="flexible" type="text" name="search_text" value="<?php echo $this->vars['search_text']; ?>">
			<span class="comment">Регулярное выражение в формате PCRE.</span>
		</td>
	</tr>
	<tr>
		<td class="title">Искать в:</td>
		<td>
			<input type="checkbox" value="true" name="search_table" id="search_table" <?php echo $this->vars['search_table_checked']; ?>> 
			<label for="search_table">Структуре таблиц</label><br>

			<input type="checkbox" value="true" name="search_trigger" id="search_trigger" <?php echo $this->vars['search_trigger_checked']; ?>> 
			<label for="search_trigger">Триггерах</label><br>

			<input type="checkbox" value="true" name="search_routine" id="search_routine" <?php echo $this->vars['search_routine_checked']; ?>> 
			<label for="search_routine">Функциях и процедурах</label><br>

		</td>
	</tr>
	<tr>
		<td class="title"></td>
		<td><input type="submit" value="Искать"></td>
	</tr>
</table>
</form>

<?php if($this->vars['found']['table'] == 'true'): ?>
<h2>Структура таблиц</h2><br>
<?php
			reset($this->vars['/result_table/'][$__key]);
			while(list($_result_table_key,) = each($this->vars['/result_table/'][$__key])):
			?>
<li><a href="javascript:;" onclick="byId('table_<?php echo $this->vars['/result_table/'][$__key][$_result_table_key]['name']; ?>').style.display = (byId('table_<?php echo $this->vars['/result_table/'][$__key][$_result_table_key]['name']; ?>').style.display == 'block' ? 'none' : 'block');"><?php echo $this->vars['/result_table/'][$__key][$_result_table_key]['name']; ?></a><br>
<pre style="display: none;" id="table_<?php echo $this->vars['/result_table/'][$__key][$_result_table_key]['name']; ?>"><?php echo $this->vars['/result_table/'][$__key][$_result_table_key]['def']; ?></pre></li>
<?php 
			endwhile;
			?>
<br>
<?php endif; ?>

<?php if($this->vars['found']['trigger'] == 'true'): ?>
<h2>Триггеры</h2><br>
<?php
			reset($this->vars['/result_trigger/'][$__key]);
			while(list($_result_trigger_key,) = each($this->vars['/result_trigger/'][$__key])):
			?>
<li><a href="javascript:;" onclick="byId('trigger_<?php echo $this->vars['/result_trigger/'][$__key][$_result_trigger_key]['trigger']; ?>').style.display = (byId('trigger_<?php echo $this->vars['/result_trigger/'][$__key][$_result_trigger_key]['trigger']; ?>').style.display == 'block' ? 'none' : 'block');"><?php echo $this->vars['/result_trigger/'][$__key][$_result_trigger_key]['table']; ?> <?php echo $this->vars['/result_trigger/'][$__key][$_result_trigger_key]['timing']; ?> <?php echo $this->vars['/result_trigger/'][$__key][$_result_trigger_key]['event']; ?> </a><br>
<pre style="display: none;" id="trigger_<?php echo $this->vars['/result_trigger/'][$__key][$_result_trigger_key]['trigger']; ?>"><?php echo $this->vars['/result_trigger/'][$__key][$_result_trigger_key]['def']; ?></pre></li>
<?php 
			endwhile;
			?>
<br>
<?php endif; ?>

<?php if($this->vars['found']['routine'] == 'true'): ?>
<h2>Процедуры и функции</h2><br>
<?php
			reset($this->vars['/result_routine/'][$__key]);
			while(list($_result_routine_key,) = each($this->vars['/result_routine/'][$__key])):
			?>
<li><a href="javascript:;" onclick="byId('routine_<?php echo $this->vars['/result_routine/'][$__key][$_result_routine_key]['routine_name']; ?>').style.display = (byId('routine_<?php echo $this->vars['/result_routine/'][$__key][$_result_routine_key]['routine_name']; ?>').style.display == 'block' ? 'none' : 'block');"><?php echo $this->vars['/result_routine/'][$__key][$_result_routine_key]['routine_type']; ?> <?php echo $this->vars['/result_routine/'][$__key][$_result_routine_key]['routine_name']; ?></a><br>
<pre style="display: none;" id="routine_<?php echo $this->vars['/result_routine/'][$__key][$_result_routine_key]['routine_name']; ?>"><?php echo $this->vars['/result_routine/'][$__key][$_result_routine_key]['def']; ?></pre></li>
<?php 
			endwhile;
			?>
<br>
<?php endif; ?>