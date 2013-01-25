<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "xhtml11.dtd">
<html>
<head>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1251">
	<META name="Keywords" content="<?php echo $this->vars['keywords']; ?>">
	<META name="Description" content="<?php echo $this->vars['description']; ?>">
		
	<TITLE><?php echo $this->vars['title']; ?></TITLE> 
	<LINK href="<?php echo CMS_URI; ?>favicon.ico" rel="shortcut icon">
	
	<!-- СТИЛИ ДИЗАЙНА /-->
	<link rel="stylesheet" type="text/css" href="/design/dev/css/main.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/design/dev/css/style-sano.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/design/dev/css/layout.css" media="screen" />	
	<link rel="stylesheet" type="text/css" href="/design/dev/css/contact.css" media="screen" />	

	<!-- СКРИПТЫ ДИЗАЙНА /-->
	<script src="http://cdn.jquerytools.org/1.2.5/full/jquery.tools.min.js"></script>	
	<script type="text/javascript" src="/design/dev/js/form.js"></script>
	
	<!-- ПО УМОЛЧАНИЮ /-->
	<script type="text/javascript" src="/js/shared/jshttprequest.js"></script>
	<script type="text/javascript" src="/js/shared/global.js"></script>
		
	
			<!--[if IE 7]>
		<link href="/design/dev/css/style-ie.css" rel="stylesheet" type="text/css" />
		<![endif]-->
</head> 

<body>
<div id="opaco" class="popup-hidden"></div>
	<div id="popup" class="popup-hidden"></div>
