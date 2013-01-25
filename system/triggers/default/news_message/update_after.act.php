<?php

require_once($this->triggers_root.'insert_after.act.php');

/**
 * Определяем URL
 */	
$query = "	
	SELECT 
		GROUP_CONCAT(tb_type.uniq_name  SEPARATOR '/') as family
    FROM news_type_relation tb_relation 
    INNER JOIN news_type as tb_type ON (tb_relation.id = tb_type.id)
    WHERE tb_relation.parent = '".$this->NEW['type_id']."'
"; 
$family = $DB->result($query);

$query = "UPDATE `news_message` SET url='$family/{$this->NEW['uniq_name']}' WHERE id='{$this->NEW['id']}'"; 
$DB->update($query);

if ($DB->result("SELECT active FROM news_message WHERE id ='{$this->NEW['id']}'") == 0) {
	Search::delete('news_message', $this->NEW['id']);
} 

?>
