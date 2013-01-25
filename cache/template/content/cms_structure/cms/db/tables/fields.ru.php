


<?php if(!empty($this->vars['check_failed'])): ?><div class="error"><?php echo $this->vars['check_failed']; ?></div><?php endif; ?>


<table width="100%" style="margin-top:10px; position:relative; z-index:10; ">
	<tr>
		<td width="30%">&nbsp;</td>
		<td align="left">
			<span class="comment">Быстрый переход к таблице:</span><br/> 
			<select onchange="document.location.href='/Admin/CMS/DB/Tables/Fields/?table_id='+this.value" style="width:300px; text-align:left;" class="chzn-select">
				<?php echo TemplateUDF::html_options(array('selected'=>TABLE_ID,'options'=>$this->vars['quick_link'])); ?>
			</select>
			<a onclick="EditWindow(<?php echo TABLE_ID; ?>, 'cms_table','void', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', '')" href="javascript:void(0);">
				<img align="top" style="margin-top:5px;" src="/design/cms/img/icons/change.gif" border="0" title="Редактировать описание таблицы">
			</a>
		</td>
		<td align="right">
			<span style="float:right;" class="button">
				<a href="./?table_id=<?php echo TABLE_ID; ?>&obligatory_update=1"><img src="/img/sdk/reload.png" border="0" align="absmiddle"> Обновить информацию о таблице</a>
			</span>
		</td>
	</tr>
</table>

<br/>

<?php echo $this->vars['cms_view']; ?>


<?php echo $this->vars['cms_enum']; ?>


<?php echo $this->vars['cms_parent']; ?>

