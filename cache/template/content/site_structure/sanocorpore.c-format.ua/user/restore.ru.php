
<form action="/<?php echo LANGUAGE_URL; ?>action/cms/restore_password/" method="POST">
	<input type="hidden" name="_return_path" value="/User/Login/">
	<input type="hidden" name="_error_path" value="<?php echo CURRENT_URL_FORM; ?>">
	<input type="hidden" name="user_id" value="<?php echo $this->vars['user_id']; ?>">
	<input type="hidden" name="auth_code" value="<?php echo $this->vars['auth_code']; ?>">
	 
	<table cellspacing="10" class="form">
		<tr>
			<td class="title"></td>
			<td>Введите новый пароль для доступа к сайту</td>
		</tr>
		<tr>
			<td class="title">Новый пароль:</td>
			<td><input class="wide" name="new_passwd" type="password" value=""></td>
		</tr>
		<tr>
			<td class="title">Подтвердите пароль:</td>
			<td><input class="wide" name="new_passwd_confirm" type="password" value=""></td>
		</tr>
		<tr>
			<td class="title">Код на картинке:</td>
			<td><div style="float:left"><?php echo $this->vars['captcha_html']; ?></div> <input type="text" maxlength="6" size="6" name="captcha_value"></td> 
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="Сменить пароль"></td>
		</tr>
	</table>
</form>
