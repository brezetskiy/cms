<?php if(empty($this->vars['content'])): ?>
		<table class="news_archive">

	<?php if($this->vars['show_subcont'] == 0): ?>
	<?php
			reset($this->vars['/news/'][$__key]);
			while(list($_news_key,) = each($this->vars['/news/'][$__key])):
			?>
		<?php if(!empty($this->vars['/news/'][$__key][$_news_key]['subtitle_year'])): ?>
			<tr class="year">
				<td></td>
				<td><?php echo $this->vars['/news/'][$__key][$_news_key]['subtitle_year']; ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td class="news_date">
				<?php echo $this->vars['/news/'][$__key][$_news_key]['date']; ?>
				<?php if(!empty($this->vars['/news/'][$__key][$_news_key]['image_src'])): ?><br><div class="news_image"><img src="<?php echo $this->vars['/news/'][$__key][$_news_key]['image_src']; ?>"></div><?php endif; ?>
			</td>
			<td class="news_title">
				<?php if($this->vars['/news/'][$__key][$_news_key]['has_content']): ?>
					<a href="/<?php echo LANGUAGE_URL; ?>news/<?php echo $this->vars['/news/'][$__key][$_news_key]['url']; ?>.html"><?php echo $this->vars['/news/'][$__key][$_news_key]['headline']; ?></a>
				<?php else: ?>
					<?php echo $this->vars['/news/'][$__key][$_news_key]['headline']; ?>
				<?php endif; ?>
				<?php if(!empty($this->vars['/news/'][$__key][$_news_key]['announcement'])): ?>
					<br><br><span class="comment"><?php echo $this->vars['/news/'][$__key][$_news_key]['announcement']; ?></span>
				<?php endif; ?>
			</td>
		</tr>
	<?php 
			endwhile;
			?>
	<?php else: ?>
		<?php
			reset($this->vars['/news/'][$__key]);
			while(list($_news_key,) = each($this->vars['/news/'][$__key])):
			?>
		<?php if(!empty($this->vars['/news/'][$__key][$_news_key]['subtitle_year'])): ?>
			<tr class="year">
				<td></td>
				<td><div class="year"><?php echo $this->vars['/news/'][$__key][$_news_key]['subtitle_year']; ?></div></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td class="news_date">
				<?php echo $this->vars['/news/'][$__key][$_news_key]['date']; ?>
				<?php if(!empty($this->vars['/news/'][$__key][$_news_key]['image_src'])): ?><br><div class="news_image"><img src="<?php echo $this->vars['/news/'][$__key][$_news_key]['image_src']; ?>"></div><?php endif; ?>
			</td>
			<td class="news_title">
				<?php if($this->vars['/news/'][$__key][$_news_key]['has_content']): ?>
					<a href="/<?php echo LANGUAGE_URL; ?>news/<?php echo $this->vars['/news/'][$__key][$_news_key]['url']; ?>.html"><?php echo $this->vars['/news/'][$__key][$_news_key]['headline']; ?></a>
				<?php else: ?>
					<?php echo $this->vars['/news/'][$__key][$_news_key]['headline']; ?>
				<?php endif; ?>
				<div><?php echo $this->vars['/news/'][$__key][$_news_key]['desc']; ?></div>
				
			</td>
		</tr>
	<?php 
			endwhile;
			?>
	<?php endif; ?>
	<tr><td colspan="2" style="text-align:center;padding-top:20px;"><?php echo $this->vars['pages_list']; ?></td></tr>
	<?php if($this->vars['remove_date_filter']): ?>
	<tr>
		<td></td>
		<td><a href="/<?php echo LANGUAGE_URL; ?>news/<?php echo $this->global_vars['type']; ?>.htm">Убрать фильтр по дате</a></td>
	</tr>
	<?php endif; ?>
	</table>
