        		<!-- Start photoslider-bullets -->
				<div class="sliderkit photoslider-bullets">
					<div class="sliderkit-nav">
						<div class="sliderkit-nav-clip">
							<ul>
								<?php
			reset($this->vars['/banner/'][$__key]);
			while(list($_banner_key,) = each($this->vars['/banner/'][$__key])):
			?>
								<li><a href="#" title="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['title']; ?>"></a></li>
								<?php 
			endwhile;
			?>
							</ul>
						</div>
					</div>
					<div class="sliderkit-panels">
						<?php
			reset($this->vars['/banner/'][$__key]);
			while(list($_banner_key,) = each($this->vars['/banner/'][$__key])):
			?>
						<div class="sliderkit-panel">
							<a href="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['link']; ?>" title="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['title']; ?>"><img src="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['image']; ?>" alt="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['description']; ?>" /></a>
						</div>
						<?php 
			endwhile;
			?>

					</div>
				</div>
				<!-- // end of photoslider-bullets -->
				
