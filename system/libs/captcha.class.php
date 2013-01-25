<?php
/** 
 * Класс для генерации картинок CAPTCHA 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

/**
 * Класс для генерации картинок CAPTCHA
 */
class Captcha {
	
	/**
	 * Функция формирует значения для вывода на картинке
	 * @return string
	 */
//	private static function captchaValue() {
//		return Misc::randomKey(6, '0123456789');
//	}
	
	/**
	 * Создает новую картинку CAPTCHA
	 * Возвращает uid картинки, который будет использоваться при проверке 
	 * правильности ввода пользователя
	 * 
	 * Параметр UniqId предназначен для того, чтобы не забивать сессию лишними данными.
	 * В сессии может существовать только одна запись с указанным uniqid.
	 * Стоит использовать значения вроде 'comment_site_$structure_id', т.е. уникальные
	 * в пределах просматриваемой страницы
	 * 
	 * @return string
	 */
	public static function create($uniq_id = null, $char_count = 6, $width = 120, $height = 30, $chars = '0123456789') {
		self::init($uniq_id);
		
		$key = md5(microtime(true).Misc::randomKey(32));
		$_SESSION['Captcha'][$key] = array(
			'value' => Misc::randomKey($char_count, $chars), 
			'expire' => time()+1200, 
			'ip' => HTTP_IP, 
			'uniq_id' => $uniq_id,
			'count' => $char_count,
			'w' => $width,
			'h' => $height,
			'chars' => $chars 
		);
//		var_dump($_SESSION['Captcha']);
		return $key;
	}
	
	/**
	 * Аналог Captcha::create, но возвращает сразу HTML код для 
	 * вставки в страницу. Используется, если нет необходимости знать uid картинки
	 *
	 * @param string $uniq_id
	 */
	public static function createHtml($uniq_id = null, $char_count = 6, $width = 120, $height = 30, $chars = '0123456789') {
		return self::getHtml(self::create($uniq_id, $char_count, $width, $height, $chars));
	}
	
	/**
	 * Возвращает HTML код с картинкой для вставки в формы, использующие 
	 * CAPTCHA
	 *
	 * @param string $captcha_id
	 * @param string $captcha_name
	 */
	public static function getHtml($captcha_id, $captcha_name = 'captcha_uid') {
		if (isset($_SESSION['Captcha'][$captcha_id])) {
			$width = $_SESSION['Captcha'][$captcha_id]['w'];
			$height = $_SESSION['Captcha'][$captcha_id]['h'];
		} else {
			$width = 120;
			$height = 30;
		}
		return "
			<input type=\"hidden\" name=\"$captcha_name\" value=\"$captcha_id\">
			<img width=\"$width\" height=\"$height\" id=\"img_$captcha_name\" src=\"/tools/cms/site/captcha.php?uid=$captcha_id\"><br>
			<span class=\"comment\"><a href=\"#\" onclick=\"byId('img_$captcha_name').src = '/tools/cms/site/captcha.php?uid=$captcha_id&refresh='+Math.round(Math.random()*100000); this.blur(); return false;\">".cms_message('CMS', 'обновить картинку')."</a></span>
		";
	}
	
	/**
	 * Проверяет правильность ввода значения с картинки пользователем
	 *
	 * @param string $captcha_id
	 * @param string $value
	 * @return boolean
	 */
	public static function check($captcha_id, $value) {
		self::init();
		
		if (isset($_SESSION['Captcha'][$captcha_id]) && $_SESSION['Captcha'][$captcha_id]['value'] == $value && $_SESSION['Captcha'][$captcha_id]['ip'] == HTTP_IP) {
			return true;
		} else {
//			x($_SESSION);
//			x($captcha_id);
//			x($value);
//			exit;
			return false;
		}
	}
	
	/**
	 * Заменяет фразу CAPTCHA на новую, используется если
	 * созданная фраза получилась нечитабельной или не
	 * удалось загрузить картинку
	 *
	 * @param string $captcha_id
	 */
	public static function refresh($captcha_id) {
		//self::init();
		
		if (isset($_SESSION['Captcha'][$captcha_id])) {
			$c = $_SESSION['Captcha'][$captcha_id];
			$_SESSION['Captcha'][$captcha_id]['value'] = Misc::randomKey($c['count'], $c['chars']);
			$_SESSION['Captcha'][$captcha_id]['expire'] = time()+1200;
		}
	}
	
