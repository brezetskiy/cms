<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "xhtml11.dtd">
<html>
<head>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1251">
	<META name="Keywords" content="<?php echo $this->vars['keywords']; ?>">
	<META name="Description" content="<?php echo $this->vars['description']; ?>">

	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=1;" />
	
	<TITLE><?php echo $this->vars['title']; ?></TITLE> 
	<LINK href="<?php echo CMS_URL; ?>favicon.ico" rel="shortcut icon">
	
	<!-- СТИЛИ ДИЗАЙНА -->
	<link rel="stylesheet" type="text/css" href="/design/dev/css/main.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/design/dev/css/style-inside.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/design/dev/css/layout.css" media="screen" />	

	<!-- СКРИПТЫ ДИЗАЙНА -->
	<script src="http://cdn.jquerytools.org/1.2.5/full/jquery.tools.min.js"></script>	
	<script type="text/javascript" src="/design/dev/js/form.js"></script>
	
	<!-- ПО УМОЛЧАНИЮ -->
	<script type="text/javascript" src="/js/shared/jshttprequest.js"></script>
	<script type="text/javascript" src="/js/shared/global.js"></script>
	
				<!--[if IE 7]>
		<link href="/design/dev/css/style-ie.css" rel="stylesheet" type="text/css" />
		<![endif]-->
	
</head> 

<body>

<body>
<!-- Вся страница /-->

