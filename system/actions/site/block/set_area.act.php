<?
/**
 * ������������� ���� �������� �����
 * @package Pilot
 * @subpackage Block
 * @author Miha Barin <barin@id.com.ua>
 * @copyright Delta-X, ltd. 2010
 */

$name 		   = globalVar($_REQUEST['name'], '');
$area          = globalVar($_REQUEST['area'], '');
$structure_id  = globalVar($_REQUEST['structure_id'], 0);
$url  		   = globalVar($_REQUEST['url'], '');


/**
 * ������������ ���������� ������ �����
 * @param int $id
 * @param string $name
 * @param string $area
 */
function change_data($id, $name, $area, $content=''){
	global $_RESULT, $structure_id, $url;
	
	$edit_link = "<a href=\"javascript:void(0);\" onclick=\"EditorWindow(\'event=editor/content&id={$id}&table_name=block&field_name=content_".LANGUAGE_CURRENT."\', \'editor{$id}\'); return false;\"><img src=\"/img/block/edit.png\" border=\"0\"> �������������</a>"; 
	$_RESULT['javascript']  = "$('#".BLOCK_PREFIX."_{$name}_edit').html('$edit_link');";
	
	$menu = "<a id=\"".BLOCK_PREFIX."_{$name}_area_site\" href=\"javascript:void(0);\" onclick=\"AjaxRequest.send(null, '/action/block/set_area/', '��������...', true, {'name':'{$name}', 'area':'site', 'structure_id':{$structure_id}, 'url':'{$url}'}); return false;\" ";
	$menu .= ($area != 'site') ? "onmouseover=\"$(this).css({ opacity: 1 });\" onmouseout=\"$(this).css({ opacity: 0.3 });\" style=\"filter:alpha(opacity=30);moz-opacity: 0.30;opacity: 0.30;\"" : "";
	$menu .= "><img src=\"/img/block/web.png\" border=\"0\" title=\"��������� �� ���� ���������\"></a>&nbsp;&nbsp;";
	
	$menu .= "<a id=\"".BLOCK_PREFIX."_{$name}_area_page\" href=\"javascript:void(0);\" onclick=\"AjaxRequest.send(null, '/action/block/set_area/', '��������...', true, {'name':'{$name}', 'area':'page', 'structure_id':{$structure_id}, 'url':'{$url}'}); return false;\" ";
	$menu .= ($area != 'page') ? "onmouseover=\"$(this).css({ opacity: 1 });\" onmouseout=\"$(this).css({ opacity: 0.3 });\" style=\"filter:alpha(opacity=30);moz-opacity: 0.30;opacity: 0.30;\"" : "";
	$menu .= "><img src=\"/img/block/page.png\" border=\"0\" title=\"��������� � �������� �������\"></a>&nbsp;&nbsp;";
				
	$menu .= "<a id=\"".BLOCK_PREFIX."_{$name}_area_url\" href=\"javascript:void(0);\" onclick=\"AjaxRequest.send(null, '/action/block/set_area/', '��������...', true, {'name':'{$name}', 'area':'url', 'structure_id':{$structure_id}, 'url':'{$url}'}); return false;\" "; 
	$menu .= ($area != 'url') ? "onmouseover=\"$(this).css({ opacity: 1 });\" onmouseout=\"$(this).css({ opacity: 0.3 });\" style=\"filter:alpha(opacity=30);moz-opacity: 0.30;opacity: 0.30;\"" : "";
	$menu .= "><img src=\"/img/block/url.png\" border=\"0\" title=\"��������� � �������� URL\"></a>";
	
	$_RESULT[BLOCK_PREFIX.'_'.$name.'_menu'] = $menu;   
	
	if(!empty($content)) {
		$_RESULT[BLOCK_PREFIX.'_'.$name.'_content'] = $content;   
	} else {
		$_RESULT['javascript'] .= "EditorWindow('event=editor/content&id={$id}&table_name=block&field_name=content_".LANGUAGE_CURRENT."', 'editor{$id}');";  
	}
}


/**
 * �������� ���� �������
 */
if(!Auth::isLoggedIn()){
	$_RESULT[BLOCK_PREFIX.'_'.$name.'_content'] = '<div style="color:red; font-size:10px; text-align:center;">����������, ���������������.</div>';   
	exit;
} 
if(!Auth::isAdmin()){
	$_RESULT[BLOCK_PREFIX.'_'.$name.'_content'] = '<div style="color:red; font-size:10px; text-align:center;">� ��� ��� ���� �� �������������� ������.</div>';   
	exit;
}


/**
 * �������� ����� � �����
 */