	/**
	 * Строит картинку CAPTCHA
	 *
	 * @param string $captcha_id
	 * @return binary
	 */
	public static function getImage($captcha_id) {
		self::init();
		
		$fonts = array(
			SITE_ROOT.'system/fonts/trebuc.ttf', 
			SITE_ROOT.'system/fonts/trebucbd.ttf', 
			/*SITE_ROOT.'system/fonts/mtcorsva.ttf', */
			/*SITE_ROOT.'system/fonts/symbol.ttf',
			SITE_ROOT.'system/fonts/verdana.ttf'*/
		);
		
		if (isset($_SESSION['Captcha'][$captcha_id])) {
			$width = $_SESSION['Captcha'][$captcha_id]['w'];
			$height = $_SESSION['Captcha'][$captcha_id]['h'];
		} else {
			$width = 120;
			$height = 30;
		}
		
		/**
		 * Создание изображения
		 */
		if (!$image = imagecreatetruecolor($width, $height)) {
			trigger_error('Unable to initialize GD', E_USER_ERROR);
		}
		imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 255, 255, 255));
		
		/**
		 * Выводим на картинке текст
		 */
		if (isset($_SESSION['Captcha'][$captcha_id])) {
			$value = $_SESSION['Captcha'][$captcha_id]['value'];
						
			/**
			 * Накладываем шумы
			 */
			for ($i=0;$i<imagesx($image);$i+=5) {
				imagettftext($image, rand(10,13), rand(-20, 20), 10+$i+rand(-4,4), 22+rand(-10,10), imagecolorallocate($image, rand(120,255), rand(120,255), rand(120,255)), $fonts[rand(0,count($fonts)-1)], chr(rand(ord('a'),ord('z'))));
			}
			
			/**
			 * Рисуем линии
			 */
//			for ($c=0;$c<2;$c++) {
//				$start = rand(4, 30);
//				$end = rand(80, 115);
//				$last_y = rand(5,25);
//				$color = imagecolorallocate($image, rand(40,90), rand(40,90), rand(40,90));
//				for ($x=$start;$x<$end;$x++) {
//					$y = $last_y + rand(-1,1);
//					if ($y < 5) $y = 5; 
//					if ($y > 25) $y = 25; 
//					imagesetpixel($image, $x, $y, $color);
//					imagesetpixel($image, $x+1, $y+1, $color);
//					$last_y = $y;
//				}
//			}
			
			for ($i=0;$i<strlen($value);$i++) {
				imagettftext($image, rand(16,18), rand(-15, 15), 10+$i*17+rand(-3,3), 22+rand(-3,3), imagecolorallocate($image, rand(0,90), rand(0,90), rand(0,90)), $fonts[rand(0,count($fonts)-1)], substr($value, $i, 1));
			}
			
			//self::noise($image);
			//self::scatter($image);
			self::blur($image);

		} else {
//			echo "<pre>";
//			print_r($_SESSION['Captcha']);
//			echo "<br>$captcha_id";
			imagerectangle($image, 0, 0, imagesx($image)-1, imagesy($image)-1, imagecolorallocate($image, 255, 0, 0));
			imagettftext($image, 8, 0, 5, 20, imagecolorallocate($image, 255, 0, 0), SITE_ROOT.'system/fonts/verdana.ttf', iconv(LANGUAGE_CHARSET, 'UTF-8//IGNORE', 'Error'));
		}
		
		/**
		 * Отдаем картинку
		 */
		ob_start();
		imagepng ($image);
		imagedestroy($image);
		return ob_get_clean();
	}
	
	/**
	 * Проверяет, начата ли сессия и чистит устаревшие данные о картинках
	 */
	private static function init($uniq_id = null) {
		if (!isset($_SESSION)) {
			session_start();
		}
		
		if (isset($_SESSION['Captcha']) && is_array($_SESSION['Captcha'])) {
			reset($_SESSION['Captcha']); 
			while (list($index,$row) = each($_SESSION['Captcha'])) { 
				if ($row['expire'] < time() || ($uniq_id !== null && $row['uniq_id'] == $uniq_id)) {
					unset($_SESSION['Captcha'][$index]);
				}
			}
		} else {
			$_SESSION['Captcha'] = array();
		}
	}
	
	
	
	
	
	
	/**
	 * Эффекты, применяемые к изображению
	 */
	
	/**
	 * Добавляет к рисунку шумы
	 *
	 * @param resource $image
	 */
	private static function noise (&$image) {
	    $imagex = imagesx($image);
	    $imagey = imagesy($image);
	
	    for ($x = 0; $x < $imagex; ++$x) {
	        for ($y = 0; $y < $imagey; ++$y) {
	            if (rand(0,1)) {
	                $rgb = imagecolorat($image, $x, $y);
	                $red = ($rgb >> 16) & 0xFF;
	                $green = ($rgb >> 8) & 0xFF;
	                $blue = $rgb & 0xFF;
	                $modifier = rand(-20,20);
	                $red += $modifier;
	                $green += $modifier;
	                $blue += $modifier;
	
	                if ($red > 255) $red = 255;
	                if ($green > 255) $green = 255;
	                if ($blue > 255) $blue = 255;
	                if ($red < 0) $red = 0;
	                if ($green < 0) $green = 0;
	                if ($blue < 0) $blue = 0;
	
	                $newcol = imagecolorallocate($image, $red, $green, $blue);
	                imagesetpixel($image, $x, $y, $newcol);
	            }
	        }
	    }
	} 
	
	/**
	 * Эффект сьезжания
	 *
	 * @param resource $image
	 */
	private static function scatter(&$image) {
	    $imagex = imagesx($image);
	    $imagey = imagesy($image);
	
	    for ($x = 0; $x < $imagex; ++$x) {
	        for ($y = 0; $y < $imagey; ++$y) {
	            $distx = rand(0, 5);
	            $disty = rand(0, 3);
	            if ($disty > 1) {
	            	$disty = 0;
	            }
	            if ($distx > 1) {
	            	$distx = 0;
	            }
	
	            if ($x + $distx >= $imagex) continue;
	            if ($x + $distx < 0) continue;
	            if ($y + $disty >= $imagey) continue;
	            if ($y + $disty < 0) continue;
	
	            $oldcol = imagecolorat($image, $x, $y);
	            $newcol = imagecolorat($image, $x + $distx, $y + $disty);
	            imagesetpixel($image, $x, $y, $newcol);
	            imagesetpixel($image, $x + $distx, $y + $disty, $oldcol);
	        }
	    }
	} 
	
	/**
	 * Эффект размытого рисунка
	 *
	 * @param resource $image
	 */
	private static function blur (&$image) {
	    $imagex = imagesx($image);
	    $imagey = imagesy($image);
	    $dist = 1;
	
	    for ($x = 0; $x < $imagex; ++$x) {
	        for ($y = 0; $y < $imagey; ++$y) {
	            $newr = 0;
	            $newg = 0;
	            $newb = 0;
	
	            $colours = array();
	            $thiscol = imagecolorat($image, $x, $y);
	
	            for ($k = $x - $dist; $k <= $x + $dist; ++$k) {
	                for ($l = $y - $dist; $l <= $y + $dist; ++$l) {
	                    if ($k < 0) { $colours[] = $thiscol; continue; }
	                    if ($k >= $imagex) { $colours[] = $thiscol; continue; }
	                    if ($l < 0) { $colours[] = $thiscol; continue; }
	                    if ($l >= $imagey) { $colours[] = $thiscol; continue; }
	                    $colours[] = imagecolorat($image, $k, $l);
	                }
	            }
	
	            foreach($colours as $colour) {
	                $newr += ($colour >> 16) & 0xFF;
	                $newg += ($colour >> 8) & 0xFF;
	                $newb += $colour & 0xFF;
	            }
	
	            $numelements = count($colours);
	            $newr /= $numelements;
	            $newg /= $numelements;
	            $newb /= $numelements;
	
	            $newcol = imagecolorallocate($image, $newr, $newg, $newb);
	            imagesetpixel($image, $x, $y, $newcol);
	        }
	    }
	} 

