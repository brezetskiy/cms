<?php echo $this->vars['cms_view']; ?>


<DIV class="context_help">
ВНИМАНИЕ!!! Перед тем, как удалять многоязычные колонки, обязательно надо произвести добавление многоязычных колонок
и убедится в том, что они успешно добавлены. При определенной комбинации смены языков удаление колонок без предварительного
добавления произведет к выходу из строя всей системы. Обязательно делайте резервную копию БД перед произведением данных
действий!!!
<br>
<br>
ALL - специальный интерфейс, удалять его нельзя! Используется он для таблиц которые отображаются как на сайте так и 
в административном интерфейсе.
</DIV>

<h1>Действия</h1>
<p><h3>Добавить несуществующие многоязычные колонки:</h3><UL>
<?php
			reset($this->vars['/add/'][$__key]);
			while(list($_add_key,) = each($this->vars['/add/'][$__key])):
			?>
	<li><a href="/<?php echo LANGUAGE_URL; ?>action/admin/sdk/language_add/?_return_path=<?php echo CURRENT_URL_LINK; ?>&interface=<?php echo $this->vars['/add/'][$__key][$_add_key]['id']; ?>"><?php echo $this->vars['/add/'][$__key][$_add_key]['title']; ?> (<?php echo $this->vars['/add/'][$__key][$_add_key]['name']; ?>)</a></li>
<?php 
			endwhile;
			?>
</UL>
<p><h3>Удалить лишние многоязычные колонки:</h3><UL>
<?php
			reset($this->vars['/del/'][$__key]);
			while(list($_del_key,) = each($this->vars['/del/'][$__key])):
			?>
	<li><a onclick="return confirm('Вы уверены в том, что хотите удалить многоязычные колонки из БД? \nВся информация в этих колонках будет потеряна!');" href="/<?php echo LANGUAGE_URL; ?>action/admin/sdk/language_del/?_return_path=<?php echo CURRENT_URL_LINK; ?>&interface=<?php echo $this->vars['/del/'][$__key][$_del_key]['id']; ?>"><?php echo $this->vars['/del/'][$__key][$_del_key]['title']; ?> (<?php echo $this->vars['/del/'][$__key][$_del_key]['name']; ?>)</a></li>
<?php 
			endwhile;
			?>
</UL>
<p>
<h1>Многоязычные колонки</h1>
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
