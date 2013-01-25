<?php
// ���������, ���� �� ����� ������� � �������
if (!Auth::structureAccess($this->OLD['id'])) {
	Action::onError(cms_message('CMS', '� ��� ��� ���� �� �������������� ������� �������'));
}

// ��������� ����������� �������
if ($this->NEW['structure_id'] != $this->OLD['structure_id'] && !Auth::structureAccess($this->NEW['structure_id'])) {
	Action::setWarning(cms_message('CMS', '�� ��������� ����������� ������ �� ������� ����� ����� �����.'));
	$this->NEW['structure_id'] = $this->OLD['structure_id'];
}

if (!IS_DEVELOPER) {
//	x($this->NEW); exit;
	$url = SITE_ROOT.'content/site_structure/'.$this->NEW['url'].'.ru.php';
	if(file_exists($url)) {
		$old_uniq_name = $DB->result("select uniq_name from site_structure where id = '".$this->NEW['id']."'");
		if ($old_uniq_name != $this->NEW['uniq_name']) {
			Action::onError(cms_message('CMS', '� ��� ��� ���� �� ��������� ����� ����� ������� �������'));
		}
	}
}