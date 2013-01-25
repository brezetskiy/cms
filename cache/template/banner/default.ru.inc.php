<?php
			reset($this->vars['/banner/'][$__key]);
			while(list($_banner_key,) = each($this->vars['/banner/'][$__key])):
			?>
<?php if($this->vars['/banner/'][$__key][$_banner_key]['type'] == 'flash'): ?> 
	<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" <?php echo $this->vars['/banner/'][$__key][$_banner_key]['tag_attr']; ?> id="banner_<?php echo $this->vars['/banner/'][$__key][$_banner_key]['id']; ?>" ALIGN="">
		<PARAM NAME="movie" VALUE="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['image_url']; ?>?link=<?php echo $this->vars['/banner/'][$__key][$_banner_key]['link']; ?>">
		<PARAM NAME="quality" VALUE="high">
		<PARAM name="wmode" value="opaque">
		<PARAM NAME="bgcolor" VALUE="#FFFFFF">
		<EMBED wmode="opaque" src="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['image_url']; ?>?{flash_var}=<?php echo $this->vars['/banner/'][$__key][$_banner_key]['link']; ?>" quality="high" bgcolor="#FFFFFF" <?php echo $this->vars['/banner/'][$__key][$_banner_key]['tag_attr']; ?> NAME="banner_<?php echo $this->vars['/banner/'][$__key][$_banner_key]['id']; ?>" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
	</OBJECT>
<?php elseif($this->vars['/banner/'][$__key][$_banner_key]['type'] == 'html'): ?>
	<?php echo $this->vars['/banner/'][$__key][$_banner_key]['html']; ?>
<?php else: ?>
	<a target="<?php if($this->vars['/banner/'][$__key][$_banner_key]['new_window'] == 'true'): ?>_blank<?php else: ?>_top<?php endif; ?>" href="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['link']; ?>"><img src="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['image_url']; ?>" <?php echo $this->vars['/banner/'][$__key][$_banner_key]['tag_attr']; ?> border="0" alt="<?php echo $this->vars['/banner/'][$__key][$_banner_key]['title']; ?>"></a>
<?php endif; ?>
<?php 
			endwhile;
			?>