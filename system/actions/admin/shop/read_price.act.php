<?php
header('Content-Type: text/html; charset=utf-8');
require_once ("system/libs/excel_classes/PHPExcel.php");
require_once ("system/libs/excel_classes/PHPExcel/Reader/Excel2007.php");

function getExtension($filename) {
    return end(explode(".", $filename));
  }

  
if ($_FILES["price"]["error"] > 0)
{
	echo "Return Code: " . $_FILES["error"] . "<br />";
}
else
{
	$ext = getExtension($_FILES["price"]["name"]);
	if ($ext != "xlsx") exit;
	move_uploaded_file($_FILES["price"]["tmp_name"], "extras/excel/price.xlsx");
	$objReader = PHPExcel_IOFactory::createReader('Excel2007');
	$objReader->setReadDataOnly(true);

	$objPHPExcel = $objReader->load("extras/excel/price.xlsx");
	$objWorksheet = $objPHPExcel->getActiveSheet();

	$row_num   = 0;
	$model     = "";
	$price_old = '';
	$price_new = '';
	foreach ($objWorksheet->getRowIterator() as $row) {

		if ($row_num)
		{
			$cell_num = 0;
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			foreach ($cellIterator as $cell)
			{
				if ($cell_num==2) $model = $cell->getValue();
				if ($cell_num==3) $price_old = $cell->getValue();
				if ($cell_num==4) $price_new = $cell->getValue();
				$cell_num++;
			};
			
			$price_old = str_replace(",",".",$price_old);
			$price_new = str_replace(",",".",$price_new);
			
			if ($price_old != $price_new)
			{
				//echo ($model . " - ". $price_old . " - " . $price_new . "<br />");
				$query = "
					update `shop_x_20022` set 
						`price`='".$price_new."'
					where `name`='".$model."'";
				$DB->update($query);	
				
				$query_2 = "
					update `shop_product` set 
						`price`='".$price_new."'
					where `name`='".$model."'";
				$DB->update($query_2);				
			};
		};
		
		$row_num++;
	};	
}



echo ("<!DOCTYPE html><script>window.close();</script>");

?>