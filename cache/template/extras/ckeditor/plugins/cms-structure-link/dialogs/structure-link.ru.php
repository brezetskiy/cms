<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<HTML>
<html>
<head>
<title>Вставка ссылки</title>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; CHARSET=Windows-1251">
	<META HTTP-EQUIV="imagetoolbar" CONTENT="no">
	<LINK href="/design/cms/css/toc.css" type="text/css" rel="stylesheet">
	<link href="/extras/ckeditor/plugins/cms-shared/dialog.css?2" rel="stylesheet" type="text/css" />
	<script language="JavaScript" src="/js/shared/global.js"></script>
	<script language="javascript" src="/js/shared/toc.js" type="text/javascript"></script>
	<script language="JavaScript">
		function insLink(link) {
			var CKEDITOR = window.parent.CKEDITOR;
			editor_name = CKEDITOR.config.activeEditorName
			var editorInstance = CKEDITOR.instances[editor_name];
			var mySelection = CKEDITOR.instances[editor_name].getSelection();
			
//			if (CKEDITOR.env.ie) {
//			    mySelection.unlock(true);
//			    selectedText = mySelection.getNative().createRange().text;
//			} else {
//			    selectedText = mySelection.getNative();
//			}

			selectedText = getSelectionHTML(mySelection.getNative())
			
			CKEDITOR.instances[editor_name].insertHtml("<a href='"+link+"'>"+selectedText+"</a>");
			CKEDITOR.dialog.getCurrent().hide()
		}
		
		// get HTML from selection
		function getSelectionHTML(selection)
		{
		   var range = (document.all ? selection.createRange() : selection.getRangeAt(selection.rangeCount - 1).cloneRange());
		
		   if (document.all)
		   {
		      return range.htmlText;
		   }
		   else
		   {
		      var clonedSelection = range.cloneContents();
		      var div = document.createElement('div');
		      div.appendChild(clonedSelection);
		      return div.innerHTML;
		   }
		}
	</script>
</head>
<body>

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
			<a id="a<?php echo $this->vars['/node/with_childs/'][$_node_key][$_node_with_childs_key]['id']; ?>" onclick="expand('<?php echo $this->vars['/node/with_childs/'][$_node_key][$_node_with_childs_key]['id']; ?>',this);" href="/tools/editor/dialog/local_link.php?id=<?php echo $this->vars['/node/with_childs/'][$_node_key][$_node_with_childs_key]['id']; ?>&action=<?php echo $this->global_vars['action']; ?>"><img align="absmiddle" src="http://<?php echo CMS_HOST; ?>/design/cms/img/js/toc/c.gif" id="img<?php echo $this->vars['/node/with_childs/'][$_node_key][$_node_with_childs_key]['id']; ?>"></a>
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