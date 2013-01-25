<?php
/**
 * ������� �����, ������� ���� � �������
 * @package Comment
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$query = "
	SELECT
		id,
		name,
		CASE pos_x
			WHEN 'left' THEN '�����'
			WHEN 'center' THEN '�� ������'
			WHEN 'right' THEN '������'
		END AS pos_x,
		CASE pos_y
			WHEN 'top' THEN '������'
			WHEN 'center' THEN '�� ������'
			WHEN 'bottom' THEN '�����'
		END AS pos_y,
		pad_x,
		pad_y,
		CONCAT(transparency, '%') AS transparency,
		use_in_editor
	FROM cms_watermark
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('name', '10%');
$cmsTable->addColumn('pos_y', '10%');
$cmsTable->addColumn('pos_x', '10%');
$cmsTable->addColumn('pad_x', '10%');
$cmsTable->addColumn('pad_y', '10%');
//$cmsTable->addColumn('transparency', '10%');
$cmsTable->addColumn('use_in_editor', '10%');
$cmsTable->setColumnParam('use_in_editor', 'editable', true);
echo $cmsTable->display();
?>