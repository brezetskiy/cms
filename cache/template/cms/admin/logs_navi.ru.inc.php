<style>

TABLE.table_lods_navi { width: 100%; border-collapse: collapse; border: 1px solid #385685; vertical-align: middle; margin:10px 0px; }
TABLE.table_lods_navi TR.odd { background-color: #f3f3f3; }
TABLE.table_lods_navi TR.even { background-color: #e9e9e9; }
TABLE.table_lods_navi TR.over { background-color: #D4DEEC; }
TABLE.table_lods_navi TR.error { background-color: #FFD4D7; }
TABLE.table_lods_navi TR.vertcenter { vertical-align: middle; }

TABLE.table_lods_navi TD { padding: 5px; border: 1px solid #7398AF; vertical-align: middle; }
TABLE.table_lods_navi TD.no_content { text-align: center; padding: 20px; background-color: #f3f3f3; }
TABLE.table_lods_navi TR TD { vertical-align: middle; }
TABLE.table_lods_navi THEAD TD { text-align: center; font-weight: bold; background-color: #B4C8D4; color: BLACK; padding: 5px 3px 5px 3px; vertical-align: middle; }
TABLE.table_lods_navi TR TH { text-align: center; font-weight: bold; background-color: #d9e3e9;color: BLACK; padding: 5px 3px 5px 3px;  vertical-align: middle;}

TABLE.table_lods_navi INPUT.fill { width: 95%; font-family: tahoma;}

</style>
 
<div style="margin:5px;">&nbsp;</div>
<center><div id="action_error" align="left" class="action_error"></div></center>  
<center><div id="action_success" align="left" class="action_success"></div></center>  

<span id="current_path" style="margin:10px 0px;"><b>Директория:</b> <span style="color:#777;"><?php echo $this->global_vars['current_path']; ?>/</span></span>  

<form id="files_form" action="/<?php echo LANGUAGE_URL; ?>action/admin/cms/logs/view/">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">

<table class="table_lods_navi"> 
	<thead>
		<tr>
			<td width="30%">Имя</td>
			<td width="10%">Тип</td>
			<td width="10%">Размер, Кб</td>
		</tr>
	</thead>
	<?php if($this->global_vars['current_path'] != LOGS_ACTIONS_ROOT && strpos($this->global_vars['current_path'], LOGS_ACTIONS_ROOT) !== FALSE): ?>
	<tr class="odd" onclick="load('<?php echo $this->global_vars['parent_path']; ?>', 0);" style="cursor: pointer;">
		<td><img src="/design/cms/img/button/up.gif" alt="На уровень вверх" width="16" border="0" height="16"></td>
		<td><img src="/design/cms/img/button/up.gif" alt="На уровень вверх" width="16" border="0" height="16"></td>
		<td><img src="/design/cms/img/button/up.gif" alt="На уровень вверх" width="16" border="0" height="16"></td>
	</tr>   
	<?php endif; ?>
	<?php if($this->vars['rows_count'] == 0): ?>  
		<tr><td class="no_content" colspan="3">Папка пуста</td></tr>
	<?php else: ?>		
		<?php
			reset($this->vars['/files/'][$__key]);
			while(list($_files_key,) = each($this->vars['/files/'][$__key])):
			?>
			<tr class="<?php echo $this->vars['/files/'][$__key][$_files_key]['class']; ?>">
				<td align="left">
					<?php if($this->vars['/files/'][$__key][$_files_key]['is_dir']): ?>
						<a href="javascript:void(0);" onclick="load('<?php echo $this->global_vars['current_path']; ?>/<?php echo $this->vars['/files/'][$__key][$_files_key]['filename']; ?>', 0, 0)">
							<img src="/design/cms/img/icons/folder.gif" border="0" align="absmiddle"> 
							<?php echo $this->vars['/files/'][$__key][$_files_key]['filename']; ?>
						</a>
					<?php else: ?> 
						<label for="file_<?php echo $this->vars['/files/'][$__key][$_files_key]['count']; ?>">
							<img src="/img/shared/ico/<?php echo $this->vars['/files/'][$__key][$_files_key]['icon']; ?>"> 
							<?php if(strpos($this->vars['/files/'][$__key][$_files_key]['filename'], '.log') !== FALSE): ?>
								<a href="javascript:void(0);" onclick="log_display('<?php echo $this->global_vars['current_path']; ?>/<?php echo $this->vars['/files/'][$__key][$_files_key]['filename']; ?>', 'date', 'desc', 0);"><?php echo $this->vars['/files/'][$__key][$_files_key]['filename']; ?></a>
							<?php elseif(strpos($this->vars['/files/'][$__key][$_files_key]['filename'], '.tar.gz') !== FALSE): ?> 
								<a href="javascript:void(0);" onclick="load('<?php echo $this->global_vars['current_path']; ?>/<?php echo $this->vars['/files/'][$__key][$_files_key]['filename']; ?>', 0, 1);"><?php echo $this->vars['/files/'][$__key][$_files_key]['filename']; ?></a>
							<?php else: ?>
								<?php echo $this->vars['/files/'][$__key][$_files_key]['filename']; ?>
							<?php endif; ?>
						</label>
					<?php endif; ?>
				</td>  
				<td align="left"><?php echo $this->vars['/files/'][$__key][$_files_key]['filetype']; ?></td> 
				<td align="right"><?php echo $this->vars['/files/'][$__key][$_files_key]['filesize']; ?></td>
			</tr>
		<?php 
			endwhile;
			?>
	<?php endif; ?>
</table>
</form> 
<?php if(!empty($this->vars['pages_list'])): ?>
	<p><div style="text-align:right;">Страница: <?php echo $this->vars['pages_list']; ?></div></p>
	<div style="margin-top:5px;">&nbsp;</div>
<?php endif; ?>
