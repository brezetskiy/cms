
<form action="/<?php echo LANGUAGE_URL; ?>action/cms/amnesia/" method="POST">
	<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
	<table cellspacing="10" class="form">
		<tr>
			<td class="title"></td>
			<td>��� ����������� ������ ������� ��� e-mail � ����������� ���� �����.</td>
		</tr>
		<tr>
			<td class="title">E-mail:</td>
			<td><input class="wide" name="email" type="text" value="<?php echo $_SESSION['auth']['email']; ?>"></td>
		</tr>
		<tr>
			<td class="title">��� �� ��������:</td>
			<td><div style="float:left"><?php echo $this->vars['captcha_html']; ?></div> <input type="text" maxlength="6" size="6" name="captcha_value"></td> 
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="��������� ������"></td>
		</tr>
	</table>
</form>