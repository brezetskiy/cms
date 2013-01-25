<script type="text/javascript" language="JavaScript" src="/js/shared/currency.js"></script>
<div id="currency_switcher" class="currency_switcher">
	<input type="hidden" name="currency_default" value="<?php echo CURRENCY_DEFAULT; ?>">

    Показать цены в: 
	<?php
			reset($this->vars['/rates/'][$__key]);
			while(list($_rates_key,) = each($this->vars['/rates/'][$__key])):
			?>
		<input type="hidden" name="currency_label_<?php echo $this->vars['/rates/'][$__key][$_rates_key]['currency_to_id']; ?>" value="<?php echo $this->vars['/rates/'][$__key][$_rates_key]['symbol']; ?>">
		<input type="hidden" name="currency_rate_<?php echo $this->vars['/rates/'][$__key][$_rates_key]['currency_from_id']; ?>_<?php echo $this->vars['/rates/'][$__key][$_rates_key]['currency_to_id']; ?>" value="<?php echo $this->vars['/rates/'][$__key][$_rates_key]['rate']; ?>">
		<a name="currency_switch_<?php echo $this->vars['/rates/'][$__key][$_rates_key]['currency_to_id']; ?>" href="javascript:void(0);" onclick="currency_change('<?php echo $this->vars['/rates/'][$__key][$_rates_key]['currency_to_id']; ?>');"><?php echo $this->vars['/rates/'][$__key][$_rates_key]['code']; ?></a> 
	<?php 
			endwhile;
			?>
</div>
<div class="delfloat"></div>