<?php echo $this->vars['cms_groups']; ?>
<div class="context_help">
	<b>�������� ������� ���������� ������� �� ����:</b>
	<li>�������� - 
		<a href="/<?php echo LANGUAGE_URL; ?>action/admin/shop/group_order/?order=name&group_id=<?php echo $this->vars['group_id']; ?>&direction=asc&_language=<?php echo LANGUAGE_CURRENT; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>">� ���������� �������</a>
		| <a href="/<?php echo LANGUAGE_URL; ?>action/admin/shop/group_order/?order=name&group_id=<?php echo $this->vars['group_id']; ?>&direction=desc&_language=<?php echo LANGUAGE_CURRENT; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>">� �������� �������</a>
	</li>
	<li>��� ����� - 
		<a href="/<?php echo LANGUAGE_URL; ?>action/admin/shop/group_order/?order=uniq_name&direction=asc&group_id=<?php echo $this->vars['group_id']; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>">� ���������� �������</a>
		| <a href="/<?php echo LANGUAGE_URL; ?>action/admin/shop/group_order/?order=uniq_name&direction=desc&group_id=<?php echo $this->vars['group_id']; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>">� �������� �������</a>
	</li>
</div>

<?php echo $this->vars['cms_products']; ?>

<?php echo $this->vars['cms_params']; ?>