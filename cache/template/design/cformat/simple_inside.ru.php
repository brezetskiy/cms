<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "xhtml11.dtd">
<html>
	<head>
		<meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1251"/>
		<meta name="Keywords" content="<?php echo $this->vars['keywords']; ?>"/>
		<meta name="Description" content="<?php echo $this->vars['description']; ?>"/>
		<title><?php echo $this->vars['title']; ?></title>
		<link href="<?php echo CMS_URL; ?>favicon.ico" rel="shortcut icon" />
	
		<!-- ����� �������� -->
		<link rel="stylesheet" type="text/css" href="/extras/jquery/lightbox/jquery.lightbox.css"/>

		<script type="text/javascript" src="/design/cformat/js/jquery-1.8.2.min.js"></script>
		<!--<script src="/design/cformat/js/jquery.fancybox.js"></script>/-->
		
		<?php if($this->vars['sliders_tmpl'] == 'slider'): ?>
			<!-- Simple Slider begin -->
			<script src="/extras/slider/simple/slider.js"></script>
			<link rel="stylesheet" type="text/css" href="/extras/slider/simple/slider.css"></script>
			<!-- end simple slider -->
		<?php elseif($this->vars['sliders_tmpl'] == 'sliderkit'): ?>
			<!-- jQuery Plugin scripts, need for slider kit -->
			<script type="text/javascript" src="/design/cformat/js/jquery.easing.1.3.min.js"></script>
			<script type="text/javascript" src="/design/cformat/js/jquery.mousewheel.min.js"></script>
		
			<!-- Slider Kit scripts begin -->
			<script type="text/javascript" src="/extras/slider/sliderkit/jquery.sliderkit.1.9.2.pack.js"></script>
			<link rel="stylesheet" type="text/css" href="/extras/slider/sliderkit/sliderkit-core.css"></script>
			
			<script type="text/javascript">
				$(window).load(function(){ //$(window).load() must be used instead of $(document).ready() because of Webkit compatibility				
								
					// Photo slider > Bullets nav
					$(".photoslider-bullets").sliderkit({
						auto:true,
						circular:true,
						mousewheel:true,
						shownavitems:5,
						panelfx:"sliding",
						panelfxspeed:1000,
						panelfxeasing:"easeOutExpo" // "easeOutExpo", "easeInOutExpo", etc.
					});
				});	
			</script>		
			<!-- end sliderkit /-->
		<?php elseif($this->vars['sliders_tmpl'] == 'nivoslider'): ?>
			<!-- nivo slider begin /-->
			<script type="text/javascript" src="/extras/slider/nivoslider/jquery.nivo.slider.pack.js"></script>
			<link rel="stylesheet" type="text/css" href="/extras/slider/nivoslider/nivoslider/default.css"></script>
			
			<script type="text/javascript">
			$(window).load(function() {
				$('#slider').nivoSlider();
			});
			</script>
			<!-- end nivo slider /-->
		<?php endif; ?>
		
       <!-- <script src="/design/cformat/js/start.js"></script>/-->

		
		<link rel="stylesheet" type="text/css" href="/design/cformat/css/newstyle.css" media="all" />
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
									<?php if(!Auth::isLoggedIn()): ?> <a class="author__top__anchor" href="/user/login/">����� �� ����</a>
									<a class="author__top__anchor" href="/user/register/">�����������������</a>
									<?php else: ?>
									���� ����� ������, <b><?php echo $_SESSION['auth']['login']; ?></b>!</a></b> (ID:<?php echo $_SESSION['auth']['id']; ?>) <a class="author__top__anchor" href="/user/info/">������������ ������</a>
									<!-- <a class="author__top__anchor" href="/user/changepassword/">������� ������</a>-->
									<a class="author__top__anchor" class="top-auth-logout" href="/action/cms/logout/?_return_path=<?php echo CURRENT_URL_LINK; ?>">�����</a>
									<?php endif; ?>
								</div>
								<div class="author__bot">
									<div class="author__bot__search-out">
										<div class="author__bot__search">
											<input class="tipped" type="text" title="�����" />
										</div>
									</div>
									<a class="btn" href="#">�����<span>&nbsp;</span></a>
								</div>
							</div>
						</div><!--end author-->
						<div class="support">
							<div class="support_i">
								<p class="support__text support__text_pb">
									�������������� ������������
								</p>
								<div class="support__contact">
									<em class="support__contact__phone">&nbsp;</em>
									<p class="support__contact__text">
										<span>����</span> (044) 392-74-33
									</p>
									<a class="support__contact__bird" href="#">&nbsp;</a>
								</div><!--end support__contact-->
								<div class="support__city">
									<ul class="support__city__list">
										<li class="support__city__list__item">
											<span>�������</span> (057) 728-39-00
										</li>
										<li class="support__city__list__item">
											<span>��������������</span> (056) 794-38-31
										</li>
										<li class="support__city__list__item">
											<span>������</span> (062) 210-24-93
										</li>
										<li class="support__city__list__item">
											<span>�����</span> (032) 229-58-93
										</li>
										<li class="support__city__list__item">
											<span>������</span> (048) 738-57-70
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
									�� ��� ����������!
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
							<img class="close" onclick="" class="exit" align="right" src="/design/dev/img/cms/cross.png" alt="�������"/>
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
									<a href="<?php echo $this->vars['/top_menu/'][$__key][$_top_menu_key]['url']; ?>" class="tmenu__list__item__anchor">������� �������<span>&nbsp;</span></a>
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

			<!--slider-conteiner-->
			<div class="main">
				<div class="main_i clearfix">
					<div class="main__left">
						<!-- ������� �������� -->
						<?php if($this->vars['shop_filter'] || $this->vars['shop_filter_selected']): ?> <!-- ��������� ������� -->
						<?php if($this->vars['shop_filter_selected']): ?>
						<div class="filter-selected">
							<div class="filter-selected_i">
								<div class="filter-selected__body">
									<h1>��������� �������</h1>
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
										<a class="filter-selected__body__line__link" href="/action/shop/clear_filter/?_return_path=<?php echo CURRENT_URL_LINK; ?>"> <em class="filter-selected__body__line__btn">&nbsp;</em>������ ��� �������</a>
									</div>
								</div>
							</div>
						</div><!--end filter-selected-->
						<?php endif; ?> <!-- ������� -->
						<?php if($this->vars['shop_filter']): ?>
						<div class="filter">
							<div class="filter_i">
								<div class="filter__body">
									<h1>������� ��� ������ </h1>
									<?php
			reset($this->vars['/filter/'][$__key]);
			while(list($_filter_key,) = each($this->vars['/filter/'][$__key])):
			?>
										<h2>���</h2>
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
						<?php endif; ?> <!-- ����� �������� ������������� ������ -->
						<?php if($this->vars['show_forum_user_top']): ?>
						<div style="width:240px;">
							<br/>
							<h1>TOP �������������</h1>
							<table class="forum_top_small">
								<thead>
									<tr>
										<td colspan="2">&nbsp;</td><td>���������</td><td>���������</td>
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
								<a href="/forum/top/">���������� ���� �������</a>
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
									��� ����� ��������
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
		<!-- ���� AJAX ��������� �� ����� �������� ��������� ������� -->
		<div id="ajaxPreloader" style="display:none;">
			��� ���������� ����������, ���������...
		</div>
		<div id="authPlaceholder" class="jqmWindow"></div>
	</body>
</html>