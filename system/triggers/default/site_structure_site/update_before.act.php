<?php

/**
 * ѕровер€ем уникальность алиасов
 */

$aliases = preg_split("~[\r\n\s\t,]+~", $this->NEW['aliases'], -1, PREG_SPLIT_NO_EMPTY);

reset($aliases); 
while (list(,$row) = each($aliases)) { 
	$DB->query("select * from site_structure_site_alias where url = '$row' and site_id != '{$this->NEW['id']}'"); 
	if ($DB->rows > 0) {
		Action::onError("ѕсевдоним $row уже используетс€ на другом сайте");
	}
}

?>