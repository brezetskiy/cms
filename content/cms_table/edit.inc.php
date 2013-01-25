<?php
$cmsShowEdit = new cmsShowEdit($table_id, $id, $copy);
$cmsShowEdit->parseFields();
echo $cmsShowEdit->show();
?>