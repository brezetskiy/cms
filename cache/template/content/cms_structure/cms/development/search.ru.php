<h1>����� �� ����������</h1>
<form id="SearchForm" action="" method="" onsubmit="doSearch();return false;">
<table class="vertical" width="100%">
	<tr>
		<td class="title">RegExp ������:</td>
		<td>
			<input class="flexible" type="text" id="pattern" name="pattern" value="/test/i">
			<span class="comment">���������� ��������� � ������� PCRE.</span>
		</td>
	</tr>
	<tr>
		<td class="title">RegExp �����:</td>
		<td>
			<input class="flexible" type="text" id="extension" name="extension" value="/\.(php|tmpl)$/"><br>
			<span class="comment">��� ���������� ������� ���������� �������� ����</span>
		</td>
	</tr>
	<tr>
		<td class="title"><label for="comments">������������ �����������:</label></td>
		<td>
			<input type="checkbox" value="1" name="ignore_comments" id="comment"><br>
			<span class="comment">���������� �������, ���� ������ ��� � ����� �� ������������ � ������������ � PHP ����.</span>
		</td>
	</tr>
	<tr>
		<td class="title"><label for="comments">������ � ��������:</label></td>
		<td><input type="checkbox" checked onclick="checkCheckbox('files', this.checked, 'SearchForm')"></td>
	</tr>
	<tr>
		<td class="title"></td>
		<td><input type="submit" value="������"></td>
	</tr>
</table>



<div id="index_size"></div>
<div id="result"></div>

</form>

<script language="JavaScript">
function doSearch() {
	AjaxRequest.send('SearchForm', '/<?php echo LANGUAGE_URL; ?>action/admin/sdk/search_index/', '���������, ��� ���������� �������...', false, {});
}
function search(filename) {
	AjaxRequest.send('SearchForm', '/<?php echo LANGUAGE_URL; ?>action/admin/sdk/search/', '����� �� '+filename+'...', false, {});
}
</script>
