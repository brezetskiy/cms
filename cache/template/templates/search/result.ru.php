<div class="post">

<div class="footer-search"><form action="/search/" method="GET" name="search_form">				
                    <input type="text" id=keyword_serch name=text value="<?php echo $this->vars['search_string']; ?>" class="search_input_text"  />
				</form></div>
<br />
<?php if(strlen($this->vars['text']) < 4): ?>
	<p>Слишком короткое условие поиска. Минимум - 4 символа.</p>
<?php elseif($this->vars['rows'] == 0): ?>
	<p>Ничего не найдено. Попробуйте изменить условия поиска.</p>
<?php else: ?>
	<table border="0" width="100%" cellpadding="10" cellspacing="0" class="search_result">
	<?php
			reset($this->vars['/search_result/'][$__key]);
			while(list($_search_result_key,) = each($this->vars['/search_result/'][$__key])):
			?>
	    <p><a href="<?php echo $this->vars['/search_result/'][$__key][$_search_result_key]['url']; ?>"><?php echo $this->vars['/search_result/'][$__key][$_search_result_key]['index']; ?>. <?php echo $this->vars['/search_result/'][$__key][$_search_result_key]['title']; ?></a><br/><br/>
		<?php echo $this->vars['/search_result/'][$__key][$_search_result_key]['content']; ?>...</p>
	<?php 
			endwhile;
			?>
	</table>
	
	<!--<?php if($this->vars['order'] == 'rel'): ?>
		<b>Отсортировано по релевантности</b> | <a href="javascript:search('date');">Сортировать по дате</a>
	<?php else: ?>
		<a href="javascript:search('rel');">Сортировать по релевантности</a> | <b>Отсортировано по дате</b>	
	<?php endif; ?>-->
<?php endif; ?>
</div>