//	WAVE TEXT DISTORTION
//  http://www.captcha.ru/captchas/multiwave/
//	
//	// periods
//		$rand1=mt_rand(750000,1200000)/10000000;
//		$rand2=mt_rand(750000,1200000)/10000000;
//		$rand3=mt_rand(750000,1200000)/10000000;
//		$rand4=mt_rand(750000,1200000)/10000000;
//		// phases
//		$rand5=mt_rand(0,31415926)/10000000;
//		$rand6=mt_rand(0,31415926)/10000000;
//		$rand7=mt_rand(0,31415926)/10000000;
//		$rand8=mt_rand(0,31415926)/10000000;
//		// amplitudes
//		$rand9=mt_rand(330,420)/110;
//		$rand10=mt_rand(330,450)/110;
//
//		//wave distortion
//
//		for($x=0;$x<$width;$x++){
//			for($y=0;$y<$height;$y++){
//				$sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$width/2+$center+1;
//				$sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;
//
//				if($sx<0 || $sy<0 || $sx>=$width-1 || $sy>=$height-1){
//					continue;
//				}else{
//					$color=imagecolorat($img, $sx, $sy) & 0xFF;
//					$color_x=imagecolorat($img, $sx+1, $sy) & 0xFF;
//					$color_y=imagecolorat($img, $sx, $sy+1) & 0xFF;
//					$color_xy=imagecolorat($img, $sx+1, $sy+1) & 0xFF;
//				}
//
//				if($color==255 && $color_x==255 && $color_y==255 && $color_xy==255){
//					continue;
//				}else if($color==0 && $color_x==0 && $color_y==0 && $color_xy==0){
//					$newred=$foreground_color[0];
//					$newgreen=$foreground_color[1];
//					$newblue=$foreground_color[2];
//				}else{
//					$frsx=$sx-floor($sx);
//					$frsy=$sy-floor($sy);
//					$frsx1=1-$frsx;
//					$frsy1=1-$frsy;
//
//					$newcolor=(
//						$color*$frsx1*$frsy1+
//						$color_x*$frsx*$frsy1+
//						$color_y*$frsx1*$frsy+
//						$color_xy*$frsx*$frsy);
//
//					if($newcolor>255) $newcolor=255;
//					$newcolor=$newcolor/255;
//					$newcolor0=1-$newcolor;
//
//					$newred=$newcolor0*$foreground_color[0]+$newcolor*$background_color[0];
//					$newgreen=$newcolor0*$foreground_color[1]+$newcolor*$background_color[1];
//					$newblue=$newcolor0*$foreground_color[2]+$newcolor*$background_color[2];
//				}
//
//				imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newred, $newgreen, $newblue));
//			}
//		}
}

?>