<br><form id="changeForm" action="" method="" >
�������� ���� ���������� �� <input class="date" onclick="scwShow(scwID('search_date_from'),event);" type="text" id="search_date_from" name="date" value="<?php echo $this->vars['date']; ?>">
<input onclick="$('#changeForm').submit();" type="submit" value="��������"></form>
<script language="JavaScript">
function doSearch() {
	AjaxRequest.send('changeForm', '/<?php echo LANGUAGE_URL; ?>action/admin/currency/currency_change/', '���������, ��� ���� ��������� ����������� ...', false, {});
}
</script>
<div id="currency">
<H2>����� ����� �� <?php echo $this->vars['date']; ?></H2>
<table border="0" class="cms_view" cellpadding="2" cellspacing="2" width="100%">
<THEAD>
	<TR>
		<td width="20%" rowspan="2">������</td>
		<td width="20%" colspan="3">���</td>
		<td width="20%" colspan="3">���</td>
	</TR>
	<TR>
		<td width="10%">����</td>
		<td width="10%">���-��</td>
		<td width="10%">����</td>
		<td width="10%">����</td>
		<td width="10%">���-��</td>
		<td width="10%">����</td>
	</TR>
</THEAD>

<TBODY>
<?php if($this->vars['show_rates']): ?>
	<?php
			reset($this->vars['/row/'][$__key]);
			while(list($_row_key,) = each($this->vars['/row/'][$__key])):
			?>
	<tr class="<?php echo $this->vars['/row/'][$__key][$_row_key]['class']; ?>" onmouseover="this.className='over';" onmouseout="this.className='<?php echo $this->vars['/row/'][$__key][$_row_key]['class']; ?>';" align="right">
		<td align="center"><span title="<?php echo $this->vars['/row/'][$__key][$_row_key]['admin_login']; ?>"><?php echo $this->vars['/row/'][$__key][$_row_key]['code']; ?></span><input type="hidden" name="currency[<?php echo $this->vars['/row/'][$__key][$_row_key]['id']; ?>][currency_id]" value="<?php echo $this->vars['/row/'][$__key][$_row_key]['id']; ?>"></td>
		<td><?php echo $this->vars['/row/'][$__key][$_row_key]['nbu_date']; ?></td>
		<td><?php echo $this->vars['/row/'][$__key][$_row_key]['nbu_amount']; ?></td>
		<td><?php echo $this->vars['/row/'][$__key][$_row_key]['nbu_rate']; ?></td>
		<td><?php echo $this->vars['/row/'][$__key][$_row_key]['cbr_date']; ?></td>
		<td><?php echo $this->vars['/row/'][$__key][$_row_key]['cbr_amount']; ?></td>
		<td><?php echo $this->vars['/row/'][$__key][$_row_key]['cbr_rate']; ?></td>
	</tr>
	<?php 
			endwhile;
			?>
<?php else: ?>
	<tr class="odd">
		<td align="center" style="height: 50px;" colspan="7">�� ��� ���� ����� ����� �� �����������</td>
	</tr>
<?php endif; ?>
</TBODY>
</table>
</div>
<br>
<H2>���������� ���� �����</H2>



<form action="/<?php echo LANGUAGE_URL; ?>action/admin/currency/cross_update/" method="POST">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
<input type="hidden" name="_table_language" value="<?php echo $this->vars['table_language']; ?>">
<table border="0" class="cms_view" cellpadding="2" cellspacing="2" width="100%">
<?php if(isset($this->vars['crossrate_definer']['admin_login'])): ?><caption>�����-����� ����������� <?php echo $this->vars['crossrate_definer']['admin_login']; ?>, <?php echo $this->vars['crossrate_definer']['dtime']; ?></caption><?php endif; ?>
<THEAD>
	<TR>
		<td width="20%">������</td>
		<?php
			reset($this->vars['/header_column/'][$__key]);
			while(list($_header_column_key,) = each($this->vars['/header_column/'][$__key])):
			?>
		<td width="<?php echo $this->vars['/header_column/'][$__key][$_header_column_key]['width']; ?>"><?php echo $this->vars['/header_column/'][$__key][$_header_column_key]['code']; ?></td>
		<?php 
			endwhile;
			?>
	</TR>
</THEAD>
<TBODY>
	<?php
			reset($this->vars['/crossrow/'][$__key]);
			while(list($_crossrow_key,) = each($this->vars['/crossrow/'][$__key])):
			?>
	<tr class="<?php echo $this->vars['/crossrow/'][$__key][$_crossrow_key]['class']; ?>" onmouseover="this.className='over';" onmouseout="this.className='<?php echo $this->vars['/crossrow/'][$__key][$_crossrow_key]['class']; ?>';">
		<td align="center"><?php echo $this->vars['/crossrow/'][$__key][$_crossrow_key]['code']; ?><input type="hidden" name="currency[<?php echo $this->vars['/crossrow/'][$__key][$_crossrow_key]['id']; ?>][currency_id]" value="<?php echo $this->vars['/crossrow/'][$__key][$_crossrow_key]['id']; ?>"></td>
		<?php
			reset($this->vars['/crossrow/crossrate/'][$_crossrow_key]);
			while(list($_crossrow_crossrate_key,) = each($this->vars['/crossrow/crossrate/'][$_crossrow_key])):
			?>
		<td width="10%" align="right">
			<?php if($this->global_vars['current_currency'] == $this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['currency_to_id']): ?>
				<?php if($this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['currency_from_id'] != $this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['currency_to_id']): ?>
					<input type="text" class="alpha" value="<?php echo $this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['rate']; ?>" name="crossrate[<?php echo $this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['currency_from_id']; ?>][<?php echo $this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['currency_to_id']; ?>]" style="text-align:right;">
				<?php else: ?>
					1.0000
					<input type="hidden" value="1.0000" name="crossrate[<?php echo $this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['currency_from_id']; ?>][<?php echo $this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['currency_to_id']; ?>]">
				<?php endif; ?>
			<?php else: ?>
				<?php if($this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['currency_from_id'] != $this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['currency_to_id']): ?>
					<?php echo $this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['rate']; ?>
				<?php else: ?>
					1.0000
					<input type="hidden" value="1.0000" name="crossrate[<?php echo $this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['currency_from_id']; ?>][<?php echo $this->vars['/crossrow/crossrate/'][$_crossrow_key][$_crossrow_crossrate_key]['currency_to_id']; ?>]">
				<?php endif; ?>
			<?php endif; ?>	
		</td>
		<?php 
			endwhile;
			?>
	</tr>
	<?php 
			endwhile;
			?>
</TBODY>
<TFOOT>
<tr class="bottom">
	<td colspan="<?php echo $this->vars['cross_table_colspan']; ?>" style="text-align:right;padding-right:50px;">
		<input type="image" src="/design/cms/img/event/table/save_changes.gif" width="143" height="21" alt="��������� ���������">
	</td>
</tr>
</TFOOT>
</table>
<span class="comment">��������� ����� ������, ������� ���� ������� � ����, ��� ������ ����� ���������� �� ��������� <?php echo $this->vars['crossrate_default_currency']; ?> (�������� ������ ������� � ���������� �������)</span><br>
</form>



