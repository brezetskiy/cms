<?php


if($this->NEW['data_type'] == 'devider' && empty($this->NEW['is_display'])){
	
	/**
	 * Связи: группа - параметры
	 */
	$tmp = $DB->query("SELECT * FROM auth_user_group_param_relation");
	$group_param_relation = array();
	
	reset($tmp);
	while(list(, $row) = each($tmp)){
		$group_param_relation[$row['group_id']][] = $row['param_id'];
	}


	/**
	 * Вывод групп пользователей
	 */
	$groups = $DB->query("SELECT id, name, comment FROM auth_user_group ORDER BY priority");

	if($DB->rows > 0){
		
		// Определяем параметры группы
		$data = $DB->query("
			select
				tb_param.id AS param_id,
				tb_param.uniq_name,
				tb_param.name,
				tb_param.description,
				tb_param.data_type,
				case tb_param.data_type
					when 'char' then 'value_char'
					when 'file' then 'value_char'
					when 'image' then 'value_char'
					when 'decimal' then 'value_decimal'
					when 'bool' then 'value_int'
					when 'fkey' then 'value_int'
					when 'fkey_table' then 'value_int'
					when 'date' then 'value_date'
					else 'value_text'
				end as field_type,
				tb_param.info_id,
				tb_param.fkey_table_id,
				tb_param.is_display,
				tb_param.required     
			from auth_user_group_param as tb_param         
			order by tb_param.priority asc
		");
		
		$insert = array();
		$devider_found = false;
		
		reset($data); 
		while (list(, $row) = each($data)) {
			
			if($row['data_type'] == 'devider' && $row['uniq_name'] == $this->NEW['uniq_name']) $devider_found = true;
			if($row['data_type'] == 'devider' && $row['uniq_name'] != $this->NEW['uniq_name']) $devider_found = false;
			
			if(!$devider_found) continue;
			if ($row['data_type'] == 'devider') continue;
			
			$insert[] = "('{$row['param_id']}', '0', '0')";
		}
		
		if(!empty($insert)){  
			$DB->insert("
				INSERT INTO auth_user_group_param (id, required, is_display)
				VALUES ".implode(', ', $insert)."   
				ON DUPLICATE KEY UPDATE required=VALUES(required), is_display=VALUES(is_display)
			"); 
		}
	}
}




?>