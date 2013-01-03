<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	This code based on KCAPTCHA PROJECT VERSION 2.0 http://captcha.ru Copyright by Kruglov Sergei
*/
class Captcha extends BaseClass
{
	const
		ALPHABET='0123456789abcdefghijklmnopqrstuvwxyz';#Алфавит. Не меняйте без изменения файлов шрифтов

	public
		$symbols='23456789abcdeghkmnpqsuvxyz',#Алвавит без подобных символов (o=0, 1=l, i=j, t=f)
		$length=6,#Длина строки в капче
		$width=120,#Ширина капчи-картинки
		$height=60,#Высота капчи-картинки
		$fluctuation=8,#Отклонение символов по вертикали
		$wh_noise=0.14,#Густота "белого" шума
		$bl_noise=0.14,#Густота "черного" шума
		$disabled=false;#Флаг отключенной капчки

	/**
	 * Конструктор, самый обыкновенный, ничем не приметный конструктор
	 */
	public function __construct()
	{
		Eleanor::LoadOptions('captcha');
		$this->length=(int)Eleanor::$vars['captcha_length'];
		$this->symbols=Eleanor::$vars['captcha_symbols'];
		$this->width=(int)Eleanor::$vars['captcha_width'];
		$this->height=(int)Eleanor::$vars['captcha_height'];
		$this->disabled=Eleanor::$Permissions->HideCaptcha();
	}

	/**
	 * Подклчение HTML кода капчи, для вывода его на странице. Код определяется шаблонизатором, но обязательно включает в себя картинку и hidden поле
	 * При этом поле для ввода капчи, вам необходимо предусмотреть самостоятельно.
	 *
	 * @param string $n Имя капчи, используется в случае, если на странице выводится две и более капчи: каждой необходимо задать свое уникальное имя
	 * @param array|FALSE $post Массив с POST запросом, если указано false, используется суперглобальный массив $_POST. Полезно, в случае проверки капчи с использованием Ajax
	 */
	public function GetCode($n='captcha',$post=false)
	{
		if($this->disabled)
			return'';
		if(!is_array($post))
			$post=&$_POST;
		Eleanor::StartSession(isset($post[$n]) ? $post[$n] : '',$n);
		$_SESSION[$n]='';
		return Eleanor::$Template->Captcha(array('name'=>$n,'w'=>$this->width,'h'=>$this->height,'s'=>session_id(),'src'=>Eleanor::$services['download']['file'].'?imageid='.session_id().'&amp;captcha='.$n));
	}

	/**
	 * Проверка корректности введенного значения капчи
	 *
	 * @param string $value Значение которое ввел пользователь
	 * @param string $n Имя капчи, используется в случае, если на странице выводится две и более капчи: каждой необходимо задать свое уникальное имя
	 * @param array|FALSE $post Массив с POST запросом, если указано false, используется суперглобальный массив $_POST. Полезно, в случае проверки капчи с использованием Ajax
	 * @param string|FALSE $sess Идентификатор сессии. Каждый раз идентификатор сессии передается в hidden поле, если вы получаете идентификатор сессии другим способом - передайте его сюда
	 */
	public function Check($value,$n='captcha',$post=false,$sess=false)
	{
		if($this->disabled)
			return true;
		if(!is_array($post))
			$post=&$_POST;
		if(!$sess)
		{
			if(!isset($post[$n]))
				return false;
			$sess=$post[$n];
		}
		Eleanor::StartSession($sess,$n);
		if(!isset($_SESSION[$n]))
			return false;
		return strcasecmp($_SESSION[$n],(string)$value)==0;
	}


	/**
	 * Разрушение капчи. Правило простое: после проверки корректности (не важно, успешно прошла или нет), капчу нужно разрушить, для исключения перебора возможных значений
	 *
	 * @param string $n Имя капчи, используется в случае, если на странице выводится две и более капчи: каждой необходимо задать свое уникальное имя
	 */
	public function Destroy($n='captcha')
	{
		if(isset($_SESSION))
			unset($_SESSION[$n]);
	}

