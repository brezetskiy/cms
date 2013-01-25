<!--<form action="/<?php echo LANGUAGE_URL; ?>action/search/result/" onsubmit="search('rel'); return false;" id="search">-->
<div class="search clearfix" style="float: none; padding-top: 0px;">
  <form action="/search/" method="GET" name="search_form" id="search">
		<input class="search-field" type="text" name="text" value="<?php echo $this->vars['search_string']; ?>" title="Поиск по сайту" />
		<input type="submit" value="">
  </form>
</div>
<!--<input class="search__text" type="text" name="text" maxlength="100" title="Поиск" value="<?php echo $this->vars['search_string']; ?>"/>
<input class="search__btn" type="submit" value="" />
<input type="hidden" name="site_id" value="<?php echo $this->vars['site_id']; ?>">
<input type="hidden" name="table" value="">
<input type="hidden" class="page_list" value="<?php echo $this->vars['page_start']; ?>" name="_offset[0]">
</form>-->
<br>
<br>
<div id="result">
</div>