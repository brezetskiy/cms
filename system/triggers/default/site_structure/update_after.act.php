<?php
$Structure = new Structure($this->table['table_name']);
$new_url = $DB->result("SELECT url FROM site_structure WHERE id='".$this->NEW['id']."'");
$Structure->move($this->OLD['url'], $new_url);

/**
 * ��������� ����
 */
if (empty($this->NEW['structure_id']) && empty($this->OLD['structure_id'])) {
	// ���������� ������������� �����
	Structure::updateSite($this->NEW['id'], $this->NEW['uniq_name']);
	
} elseif (empty($this->NEW['structure_id']) && !empty($this->OLD['structure_id'])) {
	// ������� ������� �� ������� ������� - �������� ������ �����
	Structure::createSite($this->NEW['id'], $this->NEW['uniq_name'], $this->NEW['template_id']);
	
} elseif (!empty($this->NEW['structure_id']) && empty($this->OLD['structure_id'])) {
	// ������� ����� � ��������� ������� ����� - ������� ���� ����� �������
	Structure::deleteSite($this->OLD['id']);
}

if ($DB->result("SELECT active FROM site_structure WHERE id ='{$this->NEW['id']}'") == 'false') {
	Search::delete('site_structure', $this->NEW['id']);
}
/**
 * ��������� ������ � ����������� ��������
 */
$url_new = $DB->result("SELECT url FROM site_structure WHERE id = '{$this->NEW['id']}'");
if($this->OLD['url'] != $url_new){
	$query = "
		INSERT INTO site_structure_redirect 
		SET structure_id = '{$this->NEW['id']}', 
			url_old   = '{$this->OLD['url']}', 
			url_new   = '{$url_new}', 
			admin_id  = '".Auth::getUserId()."',
			operation = 'update',
			dtime     = '".date("Y-m-d H:i:s")."'
	";
	$DB->insert($query); 
} 


?>