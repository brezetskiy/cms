<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "xhtml11.dtd">
<html>
	<head>
		<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1251">
		<META name="Keywords" content="<?php echo $this->vars['keywords']; ?>">
		<META name="Description" content="<?php echo $this->vars['description']; ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
		<TITLE><?php echo $this->vars['title']; ?> - CMS Delta-X</TITLE>
		<LINK href="<?php echo CMS_URL; ?>favicon.ico" rel="shortcut icon">
		<!-- СТИЛИ ДИЗАЙНА -->
		<link rel="stylesheet" type="text/css" href="/design/dev/css/scw.css" />
		<!-- СТИЛИ ПЛАГИНОВ -->
		<LINK rel="stylesheet" type="text/css" href="/extras/jquery/lightbox/jquery.lightbox.css">
		<!-- ПЛАГИНЫ -->
		<script type="text/javascript" src="/extras/jquery/jquery-1.4.2.min.js"></script>
		<script type="text/javascript" src="/extras/jquery/jquery.jqmodal.js"></script>
		<script type="text/javascript" src="/extras/jquery/jquery.jgrowl.min.js"></script>
		<script type="text/javascript" src="/extras/jquery/jquery.scrollTo-1.4.2.js"></script>
		<SCRIPT type="text/javascript" src="/js/shared/scw.js"></SCRIPT>
		<script type="text/javascript" src="/js/shared/jshttprequest.js"></SCRIPT>
		<script type="text/javascript" src="/js/shared/global.js"></SCRIPT>
		<!-- ПОЛЬЗОВАТЕЛИ -->
		<?php if(is_module('User')): ?> <script type="text/javascript" src="/extras/jquery/jquery.maskedinput-1.2.2.js"></script>
		<script type="text/javascript" src="/js/user/user.js"></script>
		<?php if(AUTH_OID_ENABLE): ?><script type="text/javascript" src="/js/user/oid_widget.js"></script>
		<?php endif; ?>
		<?php endif; ?> <!-- КОММЕНТАРИИ -->
		<?php if(is_module('Comment')): ?>
		<link rel="stylesheet" type="text/css" href="/css/comment/comment.css" />
		<script type="text/javascript" src="/js/comment/comment.js"></script>
		<?php endif; ?> <!-- МАГАЗИН -->
		<?php if(is_module('Shop')): ?>
		<link rel="stylesheet" type="text/css" href="/css/shop/shop.css" />
		<link rel="stylesheet" type="text/css" href="/css/shoporder/style.css" />
		<SCRIPT type="text/javascript" language="JavaScript" src="/js/shoporder/shoporder.js"></SCRIPT>
		<SCRIPT type="text/javascript" language="JavaScript" src="/js/shop/shop.js"></SCRIPT>
		<?php endif; ?> <!-- ТУРАГЕНТ -->
		<?php if(is_module('TourAgent')): ?>
		<link rel="stylesheet" type="text/css" href="/css/touragent/style.css" />
		<script type="text/javascript" src="/js/touragent/touragent.js"></script>
		<?php endif; ?>

		<?php if(is_module('Tour')): ?> <script type="text/javascript" src="/js/tour/tour.js"></script>
		<?php endif; ?> <!-- DELTA СООБЩЕНИЯ -->
		<?php if(CMS_USE_DELTA_MESSAGE): ?>
		<link rel="stylesheet" type="text/css" href="/css/cms/message.css" />
		<script type="text/javascript" src="/js/cms/message.js"></script>
		<script type="text/javascript" src="/extras/jquery/jquery.blockUI.js"></script>
		<?php endif; ?> <!-- АВТОЗАГРУЗКА ФУНКЦИЙ -->
		<script type="text/javascript">
						$().ready(function() {
			<?php
			reset($this->vars['/onload/'][$__key]);
			while(list($_onload_key,) = each($this->vars['/onload/'][$__key])):
			?>
			<?php echo $this->vars['/onload/'][$__key][$_onload_key]['function']; ?>
			<?php 
			endwhile;
			?>

			//	$('#authPlaceholder').jqm({ajax: '/action/cms/login_form/', trigger: 'a.login_trigger'});
			});</script>
		<link rel="stylesheet" type="text/css" href="/design/dev/css/newstyle.css" media="all" />
		<script type="text/javascript" language="JavaScript" src="/js/shared/currency.js"></script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
		<script type="text/javascript" src="/design/dev/js/jquery-ui-1.8.17.custom.min.js" ></script>
		<script type="text/javascript" src="/design/dev/js/jquery.formtips.1.2.5.min.js"></script>
		<script type="text/javascript" src="/design/dev/js/jquery.carouFredSel-5.5.0-packed.js"></script>
		<script type="text/javascript" src="/design/dev/js/custom.radio-check.js"></script>
		<script type="text/javascript" src="/design/dev/js/start.js"></script>
		<script src="/design/dev/js/rating.js" type="text/javascript"></script>
		<script type="text/javascript" src="/extras/jquery/lightbox/jquery.lightbox.js"></script>
	</head>
	<style>
		div.infolayer {
			display: none;
		}
	</style>
	<body>
		<div class="out">
			<div class="header">
				<div class="header_i">
					<div class="top clearfix">
						<div class="logo">
							<a href="/">&nbsp;</a>
						</div><!--end logo-->
						<div class="author">
							<div class="author_i">
								<div class="author__top clearfix">
									<?php if(!Auth::isLoggedIn()): ?> <a class="author__top__anchor" href="/user/login/">Войти на сайт</a>
									<a class="author__top__anchor" href="/user/register/">Зарегистрироватся</a>
									<?php else: ?>
									Рады снова видеть, <b><?php echo $_SESSION['auth']['login']; ?></b>!</a></b> (ID:<?php echo $_SESSION['auth']['id']; ?>) <a class="author__top__anchor" href="/user/info/">Персональные данные</a>
									<!-- <a class="author__top__anchor" href="/user/changepassword/">Сменить пароль</a>-->
									<a class="author__top__anchor" class="top-auth-logout" href="/action/cms/logout/?_return_path=<?php echo CURRENT_URL_LINK; ?>">Выход</a>
									<?php endif; ?>
								</div>
								<div class="author__bot">
									<div class="author__bot__search-out">
										<div class="author__bot__search">
											<input class="tipped" type="text" title="поиск" />
										</div>
									</div>
									<a class="btn" href="#">Найти<span>&nbsp;</span></a>
								</div>
							</div>
						</div><!--end author-->
						<div class="support">
							<div class="support_i">
								<p class="support__text support__text_pb">
									Круглосуточная техподдержка
								</p>
								<div class="support__contact">
									<em class="support__contact__phone">&nbsp;</em>
									<p class="support__contact__text">
										<span>Киев</span> (044) 392-74-33
									</p>
									<a class="support__contact__bird" href="#">&nbsp;</a>
								</div><!--end support__contact-->
								<div class="support__city">
									<ul class="support__city__list">
										<li class="support__city__list__item">
											<span>Харьков</span> (057) 728-39-00
										</li>
										<li class="support__city__list__item">
											<span>Днепропетровск</span> (056) 794-38-31
										</li>
										<li class="support__city__list__item">
											<span>Донецк</span> (062) 210-24-93
										</li>
										<li class="support__city__list__item">
											<span>Львов</span> (032) 229-58-93
										</li>
										<li class="support__city__list__item">
											<span>Одесса</span> (048) 738-57-70
										</li>
										<li class="support__city__list__item">
											<span>Beeline</span> (068) 357-18-70
										</li>
										<li class="support__city__list__item">
											<span>Life</span> (093) 585-42-13
										</li>
										<li class="support__city__list__item">
											<span>Kyivstar</span> (067) 883-97-94
										</li>
										<li class="support__city__list__item">
											<span>MTC</span> (095) 332-15-19
										</li>
										<li class="support__city__list__item">
											<span>Skype</span> ukraine_support
										</li>
									</ul>
								</div><!--end support__city-->
								<p class="support__text support__text_color">
									Мы вам перезвоним!
								</p>
							</div>
						</div><!--end support-->
					
					</div><!--end top-->
					<div id="bg_layer" class="bg_layer" onclick="">
						&nbsp;
					</div>
					<div id="cart_layer" class="cart_layer"></div>
					<div id="infolayer" class="infolayer">
						<div>
							<img class="close" onclick="" class="exit" align="right" src="/design/dev/img/cms/cross.png" alt="Закрыть"/>
							<div id="infolayer_title"></div>
							<div class="line2"></div>
						</div>
						<div id="infolayer_text" style='overflow-y:scroll; height:400px; overflow:auto;'></div>
					</div>
					<div class="tmenu clearfix">
						<em class="tmenu__l">&nbsp;</em>
						<em class="tmenu__r">&nbsp;</em>
						<ul class="tmenu__list clearfix">
							<?php
			reset($this->vars['/top_menu/'][$__key]);
			while(list($_top_menu_key,) = each($this->vars['/top_menu/'][$__key])):
			?>
								<?php if($this->vars['/top_menu/'][$__key][$_top_menu_key]['id']==75555): ?>
								<li class="tmenu__list__item">
									<a href="<?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['url']; ?>" class="tmenu__list__item__anchor">Каталог товаров<span>&nbsp;</span></a>
									<div class="submenu__bird">
										&nbsp;
									</div>
									<div class="submenu-out">
										<div class="submenu">
											<div class="submenu__top"></div>
											<div class="submenu__body clearfix">
												<div class="submenu__body_i">
													<?php
			reset($this->vars['/top_menu/allgroup_right/'][$_top_menu_key]);
			while(list($_top_menu_allgroup_right_key,) = each($this->vars['/top_menu/allgroup_right/'][$_top_menu_key])):
			?>
														<ul class="submenu__list">
															<li class="submenu__item">
																<?php echo $this->vars['/top_menu/allgroup_right/'][$_top_menu_key][$_top_menu_allgroup_right_key]['name']; ?>
															</li>
															<?php
			reset($this->vars['/top_menu/allgroup_right/subgroups/'][$_top_menu_allgroup_right_key]);
			while(list($_top_menu_allgroup_right_subgroups_key,) = each($this->vars['/top_menu/allgroup_right/subgroups/'][$_top_menu_allgroup_right_key])):
			?>
																<li class="submenu__item">
																	<a href="<?php echo $this->vars['/top_menu/allgroup_right/subgroups/'][$_top_menu_allgroup_right_key][$_top_menu_allgroup_right_subgroups_key]['url']; ?>" class="submenu__item__anchor"><?php echo $this->vars['/top_menu/allgroup_right/subgroups/'][$_top_menu_allgroup_right_key][$_top_menu_allgroup_right_subgroups_key]['name']; ?></a>
																</li>
															<?php 
			endwhile;
			?>
														</ul>
													<?php 
			endwhile;
			?>
												</div>
											</div>
										</div>
									</div>
								</li>
								<?php else: ?>
								<li class="tmenu__list__item">
									<a style="<?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['style']; ?>" href=<?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['url']; ?> class="tmenu__list__item__anchor <?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['class']; ?>"><?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['name']; ?><span>&nbsp;</span></a>
								</li>
								<?php endif; ?>
							<?php 
			endwhile;
			?>
						</ul>
					</div><!--end tmenu-->
				</div>
			</div><!--end header-->
			<div class="slider-conteiner clearfix">
				<div class="slider-min">
					<ul class="slider-min__body clearfix" id="foo3">
						<li>
							<div class="slider-min__body__item">
								<div class="slider-min__body__item_i">
									<img src="/design/dev/img/slider_m.png" width="535" height="171" alt="" />
									<h1>АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>телефоны, MP3
									<br />
									планшеты, GPS</h2>
								</div>
							</div>
						</li>
						<li>
							<div class="slider-min__body__item">
								<div class="slider-min__body__item_i">
									<img src="/design/dev/img/slider_m.png" width="535" height="171" alt="" />
									<h1>АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>телефоны, MP3
									<br />
									планшеты, GPS</h2>
								</div>
							</div>
						</li>
						<li>
							<div class="slider-min__body__item">
								<div class="slider-min__body__item_i">
									<img src="/design/dev/img/slider_m.png" width="535" height="171" alt="" />
									<h1>АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>телефоны, MP3
									<br />
									планшеты, GPS</h2>
								</div>
							</div>
						</li>
						<li>
							<div class="slider-min__body__item">
								<div class="slider-min__body__item_i">
									<img src="/design/dev/img/slider_m.png" width="535" height="171" alt="" />
									<h1>АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>телефоны, MP3
									<br />
									планшеты, GPS</h2>
								</div>
							</div>
						</li>
						<li>
							<div class="slider-min__body__item">
								<div class="slider-min__body__item_i"></div>
							</div>
						</li>
					</ul>
					<div class="slider__paginator"></div>
				</div><!--slider-min-->
				<div class="slider-min2">
					<ul class="slider-min2__body clearfix" id="doo">
						<li>
							<div class="slider-min2__body__item">
								<div class="slider-min2__body__item_i">
									<img src="/design/dev/img/slider_m2.png" width="535" height="171" alt="" />
									<h1>АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>к Microsoft Office 2010</h2>
									<h3>антивирус в подарок</h3>
								</div>
							</div>
						</li>
						<li>
							<div class="slider-min2__body__item">
								<div class="slider-min2__body__item_i">
									<img src="/design/dev/img/slider_m2.png" width="535" height="171" alt="" />
									<h1>АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>к Microsoft Office 2010</h2>
									<h3>антивирус в подарок</h3>
								</div>
							</div>
						</li>
						<li>
							<div class="slider-min2__body__item">
								<div class="slider-min2__body__item_i">
									<img src="/design/dev/img/slider_m2.png" width="535" height="171" alt="" />
									<h1>АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>к Microsoft Office 2010</h2>
									<h3>антивирус в подарок</h3>
								</div>
							</div>
						</li>
						<li>
							<div class="slider-min2__body__item">
								<div class="slider-min2__body__item_i">
									<img src="/design/dev/img/slider_m2.png" width="535" height="171" alt="" />
									<h1>АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>к Microsoft Office 2010</h2>
									<h3>антивирус в подарок</h3>
								</div>
							</div>
						</li>
						<li>
							<div class="slider-min2__body__item">
								<div class="slider-min2__body__item_i"></div>
							</div>
						</li>
					</ul>
					<div class="slider__paginator_item"></div>
				</div><!--slider-min2-->
			</div>
			<!--slider-conteiner-->
			<div class="main">
				<div class="main_i clearfix">
					<div class="main__left">
						<!-- ФИЛЬТРЫ МАГАЗИНА -->
						<?php if($this->vars['shop_filter'] || $this->vars['shop_filter_selected']): ?> <!-- Выбранные фильтры -->
						<?php if($this->vars['shop_filter_selected']): ?>
						<div class="filter-selected">
							<div class="filter-selected_i">
								<div class="filter-selected__body">
									<h1>Выбранные фильтры</h1>
									<?php
			reset($this->vars['/filter_selected/'][$__key]);
			while(list($_filter_selected_key,) = each($this->vars['/filter_selected/'][$__key])):
			?>
										<h2><?php echo $this->vars['/filter_selected/'][$__key][$_filter_selected_key]['name']; ?></h2>
										<?php
			reset($this->vars['/filter_selected/value/'][$_filter_selected_key]);
			while(list($_filter_selected_value_key,) = each($this->vars['/filter_selected/value/'][$_filter_selected_key])):
			?>
											<div class="filter-selected__body__line clearfix">
												<a class="filter-selected__body__line__link" href="/action/shop/del_filter/?field_name=<?php echo $this->vars['/filter_selected/value/'][$_filter_selected_key][$_filter_selected_value_key]['field_name']; ?>&value=<?php echo $this->vars['/filter_selected/value/'][$_filter_selected_key][$_filter_selected_value_key]['value']; ?>&group_id=<?php echo $this->vars['/filter_selected/value/'][$_filter_selected_key][$_filter_selected_value_key]['group_id']; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>"> <em class="filter-selected__body__line__btn">&nbsp;</em><?php echo $this->vars['/filter_selected/value/'][$_filter_selected_key][$_filter_selected_value_key]['name']; ?></a>
											</div>
										<?php 
			endwhile;
			?>
									<?php 
			endwhile;
			?>
									<div class="filter-selected__body__line clearfix">
										<a class="filter-selected__body__line__link" href="/action/shop/clear_filter/?_return_path=<?php echo CURRENT_URL_LINK; ?>"> <em class="filter-selected__body__line__btn">&nbsp;</em>Убрать все фильтры</a>
									</div>
								</div>
							</div>
						</div><!--end filter-selected-->
						<?php endif; ?> <!-- Фильтры -->
						<?php if($this->vars['shop_filter']): ?>
						<div class="filter">
							<div class="filter_i">
								<div class="filter__body">
									<h1>Фильтры для выбора </h1>
									<?php
			reset($this->vars['/filter/'][$__key]);
			while(list($_filter_key,) = each($this->vars['/filter/'][$__key])):
			?>
										<h2>Тип</h2>
										<ul class="filter__list">
											<?php
			reset($this->vars['/filter/value/'][$_filter_key]);
			while(list($_filter_value_key,) = each($this->vars['/filter/value/'][$_filter_key])):
			?>
												<li class="filter__list__item clearfix">
													<input type="checkbox" class="niceCheck"  id="<?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['id']; ?>" />
													<!--<img src="design/dev/img/filter_icon.png">-->
													<a class="filter__list__item__link" href="/action/shop/add_filter/?field_name=<?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['field_name']; ?>&value=<?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['value']; ?>&group_id=<?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['group_id']; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>"> <label for="<?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['id']; ?>"><?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['name']; ?>&nbsp;&nbsp;(<?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['amount']; ?>)</label></a>
												</li>
											<?php 
			endwhile;
			?>
										</ul>
									<?php 
			endwhile;
			?>
								</div>
							</div>
						</div>
						<?php endif; ?>
						<?php endif; ?> <!-- ВЫВОД РЕЙТИНГА ПОЛЬЗОВАТЕЛЕЙ ФОРУМА -->
						<?php if($this->vars['show_forum_user_top']): ?>
						<div style="width:240px;">
							<br/>
							<h1>TOP пользователей</h1>
							<table class="forum_top_small">
								<thead>
									<tr>
										<td colspan="2">&nbsp;</td><td>Сообщений</td><td>Репутация</td>
									</tr>
								</thead>
								<?php
			reset($this->vars['/forum_user_top/'][$__key]);
			while(list($_forum_user_top_key,) = each($this->vars['/forum_user_top/'][$__key])):
			?>
									<tr class="<?php echo $this->vars['/forum_user_top/'][$__key][$_forum_user_top_key]['class']; ?>">
										<td><?php echo $this->vars['/forum_user_top/'][$__key][$_forum_user_top_key]['index']; ?>.</td>
										<td class="username"><?php echo $this->vars['/forum_user_top/'][$__key][$_forum_user_top_key]['name']; ?></td>
										<td><a href="/forum/top/post/?user_id=<?php echo $this->vars['/forum_user_top/'][$__key][$_forum_user_top_key]['id']; ?>"><?php echo $this->vars['/forum_user_top/'][$__key][$_forum_user_top_key]['message_count']; ?></a></td>
										<td><?php echo $this->vars['/forum_user_top/'][$__key][$_forum_user_top_key]['reputation']; ?></td>
									</tr>
								<?php 
			endwhile;
			?>
							</table>
							<div style="text-align:right;">
								<a href="/forum/top/">Посмотреть весь рейтинг</a>
							</div>
						</div>
						<?php endif; ?>
						<div class="banner">
							<!-- <img src="/design/dev/img/banner.png" width="263" height="285" alt="" /> -->
							<?php echo TemplateUDF::banner(array('name'=>"left_banner")); ?>
						</div>
						
					</div><!--end main__left-->
					<div class="main__right">
						<?php echo $this->vars['content']; ?>
					</div><!--end main__right-->
				</div>
			</div>
			<div class="footer">
				<div class="footer_i clearfix">
					<div class="fmenu">
						<ul class="fmenu__list clearfix">
							<?php
			reset($this->vars['/top_menu/'][$__key]);
			while(list($_top_menu_key,) = each($this->vars['/top_menu/'][$__key])):
			?>
								<li class="fmenu__list__item">
									<a style="<?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['style']; ?>" href="<?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['url']; ?>" class="fmenu__list__item__anchor <?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['class']; ?>"><?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['name']; ?></a>
								</li>
							<?php 
			endwhile;
			?>
						</ul>
					</div><!--end fmenu-->
					<div class="footer__left clearfix">
						<div class="copy">
							<div class="copy_i">
								<p>
									2012 <a href="www.delta-x.ua">www.delta-x.ua</a>
								</p>
								<p>
									Все права защищены
								</p>
							</div>
						</div>
						<ul class="socnetwork clearfix">
							<li class="socnetwork__item">
								<a href="#" class="socnetwork__anchor__item socnetwork__anchor__item_fb">&nbsp;</a>
							</li>
							<li class="socnetwork__item">
								<a href="#" class="socnetwork__anchor__item socnetwork__anchor__item_vk">&nbsp;</a>
							</li>
							<li class="socnetwork__item">
								<a href="#" class="socnetwork__anchor__item socnetwork__anchor__item_tw">&nbsp;</a>
							</li>
							<li class="socnetwork__item">
								<a href="#" class="socnetwork__anchor__item socnetwork__anchor__item_sk">&nbsp;</a>
							</li>
						</ul><!--end socnetwork-->
					</div><!--end footer__left-->
					<div class="footer__mid">
						<div class="footer__mid_i clearfix">
							<?php
			reset($this->vars['/allgroup_right/'][$__key]);
			while(list($_allgroup_right_key,) = each($this->vars['/allgroup_right/'][$__key])):
			?>
								<ul class="footer__mid__menulist1">
									<?php
			reset($this->vars['/allgroup_right/subgroups/'][$_allgroup_right_key]);
			while(list($_allgroup_right_subgroups_key,) = each($this->vars['/allgroup_right/subgroups/'][$_allgroup_right_key])):
			?>
										<li class="footer__mid__menulist1__item">
											<a href="<?php echo $this->vars['/allgroup_right/subgroups/'][$_allgroup_right_key][$_allgroup_right_subgroups_key]['url']; ?>" class="footer__mid__menulist1__item__anchor"><?php echo $this->vars['/allgroup_right/subgroups/'][$_allgroup_right_key][$_allgroup_right_subgroups_key]['name']; ?></a>
										</li>
									<?php 
			endwhile;
			?>
								</ul>
							<?php 
			endwhile;
			?>
						</div>
					</div><!--end footer__mid-->
					<div class="footer__right">
						<div class="footer__right_i">
							<div class="footer__right__line clearfix">
								<p class="footer__right__text">
									Design by <a href="#">etrange</a>
								</p>
							</div>
							<div class="footer__right__line clearfix">
								<a class="footer__right__bigmir" href="#">&nbsp;</a>
							</div>
							<div class="footer__right__line clearfix">
								<a class="footer__right__lj" href="#"></a>
							</div>
						</div>
					</div><!--end footer__right-->
				</div>
			</div>
		</div>
		<!-- ОКНО AJAX СООБЩЕНИЙ ВО ВРЕМЯ ОЖИДАНИЯ ВЫОЛНЕНИЯ СКРИПТА -->
		<div id="ajaxPreloader" style="display:none;">
			Идёт обновление информации, подождите...
		</div>
		<div id="authPlaceholder" class="jqmWindow"></div>
	</body>
</html>