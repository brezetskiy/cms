<?php

require_once ("system/libs/excel_classes/PHPExcel.php");
require_once ("system/libs/excel_classes/PHPExcel/Writer/Excel2007.php");
$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);
$group_id = $_POST["id"];
//$group_id = 33270;
$counter = 2;

$objPHPExcel->getActiveSheet()->SetCellValue('A1','Марка');
$objPHPExcel->getActiveSheet()->SetCellValue('B1','Колекция');
$objPHPExcel->getActiveSheet()->SetCellValue('C1','Модель');
$objPHPExcel->getActiveSheet()->SetCellValue('D1','Старая цена');
$objPHPExcel->getActiveSheet()->SetCellValue('E1','Новая цена');

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);

$query_group_second_level = "
	SELECT
		group_id,
		uniq_name
		FROM shop_group
		WHERE `id`='".$group_id."';";
		$res_collection_second_level = $DB->query($query_group_second_level);	
	$marka = $res_collection_second_level[0]['uniq_name'];
	
	//echo ($marka);
	
	$query_group_first_level = "
		SELECT
			id,
			group_id,
			uniq_name
		FROM shop_group
		WHERE `group_id`='".$group_id."' and `active`='1';";
	$res_collection_first_level = $DB->query($query_group_first_level);
	//print_r($res_collection_first_level);		
	//$res_collection_first_level['uniq_name'];		
	//$res_collection_first_level['group_id'];	
	for ($i=0; $i<count($res_collection_first_level); $i++)
	{
		$temp_product_id = $res_collection_first_level[$i]["id"];
		$collection = $res_collection_first_level[$i]['uniq_name'];
		$query_group_product_level = "
			SELECT
				name,price
			FROM `shop_x_20022`
			WHERE `group_id`='".$temp_product_id."'";
		$res_collection_product_level = $DB->query($query_group_product_level);
		//print_r($res_collection_product_level);
		for ($j=0; $j<count($res_collection_product_level); $j++)
		{
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$counter, $marka);
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$counter, $collection);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$counter, $res_collection_product_level[$j]['name']);						
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$counter, $res_collection_product_level[$j]['price']);						
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$counter, $res_collection_product_level[$j]['price']);						
			//echo ($marka." - ".$collection." - ".$res_collection_product_level[$j]['name']."<br />");
			$counter++;
		};
		//print_r($res_collection_product_level);
	};
	
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$objWriter->save("extras/excel/price.xlsx");	

?>