if($area == 'site'){
	
	// �������� url � page �����, ���� ����� ����������
	$DB->query("DELETE FROM block WHERE uniq_name = '$name' AND ((area = 'url' AND url = '$url') OR (area = 'page' AND structure_id = '$structure_id'))");

	// �������� �� ������������� ������ ����� 
	$site_block = $DB->query_row("SELECT id, content_".LANGUAGE_CURRENT." as content, area FROM block WHERE uniq_name = '$name' AND area = 'site'");

	// ���� ����� ���� �� ����������, ������� �����
	if($DB->rows == 0){
		$site_block['id'] 	   = $DB->insert("INSERT INTO block SET uniq_name = '$name', title_".LANGUAGE_CURRENT." = '{$name}'");
		$site_block['content'] = "<div style='margin:10px; color:#999; text-align:center; font-size:10px;'>����� ����.<br/>����������, �������� �������.</div>";
	} 
	
	// ������������ ���������� �����
	change_data($site_block['id'], $name, 'site', $site_block['content']);
	
	
/**
 * �������� ����� � �������
 */
} elseif($area == 'page'){
	
	// �������� url �����, ���� ����� ����������
	$DB->query("DELETE FROM block WHERE uniq_name = '$name' AND area = 'url' AND url = '$url'");
	
	// �������� �� ������������� page �����
	$query = "
		SELECT id, content_".LANGUAGE_CURRENT." as content, area
		FROM block 
		WHERE uniq_name = '$name' AND area = 'page' AND structure_id = '$structure_id'
	";
	$page_block = $DB->query_row($query);  
	
	// ���� page ���� �� ����������, ������� �����
	if($DB->rows == 0){
		$query = "
			SELECT id, content_".LANGUAGE_CURRENT." as content, title_".LANGUAGE_CURRENT." as title, area
			FROM block 
			WHERE uniq_name = '$name' AND area = 'site'
		";
		$page_block = $DB->query_row($query);  
		if($DB->rows == 0){
			$_RESULT[BLOCK_PREFIX.'_'.$name.'_content'] = '
				<div style="color:red; font-size:10px; text-align:center;">���� ��� ������. ����������, ����������� ��������.</div>';
			exit;
		} 
		
		// �������� ������ page �����
		$query = "
			INSERT INTO block 
			SET uniq_name = '{$name}',
				content_".LANGUAGE_CURRENT." = '{$page_block['content']}',
				title_".LANGUAGE_CURRENT."   = '{$page_block['title']}',
				area 		 = 'page', 
				structure_id = '$structure_id', 
				url        	 = '$url'
			ON DUPLICATE KEY UPDATE title_".LANGUAGE_CURRENT." = VALUES(title_".LANGUAGE_CURRENT.")
		";
		$page_block['id'] = $DB->insert($query);  
	}
	
	// ������������ ���������� �����
	change_data($page_block['id'], $name, 'page', ($page_block['area'] == 'page') ? $page_block['content'] : '');	 

	
/**
 * �������� ����� � URL
 */	
} elseif($area == 'url'){
	
	// �������� �� ������������� page �����
	$query = "
		SELECT id, content_".LANGUAGE_CURRENT." as content, title_".LANGUAGE_CURRENT." as title
		FROM block 
		WHERE uniq_name = '$name' AND area = 'page' AND structure_id = '$structure_id'
	";
	$url_block = $DB->query_row($query);  
	
	// ���� page ���� �� ����������, ����� ����� ����
	if($DB->rows == 0){
		$query = "
			SELECT id, content_".LANGUAGE_CURRENT." as content, title_".LANGUAGE_CURRENT." as title
			FROM block 
			WHERE uniq_name = '$name' AND area = 'site'
		";
		$url_block = $DB->query_row($query);  
		if($DB->rows == 0){
			$_RESULT[BLOCK_PREFIX.'_'.$name.'_content'] = '
				<div style="color:red; font-size:10px; text-align:center;">���� ��� ������. ����������, ����������� ��������.</div>';
			exit;
		} 
	}
	 
	// �������� ������ url �����
	$query = "
		INSERT INTO block 
		SET uniq_name = '{$name}',
			content_".LANGUAGE_CURRENT." = '{$url_block['content']}',   
			title_".LANGUAGE_CURRENT."   = '{$url_block['title']}',
			area 		 = 'url',  
			structure_id = '$structure_id', 
			url        	 = '$url'
		ON DUPLICATE KEY UPDATE title_".LANGUAGE_CURRENT." = VALUES(title_".LANGUAGE_CURRENT.")
	";
	$url_block['id'] = $DB->insert($query);  
	
	if(empty($url_block['id'])){
		$url_block['id'] = $DB->result("SELECT id FROM block WHERE uniq_name = '$name' AND area = 'url' AND structure_id = '$structure_id' AND url = '$url'"); 
	}
	
	// ������������ ���������� �����
	change_data($url_block['id'], $name, 'url');	   
}





?>
