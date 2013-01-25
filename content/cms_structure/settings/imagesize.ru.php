<?php
/**
 * –азмер картинок, которые создаютс€ автоматически
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */

$query = "
	SELECT 
		tb_image.id, 
		tb_image.uniq_name, 
		tb_image.width, 
		tb_image.height, 
		tb_image.quality, 
		tb_watermark.name as watermark_id
	FROM cms_image_size as tb_image
	LEFT JOIN cms_watermark as tb_watermark on tb_watermark.id=tb_image.watermark_id
	ORDER BY uniq_name ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('uniq_name', '20%');
$cmsTable->addColumn('width', '20%');
$cmsTable->addColumn('height', '20%');
$cmsTable->addColumn('quality', '20%');
$cmsTable->addColumn('watermark_id', '20%');
echo $cmsTable->display();
?>