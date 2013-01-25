<?php
/**
 * Добавляем в интерфейс ALL все языки
 */

$query = "SELECT id FROM cms_interface WHERE name='ALL'";
$all_id = $DB->result($query);

$query = "DELETE FROM cms_language_usage WHERE interface_id='".$all_id."'";
$DB->delete($query);

$query = "SELECT DISTINCT language_id FROM cms_language_usage WHERE interface_id!='$all_id'";
$languages = $DB->fetch_column($query);

$query = "INSERT INTO cms_language_usage (language_id, interface_id) VALUES ('".implode("', '$all_id'), ('", $languages)."', '$all_id')";
$DB->insert($query);

// Обновляем конфиг
Install::updateMyConfig();



?>