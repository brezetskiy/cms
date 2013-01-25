<?php if(empty($this->vars['content'])): ?>	
	<div class="contetn-article">
	<table>
	<?php
			reset($this->vars['/news/'][$__key]);
			while(list($_news_key,) = each($this->vars['/news/'][$__key])):
			?>
		<tr><td>
			<?php if($this->vars['/news/'][$__key][$_news_key]['image_src']): ?><img src="<?php echo $this->vars['/news/'][$__key][$_news_key]['image_src']; ?>"  class="img-article" width="<?php echo $this->vars['/news/'][$__key][$_news_key]['image_compress_width']; ?>px" height="<?php echo $this->vars['/news/'][$__key][$_news_key]['image_compress_height']; ?>px"/><?php endif; ?>
			<div class="date"><?php echo $this->vars['/news/'][$__key][$_news_key]['day_from']; ?>.&nbsp;<?php echo $this->vars['/news/'][$__key][$_news_key]['month_from']; ?>.&nbsp;<?php echo $this->vars['/news/'][$__key][$_news_key]['year_from']; ?></div>
			<h5><a href="<?php echo CMS_URL; ?><?php echo $this->vars['/news/'][$__key][$_news_key]['url']; ?>" class="title"><?php echo $this->vars['/news/'][$__key][$_news_key]['headline']; ?></a></h5>
			<?php if($this->vars['/news/'][$__key][$_news_key]['show_subcont'] == 0): ?><p><?php echo $this->vars['/news/'][$__key][$_news_key]['desc']; ?></p><?php endif; ?>
			
		</td></tr>	
		
	<?php 
			endwhile;
			?>
	</table>
	</div>

<?php else: ?>
	<div id="content-article">
				
				<h1><?php echo $this->vars['headline']; ?></h1>
				<p><?php echo $this->vars['content']; ?></p>
				<?php if(!empty($this->vars['archive_name'])): ?>
					<div class="link_arhiv"><a href="/arhiv/article/"><?php echo $this->vars['archive_name']; ?></a></div>
				<?php endif; ?>
		
	</div>


<?php endif; ?>