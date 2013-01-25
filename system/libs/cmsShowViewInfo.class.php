<?php
/**
 * –асширение дл€ класса cmsShowView, которое позвол€ет выводить простые справочники
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */


class cmsShowViewInfo extends cmsShowView {
	
	public function __construct(DB $DBServer, $table_name) {
		$table = cmsTable::getInfoByAlias($DBServer->db_alias, $table_name);
		$query = "select * from `$table_name`";
		if (!empty($table['fk_order_name'])) {
			$query .= " order by `$table[fk_order_name]` $table[fk_order_direction]";
		}
		parent::__construct($DBServer, $query, CMS_VIEW, $table_name);
		$this->addColumn('name', '90%');
	}
}

?>