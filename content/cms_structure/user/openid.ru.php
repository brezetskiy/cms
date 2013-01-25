<?php
/** 
 * Флаги доступа 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

print "
	<div style='color:red; margin:10px; border:1px solid #ccc; padding:10px;'>
		ВАЖНО: виджет AuthOID работает с двумя протоколами OpenID и OAuth 2.0. Для провайдеров, у которых НЕ отмечена поддержка OpenID, дополнительно необходимо создать приложение на стороне провайдера и определить обработчики соединения с сервером.
	</div>
";


function cms_prefilter($row){
	$row['name'] = "<img src='/".UPLOADS_DIR."auth_user_oid_provider/icon_inline/".Uploads::getIdFileDir($row['id']).".".$row['icon_inline']."' align='absmiddle'> ". $row['name'];
	
	return $row;
}


$query = "
	select
		id,
		uniq_name,
		name_".LANGUAGE_CURRENT." as name,
		openid_enable,
		stat_reg,
		stat_auth,
		icon_inline,
		active,
		priority
	from auth_user_oid_provider
	order by priority ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_prefilter');

$cmsTable->addColumn('id', '5%', 'center', 'ID');
$cmsTable->addColumn('name', '25%');
$cmsTable->addColumn('uniq_name', '25%');
$cmsTable->addColumn('stat_reg', '10%');
$cmsTable->addColumn('stat_auth', '10%');
$cmsTable->addColumn('openid_enable', '10%');
$cmsTable->setColumnParam('openid_enable', 'editable', true);
$cmsTable->addColumn('active', '10%');
$cmsTable->setColumnParam('active', 'editable', true);
echo $cmsTable->display();