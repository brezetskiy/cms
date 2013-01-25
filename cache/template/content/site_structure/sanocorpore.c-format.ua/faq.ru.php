<?php if(!$this->vars['onefaq']): ?>	
	<div class="contetn-faq">
	<table>
	<?php
			reset($this->vars['/news/'][$__key]);
			while(list($_news_key,) = each($this->vars['/news/'][$__key])):
			?>
		<tr><td>
			<div class="faq-list">
			<h5><a href="<?php echo CMS_URL; ?><?php echo $this->vars['/news/'][$__key][$_news_key]['url']; ?>" class="title"><?php echo $this->vars['/news/'][$__key][$_news_key]['headline']; ?></a></h5>
			<p><?php echo $this->vars['/news/'][$__key][$_news_key]['content']; ?></p>
			<a href="<?php echo CMS_URL; ?><?php echo $this->vars['/news/'][$__key][$_news_key]['url']; ?>" class="readmore dark">Подробнее</a>
			</div>
		</td></tr>	
		
	<?php 
			endwhile;
			?>
	</table>
	</div>


	<div id="content-faq">
				
				<h1><?php echo $this->vars['headline']; ?></h1>
				<p><?php echo $this->vars['content']; ?></p>				
		
	</div>


<?php endif; ?>