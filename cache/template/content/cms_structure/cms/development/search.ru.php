<h1>Поиск по исходникам</h1>
<form id="SearchForm" action="" method="" onsubmit="doSearch();return false;">
<table class="vertical" width="100%">
	<tr>
		<td class="title">RegExp поиска:</td>
		<td>
			<input class="flexible" type="text" id="pattern" name="pattern" value="/test/i">
			<span class="comment">Регулярное выражение в формате PCRE.</span>
		</td>
	</tr>
	<tr>
		<td class="title">RegExp имени:</td>
		<td>
			<input class="flexible" type="text" id="extension" name="extension" value="/\.(php|tmpl)$/"><br>
			<span class="comment">Для отключения фильтра необходимо очистить поле</span>
		</td>
	</tr>
	<tr>
		<td class="title"><label for="comments">Игнорировать комментарии:</label></td>
		<td>
			<input type="checkbox" value="1" name="ignore_comments" id="comment"><br>
			<span class="comment">Установите галочку, если хотите что б поиск не производился в комментариях к PHP коду.</span>
		</td>
	</tr>
	<tr>
		<td class="title"><label for="comments">Искать в найденом:</label></td>
		<td><input type="checkbox" checked onclick="checkCheckbox('files', this.checked, 'SearchForm')"></td>
	</tr>
	<tr>
		<td class="title"></td>
		<td><input type="submit" value="Искать"></td>
	</tr>
</table>



<div id="index_size"></div>
<div id="result"></div>

</form>

<script language="JavaScript">
function doSearch() {
	AjaxRequest.send('SearchForm', '/<?php echo LANGUAGE_URL; ?>action/admin/sdk/search_index/', 'Подождите, идёт построение индекса...', false, {});
}
function search(filename) {
	AjaxRequest.send('SearchForm', '/<?php echo LANGUAGE_URL; ?>action/admin/sdk/search/', 'Поиск по '+filename+'...', false, {});
}
</script>
