<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Управление загруженными файлами для публикаций.
*/
class Uploader extends BaseClass
{
	const
		FILENAME='Filedata';#Имя input type="file" при загрузке файла через flash

	public
		$prevsuff='_preview',#Суффикс превьюшек
		$pp=20,#Количество файлов на страницу
		$preview=array('','jpeg','jpg','png','bmp','gif'),#Типы файлов для которых необходимо создавать превьюшки

		$allow_delete=true,#Разрешить удаление файлов и папок?
		$allow_walk=true,#Позволить "гулять" по папкам

		$buttons_top=array(#Кнопки управления интерфейса
			'create_file'=>false,#Создание файла
			'show_previews'=>true,#Показать / скрыть превьюшки
			'create_previews'=>true,#Включение / выключение создание превьюшек
			'watermark'=>true,#Включение / выключение создание ватермарка
			'create_folder'=>true,#Создание каталога
			'update'=>true,#Обновление содержимого
		),

		$buttons_item=array(#Кнопки управление конкретным каталогом или файлом
			#Для файлов
			'edit'=>false,#Правка файла
			'insert_attach'=>true,#Вставить файл используя ownbb код [attach]
			'insert_link'=>true,#Вставить ссылку на файл
			'file_rename'=>true,#Переименование файла
			'file_delete'=>true,#Удаление файла
			#Для каталогов
			'folder_rename'=>true,#Переименование каталога
			'folder_open'=>false,#Открытие каталога (переход в каталог)
			'folder_delete'=>true,#Удаление каталога
		),
		$editable=array('php','css','txt','js','html','htm'),#Перечень файлов, которые возможно редактировать

		$max_size,#Максимальный размер всех залитых файлов. Поставьте в false, если не хотите, чтобы пользователь мог загружать файлы.
		$max_files=0,#Максимальное число файлов, которые пользователь может загрузить

		$watermark,#true - всегда ставить, false - всегда не ставить, null - выбирает пользователь
		$previews,#true - всегда делать, false - всегда не делать, null - выбирает пользователь
		$types=array();#Типы файлов, разрешенные для аплоада

	protected
		$pathlimit='',#Предел перемещений, за который нельзя заходить
		$vars,#Внутренние переменные
		$path='';#Ограничитель. За пределы этого каталога выходить нельзя.

	/**
	 * Конструктор загрузчика файлов
	 *
	 * @param string|FALSE $path Полный абсолютный путь к коневому каталогу загрузчика. Обязательно с / в конце.
	 * @param string $tpl Название класса оформления загрузчика
	 */
	public function __construct($path=false,$tpl='Uploader')
	{
		$this->vars=Eleanor::LoadOptions('files',true);
		$this->max_size=Eleanor::$Permissions->MaxUpload();
		if($this->vars['thumbs'])
			$this->preview=explode(',',Strings::CleanForExplode($this->vars['thumb_types']));
		if(!$this->vars['watermark'] or $this->max_size===false)
			$this->watermark=false;
		$this->pathlimit=($path ? preg_replace('#/|\\\\#',DIRECTORY_SEPARATOR,rtrim($path,'/\\')) : Eleanor::$root.Eleanor::$uploads).DIRECTORY_SEPARATOR;
		if($tpl)
			Eleanor::$Template->queue[]=$tpl;
		$this->uid=Eleanor::$Login->GetUserValue('id');
	}

