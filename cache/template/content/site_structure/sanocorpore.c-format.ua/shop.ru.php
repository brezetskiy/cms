<?php if($this->vars['show'] == 'product'): ?>

<div class="tovar-info-block">
<?php
			reset($this->vars['/item/'][$__key]);
			while(list($_item_key,) = each($this->vars['/item/'][$__key])):
			?>
	<div class="product marginTop">

			<div class="image"><img src="<?php echo $this->vars['/item/'][$__key][$_item_key]['img']; ?>"></div>
				<div class="item">					
					<h1><?php echo $this->vars['/item/'][$__key][$_item_key]['name']; ?></h1>
					<p class="strong"><?php echo $this->vars['/item/'][$__key][$_item_key]['notice']; ?></p>
					<p><?php echo $this->vars['/item/'][$__key][$_item_key]['info']; ?> </p>
					
					<a href="#" onClick="return showFadeCart(<?php echo $this->vars['/item/'][$__key][$_item_key]['id']; ?>, this);" data-amount="1" class="wants">ץמקף</a>
					<div class="price"><span data-price="<?php echo $this->vars['/item/'][$__key][$_item_key]['price']; ?>"><?php echo $this->vars['/item/'][$__key][$_item_key]['price']; ?></span><div class="weight"><span class="re-weight">1000</span> <?php echo $this->vars['/item/'][$__key][$_item_key]['measure']; ?></div></div>						
					<div class="weight-block"><input type="text" value="1" class="amount small" maxlength="2"/>, <input type="text" value="000" maxlength='3' class="amount-s">
					<?php if($this->vars['/item/'][$__key][$_item_key]['measure'] == 'לכ'): ?> כ.<?php elseif($this->vars['/item/'][$__key][$_item_key]['measure'] == 'דנאלל'): ?> ךד.<?php endif; ?></div>
				</div>	
		
			
		</div><!--in-cart-block end-->
	</div><!--product /-->
<?php 
			endwhile;
			?>	
</div><!--tovar-info-block end-->

<?php if($this->vars['is_related']): ?>
<script>
		$(document).ready(function() {
		  $('#roundscroll ul').roundabout({
			 btnNext: ".roundscroll-next",
			 btnPrev: ".roundscroll-prev",
			 minOpacity: minopacity,
			 maxOpacity: maxopacity,
			 duration: 800,
			 minScale: 1.0			  
		  });
	   });
</script>
<div id="roundscroll">
				<div class="go-button roundscroll-prev"><a rel="nofollow" href="#" title="Previous"></a></div>
				<div class="go-button roundscroll-next"><a rel="nofollow" href="#" title="Next"></a></div>
						
				<ul>
					<?php
			reset($this->vars['/related/'][$__key]);
			while(list($_related_key,) = each($this->vars['/related/'][$__key])):
			?>
					<li><a href="/<?php echo $this->vars['/related/'][$__key][$_related_key]['url']; ?>.html"><img src="<?php echo $this->vars['/related/'][$__key][$_related_key]['img']; ?>" /></a></li>					
					<?php 
			endwhile;
			?>
					<?php
			reset($this->vars['/related/'][$__key]);
			while(list($_related_key,) = each($this->vars['/related/'][$__key])):
			?>
					<li><a href="/<?php echo $this->vars['/related/'][$__key][$_related_key]['url']; ?>.html"><img src="<?php echo $this->vars['/related/'][$__key][$_related_key]['img']; ?>" /></a></li>					
					<?php 
			endwhile;
			?>
					
				</ul>
</div>
<?php endif; ?>


<?php elseif($this->vars['show'] == 'catalog'): ?>



				<div class="catalog">
				<?php
			reset($this->vars['/model/'][$__key]);
			while(list($_model_key,) = each($this->vars['/model/'][$__key])):
			?>
				<div class="item marginTop">
					<a href="/<?php echo $this->vars['/model/'][$__key][$_model_key]['url']; ?>.html"><img src="<?php echo $this->vars['/model/'][$__key][$_model_key]['img']; ?>"/></a>
					<div class="title"><a href="/<?php echo $this->vars['/model/'][$__key][$_model_key]['url']; ?>.html"><?php echo $this->vars['/model/'][$__key][$_model_key]['name']; ?> </a></div>
					
					<div class="price"><span data-price="<?php echo $this->vars['/model/'][$__key][$_model_key]['price']; ?>"><?php echo $this->vars['/model/'][$__key][$_model_key]['price']; ?></span><div class="weight"><span class="re-weight">1000</span> <?php echo $this->vars['/model/'][$__key][$_model_key]['measure']; ?></div></div>	
					<a href="#" onClick="return showFadeCart(<?php echo $this->vars['/model/'][$__key][$_model_key]['id']; ?>, this);" class="wants" data-amount="1">ÕÎ×Ó</a>
					<div class="weight-block"><input type="text" value="1" class="amount small" maxlength="2"/>, <input type="text" value="000" maxlength='3' class="amount-s">
					<?php if($this->vars['/model/'][$__key][$_model_key]['measure'] == 'לכ'): ?> כ.<?php elseif($this->vars['/model/'][$__key][$_model_key]['measure'] == 'דנאלל'): ?> ךד.<?php endif; ?></div>
				</div>
				<?php 
			endwhile;
			?>
		</div>

<div class="content-article">		
<?php echo $this->vars['content_ru']; ?>
</div>
<?php endif; ?>
