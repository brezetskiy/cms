<?php
// ��������� ����� ������ � ���������� �������
if (!Auth::structureAccess($current_id)) {
	Action::onError(cms_message('CMS', '� ��� ��� ���� �� �������������� ������� �������'));
}

if (!IS_DEVELOPER) {
	$url = $DB->result("select url from site_structure where id=$current_id");
	if(file_exists(SITE_ROOT.'content/site_structure/'.$url.'.ru.php')) {
		Action::onError(cms_message('CMS', '� ��� ��� ���� �� �������� ������� �������'));
	}
}