	/**
	 * Получение HTML кода загрузчика. Метод можно вызывать несколько раз, передавая каждый раз уникальный параметр $uniq для создания нескольких независимых загрузчиков на странице
	 *
	 * @param string|FALSE $path Относительный путь внутреннего каталога для загрузки файла. В случае передачи FALSE, загрузка будет происходить в temp каталог
	 * @param string $uniq Уникальная строка-идентификатор каждого отдельного загрузчика на странице
	 * @param string|FALSE $title Название загрузчика
	 */
	public function Show($path=false,$uniq='',$title=false)
	{
		$max_upload=Files::SizeToBytes(ini_get('upload_max_filesize'));
		if(is_int($this->max_size))
		{
			if($this->max_size<$max_upload)
				$max_upload=$this->max_size;
			#Если задан макимальный размер всех файлов - мы убираем возможность "гулять" вне нашей папки, потому что в этом случае подсчитать объем загруженных файлов - невозможно.
			$this->allow_walk=false;
		}
		if($this->max_files>0)
		{
			$this->allow_walk=false;
			if($path=='')
				$path=false;
		}

		if(!isset($_SESSION))
			Eleanor::StartSession();
		if($path===false)
		{
			$newf=Eleanor::GetCookie(__class__.'-'.$uniq);
			if(!$newf)
			{
				$newf=uniqid();
				Eleanor::SetCookie(__class__.'-'.$uniq,$newf);
			}
			$this->path=$this->pathlimit.'temp'.DIRECTORY_SEPARATOR.$newf.DIRECTORY_SEPARATOR;
			$_SESSION[__class__][$uniq]=array(
				'prevsuff'=>$this->prevsuff,
				'pp'=>$this->pp,
				'preview'=>$this->preview,
				'allow_walk'=>$this->allow_walk,
				'allow_delete'=>$this->allow_delete,
				'buttons_top'=>$this->buttons_top,
				'buttons_item'=>$this->buttons_item,
				'max_size'=>$this->max_size,
				'max_files'=>$this->max_files,
				'watermark'=>$this->watermark,
				'previews'=>$this->previews,
				'pathlimit'=>$this->pathlimit,
				'types'=>$this->types,
				'path'=>$this->path,
				'tmp'=>true,
				'uid'=>$this->uid,
			);
		}
		else
		{
			$path=preg_replace('#/|\\\\#',DIRECTORY_SEPARATOR,trim($path,'/\\'));
			$this->path=$this->pathlimit.($path ? Files::Windows($path).DIRECTORY_SEPARATOR : '');
			$_SESSION[__class__][$uniq]=array(
				'prevsuff'=>$this->prevsuff,
				'pp'=>$this->pp,
				'preview'=>$this->preview,
				'allow_walk'=>$this->allow_walk,
				'allow_delete'=>$this->allow_delete,
				'buttons_top'=>$this->buttons_top,
				'buttons_item'=>$this->buttons_item,
				'max_size'=>$this->max_size,
				'max_files'=>$this->max_files,
				'watermark'=>$this->watermark,
				'previews'=>$this->previews,
				'pathlimit'=>$this->pathlimit,
				'types'=>$this->types,
				'path'=>$this->path,
				'tmp'=>false,
				'uid'=>$this->uid,
			);
		}
		if(!is_dir($this->path))
			Files::MkDir($this->path);

		if(isset($this->watermark))
			$this->buttons_top['watermark']=false;
		if(isset($this->previews))
			$this->buttons_top['create_previews']=false;
		return Eleanor::$Template->UplUploader(
			$this->buttons_top,
			$title===false ? Eleanor::$Language['uploader']['file_manag'] : $title,
			$this->max_size===false ? false : $max_upload,
			$this->types,
			$uniq
		);
	}

	/**
	 * Получение адреса рабочего каталога загрузчика для работы с загруженными файлами
	 *
	 * @param string $uniq Уникальная строка-идентификатор загрузчика
	 * @param string|FALSE $sess Идентификатор сессии
	 */
	public function WorkingPath($uniq='',$sess=false)
	{
		if($sess===false)
		{
			$f=Eleanor::GetCookie(__class__.'-'.$uniq);
			return$f ? Eleanor::$root.Eleanor::$uploads.'/temp/'.$f : false;
		}
		if(!isset($_SESSION))
			Eleanor::StartSession($sess);
		return isset($_SESSION[__class__][$uniq]) ? $_SESSION[__class__][$uniq]['path'] : false;
	}

