<?php
/** 
 * ��������, ������� ���������� ������ ������� ������ ��� ���� ���� ext_select
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 * @todo 
 * 1. ��������� �������� �� ����������.
 * 2. �������������� ������.
 * 4. ���������� �� ����������� ��������
 * 6. ��������� ����������� � 5 �������
 */ 

/**
 * �������, �� ������� ��������� �������, ������� �������������
 */
define('LEVEL', globalVar($_REQUEST['level'], 0));

/**
 * id �������, �� ������� ��������� ����������� ����. 
 * ���� �������� ��������� ��� ���������� ������
 */
define('FK_TABLE_ID', globalVar($_REQUEST['fk_table_id'], 0));

/**
 * �������� ��������� name � �������� checkbox
 */
define('CHECKBOX_NAME', globalVar($_REQUEST['checkbox_name'], ''));

/**
 * id ����, ������� �������������. ������������ ��� ����������� ��������,
 * ������� ��� ���� � ������ ����. ����� ���� ������ �������� ���������� ��� ������
 * ���������, ��� � �������� ��� ��������� ����. ����� ��� �������� �������� �������.
 */
define('MASTER_ID', globalVar($_REQUEST['master_id'], 0));

/**
 * id ������, ������ �� ������� ����� � �������� ������� �������.
 * ������������ ��� ������� ��������, ������� ������������ � �������.
 */
define('PARENT_ID', globalVar($_REQUEST['parent_id'], 0));

/**
 * id ����, � ������� ����� ����������� ����������, �������������� ������ ��������
 * ������������ ��� ����� + �� �����, ���� � ������ ������� ��� �����������.
 */
define('PARENT_ELEMENT', globalVar($_REQUEST['parent_element'], ''));

/**
 * �������� �������, � ������� ����������� �����
 * 
 */
define('RELATION_TABLE_NAME', globalVar($_REQUEST['relation_table_name'], ''));

/**
 * �������� ������� � ������� RELATION_TABLE_NAME, � ������� ��������� �� ������� FK_TABLE_ID
 *
 */
define('RELATION_SELECT_FIELD', globalVar($_REQUEST['relation_select_field'], ''));

/**
 * �������� ������� � ������� RELATION_TABLE_NAME, � ������� �������� �������� MASTER_ID
 *
 */
define('RELATION_PARENT_FIELD', globalVar($_REQUEST['relation_parent_field'], ''));

/**
 * �������� ������������ ������, ������������ ���:
 * 1. ����������� �������� ������ � ��� �����, ������� �������� ������������� � �������
 * 2. ����������� ������ ������� �������.
 * 3. ����������� ������� �������� ������ � �������, ������� ������ ����������.
 */
$parent_tables = cmsTable::getParentTables(FK_TABLE_ID);

// ������� �� ������ ������������ ������� �� ������, ������� ��� �������
if (count($parent_tables) > 1) {
	for($i=0; $i<LEVEL-1;$i++) {
		array_shift($parent_tables);
	}
}

// ���������� � �������, ������� �� �������
$table = cmsTable::getInfoById(reset($parent_tables));

$DBServer = DB::factory($table['db_alias']);


// ���������� �������� ���������, ������� ���������� �������������
if ($table['id'] != $table['parent_table_id']) {
	Misc::extMultipleOpen($DBServer, MASTER_ID, $parent_tables, RELATION_TABLE_NAME, RELATION_SELECT_FIELD, RELATION_PARENT_FIELD);
} else {
	$query = "
		DROP TEMPORARY TABLE IF EXISTS `tmp_open`;
		CREATE TEMPORARY TABLE `tmp_open` (id INT UNSIGNED NOT NULL, PRIMARY KEY (id)) ENGINE=MyISAM;
	";
	$DBServer->multi($query);
	$query = "
		INSERT IGNORE INTO tmp_open (id) 
		SELECT tb_optimized.parent
		FROM `".$table['relation_table_name']."` AS  tb_optimized
		INNER JOIN `".RELATION_TABLE_NAME."` AS tb_relation ON tb_optimized.id=tb_relation.`".RELATION_SELECT_FIELD."`
		WHERE 
			tb_relation.`".RELATION_PARENT_FIELD."`='".MASTER_ID."'
			AND tb_optimized.id<>tb_optimized.parent
	";
	$DBServer->insert($query);
}

$code = uniqid();
$query = "
	SELECT
		tb_main.id,
		tb_main.`$table[fk_show_name]` AS name,
		IF(tb_relation.`".RELATION_SELECT_FIELD."` IS NULL, '', 'checked') AS checked,
		IF(tb_open.id IS NOT NULL, 'true', 'false') AS open
	FROM `$table[table_name]` AS tb_main
	LEFT JOIN `".RELATION_TABLE_NAME."` AS tb_relation ON tb_relation.`".RELATION_PARENT_FIELD."`='".MASTER_ID."' AND tb_relation.`".RELATION_SELECT_FIELD."`=tb_main.id
	LEFT JOIN tmp_open AS tb_open ON tb_open.id=tb_main.id
	WHERE tb_main.`$table[parent_field_name]`='".PARENT_ID."'
	ORDER BY tb_main.`$table[fk_order_name]` ASC
";
$data = $DBServer->query($query);
if ($DBServer->rows == 0) {
	$_RESULT = array('exec' => "extMultipleNoSubmenu('".PARENT_ELEMENT."');");
//	echo '<span class="comment" style="margin-left:'.(10 * LEVEL).'px;">��� ����������</span><br>';
	exit;
}

$exec = array();
reset($data);
while(list($index, $row) = each($data)) {
	if (count($parent_tables) > 1 || $table['id'] == $table['parent_table_id']) {
		$function = 'extMultiple(\''.$code.'_'.$row['id'].'\', \''.CHECKBOX_NAME.'\', '.FK_TABLE_ID.', '.(LEVEL + 1).', '.$row['id'].', '.MASTER_ID.', \''.RELATION_TABLE_NAME.'\', \''.RELATION_SELECT_FIELD.'\', \''.RELATION_PARENT_FIELD.'\');';
		$style = ' style="margin-left:'.(10 * LEVEL).'px;" ';
//		$checked = ($row['open'] === 'true') ? 'checked' : '';
		if ($table['id'] == $table['parent_table_id']) {
			echo '<input '.$style.' type="checkbox" '.$row['checked'].' name="'.CHECKBOX_NAME.'" value="'.$row['id'].'"> ';
			$style = '';
		}
		echo '<a hidefocus '.$style.' href="javascript:void();" onclick="'.$function.'; return false;"><img src="/img/shared/toc/plus.png" border="0" width="11" height="11" id="img_'.$code.'_'.$row['id'].'"> '.$row['name'].'</a><br><div style="display:none;" id="'.$code.'_'.$row['id'].'"></div>';
		if ($row['open'] == 'true') {
			$exec[] = $function;
		}
	} else {
		echo '<input '.$row['checked'].' style="margin-left:'.(10 * LEVEL).'px;" type="checkbox" name="'.CHECKBOX_NAME.'" value="'.$row['id'].'" id="'.$code.'_'.$row['id'].'"><label for="'.$code.'_'.$row['id'].'">'.$row['name'].'</label><br>';
	}
}

$_RESULT = array('exec' => implode("; ", $exec));

exit;
?>