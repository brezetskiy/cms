<?php if(!$this->vars['ajax']): ?>
<div class="vote" id="vote">
<?php endif; ?>
<?php echo $this->vars['vote_id']; ?>
	<div class="vote-title"><?php echo $this->vars['question']; ?></div>
	<form action="/<?php echo LANGUAGE_URL; ?>action/vote/vote/" method="post" id="vote_form" class="vote-form" onsubmit="AjaxRequest.form('vote_form', 'Голосуем...');return false;">
		<input type="hidden" name="_return_path" value="/Vote/?id=<?php echo $this->vars['vote_id']; ?>">
		<input type="hidden" name="topic_id" value="<?php echo $this->vars['topic_id']; ?>">
		<input type="hidden" name="structure_id" value="<?php echo $this->vars['structure_id']; ?>">
		<input type="hidden" name="template" value="vote/result">
		<input type="hidden" name="form_name" value="vote_form">
		<div class="voting__body__form">
		<?php
			reset($this->vars['/answer/'][$__key]);
			while(list($_answer_key,) = each($this->vars['/answer/'][$__key])):
			?>			
			<?php if($this->vars['/answer/'][$__key][$_answer_key]['type']=='single'): ?> 
					<input id="answer_<?php echo $this->vars['/answer/'][$__key][$_answer_key]['id']; ?>" type="radio" class="RadioClass" name="vote" value="<?php echo $this->vars['/answer/'][$__key][$_answer_key]['id']; ?>">
					<label for="answer_<?php echo $this->vars['/answer/'][$__key][$_answer_key]['id']; ?>" class="RadioLabelClass"><?php echo $this->vars['/answer/'][$__key][$_answer_key]['answer']; ?></label><br/>
			<?php else: ?>
					<input type="checkbox" name="answer_list[]" id="answer_<?php echo $this->vars['/answer/'][$__key][$_answer_key]['id']; ?>" value="<?php echo $this->vars['/answer/'][$__key][$_answer_key]['id']; ?>"> <label class="answer" for="answer_<?php echo $this->vars['/answer/'][$__key][$_answer_key]['id']; ?>"><?php echo $this->vars['/answer/'][$__key][$_answer_key]['answer']; ?></label>
			<?php endif; ?>			
		<?php 
			endwhile;
			?>
		</div>
		<input type="submit" value="Отправить">
	</form>
<?php if(!$this->vars['ajax']): ?>
	</div>
<?php endif; ?>

		
		


