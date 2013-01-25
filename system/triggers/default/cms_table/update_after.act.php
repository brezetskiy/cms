<?php 
/**
* ��������� ���� � �������� ��� ��������� �������� �������
* @package Main_Actions
* @subpackage Triggers
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
*/


/**
 * ��������������� ���������� � ����������
 */
if ($this->OLD['db_id'] != $this->NEW['db_id'] || $this->OLD['name'] != $this->NEW['name']) {
	
	$query = "
		SELECT CONCAT(alias, '/')
		FROM cms_db
		WHERE id='".$this->OLD['db_id']."'
	";
	$source = TRIGGERS_ROOT . $DB->result($query) . $this->OLD['name'] .'/';
	
	$query = "
		SELECT CONCAT(alias, '/')
		FROM cms_db
		WHERE id='".$this->NEW['db_id']."'
	";
	$destination = TRIGGERS_ROOT . $DB->result($query) . $this->NEW['name'] .'/';
	
	if (file_exists($destination)) {
		// ���������� ���������� - ����������
		Action::setError(cms_message('CMS', '�� ������� ������������� ���������� � ����������, ��� ��� ���������� ���������� ��� ����������.'));
	} elseif (file_exists($source)) {
		// �������� ���������� - ����������
		Filesystem::rename($source, $destination, false);
		Action::setLog(cms_message('CMS', '���������� � ���������� ������� �������������.'));
	}
}


/**
 * �������� ���� � ���������
 */
if ($this->NEW['name'] != $this->OLD['name']) {
	
	// /i/
	$query = "select uniq_name from cms_image_size";
	$data =  $DB->fetch_column($query);
	reset($data); 
	while (list(,$row) = each($data)) { 
		Filesystem::delete(SITE_ROOT."i/$row/".$this->NEW['name'].'/');
		Filesystem::delete(SITE_ROOT."i/$row/".$this->OLD['name'].'/');
	}
	
	$source = UPLOADS_ROOT . $this->OLD['name'] . '/';
	$destination = UPLOADS_ROOT . $this->NEW['name'] . '/';
	
	if (is_dir($source) && !file_exists($destination)) {
		Filesystem::rename($source, $destination, false);
		Action::setLog(cms_message('CMS', '���������� � ���������� ������� �������������.'));
	} elseif (is_dir($source) && file_exists($destination)) {
		Action::setError(cms_message('CMS', '���������� � ���������� %s ������ �������������, ��� ��� ���������� ���������� ��� ����������.', Uploads::getURL($source)));
	} else {
		Action::setLog(cms_message('CMS', '� ������� ��� ����� � �������.'));
	}
	
	// �������� �������� �������
	$query = "select alias from cms_db where id='".$this->OLD['db_id']."'";
	$alias = $DB->result($query);
	$DBServer = DB::factory($alias);
	$query = "alter table `".$this->OLD['name']."` rename to `".$this->NEW['name']."`";
	$DBServer->query($query);
}


?>