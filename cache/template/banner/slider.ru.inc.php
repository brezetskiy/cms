        <div class="wrap_slider">
            <div id="slider">
				<?php
			reset($this->vars['/banner/'][$__key]);
			while(list($_banner_key,) = each($this->vars['/banner/'][$__key])):
			?>
                <a target="<?php if($this->vars['/banner/'][$__key][$_banner_key]['new_window'] == 'true'): ?>_blank<?php else: ?>_top<?php endif; ?>" href="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['link']; ?>">
					<div class="slider_border"> 
						<img src="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['image']; ?>" <?php echo $this->vars['/banner/'][$__key][$_banner_key]['tag_attr']; ?> border="0" alt="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['title']; ?>" class="slider_photo"/>
						<?php if(!empty($this->vars['/banner/'][$__key][$_banner_key]['title'])): ?><div class="desc"><span><?php echo $this->vars['/banner/'][$__key][$_banner_key]['title']; ?></span><br/><?php if(!empty($this->vars['/banner/'][$__key][$_banner_key]['description'])): ?><span><?php echo $this->vars['/banner/'][$__key][$_banner_key]['description']; ?></span><?php endif; ?></div><?php endif; ?>
					</div>
				</a>
				<?php 
			endwhile;
			?>
            </div>

            <div id="slider_ball"> 
            </div>
        </div>
