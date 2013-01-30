<h1><?php echo $this->vars['module']; ?></h1>
<a href="../"><img src="/design/cms/img/button/up.gif" border="0"> Вернуться к списку модулей</a>

<br>
<?php echo $this->vars['cms_table_relations']; ?>

<p>
<?php if($this->vars['show_tables']): ?>
	<h3>Таблицы</h3>
	<ul>
		<?php
			reset($this->vars['/table/'][$__key]);
			while(list($_table_key,) = each($this->vars['/table/'][$__key])):
			?>
		<li style="color:blue;"><?php echo $this->vars['/table/'][$__key][$_table_key]['name']; ?></li>
		
			<?php if(!empty($this->vars['/table/'][$__key][$_table_key]['content'])): ?>
				<b>Контент (content/)</b>
				<ul>
					<?php
			reset($this->vars['/table/content/'][$_table_key]);
			while(list($_table_content_key,) = each($this->vars['/table/content/'][$_table_key])):
			?>
					<li><?php echo $this->vars['/table/content/'][$_table_key][$_table_content_key]['name']; ?></li>
					<?php 
			endwhile;
			?>
				</ul>
			<?php endif; ?>
			<?php if(!empty($this->vars['/table/'][$__key][$_table_key]['uploads'])): ?>
				<b>Закачанные файлы (uploads/)</b>
				<ul>
					<?php
			reset($this->vars['/table/uploads/'][$_table_key]);
			while(list($_table_uploads_key,) = each($this->vars['/table/uploads/'][$_table_key])):
			?>
					<li><?php echo $this->vars['/table/uploads/'][$_table_key][$_table_uploads_key]['name']; ?></li>
					<?php 
			endwhile;
			?>
				</ul>
			<?php endif; ?>
			<?php if(!empty($this->vars['/table/'][$__key][$_table_key]['triggers'])): ?>
				<b>Триггеры (system/triggers/)</b>
				<ul>
					<?php
			reset($this->vars['/table/triggers/'][$_table_key]);
			while(list($_table_triggers_key,) = each($this->vars['/table/triggers/'][$_table_key])):
			?>
					<li><?php echo $this->vars['/table/triggers/'][$_table_key][$_table_triggers_key]['name']; ?></li>
					<?php 
			endwhile;
			?>
				</ul>
			<?php endif; ?>
		<?php 
			endwhile;
			?>
	</ul>
	<p>
<?php endif; ?>


<?php if($this->vars['show_events']): ?>
	<h3>События</h3>
	<ul>
		<?php
			reset($this->vars['/event/'][$__key]);
			while(list($_event_key,) = each($this->vars['/event/'][$__key])):
			?>
			<li><?php echo $this->vars['/event/'][$__key][$_event_key]['file']; ?></li>
		<?php 
			endwhile;
			?>
	</ul>
	<p>
<?php endif; ?>

<?php if($this->vars['show_crontab']): ?>
	<h3>Crontab</h3>
	<ul>
		<li><?php echo $this->vars['crontab']; ?></li>
	</ul>
	<p>
<?php endif; ?>

<?php if($this->vars['show_templates']): ?>
	<h3>Шаблоны</h3>
	<ul>
		<?php
			reset($this->vars['/template/'][$__key]);
			while(list($_template_key,) = each($this->vars['/template/'][$__key])):
			?>
			<li><?php echo $this->vars['/template/'][$__key][$_template_key]['file']; ?></li>
		<?php 
			endwhile;
			?>
	</ul>
	<p>
<?php endif; ?>


<?php if($this->vars['show_includes']): ?>
	<h3>Includes</h3>
	<ul>
		<?php
			reset($this->vars['/includes/'][$__key]);
			while(list($_includes_key,) = each($this->vars['/includes/'][$__key])):
			?>
			<li><?php echo $this->vars['/includes/'][$__key][$_includes_key]['file']; ?></li>
		<?php 
			endwhile;
			?>
	</ul>
	<p>
<?php endif; ?>


