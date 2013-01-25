<?php if(empty($this->vars['content'])): ?>	
	<div class="contetn-faq">
	<table>
	<?php
			reset($this->vars['/news/'][$__key]);
			while(list($_news_key,) = each($this->vars['/news/'][$__key])):
			?>
		<tr><td>
			<div class="faq-list">
			<h5><?php echo $this->vars['/news/'][$__key][$_news_key]['headline']; ?></h5>
			<p><?php echo $this->vars['/news/'][$__key][$_news_key]['content']; ?></p>
			<?php if($this->vars['/news/'][$__key][$_news_key]['readmore']): ?><a href="<?php echo CMS_URL; ?><?php echo $this->vars['/news/'][$__key][$_news_key]['url']; ?>" class="readmore dark">Подробнее</a><?php endif; ?>
			</div>
		</td></tr>	
		
	<?php 
			endwhile;
			?>
	</table>
	</div>

<?php else: ?>
	<div id="content-faq">
				
				<h1><?php echo $this->vars['headline']; ?></h1>
				<p><?php echo $this->vars['content']; ?></p>				
		
	</div>


<?php endif; ?>