<?php echo $this->vars['cms_table']; ?>

<div class="context_help">
	<b>�������� ������� ���������� �� �������� ��������:</b>
		<a href="/<?php echo LANGUAGE_URL; ?>action/admin/gallery/gallery_order/?group_id=<?php echo $_GET['group_id']; ?>&direction=asc&_language=<?php echo LANGUAGE_CURRENT; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>">� ���������� �������</a>
		| <a href="/<?php echo LANGUAGE_URL; ?>action/admin/gallery/gallery_order/?group_id=<?php echo $_GET['group_id']; ?>&direction=desc&_language=<?php echo LANGUAGE_CURRENT; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>">� �������� �������</a>
</div>
<?php echo $this->vars['gallery']; ?>