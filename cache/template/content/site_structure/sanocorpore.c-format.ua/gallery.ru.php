<?php if($this->vars['photocount'] > 0): ?>
	<?php
			reset($this->vars['/photo/'][$__key]);
			while(list($_photo_key,) = each($this->vars['/photo/'][$__key])):
			?>
			<div class="gallery_subunit">
				<a title="" target="_blank" rel="lightbox-content" href="/uploads/<?php echo $this->vars['/photo/'][$__key][$_photo_key]['photo']; ?>" class="lightbox-enabled">
					<img src='/i/cms_gallery/<?php echo $this->vars['/photo/'][$__key][$_photo_key]['photo']; ?>'></a><br>
				<?php echo $this->vars['/photo/'][$__key][$_photo_key]['comment']; ?>
			</div>
	<?php 
			endwhile;
			?>
	<div class="gallery_pages"><?php echo $this->vars['pages_list']; ?></div>
<?php endif; ?>
<?php
			reset($this->vars['/child/'][$__key]);
			while(list($_child_key,) = each($this->vars['/child/'][$__key])):
			?>
	<div class="gallery_subunit"><a href="/Gallery/<?php echo $this->vars['/child/'][$__key][$_child_key]['url']; ?>/"><img src="/i/cms_gallery/<?php echo $this->vars['/child/'][$__key][$_child_key]['photo']; ?>"><br><?php echo $this->vars['/child/'][$__key][$_child_key]['name']; ?></a></div>
<?php 
			endwhile;
			?>
<div style="clear:both;"></div>
<?php if(!empty($_GET['_GALLERY_URL'])): ?><div style="padding:20px 0 0 0;"><a href="/Gallery/<?php echo $this->vars['parent']; ?>" >На уровень выше</a></div><?php endif; ?>
<br><br>
