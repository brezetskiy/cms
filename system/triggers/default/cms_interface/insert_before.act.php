<?php
if (!in_array($this->NEW['default_language'], $this->NEW['_language'])) {
	Action::onError(cms_message('CMS', '�����, ������� ������ "�� ���������" ��� � ������ ����������.'));
}

?>