<?php echo $this->vars['cms_view']; ?>


<DIV class="context_help">
��������!!! ����� ���, ��� ������� ������������ �������, ����������� ���� ���������� ���������� ������������ �������
� �������� � ���, ��� ��� ������� ���������. ��� ������������ ���������� ����� ������ �������� ������� ��� ����������������
���������� ���������� � ������ �� ����� ���� �������. ����������� ������� ��������� ����� �� ����� ������������� ������
��������!!!
<br>
<br>
ALL - ����������� ���������, ������� ��� ������! ������������ �� ��� ������ ������� ������������ ��� �� ����� ��� � 
� ���������������� ����������.
</DIV>

<h1>��������</h1>
<p><h3>�������� �������������� ������������ �������:</h3><UL>
<?php
			reset($this->vars['/add/'][$__key]);
			while(list($_add_key,) = each($this->vars['/add/'][$__key])):
			?>
	<li><a href="/<?php echo LANGUAGE_URL; ?>action/admin/sdk/language_add/?_return_path=<?php echo CURRENT_URL_LINK; ?>&interface=<?php echo $this->vars['/add/'][$__key][$_add_key]['id']; ?>"><?php echo $this->vars['/add/'][$__key][$_add_key]['title']; ?> (<?php echo $this->vars['/add/'][$__key][$_add_key]['name']; ?>)</a></li>
<?php 
			endwhile;
			?>
</UL>
<p><h3>������� ������ ������������ �������:</h3><UL>
<?php
			reset($this->vars['/del/'][$__key]);
			while(list($_del_key,) = each($this->vars['/del/'][$__key])):
			?>
	<li><a onclick="return confirm('�� ������� � ���, ��� ������ ������� ������������ ������� �� ��? \n��� ���������� � ���� �������� ����� ��������!');" href="/<?php echo LANGUAGE_URL; ?>action/admin/sdk/language_del/?_return_path=<?php echo CURRENT_URL_LINK; ?>&interface=<?php echo $this->vars['/del/'][$__key][$_del_key]['id']; ?>"><?php echo $this->vars['/del/'][$__key][$_del_key]['title']; ?> (<?php echo $this->vars['/del/'][$__key][$_del_key]['name']; ?>)</a></li>
<?php 
			endwhile;
			?>
</UL>
<p>
<h1>������������ �������</h1>
<?php
			reset($this->vars['/interface/'][$__key]);
			while(list($_interface_key,) = each($this->vars['/interface/'][$__key])):
			?>
<p><h3><?php echo $this->vars['/interface/'][$__key][$_interface_key]['name']; ?></h3>
<ul>
	<?php
			reset($this->vars['/interface/field/'][$_interface_key]);
			while(list($_interface_field_key,) = each($this->vars['/interface/field/'][$_interface_key])):
			?>
		<li><b><?php echo $this->vars['/interface/field/'][$_interface_key][$_interface_field_key]['table_name']; ?></b> (<?php echo $this->vars['/interface/field/'][$_interface_key][$_interface_field_key]['field']; ?></a>) </li>
	<?php 
			endwhile;
			?>
</ul>
<?php 
			endwhile;
			?>
