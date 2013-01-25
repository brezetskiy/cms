<?php

/**
 * Класс для работы с шаблонами, расположенными в базе данных
 * @package Pilot
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
class TemplateDB extends Template {
	
	
	/**
	 * Название модуля
	 *
	 * @var string
	 */
	protected static $modules = null;
	
	
	/**
	 * Название шаблона
	 *
	 * @var string
	 */
	public $title = '';
	
	
	/**
	 * Конструктор
	 *
	 * @param string $table_name
	 * @param string $module
	 * @param string $template
	 * @param string $language
	 */
	public function __construct($table_name, $module, $template, $language = null) {
		global $DB;
		
		$this->initModules();
		$this->initRegularExpressions();
		
		$module = strtolower($module);
		$language = (is_null($language)) ? LANGUAGE_CURRENT: $language;
		
		if (!isset(self::$modules[$module])) trigger_error(cms_message('CMS', 'Модуль %s - не существует.', $module), E_USER_ERROR);
		$design_field = ($table_name == 'cms_mail_template') ? 'design_id, ' : '';
		
		$template_data = $DB->query_row("
			select 
				id, 
				$design_field 
				content_$language as content, 
				name_$language as name, 
				unix_timestamp(update_tstamp) as update_tstamp 
			from $table_name
			where module_id = '".self::$modules[$module]."' and uniq_name = '$template'
		");
		if ($DB->rows == 0) trigger_error(cms_message('CMS', 'Шаблон %s в модуле %s - не существует.', $template, $module), E_USER_ERROR);
		 
		$template_data['table'] = $table_name;
		
		$this->title = $template_data['name'];
		$this->compiled_file = CACHE_ROOT."template/content/$table_name/$module/$template.$language.php";
		$compile = true;
		
		if (is_file($this->compiled_file)) {
			$class_stat = stat(__FILE__);
			$compiled_stat = stat($this->compiled_file);
			$this->last_modified = $template_data['update_tstamp'];
			
			if ($compiled_stat['mtime'] > $template_data['update_tstamp'] && $compiled_stat['mtime'] > $class_stat['mtime']) $compile = false;
		}
		 
		$template_data['content'] = $this->initMailDesign($template_data); 
		if ($compile) $this->saveCompiledTemplate($this->compiled_file, $template_data['content']);
	}
	  
	
	/**
	 * Подргузка данных о модулях системы 
	 * @return void
	 */
	protected function initModules() {
		global $DB;
		
		if (!is_null(self::$modules)) return;
		self::$modules = $DB->fetch_column("select id, lower(name) as name from cms_module", 'name', 'id');
	}
	
	
	/**
	 * Помещает контент шаблона письма внутрь дизайна
	 *
	 * @param array $template
	 */
	protected function initMailDesign($template){
		global $DB;
		 
		if(empty($template['table']) || $template['table'] != 'cms_mail_template') return $template['content']; 
		if(empty($template['design_id'])) return $template['content'];
		    
		$design_path = $DB->result("
			SELECT CONCAT(tb_group.name, '/mail/', tb_design.name) as path
			FROM cms_mail_design as tb_design
			INNER JOIN site_template_group as tb_group ON tb_group.id = tb_design.group_id
			WHERE tb_design.id = '{$template['design_id']}'
		");
		
		$TmplMailDesign = new Template(SITE_ROOT.'design/'.$design_path);
		$TmplMailDesign->set('content', $template['content']);
		return $TmplMailDesign->display();
	}
	
}


?>