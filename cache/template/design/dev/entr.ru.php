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
		<script type="text/javascript" language="JavaScript" src="/js/shared/currency.js"></script>
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

			//$('#authPlaceholder').jqm({ajax: '/action/cms/login_form/', trigger: 'a.login_trigger'});
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
									Мы вам пеерезвоним!
								</p>
							</div>
						</div><!--end support-->
						<!-- БЛОКИ КОРЗИНЫ -->
						<!-- КОРЗИНА МАГАЗИНА -->
						<div class="basket clarfix">
							<div class="basket__top"></div>
							<div class="basket__body">
								<div class="basket__body_i">
									<?php echo $this->vars['cart']; ?>
								</div>
							</div>
						</div><!--end basket-->
					</div><!--end top-->
					<!-- БЛОКИ КОРЗИНЫ -->
					<div id="bg_layer" class="bg_layer" onclick="cart_hide(); touragent_info_hide();">
						&nbsp;
					</div>
					<div id="cart_layer" class="cart_layer"></div>
					<div id="infolayer" class="infolayer">
						<div>
					
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
			<div class="slider-conteiner">
				<div class="slider">
					<ul class="slider__body clearfix" id="foo">
						<li>
							<div class="slider__body__item">
								<div class="slider__body__item_i">
									<img src="/design/dev/img/slider/02.jpg" width="1094" height="358" alt="" />
									<h1 class="slider__body__item__title">АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>к Microsoft Office 2010</h2>
									<h3>антивирус в подарок</h3>
									<div class="slider__body__item__btn-out">
										<a href="#" class="btn btn_big">Заказать сейчас<span>&nbsp;</span></a>
									</div>
								</div>
							</div>
						</li>
						<li>
							<div class="slider__body__item">
								<div class="slider__body__item_i">
									<img src="/design/dev/img/slider/02.jpg" width="1094" height="358" alt="" />
									<h1 class="slider__body__item__title">АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>к Microsoft Office 2010</h2>
									<h3>антивирус в подарок</h3>
									<div class="slider__body__item__btn-out">
										<a href="#" class="btn btn_big">Заказать сейчас<span>&nbsp;</span></a>
									</div>
								</div>
							</div>
						</li>
						<li>
							<div class="slider__body__item">
								<div class="slider__body__item_i">
									<img src="/design/dev/img/slider/02.jpg" width="1094" height="358" alt="" />
									<h1 class="slider__body__item__title">АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>к Microsoft Office 2010</h2>
									<h3>антивирус в подарок</h3>
									<div class="slider__body__item__btn-out">
										<a href="#" class="btn btn_big">Заказать сейчас<span>&nbsp;</span></a>
									</div>
								</div>
							</div>
						</li>
						<li>
							<div class="slider__body__item">
								<div class="slider__body__item_i">
									<h1 class="slider__body__item__title">АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>к Microsoft Office 2010</h2>
									<h3>антивирус в подарок</h3>
									<div class="slider__body__item__btn-out">
										<a href="#" class="btn btn_big">Заказать сейчас<span>&nbsp;</span></a>
									</div>
								</div>
							</div>
						</li>
						<li>
							<div class="slider__body__item">
								<div class="slider__body__item_i">
									<h1 class="slider__body__item__title">АКЦИЯ!</h1>
									<h4>21 декабря - 20 января</h4>
									<h2>к Microsoft Office 2010</h2>
									<h3>антивирус в подарок</h3>
									<div class="slider__body__item__btn-out">
										<a href="#" class="btn btn_big">Заказать сейчас<span>&nbsp;</span></a>
									</div>
								</div>
							</div>
						</li>
					</ul>
					<div class="slider__paginator"></div>
					<em class="slider__lcorn">&nbsp;</em>
					<em class="slider__rcorn">&nbsp;</em>
				</div>
			</div>
			<!--slider-conteiner-->
			<div class="main">
				<div class="main_i clearfix">
					<div class="main__left">
						<div class="catalogue">
							<div class="catalogue-i">
								<h1 class="catalogue__title">Каталог товаров </h1>
								<div class="catalogue__inner">
									<?php
			reset($this->vars['/allgroup_right/'][$__key]);
			while(list($_allgroup_right_key,) = each($this->vars['/allgroup_right/'][$__key])):
			?>
										<h2 class="catalogue__inner__title"><?php echo $this->vars['/allgroup_right/'][$__key][$_allgroup_right_key]['name']; ?></h2>
										<ul class="catalogue__list">
											<?php
			reset($this->vars['/allgroup_right/subgroups/'][$_allgroup_right_key]);
			while(list($_allgroup_right_subgroups_key,) = each($this->vars['/allgroup_right/subgroups/'][$_allgroup_right_key])):
			?>
												<li class="catalogue__list__item">
													<a href='<?php echo $this->vars['/allgroup_right/subgroups/'][$_allgroup_right_key][$_allgroup_right_subgroups_key]['url']; ?>' class="catalogue__list__item__anchor"><?php echo $this->vars['/allgroup_right/subgroups/'][$_allgroup_right_key][$_allgroup_right_subgroups_key]['name']; ?></a>
												</li>
											<?php 
			endwhile;
			?>
											<li class="catalogue__list__item">
												<a href='<?php echo $this->vars['/allgroup_right/'][$__key][$_allgroup_right_key]['url']; ?>' class="catalogue__list__item__anchor catalogue__list__item__anchor__all">Все <?php echo $this->vars['/allgroup_right/'][$__key][$_allgroup_right_key]['name']; ?></a>
											</li>
										</ul>
									<?php 
			endwhile;
			?>
								</div>
							</div>
						</div><!--end catalogue-->
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
										<div class="filter-selected__body__line clearfix">
											<?php
			reset($this->vars['/filter_selected/value/'][$_filter_selected_key]);
			while(list($_filter_selected_value_key,) = each($this->vars['/filter_selected/value/'][$_filter_selected_key])):
			?>
												<a class="filter-selected__body__line__link" href="/action/shop/del_filter/?field_name=<?php echo $this->vars['/filter_selected/value/'][$_filter_selected_key][$_filter_selected_value_key]['field_name']; ?>&value=<?php echo $this->vars['/filter_selected/value/'][$_filter_selected_key][$_filter_selected_value_key]['value']; ?>&group_id=<?php echo $this->vars['/filter_selected/value/'][$_filter_selected_key][$_filter_selected_value_key]['group_id']; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>"> <em class="filter-selected__body__line__btn">&nbsp;</em> <?php echo $this->vars['/filter_selected/value/'][$_filter_selected_key][$_filter_selected_value_key]['name']; ?></a>
											<?php 
			endwhile;
			?>
										</div>
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
													<input type="checkbox" class="niceCheck" checked="checked" id="<?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['id']; ?>" />
													<a class="filter__list__item__link" href="/action/shop/add_filter/?field_name=<?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['field_name']; ?>&value=<?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['value']; ?>&group_id=<?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['group_id']; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>"> <label for="<?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['id']; ?>"><?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['name']; ?>&nbsp;&nbsp;(<?php echo $this->vars['/filter/value/'][$_filter_key][$_filter_value_key]['amount']; ?>)</label></a>
												</li>
											<?php 
			endwhile;
			?>
										</ul>
									<?php 
			endwhile;
			?>
									<?php endif; ?>
									<?php endif; ?>
									<div class="banner">
										<?php echo TemplateUDF::banner(array('name'=>"left_banner")); ?> <!-- <img src="/design/dev/img/banner.png" width="263" height="285" alt="" /> -->
									</div>
									<div class="voting">
										<div class="voting__top">
											&nbsp;
										</div>
										<div class="voting__body">
											<div class="voting__body_i">
												<h1 class="voting__body__title">Покупать ли софт?</h1>
												<div class="voting__body__form">
													<div class="voting__body__form__line clearfix">
														<input type="radio" class="niceRadio" name="vote" checked="checked" id="rad1" />
														<label for="rad1">Да</label>
													</div>
													<div class="voting__body__form__line clearfix">
														<input type="radio" class="niceRadio" name="vote" id="r2" />
														<label for="r2">Нет, (можно скачачь с инета)</label>
													</div>
													<div class="voting__body__form__line clearfix">
														<input type="radio" class="niceRadio" name="vote" id="r3" />
														<label for="r3">Может быть</label>
													</div>
													<div class="voting__body__form__line clearfix">
														<input type="radio" class="niceRadio" name="vote" id="r4" />
														<label for="r4">Да</label>
													</div>
													<div class="voting__body__form__line voting__body__form__line_btn clearfix">
														<a class="btn btn_vote" href="#">Голосовать<span>&nbsp;</span></a>
													</div>
													<div class="voting__body__form__line nomarg clearfix">
														<a class="voting__body__anchor" href="#">Посмотреть результаты</a>
													</div>
												</div>
											</div>
										</div>
									</div><!--end voting-->
								</div><!--end main__left-->
								<div class="main__right">
									<div class="best-sellers">
										<div class="best-sellers_i">
											<div class="best-sellers__header best-sellers__header_bpad clearfix">
												<h1 class="best-sellers__header__title">Популярные товары</h1>
												<a class="best-sellers__header__title__anchor" href="#">Популярные</a>
											</div>
											<div class="best-sellers-carousel">
												<div class="best-sellers-carousel__top"></div>
												<div class="best-sellers-carousel__body">
													<div class="best-sellers-carousel__body_i clearfix">
														<ul class="mycarousel" id="foo2">
															<?php
			reset($this->vars['/best_product/'][$__key]);
			while(list($_best_product_key,) = each($this->vars['/best_product/'][$__key])):
			?>
																<li>
																	<div class="mycarousel__item clearfix ">
																		<a class="mycarousel__item__img clearfix" href="#"> <img src="/uploads/<?php echo $this->vars['/best_product/'][$__key][$_best_product_key]['img']; ?>" width="84" height="98" alt="" /> </a>
																		<a class="mycarousel__item__name clearfix" href="<?php echo $this->vars['/best_product/'][$__key][$_best_product_key]['url']; ?>.html"><?php echo $this->vars['/best_product/'][$__key][$_best_product_key]['name']; ?></a>
																		<div class="mycarousel__item__block clearfix">
																			<p class="mycarousel__item__price clearfix" >
																				<?php echo $this->vars['/best_product/'][$__key][$_best_product_key]['price']; ?> грн.
																			</p>
																			<?php if(!$this->vars['/best_product/'][$__key][$_best_product_key]['in_order']): ?> <a id="cart_link_<?php echo $this->vars['/best_product/'][$__key][$_best_product_key]['id']; ?>" href="javascript:void(0);" onclick="cart_invoke(<?php echo $this->vars['/best_product/'][$__key][$_best_product_key]['id']; ?>);" class="btn btn_mycarousel">В корзину<span>&nbsp;</span></a> <?php else: ?> <a id="cart_link_<?php echo $this->vars['/best_product/'][$__key][$_best_product_key]['id']; ?>" href="javascript:void(0);" onclick="cart_invoke(0);" class="btn btn_mycarousel">В корзинe<span>&nbsp;</span></a> <?php endif; ?> <!--<a href="#" class="btn btn_mycarousel">В корзину
																			<span>&nbsp;</span>
																			</a> -->
																		</div>
																	</div>
																</li>
															<?php 
			endwhile;
			?>
														</ul>
														<a class="prev" id="foo2_prev" href="#"><span>prev</span></a>
														<a class="next" id="foo2_next" href="#"><span>next</span></a>
													</div>
												</div>
											</div>
										</div>
									</div><!--end best-sellers-->
									<div class="desc-shop">
										<div class="desc-shop_i">
											<div class="breadcrumbs">
												<p>
													<?php
			reset($this->vars['/path/'][$__key]);
			while(list($_path_key,) = each($this->vars['/path/'][$__key])):
			?>
														<?php if(!empty($this->vars['/path/'][$__key][$_path_key]['url'])): ?><a class="breadcrumbs__item" href="<?php echo $this->vars['/path/'][$__key][$_path_key]['url']; ?>"><?php echo $this->vars['/path/'][$__key][$_path_key]['name']; ?></a>
														>
														<?php else: ?> <span><?php echo $this->vars['/path/'][$__key][$_path_key]['name']; ?></span>
														<?php endif; ?>
													<?php 
			endwhile;
			?>
												</p>
											</div><!--end breadcrumbs-->
											<h1 class="desc-shop__title"><?php echo $this->vars['title']; ?></h1>
											<p class="desc-shop__text">
												<?php echo $this->vars['content']; ?>
											</p>
										</div>
									</div><!--end desc-shop-->
								</div><!--end main__right-->
							</div>
						</div>
						<!--<div class="sponsor">
						<div class="sponsor__top"></div>
						<div class="sponsor__body">
						<ul class="sponsor__list clearfix">
						<li class="sponsor__list__item"><a href="#" class="sponsor__list__item__anchor"><img src="/design/dev/img/sponsor/01.png" width="144" height="80" alt="" /></a></li>
						<li class="sponsor__list__item"><a href="#" class="sponsor__list__item__anchor"><img src="/design/dev/img/sponsor/02.png" width="144" height="80" alt="" /></a></li>
						<li class="sponsor__list__item"><a href="#" class="sponsor__list__item__anchor"><img src="/design/dev/img/sponsor/03.png" width="144" height="80" alt="" /></a></li>
						<li class="sponsor__list__item"><a href="#" class="sponsor__list__item__anchor"><img src="/design/dev/img/sponsor/04.png" width="144" height="80" alt="" /></a></li>
						<li class="sponsor__list__item"><a href="#" class="sponsor__list__item__anchor"><img src="/design/dev/img/sponsor/05.png" width="144" height="80" alt="" /></a></li>
						<li class="sponsor__list__item"><a href="#" class="sponsor__list__item__anchor"><img src="/design/dev/img/sponsor/06.png" width="144" height="80" alt="" /></a></li>
						</ul>
						</div>
						</div><!--end sponsor-->
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