<div id="wrap" class="page-inside">

	<!-- Основная часть страницы /-->
	<div id="wrap-content">	<div id="wrap-content1"><div id="wrap-content2">
	<div id="body_right"></div>
    <!-- Шапка /-->
    <div id="header"> 
		<div id="phone">наш телефон <br /><span>044</span>&nbsp;&nbsp;<span class="phone">3740626</span></div>
		<div class="logo"><a href="/" title="Sano Corpore" id="header_logo"></a></div>
		<div id="header-image"><img src="/design/dev/img/sanocorpore/header-st-page.png" title="Image"/></div>
		
	</div>
	<!-- Шапка /-->
	
	<!-- Основной текст /-->
	<div id="content">
	
		<!-- begin right-column /-->
		<div id="right-column" >
			<!-- begin right menu /-->
			<div id="right-menu">
				<ul class="menu">
				<?php
			reset($this->vars['/top_menu/'][$__key]);
			while(list($_top_menu_key,) = each($this->vars['/top_menu/'][$__key])):
			?>				
				<li class="menu__list__item"><a href="<?php echo CMS_URI; ?><?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['url']; ?>" class="menu__list__item__link <?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['class']; ?>" <?php if($this->vars['/top_menu/'][$__key][$_top_menu_key]['clickable']==0): ?>onClick="return false;"<?php endif; ?>><?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['name']; ?></a>
				<ul class="submenu__list" <?php if($this->vars['/top_menu/'][$__key][$_top_menu_key]['submenuactive']): ?>style="display:block"<?php endif; ?>>
				<?php if($this->vars['/top_menu/'][$__key][$_top_menu_key]['show_submenu']): ?>
							
								<?php
			reset($this->vars['/top_menu/submenu/'][$_top_menu_key]);
			while(list($_top_menu_submenu_key,) = each($this->vars['/top_menu/submenu/'][$_top_menu_key])):
			?>
									<li class="submenu__list__item"><a href="<?php echo $this->vars['/top_menu/submenu/'][$_top_menu_key][$_top_menu_submenu_key]['url']; ?>" class="submenu__list__item__link <?php echo $this->vars['/top_menu/submenu/'][$_top_menu_key][$_top_menu_submenu_key]['class']; ?> "><?php echo $this->vars['/top_menu/submenu/'][$_top_menu_key][$_top_menu_submenu_key]['name']; ?></a>
									<?php if($this->vars['/top_menu/submenu/'][$_top_menu_key][$_top_menu_submenu_key]['show_menu']): ?>
										<div class="submenu__in">
											<ul class="submenu__in__list">
											<?php
			reset($this->vars['/top_menu/submenu/nextlevel/'][$_top_menu_submenu_key]);
			while(list($_top_menu_submenu_nextlevel_key,) = each($this->vars['/top_menu/submenu/nextlevel/'][$_top_menu_submenu_key])):
			?>
												<li class="submenu__list__item"><a href="http://<?php echo $this->vars['/top_menu/submenu/nextlevel/'][$_top_menu_submenu_key][$_top_menu_submenu_nextlevel_key]['url']; ?>" class="submenu__list__item__link "><span>&nbsp;</span><?php echo $this->vars['/top_menu/submenu/nextlevel/'][$_top_menu_submenu_key][$_top_menu_submenu_nextlevel_key]['name']; ?></a></li>
											<?php 
			endwhile;
			?>
											<ul>
										</div>
									<?php endif; ?>
									</li>
								<?php 
			endwhile;
			?>
					<?php endif; ?>
					</ul>
				</li>
				<?php 
			endwhile;
			?>
			</ul>
			</div>
			<!-- end right menu /-->
			
			<!-- banner /-->
			<div id="block-banner">
				<?php
			reset($this->vars['/banners_right/'][$__key]);
			while(list($_banners_right_key,) = each($this->vars['/banners_right/'][$__key])):
			?>
					<a href="<?php echo CMS_URI; ?><?php echo $this->vars['/banners_right/'][$__key][$_banners_right_key]['link']; ?>"><img src="<?php echo $this->vars['/banners_right/'][$__key][$_banners_right_key]['image_url']; ?>" title="" width="277px" height="119px" class="img"/></a>					
				<?php 
			endwhile;
			?>
			</div>
			<!-- end banner /-->
		</div>
		<!-- end right-column /-->

								
		<!-- begin center-column /-->
		<div id="center-column" class="center-column-inside">
			<!-- Навигация /-->
			<div id="breadcrumbs">
				<div>
					<?php
			reset($this->vars['/path/'][$__key]);
			while(list($_path_key,) = each($this->vars['/path/'][$__key])):
			?>
						<a href="<?php echo $this->vars['/path/'][$__key][$_path_key]['url']; ?>"><?php echo $this->vars['/path/'][$__key][$_path_key]['name']; ?></a> 
					<?php 
			endwhile;
			?>
					<?php if($this->vars['last_word_in_path']): ?><span><?php echo $this->vars['last_word_in_path']; ?></span><?php endif; ?>
				</div>
			</div>
			<!-- end Навигация /-->
			<div id="content-wrap">
			<h2><?php echo $this->vars['headline']; ?></h2>
			<?php echo $this->vars['content']; ?>
			</div>
		</div>	
		<!-- end center-column /-->
		<div class="clear">&nbsp;</div>
		
		<?php if($this->vars['show_news']): ?>
		<div class="content-bottom">
			<div class="left title-block"><h2 class="title-m title-news"><a href="<?php echo CMS_URL; ?>arhiv/news">Новости</a></h2></div> 
			<div class="right news_next"><a class="news-prev">&nbsp;</a>&nbsp;&nbsp;<a class="news-next">&nbsp;</a></div>		
			<div class="clear">&nbsp;</div>
			<div class="scrollable-news-inside">
				<div class="items" style="width:6000px;">
					<ul class="news section">
						<?php
			reset($this->vars['/news/'][$__key]);
			while(list($_news_key,) = each($this->vars['/news/'][$__key])):
			?>
						<li>
							<img src="<?php echo $this->vars['/news/'][$__key][$_news_key]['image_src']; ?>" title="News"/>
							<div class="date"><?php echo $this->vars['/news/'][$__key][$_news_key]['day_from']; ?>.&nbsp;<?php echo $this->vars['/news/'][$__key][$_news_key]['month_from']; ?>.&nbsp;<?php echo $this->vars['/news/'][$__key][$_news_key]['year_from']; ?></div>
							<h5><a href="<?php echo $this->vars['/news/'][$__key][$_news_key]['url']; ?>" class="title"><?php echo $this->vars['/news/'][$__key][$_news_key]['headline']; ?></a></h5>
							<p><?php echo $this->vars['/news/'][$__key][$_news_key]['desc']; ?>
							</p>
						</li>
						<?php if($this->vars['/news/'][$__key][$_news_key]['i'] == 0 ): ?></ul><ul class="section news"><?php endif; ?>
						<?php 
			endwhile;
			?>
					</ul>					
			</div></div>
		</div>
		<?php endif; ?>
		
	</div>
	<!-- #end Основной текст /-->
	
	</div></div></div>
	<!--End Основная часть страницы /-->
	<div id="hFooter"></div>
</div>
<div id="footer"><div class="footer-top"></div>
		<div class="footer-wrap">
				<div id="phone">наш телефон <br /><span>044</span>&nbsp;&nbsp;<span class="phone">5903400</span></div>
				<div class="footer-logo"></div>
				<div class="footer-search"><form action="/search/" method="GET" name="search_form">				
                    <input type="text" id=keyword_serch name=text value="Поиск" class="search_input_text"  onblur="if(this.value=='') this.value='Поиск';" onfocus="if(this.value=='Поиск') this.value='';" />
				</form></div>
		</div>
</div>
<script type="text/javascript" src="/design/dev/js/global.js"></SCRIPT>

</body>
</html>