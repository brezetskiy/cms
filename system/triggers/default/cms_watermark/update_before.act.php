<?php
/**
 * �������, ������� ��������� ����� ��������� �������� �����
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */

if ($this->NEW['transparency'] == 100) {
	Action::onError(cms_message('CMS', '������ ��������� �������� ����� 100% ������������. ��� ����, ��� � ������ ������� ���� ��������� ��� ���������.'));
}

?>