<?php if($this->vars['question'] != ''): ?>
	<table class="form">
	<tr>
		<td colspan="3"><h2><?php echo $this->vars['question']; ?></h2></td>
	</tr>
	<?php
			reset($this->vars['/answer/'][$__key]);
			while(list($_answer_key,) = each($this->vars['/answer/'][$__key])):
			?>
	<tr>
		<td style="vertical-align:middle;" width="40%"><b><?php echo $this->vars['/answer/'][$__key][$_answer_key]['answer']; ?></b></td>
		<td style="vertical-align:middle;" width="10%"> <?php echo $this->vars['/answer/'][$__key][$_answer_key]['votes']; ?> (<?php echo $this->vars['/answer/'][$__key][$_answer_key]['percent']; ?>%)</td>
		<td style="vertical-align:middle;" width="50%"><img src="/img/vote/bar.gif" alt="<?php echo $this->vars['/answer/'][$__key][$_answer_key]['votes']; ?> (<?php echo $this->vars['/answer/'][$__key][$_answer_key]['percent']; ?>%)" border="0" height="10" width="<?php echo $this->vars['/answer/'][$__key][$_answer_key]['width']; ?>" vspace="10"></td>
	</tr>
	<?php 
			endwhile;
			?>
	<tr>
		<td><b>Итого голосов:</b></td>
		<td><?php echo $this->vars['total']; ?></td>
	</tr>
	<tr>
		<td class="title"></td>
		<td colspan="2">Время проведения опроса: с <?php echo $this->vars['start']; ?> по <?php echo $this->vars['end']; ?></td>
	</tr>
	<?php if($this->vars['comment'] != ''): ?>
	<tr>
		<td><b>Комментарий:</b></td>
		<td colspan="2"><?php echo $this->vars['comment']; ?></td>
	</tr>
	<?php endif; ?>
</table>
<BR>
<BR>
<BR>

	<B>Посмотреть результаты других опросов:</B>
<?php else: ?>
	<b>Посмотреть результаты опросов:</b>
<?php endif; ?>
<ul>
<?php
			reset($this->vars['/vote/'][$__key]);
			while(list($_vote_key,) = each($this->vars['/vote/'][$__key])):
			?>
	<li><a style="<?php echo $this->vars['/vote/'][$__key][$_vote_key]['style']; ?>" href="/Vote/?id=<?php echo $this->vars['/vote/'][$__key][$_vote_key]['id']; ?>"><?php echo $this->vars['/vote/'][$__key][$_vote_key]['question']; ?></a>
<?php 
			endwhile;
			?>
</ul>