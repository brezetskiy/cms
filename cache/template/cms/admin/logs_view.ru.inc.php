<style>

TABLE.table_logs_navi { width: 100%; border-collapse: collapse; border: 1px solid #385685; vertical-align: middle; margin:10px 0px; }
TABLE.table_logs_navi TR.odd { background-color: #f3f3f3; }
TABLE.table_logs_navi TR.even { background-color: #e9e9e9; }
TABLE.table_logs_navi TR.over { background-color: #D4DEEC; }
TABLE.table_logs_navi TR.error { background-color: #FFD4D7; }
TABLE.table_logs_navi TR.vertcenter { vertical-align: middle; }

TABLE.table_logs_navi TD { padding: 5px; border: 1px solid #7398AF; vertical-align: middle; }
TABLE.table_logs_navi TD.no_content { text-align: center; padding: 20px; background-color: #f3f3f3; }
TABLE.table_logs_navi TR TD { vertical-align: middle; }
TABLE.table_logs_navi THEAD TD { text-align: center; font-weight: bold; background-color: #B4C8D4; color: BLACK; padding: 5px 3px 5px 3px; vertical-align: middle; }
TABLE.table_logs_navi TR TH { text-align: center; font-weight: bold; background-color: #d9e3e9;color: BLACK; padding: 5px 3px 5px 3px;  vertical-align: middle;}

TABLE.table_logs_navi INPUT.fill { width: 95%; font-family: tahoma;}
 
TABLE.table_logs_navi div.uprising { position:absolute; padding:10px; border:1px solid #385685; background-color:#fff; display:none; right:20px; max-width:1000px;}
TABLE.table_logs_navi div.uprising div.title{ color:red; font-size:16px; }
</style>

<script>
	function uprising_display(id){  
		$("div[id^='uprising']").hide();
		$('#'+id).show();
	}
</script>

<div style="margin:5px;">&nbsp;</div>
<center><div id="action_error" align="left" class="action_error"></div></center>  
<center><div id="action_success" align="left" class="action_success"></div></center>  

<span id="current_path" style="margin:10px 0px;"><b>Лог:</b> <span style="color:#777;"><?php echo $this->global_vars['path']; ?></span></span>  

<form id="files_form" action="/<?php echo LANGUAGE_URL; ?>action/admin/cms/logs/view/">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">

<?php if(!empty($this->vars['pages_list'])): ?><div style="text-align:right;">Страница: <?php echo $this->vars['pages_list']; ?></div><?php endif; ?>

<table class="table_logs_navi"> 
	<thead>
		<tr>
			<td width="20%">
				Дата &nbsp;
				<a id="sort_name" href="javascript:void(0);" onclick="log_display('<?php echo $this->global_vars['path']; ?>', 'date', 'asc', 0)">&uarr;</a>
				<a id="sort_name" href="javascript:void(0);" onclick="log_display('<?php echo $this->global_vars['path']; ?>', 'date', 'desc', 0)">&darr;</a> 
			</td>
			<td width="30%">
				Пользователь &nbsp;
				<a id="sort_name" href="javascript:void(0);" onclick="log_display('<?php echo $this->global_vars['path']; ?>', 'user_login', 'asc', 0)">&uarr;</a>
				<a id="sort_name" href="javascript:void(0);" onclick="log_display('<?php echo $this->global_vars['path']; ?>', 'user_login', 'desc', 0)">&darr;</a> 
			</td>
			<td width="20%">IP</td>
			<td width="20%">Локальный IP</td>
			<td width="1%">$_REQUEST</td>
			<td width="1%">$_SESSION</td> 
		</tr>
	</thead>
	<?php
			reset($this->vars['/rows/'][$__key]);
			while(list($_rows_key,) = each($this->vars['/rows/'][$__key])):
			?>
		<tr class="<?php echo $this->vars['/rows/'][$__key][$_rows_key]['class']; ?>">
			<td align="center"><?php echo $this->vars['/rows/'][$__key][$_rows_key]['date']; ?></td>  
			<td align="left"><?php echo $this->vars['/rows/'][$__key][$_rows_key]['user_login']; ?></td> 
			<td align="center"><?php echo $this->vars['/rows/'][$__key][$_rows_key]['ip']; ?></td>
			<td align="center"><?php echo $this->vars['/rows/'][$__key][$_rows_key]['local_ip']; ?></td>
			<td align="left">
				<center><a href="javascript:void(0);" onclick="uprising_display('uprising_request_<?php echo $this->vars['/rows/'][$__key][$_rows_key]['count']; ?>');"><img src="/img/cms/logs/data.png" border="0" align="absmiddle"></a></center>
				<div id="uprising_request_<?php echo $this->vars['/rows/'][$__key][$_rows_key]['count']; ?>" class="uprising">
					<div class="title" style="float:left;">$_REQUEST:</div>
					<div style="float:right; margin:5px;"><a href="javascript:void(0);" onclick="$('#uprising_request_<?php echo $this->vars['/rows/'][$__key][$_rows_key]['count']; ?>').hide();">Закрыть</a></div>
					<div style="clear:both;"></div>
					<?php echo $this->vars['/rows/'][$__key][$_rows_key]['request']; ?>
				</div>
			</td>
			<td align="left">
				<center><a href="javascript:void(0);" onclick="uprising_display('uprising_session_<?php echo $this->vars['/rows/'][$__key][$_rows_key]['count']; ?>');"><img src="/img/cms/logs/data.png" border="0" align="absmiddle"></a></center>
				<div id="uprising_session_<?php echo $this->vars['/rows/'][$__key][$_rows_key]['count']; ?>" class="uprising">
					<div class="title" style="float:left;">$_SESSION:</div>
					<div style="float:right; margin:5px;"><a href="javascript:void(0);" onclick="$('#uprising_session_<?php echo $this->vars['/rows/'][$__key][$_rows_key]['count']; ?>').hide();">Закрыть</a></div>
					<div style="clear:both;"></div>
					<?php echo $this->vars['/rows/'][$__key][$_rows_key]['session']; ?>
				</div>
			</td>
		</tr>
	<?php 
			endwhile;
			?>
</table>
</form> 

<?php if(!empty($this->vars['pages_list'])): ?><div style="text-align:right;">Страница: <?php echo $this->vars['pages_list']; ?></div><?php endif; ?>
