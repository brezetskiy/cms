<h1>Статистика регистрации за месяц</h1>
<br>
<form action="" method="GET">
<table class="vertical">
<tr>
	<td>Месяц:</td>
	<td>
		<select name="month"><?php echo TemplateUDF::html_options(array('options'=>$this->vars['monthes'],'selected'=>$this->vars['month'])); ?></select>
		<input type="text" name="year" value="<?php echo $this->vars['year']; ?>" size="4">
	</td>
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
renderDataChart('StackedColumn3D.swf', 600, 320, 'chart', xml);
</script>

<?php echo $this->vars['cms_table']; ?>

<!--<table class="cms_view" style="margin:10px 0 0 0">
	<tr class="odd">
		<td width="50%"> 
			Всего зарегистрированых пользователей на сайте:
		</td>
		<td>
		<?php echo $this->vars['num_users']; ?>
		</td>
	</tr>
	<tr class="even">
		<td>
			Количество пользователей зарегистрированых в этом месяце:
		</td>
		<td>
		<?php echo $this->vars['num_users_month']; ?>
		</td>
	</tr>
</table>-->