	/**
	 * Перемещение загруженных файлов в определенный каталог
	 *
	 * @param string $path Путь к катлогу, куда необходимо переместить загруженные файлы. Если каталог не существует, он будет создан
	 * @param string $uniq Уникальная строка-идентификатор загрузчика
	 * @param string|FALSE $sess Идентификатор сессии
	 */
	public function MoveFiles($path,$uniq='',$sess=false)
	{
		if($sess===false)
		{
			$oldpath=Eleanor::GetCookie(__class__.'-'.$uniq);
			$oldpath=preg_replace('#[^a-z0-9]+#i','',$oldpath);
			if(!$oldpath)
				throw new EE('Upload error',EE::USER);
			$oldpath=Eleanor::$root.Eleanor::$uploads.'/temp/'.$oldpath;
		}
		else
		{
			if(!isset($_SESSION))
				Eleanor::StartSession($sess);
			if(!isset($_SESSION[__class__][$uniq]))
				throw new EE('Upload error',EE::USER);
			if(!$_SESSION[__class__][$uniq]['tmp'])
				return array('from'=>'','to'=>'');
			$oldpath=$_SESSION[__class__][$uniq]['path'];
		}
		if(!file_exists($oldpath) or !glob($oldpath.'/*'))
			return array('from'=>'','to'=>'');
		$newpath=Eleanor::FormatPath($path,Eleanor::$uploads);
		if(is_dir($newpath))
			Files::Delete($newpath);
		$bd=dirname($newpath);
		if(!is_dir($bd))
			Files::MkDir($bd);
		if(rename($oldpath,$newpath))
		{
			$rl=strlen(Eleanor::$root);
			if(Eleanor::$os=='w')
			{
				$oldpath=str_replace(DIRECTORY_SEPARATOR,'/',$oldpath);
				$newpath=str_replace(DIRECTORY_SEPARATOR,'/',$newpath);
			}
			return array('from'=>substr($oldpath,$rl),'to'=>substr($newpath,$rl));
		}
		throw new EE('Upload error',EE::ENV);
	}

	/**
	 * Осуществление перехода по каталогам, внутри основного каталога
	 *
	 * @param string $start Путь текущего положения
	 * @param string $to Путь, куда нужно перейти
	 */
	protected function GetPath($start,$to='')
	{
		$start=Files::Windows(trim($start,'/\\'));
		$p=realpath($this->path.($start ? $start.DIRECTORY_SEPARATOR : '').trim($to,'/\\'));
		if(is_dir($p))
			$p.=DIRECTORY_SEPARATOR;
		if($p and ($this->allow_walk or strncmp($p,$this->path,strlen($this->path))==0) and strncmp($p,$this->pathlimit,strlen($this->pathlimit))==0)
			return$p;
		if(!is_dir($this->path))
			Files::MkDir($this->path);
		return$this->path;
	}

	/**
	 * Получение количества загруженных файлов и их общего размера
	 *
	 * @param string $path Путь к каталогу, в котором находятся файлы
	 */
	protected function FilesSize($path)
	{
		if(!is_dir($path))
			return array(0,0);
		$size=$cnt=0;
		$files=glob(rtrim($path,'/\\').'/*',GLOB_MARK);
		$oldk=-1;
		if($files)
			foreach($files as $k=>&$v)
			{
				if(substr($v,-1)==DIRECTORY_SEPARATOR)
				{
					list($t)=$this->FilesSize($v);
					$size+=$t[0];
					$cnt+=$t[1];
				}
				elseif($oldk>=0 and $v==substr_replace($files[$oldk],$this->prevsuff,strrpos($files[$oldk],'.'),0))
					continue;
				else
				{
					$oldk=$k;
					$size+=filesize($v);
					++$cnt;
				}
			}
		return array($size,$cnt);
	}

	/**
	 * Загрузка опций аплоадера из сессии при ajax или upload запросах
	 *
	 * @param string $uniq Уникальная строка-идентификатор загрузчика
	 */
	protected function LoadOptions($u)
	{
		if(!isset($_SESSION[__class__][$u]))
			return false;
		foreach($_SESSION[__class__][$u] as $k=>&$v)
			if(property_exists($this,$k))
				$this->$k=$v;
		return true;
	}
}