	/**
	 * Непосредственный вывод картинки. Сразу после вызова метода настоятельно рекомедуется завершать выполнение скрипта die;
	 *
	 * @param string $sess Идентификатор сессии
	 * @param string $n Имя капчи, используется в случае, если на странице выводится две и более капчи: каждой необходимо задать свое уникальное имя
	 */
	public function GetImage($sess,$n='captcha')
	{
		Eleanor::StartSession($sess,$n);
		$s='';
		$l=mb_strlen($this->symbols)-1;
		for($i=0;$i<$this->length;$i++)
			$s.=mb_substr($this->symbols,mt_rand(0,$l),1);
		$_SESSION[$n]=$s;
		$f_color=array(mt_rand(0,100),mt_rand(0,100),mt_rand(0,100));
		$b_color=array(mt_rand(150,255),mt_rand(150,255),mt_rand(150,255));
		$alen=strlen(self::ALPHABET);
		$fonts=glob(Eleanor::$root.'core/others/captcha_fonts/*.png');
		if(!$fonts)
			throw new EE('No fonts in core/others/captcha_fonts/!');
		$font=imagecreatefrompng($fonts[array_rand($fonts)]);
		unset($fonts);
		imagealphablending($font,true);
		$ffw=imagesx($font);
		$ffh=imagesy($font)-1;
		$fm=array();
		$symbol=0;
		$rs=false;
		#Подготовим данные для нашего алфавита
		for($i=0;$i<$ffw and $symbol<$alen;$i++)
		{
			$trans=(imagecolorat($font,$i,0) >> 24)==127;
			if(!$rs and !$trans)
			{
				$fm[substr(self::ALPHABET,$symbol,1)]=array('start'=>$i);
				$rs=true;
			}
			elseif($rs and $trans)
			{
				$fm[substr(self::ALPHABET,$symbol,1)]['end']=$i;
				$rs=false;
				$symbol++;
			}
		}
		$img=imagecreatetruecolor($this->width,$this->height);
		imagealphablending($img,true);
		$white=imagecolorallocate($img,255,255,255);
		$black=imagecolorallocate($img,0,0,0);
		imagefilledrectangle($img,0,0,$this->width-1,$this->height-1,$white);
		$x=1;
		for($i=0;$i<$this->length;$i++)
		{
			$odd=mt_rand(0,1);
			if($odd==0)
				$odd=-1;
			$m=$fm[substr($s,$i,1)];
			$y=(($i%2)*$this->fluctuation-$this->fluctuation/2)*$odd+mt_rand(-round($this->fluctuation/3),round($this->fluctuation/3))+($this->height-$ffh)/2;
			if($y<0)
				$y=0;
			$shift=0;
			if($i>0)
			{
				$shift=10000;
				for($sy=3;$sy<$ffh-10;$sy++)
					for($sx=$m['start']-1;$sx<$m['end'];$sx++)
					{
						$rgb=imagecolorat($font,$sx,$sy);
						$opacity=$rgb>>24;
						if($opacity<127)
						{
							$py=$sy+$y;
							if($py>$this->height)
								break;
							$left=$sx-$m['start']+$x;
							for($px=min($left,$this->width-1);$px>$left-200 and $px>=0;$px--)
							{
								$color=imagecolorat($img,$px,$py) & 0xff;
								if($color+$opacity<170)
								{
									if($shift>$left-$px)
										$shift=$left-$px;
									break;
								}
							}
							break;
						}
					}
				if($shift==10000)
					$shift=mt_rand(4,6);
			}
			imagecopy($img,$font,$x-$shift,$y,$m['start'],1,$m['end']-$m['start'],$ffh);
			$x+=$m['end']-$m['start']-$shift;
		}

		for($i=0;$i<($this->height-30)*$x*$this->wh_noise;$i++)
			imagesetpixel($img,mt_rand(0,$x-1),mt_rand(10,$this->height-15),$white);
		for($i=0;$i<($this->height-30)*$x*$this->bl_noise;$i++)
			imagesetpixel($img,mt_rand(0,$x-1),mt_rand(10,$this->height-15),$black);

		$center=$x/2;
		$out_img=imagecreatetruecolor($this->width,$this->height);
		$background=imagecolorallocate($out_img,$b_color[0],$b_color[1],$b_color[2]);
		imagefilledrectangle($out_img,0,0,$this->width-1,$this->height-1,$background);

		$rand1=mt_rand(750000,1200000)/10000000;
		$rand2=mt_rand(750000,1200000)/10000000;
		$rand3=mt_rand(750000,1200000)/10000000;
		$rand4=mt_rand(750000,1200000)/10000000;

		$rand5=mt_rand(0,31415926)/10000000;
		$rand6=mt_rand(0,31415926)/10000000;
		$rand7=mt_rand(0,31415926)/10000000;
		$rand8=mt_rand(0,31415926)/10000000;

		$rand9=mt_rand(330,420)/110;
		$rand10=mt_rand(330,450)/110;

		#Искривление изображения
		for($x=0;$x<$this->width;$x++)
			for($y=0;$y<$this->height;$y++)
			{
				$sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$this->width/2+$center+1;
				$sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;

				if($sx<0 or $sy<0 or $sx>=$this->width-1 or $sy>=$this->height-1)
					continue;
				else
				{
					$color=imagecolorat($img,$sx,$sy) & 0xFF;
					$color_x=imagecolorat($img,$sx+1,$sy) & 0xFF;
					$color_y=imagecolorat($img,$sx,$sy+1) & 0xFF;
					$color_xy=imagecolorat($img,$sx+1,$sy+1) & 0xFF;
				}

				if($color==255 and $color_x==255 and $color_y==255 and $color_xy==255)
					continue;
				elseif($color==0 and $color_x==0 and $color_y==0 and $color_xy==0)
				{

						$nr=$f_color[0];
						$ng=$f_color[1];
						$nb=$f_color[2];

					//continue;
				}
				else
				{
					$frsx=$sx-floor($sx);
					$frsy=$sy-floor($sy);
					$frsx1=1-$frsx;
					$frsy1=1-$frsy;

					$newcolor=$color*$frsx1*$frsy1+$color_x*$frsx*$frsy1+$color_y*$frsx1*$frsy+$color_xy*$frsx*$frsy;

					if($newcolor>255)
						$newcolor=255;
					$newcolor=$newcolor/255;
					$newcolor0=1-$newcolor;
					$nr=$newcolor0*$f_color[0]+$newcolor*$b_color[0];
					$ng=$newcolor0*$f_color[1]+$newcolor*$b_color[1];
					$nb=$newcolor0*$f_color[2]+$newcolor*$b_color[2];
				}
				imagesetpixel($out_img,$x,$y,imagecolorallocate($out_img,$nr,$ng,$nb));
			}
		header('Cache-Control: no-store');
		if(function_exists('imagejpeg'))
		{
			header('Content-Type: image/jpeg');
			imagejpeg($out_img,null,80);
		}
		elseif(function_exists('imagegif'))
		{
			header('Content-Type: image/gif');
			imagegif($out_img);
		}
		elseif(function_exists('imagepng'))
		{
			header('Content-Type: image/x-png');
			imagepng($out_img,null,80);
		}
	}
}