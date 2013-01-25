<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<HTML>
<html>
<head>
<title>Вставка ссылки</title>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; CHARSET=Windows-1251">
	<META HTTP-EQUIV="imagetoolbar" CONTENT="no">
	<LINK href="/design/cms/css/toc.css" type="text/css" rel="stylesheet">
	<script language="JavaScript" src="/js/shared/global.js"></script>
	<script language="javascript" src="/js/shared/toc.js" type="text/javascript"></script>
	<script language="JavaScript">
		function insLink(link) {
			window.opener.frames.EditFrame.focus();
			var range = window.opener.frames.EditFrame.document.selection.createRange();
			range.pasteHTML('<a href="' + link + '">' + range.htmlText + '</a>');
			window.close();
		}
	</script>
</head>
<body on1selectstart="javascript:return false;" onKeyPress="EnterEsc(event);" onContextMenu="return false;">
<a href="javascript:insLink('/');">Корень сервера</a>

<?php
			reset($this->vars['/ul_root/'][$__key]);
			while(list($_ul_root_key,) = each($this->vars['/ul_root/'][$__key])):
			?>
<ul id="ULRoot" class="Shown" style="behavior:url('#default#saveSnapshot')">
<?php 
			endwhile;
			?>

<?php
			reset($this->vars['/ul_hidden/'][$__key]);
			while(list($_ul_hidden_key,) = each($this->vars['/ul_hidden/'][$__key])):
			?>
	<ul class="Hidden" ID="ul<?php echo $this->vars['/ul_hidden/'][$__key][$_ul_hidden_key]['id']; ?>">
<?php 
			endwhile;
			?>

<?php
			reset($this->vars['/node/'][$__key]);
			while(list($_node_key,) = each($this->vars['/node/'][$__key])):
			?>
	<?php
			reset($this->vars['/node/with_childs/'][$_node_key]);
			while(list($_node_with_childs_key,) = each($this->vars['/node/with_childs/'][$_node_key])):
			?>
		<li>
			<a id="a<?php echo $this->vars['/node/with_childs/'][$_node_key][$_node_with_childs_key]['id']; ?>" onclick="expand('<?php echo $this->vars['/node/with_childs/'][$_node_key][$_node_with_childs_key]['id']; ?>',this);" href="/tools/editor/dialog/local_link.php?id=<?php echo $this->vars['/node/with_childs/'][$_node_key][$_node_with_childs_key]['id']; ?>&action=<?php echo $this->global_vars['action']; ?>"><img align="absmiddle" src="/design/cms/img/js/toc/c.gif" id="img<?php echo $this->vars['/node/with_childs/'][$_node_key][$_node_with_childs_key]['id']; ?>"></a>
			<a href="javascript:insLink('<?php echo $this->vars['/node/with_childs/'][$_node_key][$_node_with_childs_key]['url']; ?>');"><?php echo $this->vars['/node/with_childs/'][$_node_key][$_node_with_childs_key]['name']; ?></a>
			<IFRAME NAME="fra<?php echo $this->vars['/node/with_childs/'][$_node_key][$_node_with_childs_key]['id']; ?>"></IFRAME>
		</li>
		<ul class="Hidden" ID="ul<?php echo $this->vars['/node/with_childs/'][$_node_key][$_node_with_childs_key]['id']; ?>"></ul>
	<?php 
			endwhile;
			?>
	<?php
			reset($this->vars['/node/no_childs/'][$_node_key]);
			while(list($_node_no_childs_key,) = each($this->vars['/node/no_childs/'][$_node_key])):
			?>
		<LI><img align="absmiddle" src="/img/1x1.gif"> <a href="javascript:insLink('<?php echo $this->vars['/node/no_childs/'][$_node_key][$_node_no_childs_key]['url']; ?>');"><?php echo $this->vars['/node/no_childs/'][$_node_key][$_node_no_childs_key]['name']; ?></a></LI>
	<?php 
			endwhile;
			?>
<?php 
			endwhile;
			?>

</ul>

<?php
			reset($this->vars['/script/'][$__key]);
			while(list($_script_key,) = each($this->vars['/script/'][$__key])):
			?>
<script language="javascript" type="text/javascript">
if (window.parent.loadNode != null && window.parent != self) {
	elem = document.getElementById("ul<?php echo $this->vars['/script/'][$__key][$_script_key]['id']; ?>");
	if (elem && elem.innerHTML) {
		window.parent.loadNode("<?php echo $this->vars['/script/'][$__key][$_script_key]['id']; ?>", elem);
		location.replace("about:blank");
	}
}
</script>
<?php 
			endwhile;
			?>
</body>
</HTML>