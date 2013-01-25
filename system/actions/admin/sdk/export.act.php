<?php
/**
 * Ёкспорт данных
 */
$table_id = globalVar($_POST['table_id'], 0);
$parent_id = globalVar($_POST['parent_id'], 0);
$export_id = globalVar($_POST['export_id'], array());
$export_tables = globalVar($_POST['export_tables'], array());

// ќпредел€ем им€ файла
$query = "SELECT name FROM cms_table WHERE id='".$table_id."'";
$table_name = $DB->result($query);

header('Content-Type: application/download_file');
header('Content-Disposition: attachment; filename="'.$table_name.'_'.date('Ymd').'.xml"');

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n<root>";
SQLExport::export2xml($table_id, $parent_id, $export_tables, $export_id);
echo "\n</root>";
exit;
?>