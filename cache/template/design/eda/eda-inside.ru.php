<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1251">
	<META name="Keywords" content="<?php echo $this->vars['keywords']; ?>">
	<META name="Description" content="<?php echo $this->vars['description']; ?>">
	<META name="X-Change-Frequency" content="<?php echo $this->vars['change_frequency']; ?>">
	<META name="X-Page-Priority" content="<?php echo $this->vars['page_priority']; ?>">
	<META name="X-Last-Modified" content="<?php echo $this->vars['last_modified']; ?>">
	<LINK href="<?php echo CMS_URL; ?>favicon.ico" rel="shortcut icon">
	<title><?php echo $this->vars['title']; ?></title>

	<!-- стили шаблона /-->
	<link href="/design/eda/css/main.css" rel="stylesheet" type="text/css" media="screen, projection" />
	<link href="/design/eda/css/form_overlow.css" rel="stylesheet" type="text/css" media="screen, projection" />
		<!-- скрипты шаблона /-->
	<script type="text/javascript" src="/design/eda/js/jquery-1.6.4.js"></script>
	<script type="text/javascript" src="/design/eda/js/jquery.roundabout.js"></script>
	<script type="text/javascript" src="/design/eda/js/start.js"></script>	
	<script type="text/javascript" src="/design/eda/js/imagesize.js"></script>	
	

	<!-- скрипты по умолчанию /-->
	<SCRIPT type="text/javascript" language="JavaScript" src="/js/shared/jshttprequest.js"></SCRIPT>
	<SCRIPT type="text/javascript" language="JavaScript" src="/js/shoporder/shoporder.js"></SCRIPT>
	<SCRIPT type="text/javascript" language="JavaScript" src="/js/shop/shop.js"></SCRIPT> 
	<!--<SCRIPT type="text/javascript" language="JavaScript" src="/js/shared/global.js"></SCRIPT>/-->
	<SCRIPT type="text/javascript" language="JavaScript" src="/design/eda/js/global.js"></SCRIPT>
	
	<script type="text/javascript" src="/design/eda/js/jquery.simplemodal.js"></script>	
	<script type="text/javascript" src="/design/eda/js/function.js"></script>	

	<script type="text/javascript">
		var minopacity = -0.1;
		var maxopacity = 0.9;
	</script>
	<!--[if lt IE 9]>
			<link href="/design/eda/css/style-ie-lt8.css" rel="stylesheet" type="text/css" />
			<script src="/design/eda/js/start-ie.js"></script>		
			<script type="text/javascript">
				var minopacity = 1.0;
				var maxopacity = 1.0;
			</script>
	<![endif]-->
		<!--[if IE]>
			<link href="/design/eda/css/style-ie.css" rel="stylesheet" type="text/css" />			
		<![endif]-->

</head>

<body>

<!-- Вся страница /-->
<div id="wrap">

	
		<div id="wrap-header"><div class="content">
		<!-- Шапка /-->

		<div id="header"> 
			<a class="logo" alt="" title="Козачок" href="/"></a>
			
			<div class="search"><form action="/search/" method="GET" name="search_form">
						<input type="text" id="keyword_serch" name="text" value="Поиск" class="search_input_text" onblur="if(this.value=='') this.value='Поиск';" onfocus="if(this.value=='Поиск') this.value='';">
					</form>
			</div>
			
			<div class="logining">
				<?php
			reset($this->vars['/top_menu/'][$__key]);
			while(list($_top_menu_key,) = each($this->vars['/top_menu/'][$__key])):
			?>
					<?php if(empty($this->vars['/top_menu/'][$__key][$_top_menu_key]['url'])): ?>
					<span><?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['name']; ?></span>
					<?php else: ?>
					<a href="<?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['url']; ?>"><?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['name']; ?></a>
					<?php endif; ?>
				<?php 
			endwhile;
			?>
			</div>
			
			<div class="phone">044 345 300 200</div>
		</div>

		<!-- menu /-->
		<div id="container">
			<div class="topmenu">
					<ul class="menu">
						<?php
			reset($this->vars['/catalogmenu/'][$__key]);
			while(list($_catalogmenu_key,) = each($this->vars['/catalogmenu/'][$__key])):
			?>
								<li><a href="<?php echo CMS_URI; ?>/<?php echo $this->vars['/catalogmenu/'][$__key][$_catalogmenu_key]['url']; ?>/"><img src="<?php echo $this->vars['/catalogmenu/'][$__key][$_catalogmenu_key]['image']; ?>" class="expando" width="107px" height="107px"/></a></li>
						<?php 
			endwhile;
			?>
					</ul>
			</div>
			
		<a href="/ShopOrder/" id="cart">
				<div class="cart" >
					<ul>
						<li class="ico"><span>в корзине:</span> <span id="bsk-amount"><?php echo $this->vars['amount']; ?> 
							<?php if($this->vars['amount'] == 1): ?>
								товар
							<?php elseif(($this->vars['amount'] >= 2)&&($this->vars['amount'] <= 4)): ?>
								товара
							<?php else: ?>
								товаров
							<?php endif; ?>
							</span>
						</li>
						<li><span>на сумму:</span> <span id="bsk-pr"><?php echo $this->vars['sum']; ?></span> грн.						
						</li>
					</ul>
				</div><!--cart end-->
			
			</a>
		</div>

		<div class="container">
			
			<table class="titlePage" cellspacing="0" cellpadding="0"><tr><td class="tp-left"></td><?php if($this->vars['headline']): ?><td class="tp-middle" ><h1><?php echo $this->vars['headline']; ?></h1></td><?php endif; ?><td class="tp-right"></td></tr></table>
			
			
			<?php echo $this->vars['content']; ?>
			
		</div>

	
		
	</div>
	</div>
	<div id="wrap-content">

			<div class="content">
							
				<div class="banner marginTop">
					<?php
			reset($this->vars['/banner_bottom/'][$__key]);
			while(list($_banner_bottom_key,) = each($this->vars['/banner_bottom/'][$__key])):
			?>
					<a href="<?php echo CMS_URI; ?><?php echo $this->vars['/banner_bottom/'][$__key][$_banner_bottom_key]['link']; ?>"><img src="<?php echo $this->vars['/banner_bottom/'][$__key][$_banner_bottom_key]['image_url']; ?>" ></a>
					<?php 
			endwhile;
			?>
				</div>
				
			</div>
		<div id="hFooter"></div>
		</div>
		
	
</div>
<div id="footer">
	<div id="footer-wrap">
	<div class="footer-menu">
	<?php
			reset($this->vars['/catalogmenu/'][$__key]);
			while(list($_catalogmenu_key,) = each($this->vars['/catalogmenu/'][$__key])):
			?>
	<a href="<?php echo CMS_URI; ?>/<?php echo $this->vars['/catalogmenu/'][$__key][$_catalogmenu_key]['url']; ?>/"><?php echo $this->vars['/catalogmenu/'][$__key][$_catalogmenu_key]['name']; ?></a>
	<?php 
			endwhile;
			?>
	</div>
	</div>
</div>
</body>
</html>