<h1>Расписание выполнения задач</h1><br />
	<ol>
	<?php
			reset($this->vars['/goodscript/'][$__key]);
			while(list($_goodscript_key,) = each($this->vars['/goodscript/'][$__key])):
			?>
		<li>
			<span title="<?php if($this->vars['/goodscript/'][$__key][$_goodscript_key]['time'] != ''): ?>Время выполнения скрипта: <?php echo $this->vars['/goodscript/'][$__key][$_goodscript_key]['time']; ?> сек. <?php else: ?>Время выполнения скрипта не определено.<?php endif; ?>"><?php echo $this->vars['/goodscript/'][$__key][$_goodscript_key]['name']; ?></span><br/>
			<span style="color:#777; font-size:10px;"><?php if(!empty($this->vars['/goodscript/'][$__key][$_goodscript_key]['dtime'])): ?>Дата и время запуска скрипта: <?php echo $this->vars['/goodscript/'][$__key][$_goodscript_key]['dtime']; ?>, <?php echo $this->vars['/goodscript/'][$__key][$_goodscript_key]['status']; ?>.<?php else: ?>Дата и время запуска скрипта не определены.<?php endif; ?></span>
			<br /><br />
		</li>
	<?php 
			endwhile;
			?>
	</ol>
<?php if(IS_DEVELOPER): ?>
	<h1><font color=red>Скрипты без расписания</font></h1><br />
	<ol>
	<?php
			reset($this->vars['/bedscript/'][$__key]);
			while(list($_bedscript_key,) = each($this->vars['/bedscript/'][$__key])):
			?>
		<li><?php echo $this->vars['/bedscript/'][$__key][$_bedscript_key]['name']; ?></li>
	<?php 
			endwhile;
			?>
	</ol>
	<h1><font color=red>Директории не соответсвуют названиям модулей</font></h1><br />
	<ol>
	<?php
			reset($this->vars['/bedsubpack/'][$__key]);
			while(list($_bedsubpack_key,) = each($this->vars['/bedsubpack/'][$__key])):
			?>
		<li><?php echo $this->vars['/bedsubpack/'][$__key][$_bedsubpack_key]['name']; ?></li>
	<?php 
			endwhile;
			?>
	</ol>
	<h1><font color=red>Неправильное количество параметров для crontab</font></h1><br />
	<ol>
	<?php
			reset($this->vars['/bad_param/'][$__key]);
			while(list($_bad_param_key,) = each($this->vars['/bad_param/'][$__key])):
			?>
		<li><?php echo $this->vars['/bad_param/'][$__key][$_bad_param_key]['name']; ?></li>
	<?php 
			endwhile;
			?>
	</ol>
<?php endif; ?>
<br>
<h1>Задачи для выполнения</h1>
<br><br>
<?php echo $this->global_vars['cron_str']; ?>
