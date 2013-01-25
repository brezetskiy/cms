<?php
			reset($this->vars['/comment/'][$__key]);
			while(list($_comment_key,) = each($this->vars['/comment/'][$__key])):
			?>
	<div id="comment_<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>" style="margin-left:20px;">
		<div class="comment">
			<div class="comment_text">
				<img class="abs1" src="/img/comment/corner_l_t.gif" />	
				<img class="abs2" src="/img/comment/corner_t_r.gif" />
				<div id="comment-content-<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>" style="padding:5px;"><?php echo $this->vars['/comment/'][$__key][$_comment_key]['comment']; ?></div>
				<img class="abs3" src="/img/comment/corner_b_r.gif" />
				<img class="abs4" src="/img/comment/corner_b.gif" />	
			</div>
		</div>	
		<div class="comment_delfloat"></div>
		<div class="comment_author">
			Написал <b><?php echo $this->vars['/comment/'][$__key][$_comment_key]['login']; ?></b> <?php echo $this->vars['/comment/'][$__key][$_comment_key]['date']; ?> в <?php echo $this->vars['/comment/'][$__key][$_comment_key]['time']; ?>
			<?php if(Auth::isLoggedIn() || COMMENT_NOT_REGISTER): ?> 
				<a class="comment_answer" href="#" onclick="$('#form_<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>').css('display', 'block'); return false;">Ответить</a>
			<?php endif; ?>
			<?php if(Auth::isAdmin()): ?>
				[<?php echo $this->vars['/comment/'][$__key][$_comment_key]['ip']; ?> | <?php echo $this->vars['/comment/'][$__key][$_comment_key]['local_ip']; ?>]
				<a href="/action/admin/comment/delete/?id=<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>" onclick="return confirm('Удаление коментария привидет к удалению всех ответов! Продолжить удаление?')" style="color:red;">Удалить</a>
				<?php if($this->vars['/comment/'][$__key][$_comment_key]['active']): ?>
					<span id="comment_publish_<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>"><a href="#" onclick="Comment.publish(<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>, 0);return false;" style="color:brown;">Запретить</a></span>
				<?php else: ?>
					<span id="comment_publish_<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>"><a href="#" onclick="Comment.publish(<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>, 1);return false;" style="color:green;">Опубликовать</a></span>
				<?php endif; ?>
				<span id="comment_edit_<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>"><a href="javascript:void(0);" onclick="Comment.edit(<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>); return false;" style="color:#1873B4;">Редактировать</a></span>
			<?php endif; ?>
			
		</div>
		<div class="comment_delfloat"></div>
		<?php if(COMMENT_NOT_REGISTER || Auth::isLoggedIn()): ?> 
			<div id="form_<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>" <?php if($this->vars['/comment/'][$__key][$_comment_key]['display']): ?>style="display:block"<?php endif; ?> class="comment_form">
				<form id="realform_<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>" action="/<?php echo LANGUAGE_URL; ?>action/comment/add/" method="POST">
					<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
					<input type="hidden" name="id" value="<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>">
					<input type="hidden" name="object_id" value="<?php echo $this->vars['/comment/'][$__key][$_comment_key]['object_id']; ?>">
					<input type="hidden" name="comment_id" value="<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>">
					<input type="hidden" name="table_name" value="<?php echo $this->vars['/comment/'][$__key][$_comment_key]['table_name']; ?>">
					<?php if(!Auth::isLoggedIn() && COMMENT_NOT_REGISTER): ?>
						<table class="comment_extend" cellpadding="0" cellspacing="5">
							<tr>
								<td>Имя:</td>
								<td><input name="user_name" type="text" value="<?php echo $this->vars['/comment/'][$__key][$_comment_key]['new_user_name']; ?>" /></td>
							</tr>
							<tr>
								<td>E-mail:</td>
								<td><input name="user_email" type="text" value="<?php echo $this->vars['/comment/'][$__key][$_comment_key]['new_user_email']; ?>" /></td>
							</tr>
							<tr>
								<td>
									<?php echo $this->vars['/comment/'][$__key][$_comment_key]['captcha_html']; ?>
								</td>
								<td valign="middle"><input type="text" maxlength="6" size="6" name="captcha_value"></td> 
							</tr>
							<tr>
								<td colspan="2"><textarea name="comment"><?php echo $this->vars['/comment/'][$__key][$_comment_key]['new_comment']; ?></textarea></td>
							</tr>
							
						</table>
					<?php else: ?>
						<textarea id="textarea_comment_<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>" name="comment" class="comment_textarea"><?php echo $this->vars['/comment/'][$__key][$_comment_key]['comment']; ?></textarea>
					<?php endif; ?>
					<span style="font-size:10px; color:grey">Ваш комментарий будет добавлен после проверки администратором</span><br /> 
					<input type="button" class="comment_button" value="Написать" onclick="$('#realform_<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>').submit();">
					<input type="button" class="comment_button" value="Отмена"  onclick="$('#form_<?php echo $this->vars['/comment/'][$__key][$_comment_key]['id']; ?>').css('display', 'none'); return false;">
				</form>	
			</div>
		<?php endif; ?>
		<?php echo $this->vars['/comment/'][$__key][$_comment_key]['subcomment']; ?>			
	</div>
<?php 
			endwhile;
			?>

<div onclick="Comment.hideTask();" id="bg_layer" class="bg_layer"></div>
<div id="task" class="task_layer">
	<div class="tasktitle">
		Редактирование коментария
		<br />
	</div>
	<form action="/<?php echo LANGUAGE_URL; ?>action/comment/edit/" method="POST">
		<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
		<input id="edit_comment_id" type="hidden" name="edit_comment_id" value="<?php echo $this->vars['id']; ?>">
		<div>
			<center>
				<textarea id="edit_commnet" name="edit_comment"></textarea>
			</center>
		</div>
		<br />
		<div>
			<center>
				<input type="submit" value="Сохранить" />
				<input onclick="Comment.hideTask(); return false;" type="button" value="Отменить" />
			</center>
		</div>
	</form>	
	
	
</div>
