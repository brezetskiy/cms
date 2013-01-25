<?php
/** 
 * ������� �������� ������� � ���� ������
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.ua> 
 * @copyright Delta-X, ltd. 2009 
 */ 


class DateToString {
	
	public static function get($tstamp) {
		$delta = time() - $tstamp;
		$yesterday = mktime(0,0,0,date('m'),date('d')-1, date('Y'));
		if ($delta < 3600) {
			// ������ ����
			return self::getMinute(ceil($delta/60));
		} elseif ($delta >= 3600 && $delta < 3600*12) {
			// 1-12 ����� �����
			$hour = self::getHour(floor($delta/3600));
			return $hour.' '.self::getMinute(ceil($delta/60));
		} elseif (date('d.m.Y') == date('d.m.y', $tstamp)) {
			// �������, ����� 12 ����� �����
			return "������� � ".date('h:i', $tstamp); 
		} elseif (date('d.m.Y', $yesterday) == date('d.m.Y', $tstamp)) {
			// �����
			return '����� � '.date('H:i', $tstamp);
		} else {
			return date(LANGUAGE_DATE.' H:i', $tstamp);
		}
	}
	
	private static function getMinute($minute) {
		$minute = $minute % 60;
		if (in_array($minute, array(1,21,31,41,51))) {
			return $minute.' ������ �����';
		} elseif (in_array($minute, array(2,22,23,3,32,33,34,4,42,43,44,52,53,54))) {
			return $minute.' ������ �����';
		} else {
			return $minute.' ����� �����';
		}
	}
	
	private static function getHour($hour) {
		if ($hour == 1) {
			return '1 ���';
		} elseif (in_array($hour, array(2,3,4))) {
			return $hour.' ����';
		} else {
			return $hour.' �����';
		}
	}
}



?>