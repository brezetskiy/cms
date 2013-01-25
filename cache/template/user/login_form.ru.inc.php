
<table class="form">
	<tr>
		<td class="title"></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="title"></td>  
		<td>  
			<?php if(!empty($this->vars['headline'])): ?>
				<?php echo $this->vars['headline']; ?>
			<?php else: ?>
				Запрошенная Вами страница доступна только для зарегистрированных пользователей. Введите свой e-mail и пароль:
			<?php endif; ?>
		</td>
	</tr>
	<?php if(AUTH_OID_ENABLE): ?><tr><td class="title"></td><td><?php echo TemplateUDF::oid_widget(array('name'=>"error_registered_auth_form",'template'=>"context")); ?></td></tr><?php endif; ?> 
</table>
 
<form id="form-auth-login" action="/<?php echo LANGUAGE_URL; ?>action/cms/login/" method="POST">
<input type="hidden" name="user_mode" value="registered"> 
<input type="hidden" name="source" value="site"> 
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">

<table class="form">	
	<tr>
		<td class="title">E-mail:</td>
		<td><input id="auth_login_email" class="validate[required] text-input wide" type="text" name="login" value=""></td>
	</tr>
	<tr>
		<td class="title">Пароль:</td> 
		<td><input id="auth_login_passwd" class="validate[required] text-input wide" type="password" name="passwd" value=""></td>
	</tr>
 
	<?php if($this->global_vars['is_captcha'] && !empty($this->vars['captcha_html'])): ?>
		<tr>
			<td class="title">Число на картинке<span class="asterix">*</span>:</td>
			<td>
				<table cellspacing="0" cellpadding="0"><tr><td><?php echo $this->vars['captcha_html']; ?></td><td><input type="text" maxlength="6" size="6" name="captcha_value"></td></tr></table>
				<span class="comment">Введите число, показанное на картинке</span>
			</td>
		</tr>
	<?php endif; ?>
	 
	<tr>
		<td class="title"></td>
		<td><a href="/User/Reminder/">Напомнить пароль?</a></td>
	</tr>
	<tr>
		<td class="title"></td> 
		<td><input type="checkbox" name="remember" value="1" id="remember" checked><label for="remember">Запомнить меня на этом компьютере</label></td>
	</tr>
	<tr>
		<td class="title"></td>
		<td><input type="submit" value="Вход"></td>
	</tr>
</table>
</form>