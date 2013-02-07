<html>
<head>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
	<title>"Пилот" - эффективное и простое управление веб-сайтом [www.delta-x.com.ua].</title>
	<link rel="stylesheet" type="text/css" href="/design/cms/css/auth.css">
	
	<script type="text/javascript" src="/extras/jquery/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="/extras/jquery/jqModal.js"></script>
	<script type="text/javascript" src="/extras/jquery/jquery.jgrowl.min.js"></script>
	<script type="text/javascript" src="/js/shared/jshttprequest.js"></SCRIPT>
	<script type="text/javascript" src="/js/shared/global.js"></SCRIPT>
	<script type="text/javascript" src="/js/user/user.js"></script>
	<script type="text/javascript" src="/js/user/oid_widget.js"></script>
		
	<!-- DELTA СООБЩЕНИЯ -->
	<link rel="stylesheet" type="text/css" href="/css/cms/message.css" />
	<script type="text/javascript" src="/js/cms/message.js"></script>
	<script type="text/javascript" src="/extras/jquery/jquery.blockUI.js"></script>
	
	<script type="text/javascript">
		function byId(id) {
			return document.getElementById(id);
		}
	</script>
</head>

<BODY onLoad="document.getElementById('login_id').focus();" >

<style>
	table.oid_block_table { width: 500px !important; }
	table.oid_block_table input[type=text] { width: 200px !important; }
	table.oid_block_table form { padding-bottom:0px; margin-bottom:0px; }
</style>

<script>
	$().ready(function() {
		<?php
			reset($this->vars['/onload/'][$__key]);
			while(list($_onload_key,) = each($this->vars['/onload/'][$__key])):
			?>
			<?php echo $this->vars['/onload/'][$__key][$_onload_key]['function']; ?>  
		<?php 
			endwhile;
			?>
	});
</script>
  
<?php
			reset($this->vars['/error/'][$__key]);
			while(list($_error_key,) = each($this->vars['/error/'][$__key])):
			?><div class="delta_error"><?php echo $this->vars['/error/'][$__key][$_error_key]['message']; ?></div><?php 
			endwhile;
			?>
<?php
			reset($this->vars['/success/'][$__key]);
			while(list($_success_key,) = each($this->vars['/success/'][$__key])):
			?><div class="delta_success"><?php echo $this->vars['/success/'][$__key][$_success_key]['message']; ?></div><?php 
			endwhile;
			?>
<?php
			reset($this->vars['/info/'][$__key]);
			while(list($_info_key,) = each($this->vars['/info/'][$__key])):
			?><div class="delta_info"><?php echo $this->vars['/info/'][$__key][$_info_key]['message']; ?></div><?php 
			endwhile;
			?>
<?php
			reset($this->vars['/warning/'][$__key]);
			while(list($_warning_key,) = each($this->vars['/warning/'][$__key])):
			?><div class="delta_warning"><?php echo $this->vars['/warning/'][$__key][$_warning_key]['message']; ?></div><?php 
			endwhile;
			?>

<div style="width:100%; color:#CC6600; margin-top:150px;">
	<center><a href="/" class="signature">Вернуться к просмотру сайта</a></center>
	<center><?php echo $this->vars['login_form']; ?></center>
	<center><br/><a href="http://www.delta-x.com.ua" class="signature">Copyright Delta-X &reg; ltd, 1998-2012</a></center>
</div>

</body>
</html>