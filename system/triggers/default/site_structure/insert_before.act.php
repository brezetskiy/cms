<?php
// ���� �� ������ � ������������� �������
if (!Auth::structureAccess($this->NEW['structure_id'])) {
	Action::onError(cms_message('CMS', '�� �� ������ ��������� �������� � ���� �������'));
}
