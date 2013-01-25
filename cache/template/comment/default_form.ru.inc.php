<br>
<?php if(COMMENT_NOT_REGISTER || Auth::isLoggedIn()): ?> 
	<a name="comment"></a>
	
	<input type="button" class="comment_button" value="Добавить комментарий" onclick="$('#form_start').css('display', 'block');return false;">
	<div id="form_start" class="comment_form"  <?php if($this->vars['display']): ?>style="display:block"<?php endif; ?>>
		<form id="realform_start" action="/<?php echo LANGUAGE_URL; ?>action/comment/add/" method="POST">
			<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
			<input type="hidden" name="id" value="0">
			<input type="hidden" name="object_id" value="<?php echo $this->vars['object_id']; ?>">
			<input type="hidden" name="table_name" value="<?php echo $this->vars['table_name']; ?>">
			<?php if(!Auth::isLoggedIn() && COMMENT_NOT_REGISTER): ?>
				<table cellpadding="0" cellspacing="5">
					<tr>
						<td>Имя:</td>
						<td><input name="user_name" type="text" value="<?php echo $this->vars['new_user_name']; ?>" /></td>
					</tr>
					<tr>
						<td>E-mail:</td>
						<td><input name="user_email" type="text" value="<?php echo $this->vars['new_user_email']; ?>" /></td>
					</tr>
					<tr>
						<td><?php echo $this->vars['captcha_html']; ?></td>
						<td valign="middle"><input type="text" maxlength="6" size="6" name="captcha_value"></td> 
					</tr>
					<tr>
						<td colspan="2"><textarea name="comment"><?php echo $this->vars['new_comment']; ?></textarea></td>
					</tr>
				</table>		
			<?php else: ?>
				<textarea id="textarea_comment_start" name="comment" class="comment_textarea"><?php echo $this->vars['comment']; ?></textarea>
			<?php endif; ?>
			<span style="font-size:10px; color:grey;">Ваш комментарий будет добавлен после проверки администратором</span><br />
		
			<input type="button" class="comment_button" value="Написать" onclick="$('#realform_start').submit();">
			<input type="button" class="comment_button" value="Отмена"  onclick="$('#form_start').css('display', 'none'); return false;">
		</form>
	</div>
<?php else: ?>
	<a href="/user/register/"><b>Только зарегистрированные пользователи могут оставлять комментарии</b></a>
<?php endif; ?>
