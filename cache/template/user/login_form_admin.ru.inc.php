
<div class="form">
	<div class="block">
		<form name="LoginForm" action="/<?php echo LANGUAGE_URL; ?>action/cms/login/" method="POST">
			<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
			<input type="hidden" name="source" value="admin">
			
			<br>Не забудьте, логин и пароль чувствительны к регистру букв.<BR><BR>
			
			<B>Логин:</B><BR><input class="text border" type="text" id="login_id" name="login" value=""><BR><BR>
			<B>Пароль:</B><BR><input class="text border" type="password" name="passwd"><BR><BR> 
			
			<?php if($this->global_vars['is_captcha'] && !empty($this->vars['captcha_html'])): ?>
				<b>Число на картинке:</b><br/>
				<input autocomplete="off" class="text border" type="text" name="captcha_value">
				<div id="captcha_html"><?php echo $this->vars['captcha_html']; ?></div><br/>
			<?php endif; ?>
			
			<input type="checkbox" checked value="1" name="remember" id="remember"><label for="remember">Запомнить меня</label><BR><BR>
			<input class="button border" type="submit" value="ВХОД"> 
			 
			&nbsp;&nbsp;<a class="forgot" href="?amnesia=1">напомнить пароль?</a>
		</form>		
			
		<?php echo TemplateUDF::oid_widget(array('name'=>"error_registered_auth_form",'template'=>"context")); ?>
		<br/><br/>
	</div>
</div>
			