<div id="wrap">

	<!-- Основная часть страницы /-->
	<div id="wrap-content1"><div id="wrap-content2">
	<div id="body_right"></div>

    <!-- Шапка /-->
    <div id="header"> 
		<div id="phone">наш телефон <br /><span>044</span>&nbsp;&nbsp;<span class="phone">3740626</span></div>
		<div class="logo"><a href="/" title="Sano Corpore" id="header_logo"></a></div>
		
		<!-- spec offer /-->
		<div id="specoffer">
			<?php
			reset($this->vars['/news_specoffer/'][$__key]);
			while(list($_news_specoffer_key,) = each($this->vars['/news_specoffer/'][$__key])):
			?>
			<img src="<?php echo $this->vars['/news_specoffer/'][$__key][$_news_specoffer_key]['image_src']; ?>" title="specoffer"/>	
			<div><div>
				<h3><a href="<?php echo CMS_URL; ?><?php echo $this->vars['/news_specoffer/'][$__key][$_news_specoffer_key]['url']; ?>"><span><?php echo $this->vars['/news_specoffer/'][$__key][$_news_specoffer_key]['title1']; ?></span><br/> <?php echo $this->vars['/news_specoffer/'][$__key][$_news_specoffer_key]['title2']; ?></a></h3>
				<p><?php echo $this->vars['/news_specoffer/'][$__key][$_news_specoffer_key]['desc']; ?></p>
			</div></div>
			<?php 
			endwhile;
			?>
		</div>
		<!-- end spec offer /-->
		
		<!-- slider /-->	
		
		<div id="slider"><?php
			reset($this->vars['/banners_slider/'][$__key]);
			while(list($_banners_slider_key,) = each($this->vars['/banners_slider/'][$__key])):
			?>
           <?php if($this->vars['/banners_slider/'][$__key][$_banners_slider_key]['i']==1): ?> <img src="<?php echo $this->vars['/banners_slider/'][$__key][$_banners_slider_key]['image_url']; ?>" alt="" id="sl<?php echo $this->vars['/banners_slider/'][$__key][$_banners_slider_key]['i']; ?>" class="active-slide" usemap="#Navigation"/>
		   <?php else: ?><img src="<?php echo $this->vars['/banners_slider/'][$__key][$_banners_slider_key]['image_url']; ?>" alt="" id="sl<?php echo $this->vars['/banners_slider/'][$__key][$_banners_slider_key]['i']; ?>" usemap="#Navigation"/><?php endif; ?>
      
		<?php 
			endwhile;
			?>  </div>

	
		<div id="slider-ball">
				<a  class="active sl-ball1" rel="1"></a>
				<a  class="sl-ball2" rel="2"></a>
				<a class="sl-ball3" rel="3"></a>
				<a  class="sl-ball4" rel="4"></a>
				<!--<a  class="sl-ball5" rel="5"></a> /-->
		</div>
		<map id="Navigation" name="Navigation">
			<area shape="poly" coords="50, 100, 300, 0, 550, 45, 680, 100, 680, 350, 540, 460, 180, 320" href="#" alt="Информация" />
		</map>
		<!-- end slider /-->
		
		<div id="header_menu">
			<ul class="menu">
				<?php
			reset($this->vars['/top_menu/'][$__key]);
			while(list($_top_menu_key,) = each($this->vars['/top_menu/'][$__key])):
			?>				
				<li class="menu__list__item"><a href="<?php echo CMS_URI; ?><?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['url']; ?>" class="menu__list__item__link" <?php if($this->vars['/top_menu/'][$__key][$_top_menu_key]['clickable']==0): ?>onClick="return false;"<?php endif; ?>><?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['name']; ?></a>
				<?php if($this->vars['/top_menu/'][$__key][$_top_menu_key]['show_submenu']): ?>
							<ul class="submenu__list">
								<?php
			reset($this->vars['/top_menu/submenu/'][$_top_menu_key]);
			while(list($_top_menu_submenu_key,) = each($this->vars['/top_menu/submenu/'][$_top_menu_key])):
			?>
									<li class="submenu__list__item"><a href="<?php echo $this->vars['/top_menu/submenu/'][$_top_menu_key][$_top_menu_submenu_key]['url']; ?>" class="submenu__list__item__link "><?php echo $this->vars['/top_menu/submenu/'][$_top_menu_key][$_top_menu_submenu_key]['name']; ?></a>
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
							</ul>
				<?php endif; ?>
				</li>
				<?php 
			endwhile;
			?>
			</ul>
		</div>
	</div>
	<!-- Шапка /-->
	
	<!-- Основной текст /-->
	<div id="content">
		<div id="left-column">		
			<div class="left title-block"><h2 class="title-m title-news"><a href="<?php echo CMS_URL; ?>arhiv/news">Новости</a></h2></div>
			<div class="left news_next"><a  class="news-prev prev">&nbsp;</a>&nbsp;&nbsp;<a  class="news-next next">&nbsp;</a></div>			
			<div class="clear">&nbsp;</div>
			<div class="scrollable">
				
				<div class="items" style="width:<?php echo $this->vars['left_news_width']; ?>px;">
					<ul id="news" class="section">
					<?php
			reset($this->vars['/news/'][$__key]);
			while(list($_news_key,) = each($this->vars['/news/'][$__key])):
			?>
						<li>
							<img src="<?php echo $this->vars['/news/'][$__key][$_news_key]['image_src']; ?>" alt="" title=""/>
							<div class="date"><?php echo $this->vars['/news/'][$__key][$_news_key]['day_from']; ?>.&nbsp;<?php echo $this->vars['/news/'][$__key][$_news_key]['month_from']; ?>.&nbsp;<?php echo $this->vars['/news/'][$__key][$_news_key]['year_from']; ?></div>
							<h5><a href="<?php echo CMS_URI; ?><?php echo $this->vars['/news/'][$__key][$_news_key]['url']; ?>" class="title"><?php echo $this->vars['/news/'][$__key][$_news_key]['headline']; ?></a></h5>
							<p><?php echo $this->vars['/news/'][$__key][$_news_key]['desc']; ?>
							</p>
						</li>
						<?php if($this->vars['/news/'][$__key][$_news_key]['i'] == 0 ): ?></ul><ul id="news" class="section"><?php endif; ?>
					
					<?php 
			endwhile;
			?>
					</ul>					
				</div>
			</div>

		</div>
		<!-- end left-column /-->
		
		<!-- begin right-column /-->
		<div id="right-column" >
			<!-- block trener /-->
			<div id="block-trener">
				<div class='tr-mask'>
					<a class="trener-prev">&nbsp;</a>&nbsp;&nbsp;<a class="trener-next" >&nbsp;</a>
				</div>	
				<div class="scrolltrener">
					<div class="items" style="width:2000px;">
						<?php
			reset($this->vars['/treners/'][$__key]);
			while(list($_treners_key,) = each($this->vars['/treners/'][$__key])):
			?>
						<div class="trener-right section">								
							<div class="trener-img"><img src="<?php echo $this->vars['/treners/'][$__key][$_treners_key]['photo']; ?>"></div>
						</div>
						<?php 
			endwhile;
			?>
					</div>					
				</div>

				<div id="trener-name-slider">
					<?php
			reset($this->vars['/treners/'][$__key]);
			while(list($_treners_key,) = each($this->vars['/treners/'][$__key])):
			?>
					<div class="blocktrener <?php if($this->vars['/treners/'][$__key][$_treners_key]['i']==0): ?>active<?php endif; ?>" name="<?php echo $this->vars['/treners/'][$__key][$_treners_key]['i']; ?>" rel="<?php echo $this->vars['/treners/'][$__key][$_treners_key]['id']; ?>">
						<div class="name-tr"><span><span class='ul-tr'><?php echo $this->vars['/treners/'][$__key][$_treners_key]['name']; ?></span></span></div>
						<div class="post-tr"><?php echo $this->vars['/treners/'][$__key][$_treners_key]['position']; ?></div>	
					</div>	
					<?php 
			endwhile;
			?>
				</div>
				<div class="tr-q" id='contact-form'><a href="#" class="buttoncontent contact"><span>Задать вопрос</span></a></div>
				
			</div>
			<!-- end block trener /-->
			
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
		<div id="center-column" class="center-column">
				<h2 class="title-m"><a href="<?php echo CMS_URL; ?>arhiv/article">Пресса о нас</a></h2>
					<?php
			reset($this->vars['/article/'][$__key]);
			while(list($_article_key,) = each($this->vars['/article/'][$__key])):
			?>
					<div class="block-center">
						<p><?php echo $this->vars['/article/'][$__key][$_article_key]['desc']; ?> </p>
						<a href="<?php echo CMS_URL; ?><?php echo $this->vars['/article/'][$__key][$_article_key]['url']; ?>" class="readmore dark">Подробнее</a>
					</div>
					<?php 
			endwhile;
			?>
				<div class="margintop">&nbsp;</div>
				<div class="block-vote-center">
					<h2 class="title-m">Голосование</h2>
					<div class="block-center">
						<?php echo TemplateUDF::vote(array()); ?>
						<div class="clear">&nbsp;</div>
					</div>
				</div>

		</div>	
		<!-- end center-column /-->
		<div class="clear">&nbsp;</div>
		
	</div>
	<!-- #end Основной текст /-->
	
	</div></div>
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

<script type='text/javascript' src='/design/dev/js/jquery.simplemodal.js'></script>
<script type='text/javascript' src='/design/dev/js/contact.js'></script>

</body>
</html>