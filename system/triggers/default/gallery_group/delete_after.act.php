<?php
/**
 * Удаление группы фотогаллереи
 * @package Pilot
 * @subpackage Gallery
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

$Gallery = new Gallery('gallery_group', $this->OLD['id']);
$Gallery->deleteGroup();

?>