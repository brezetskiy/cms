<?php
/**
 * —качивание измененных файлов
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@id.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$uid = date('Ymd_His');
$list = TMP_ROOT . 'patch_'. $uid .'.txt';
$data = '';

if(ini_get('safe_mode') != 0) {
	Action::onError(cms_message('CMS', 'Ќевозможно выполнить операцию в безопасном режиме')); // невозможно выполнить операцию в безопасном режиме
}

reset($_POST['files']);
while (list(,$file) = each($_POST['files'])) {
	$data .= $file."\n";
}

file_put_contents($list, $data);

exec('/bin/tar -czf '.TMP_ROOT.$uid.'.tar.gz --files-from='.$list, $output).' --directory='.SITE_ROOT;


header('Content-Type: application/download_file');
header('Content-Disposition: attachment; filename="'.$uid.'.tgz"');
echo file_get_contents(TMP_ROOT.$uid.'.tar.gz');
unlink($list);
unlink(TMP_ROOT.$uid.'.tar.gz');
exit;
?>