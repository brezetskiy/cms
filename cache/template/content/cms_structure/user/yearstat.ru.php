<h1>Статистика регистрации за год</h1>
<br>
<form action="" method="GET">
<table class="vertical">
<tr>
	<td>
		<select name="site_id"><option value="0">Все сайты</option><?php echo TemplateUDF::html_options(array('options'=>$this->vars['sites'],'selected'=>$this->vars['site_id'])); ?></select>
	</td>
	<td><input type="submit" value="Показать"></td>
</tr>
</table>
</form>

<div id="chartdiv"></div>
<script>
var xml = "<?php echo $this->vars['chart_xml']; ?>";
renderDataChart('<?php echo $this->vars['type']; ?>Line.swf', 800, 400, 'chart', xml);
</script>

<?php echo $this->vars['cms_table']; ?>