<?php else: ?>
	<div id="content">
		<?php if($this->vars['is_previous'] == 1 || $this->vars['is_next'] == 1): ?>
		<div class="post-nav">
		   <?php if($this->vars['is_previous'] == 1): ?><div class="previous"><a href="/<?php echo LANGUAGE_URL; ?>news/<?php echo $this->vars['previous_url']; ?>.html"><?php echo $this->vars['previous_name']; ?></a></div><?php endif; ?>
		   <?php if($this->vars['is_next'] == 1): ?><div class="next"><a href="/<?php echo LANGUAGE_URL; ?>news/<?php echo $this->vars['next_url']; ?>.html"><?php echo $this->vars['next_name']; ?></a></div><?php endif; ?>
		</div>
		<?php endif; ?>
		<div class="post" id="post-<?php echo $this->vars['message_id']; ?>" style="margin-top: 0;">
			<table id="post-head">
				 <tr>
				  	<td id="head-date">
			     		<div class="date">
			     			<?php echo $this->vars['day_from']; ?> <span><?php echo $this->vars['month_from']; ?></span> 	
			     		</div>
			     	</td>
			     	<td>
			     	 <div class="title">
			            <h2><?php echo $this->vars['headline']; ?></h2>
			          <!-- <div class="postdata">
			               <span class="category">
			               		<a href="/news/<?php echo $this->vars['type_uniq_name']; ?>.htm"><?php echo $this->vars['type_name']; ?></a>
			               </span>
			               <span class="right mini-add-comment">  
			               	  <a href="#comment" onclick="$('#form_start').css('display', 'block');">Добавить комментарий</a>
			               </span>
			            </div> /-->
			         </div>
			     	</td>
			     </tr>
			</table>
		         
		    <div class="entry">
				
		       	<p><?php echo $this->vars['content']; ?></p>
			</div><!--/entry -->
		
			<!-- <a style="float:left;margin-right:25px;margin-top:1px;" class="addthis_counter addthis_pill_style"></a> /-->
			<!--<a href="http://twitter.com/share" class="twitter-share-button" data-count="horizontal" data-via="delta-x">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>-->
			<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=YOUR-ACCOUNT-ID"></script>
           	
			<!--<?php echo TemplateUDF::comment(array('table_name'=>'news_message','object_id'=>$this->vars['message_id'])); ?> /-->
			
		</div><!--/post -->
		
	</div><!--/content -->
	<br/>
	<div class="link_arhiv">
		<?php if(!empty($this->vars['archive_name'])): ?>
	   		<a href="/arhiv/news/"><?php echo $this->vars['archive_name']; ?></a>
   		<?php else: ?>
	   		<a href="/arhiv/news/"><?php echo $this->vars['type_name']; ?></a>
   		<?php endif; ?>
	</div>
	<br />	<br />
	<h3>Другие новости:</h3>
		<br />
	<table>
		<?php
			reset($this->vars['/nearby/'][$__key]);
			while(list($_nearby_key,) = each($this->vars['/nearby/'][$__key])):
			?>
			<tr>
				<td class="news_date">
					<?php echo $this->vars['/nearby/'][$__key][$_nearby_key]['date']; ?> 
				</td>
				<td class="news_title">
				<?php if($this->vars['/nearby/'][$__key][$_nearby_key]['content']): ?>
					<a class="<?php echo $this->vars['/nearby/'][$__key][$_nearby_key]['class']; ?>" href="/<?php echo LANGUAGE_URL; ?>news/<?php echo $this->vars['/nearby/'][$__key][$_nearby_key]['url']; ?>.html"><?php echo $this->vars['/nearby/'][$__key][$_nearby_key]['headline']; ?></a>
				<?php else: ?>
					<?php echo $this->vars['/nearby/'][$__key][$_nearby_key]['headline']; ?>
				<?php endif; ?>
				</td>
			</tr>
		<?php 
			endwhile;
			?>
	</table>

<?php endif; ?>