<?php if($this->vars['show_tools']): ?>
	<h3>Tools</h3>
	<ul>
		<?php
			reset($this->vars['/tools/'][$__key]);
			while(list($_tools_key,) = each($this->vars['/tools/'][$__key])):
			?>
			<li><?php echo $this->vars['/tools/'][$__key][$_tools_key]['file']; ?></li>
		<?php 
			endwhile;
			?>
	</ul>
	<p>
<?php endif; ?>

<?php if($this->vars['show_site']): ?>
	<h3>Структура сайта</h3>
	<ul>
		<?php
			reset($this->vars['/site/'][$__key]);
			while(list($_site_key,) = each($this->vars['/site/'][$__key])):
			?>
			<li><a href="http://<?php echo $this->vars['/site/'][$__key][$_site_key]['url']; ?>/" target="_blank">http://<?php echo $this->vars['/site/'][$__key][$_site_key]['file']; ?>/</a></li>
		<?php 
			endwhile;
			?>
	</ul>
	<p>
<?php endif; ?>
<?php if($this->vars['show_site_template']): ?>
	<h3>Шаблоны сайта</h3>
	<ul>
		<?php
			reset($this->vars['/site_template/'][$__key]);
			while(list($_site_template_key,) = each($this->vars['/site_template/'][$__key])):
			?>
			<li><?php echo $this->vars['/site_template/'][$__key][$_site_template_key]['file']; ?></li>
		<?php 
			endwhile;
			?>
	</ul>
	<p>
<?php endif; ?>

<?php if($this->vars['show_admin']): ?>
	<h3>Структура административного интерфейса</h3>
	<ul>
		<?php
			reset($this->vars['/admin/'][$__key]);
			while(list($_admin_key,) = each($this->vars['/admin/'][$__key])):
			?>
			<li><?php echo $this->vars['/admin/'][$__key][$_admin_key]['file']; ?></li>
		<?php 
			endwhile;
			?>
	</ul>
	<p>
<?php endif; ?>
<?php if($this->vars['show_admin_template']): ?>
	<h3>Шаблоны административного интерфейса</h3>
	<ul>
		<?php
			reset($this->vars['/admin_template/'][$__key]);
			while(list($_admin_template_key,) = each($this->vars['/admin_template/'][$__key])):
			?>
			<li><?php echo $this->vars['/admin_template/'][$__key][$_admin_template_key]['file']; ?></li>
		<?php 
			endwhile;
			?>
	</ul>
	<p>
<?php endif; ?>


<?php if($this->vars['show_css']): ?>
	<h3>Таблицы стилей</h3>
	<ul>
		<?php
			reset($this->vars['/css/'][$__key]);
			while(list($_css_key,) = each($this->vars['/css/'][$__key])):
			?>
			<li><?php echo $this->vars['/css/'][$__key][$_css_key]['file']; ?></li>
		<?php 
			endwhile;
			?>
	</ul>
	<p>
<?php endif; ?>

<?php if($this->vars['show_img']): ?>
	<h3>Картинки</h3>
	<ul>
		<?php
			reset($this->vars['/img/'][$__key]);
			while(list($_img_key,) = each($this->vars['/img/'][$__key])):
			?>
			<li><a href="/img/<?php echo $this->vars['/img/'][$__key][$_img_key]['file']; ?>" target="_blank"><?php echo $this->vars['/img/'][$__key][$_img_key]['file']; ?></a></li>
		<?php 
			endwhile;
			?>
	</ul>
	<p>
<?php endif; ?>

<?php if($this->vars['show_js']): ?>
	<h3>JavaScript</h3>
	<ul>
		<?php
			reset($this->vars['/js/'][$__key]);
			while(list($_js_key,) = each($this->vars['/js/'][$__key])):
			?>
			<li><?php echo $this->vars['/js/'][$__key][$_js_key]['file']; ?></li>
		<?php 
			endwhile;
			?>
	</ul>
	<p>
<?php endif; ?>

<center><b><a href="/<?php echo LANGUAGE_URL; ?>action/admin/sdk/download_module/?module_id=<?php echo $this->vars['module_id']; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>">Скачать файлы модуля</a></b></center>
