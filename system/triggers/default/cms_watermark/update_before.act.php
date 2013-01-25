<?php
/**
 * —обытие, которое возникает после изменени€ вод€ного знака
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */

if ($this->NEW['transparency'] == 100) {
	Action::onError(cms_message('CMS', 'Ќельз€ назначать вод€ному знаку 100% прозрачность. ƒл€ того, что б убрать вод€ной знак отключите его наложение.'));
}

?>