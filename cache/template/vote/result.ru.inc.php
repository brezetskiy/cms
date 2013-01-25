<div class="vote-title"><?php echo $this->vars['question']; ?></div>
<div id="vote-line">
<?php
			reset($this->vars['/answer/'][$__key]);
			while(list($_answer_key,) = each($this->vars['/answer/'][$__key])):
			?>
	<div class="vote_line">
		<div class="vote_line_left"><?php echo $this->vars['/answer/'][$__key][$_answer_key]['answer']; ?></div>
		<div class="vote_line_right"><?php echo $this->vars['/answer/'][$__key][$_answer_key]['percent']; ?>%</div>
		<div class="ground">
			<?php if($this->vars['/answer/'][$__key][$_answer_key]['percent']!=0): ?>
			<div class="progress" style="width: <?php echo $this->vars['/answer/'][$__key][$_answer_key]['percent_int']; ?>%;"></div>
			<?php endif; ?>
		</div>
	</div>
<?php 
			endwhile;
			?>
<div class="count_question">Всего голосов: <?php echo $this->vars['total']; ?></div>
</div>