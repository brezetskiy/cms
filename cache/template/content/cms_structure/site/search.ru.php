<style>
	.myvertical {
		border-collapse:collapse;
		border:1px solid #DDDDDD;
	}
	.myvertical td {
		border:1px solid #DDDDDD;
		line-height:24px;
	}
	.myvertical a {
		margin:0 10px;
	}

</style>
<br />
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
<h2>Статистика</h2>
<li>Количество записей в индексе - <?php echo $this->vars['index_count']; ?></li>
<li>Количество запросов в поиск - <?php echo $this->vars['number_request']; ?></li>
<br />
<h2>Статистика частых поисковых запросов</h2>
<?php
			reset($this->vars['/searchphraze/'][$__key]);
			while(list($_searchphraze_key,) = each($this->vars['/searchphraze/'][$__key])):
			?>
	<a target="_blank" href="/search?text=<?php echo $this->vars['/searchphraze/'][$__key][$_searchphraze_key]['keyword']; ?>"><?php echo $this->vars['/searchphraze/'][$__key][$_searchphraze_key]['keyword']; ?></a><sup><?php echo $this->vars['/searchphraze/'][$__key][$_searchphraze_key]['count']; ?></sup>&nbsp;&nbsp;&nbsp;
<?php 
			endwhile;
			?>

<h2>татистика по запросам, которые вернули пустой результат</h2>
<?php
			reset($this->vars['/nullresult/'][$__key]);
			while(list($_nullresult_key,) = each($this->vars['/nullresult/'][$__key])):
			?>
	<a target="_blank" href="/search?text=<?php echo $this->vars['/nullresult/'][$__key][$_nullresult_key]['keyword']; ?>"><?php echo $this->vars['/nullresult/'][$__key][$_nullresult_key]['keyword']; ?></a><sup><?php echo $this->vars['/nullresult/'][$__key][$_nullresult_key]['count']; ?></sup>&nbsp;&nbsp;&nbsp;
<?php 
			endwhile;
			?>

<h2>Последние 200 запросов</h2>
<?php
			reset($this->vars['/lastresult/'][$__key]);
			while(list($_lastresult_key,) = each($this->vars['/lastresult/'][$__key])):
			?>
	<a target="_blank" href="/search?text=<?php echo $this->vars['/lastresult/'][$__key][$_lastresult_key]['keyword']; ?>"><?php echo $this->vars['/lastresult/'][$__key][$_lastresult_key]['keyword']; ?></a><sup><?php echo $this->vars['/lastresult/'][$__key][$_lastresult_key]['count']; ?></sup>&nbsp;&nbsp;&nbsp;
<?php 
			endwhile;
			?>
