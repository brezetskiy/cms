<b>Найдено файлов: <?php echo $this->vars['files_found']; ?></b>
<p>
<ul>
<?php
			reset($this->vars['/result/'][$__key]);
			while(list($_result_key,) = each($this->vars['/result/'][$__key])):
			?>
	<input type="checkbox" checked name="files[]" value="<?php echo $this->vars['/result/'][$__key][$_result_key]['file']; ?>"><a target="_blank" href="./Result/?file=<?php echo $this->vars['/result/'][$__key][$_result_key]['file_url']; ?>&pattern=<?php echo $this->global_vars['pattern']; ?>#match_1">/<?php echo $this->vars['/result/'][$__key][$_result_key]['filename']; ?> (<?php echo $this->vars['/result/'][$__key][$_result_key]['matches_count']; ?>)</a><br>
<?php 
			endwhile;
			?>
</ul>
