<div class="slider-wrapper theme-default">
    <div id="slider" class="nivoSlider">
		<?php
			reset($this->vars['/banner/'][$__key]);
			while(list($_banner_key,) = each($this->vars['/banner/'][$__key])):
			?>
			<a href="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['link']; ?>" title="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['title']; ?>"><img src="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['image']; ?>" alt="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['description']; ?>" /></a>
		<?php 
			endwhile;
			?>
    </div>
</div>
<div id="htmlcaption" class="nivo-html-caption">
</div>
				
