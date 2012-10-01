<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
class Editor extends BaseClass
{
	public
		$type='bb',#Тип редактора, выбор - см конструктор класса.

		$smiles=true,#Разрещить использование смайлов
		$ownbb=true,#Разрешить использование своих ББ кодов? Полезно отключать, когда при помощи ББ редактора формируется письмо на отправку

		$editors=array(),#Перечень редакторов. Смотри в конструктор
		$visual=array(),#Перечень визуальных редакторов

		$imgalt='';#Автоматическое прописывание альтов к картинкам

	protected
		$cht=array();#Специальный массив для функции CheckTag

	public function __construct()#ToDo! To trait & editor_result
	{
		$this->editors=array(
			'no'=>'textarea',
			'bb'=>'Eleanor BB Editor',
			'ckeditor'=>'CKEditor',
			'tinymce'=>'TinyMCE',
			'codemirror'=>'CodeMirror',
		);
		#Перечень визуальных редакторов
		$this->visual=array('ckeditor','tinymce');
		Eleanor::LoadOptions('editor');
		if($type=Eleanor::$Login->GetUserValue('editor'))
			$this->type=$type;
		else
			$this->type=Eleanor::$vars['editor_type'];
	}

	/*
		Функция просто возвращает содержимое для редактора, без самого редактора!
	*/
	public function GetEdit($text)
	{
		if($this->ownbb)
		{
			$text=OwnBB::Parse($text,OwnBB::EDIT);
			$text=OwnBB::StoreNotParsed($text,OwnBB::SAVE);
		}

		$text=preg_replace('#(<[^>]+href=")go\.php\?([a-z]{3,7}://[^>]*>)#','\1\2',$text);
		$text=preg_replace('#<img class="smile" alt="([^"]+)"[^>]*>#i','\1',$text);
		if($this->imgalt)
		{
			$imgalt=htmlspecialchars($this->imgalt,ELENT,CHARSET,false);
			$text=str_replace(array(' alt="'.$imgalt.'" title="'.$imgalt.'"',' alt="'.$imgalt.'"'),'',$text);
		}

		if($this->type=='bb')
			$text=BBcodes::Load($text);

		if($this->ownbb)
			$text=OwnBB::ParseNotParsed($text,false);
		return$text;
	}

	/*
		Функция, которая покажет наше текстовое поле в зависимости от настроек
		$addon['bypost']=true означает, что текст в редактор возвращается из-за возникшей ошибки в пришедшей от пользователя инфе
	*/
	public function Area($name,$text='',$addon=array(),$tpl='Editor')
	{
		if(empty($addon['bypost']))
			$text=$this->GetEdit($text);
		$id=preg_replace('#\W+#','',$name);
		$GLOBALS['jscripts'][]='js/dropdown.js';
		if($this->ownbb)
		{
			$text=OwnBB::StoreNotParsed($text,OwnBB::SHOW);
			foreach(OwnBB::$np as &$v)
				$v['t']=htmlspecialchars($v['t'],ELENT,CHARSET);
			$text=OwnBB::ParseNotParsed($text,false);
		}
		switch($this->type)
		{
			case'bb':#Родной ББ
				$GLOBALS['jscripts'][]='js/eleanor_bb.js';
				$html=Eleanor::$Template->{isset($addon['bbtpl']) ? $addon['bbtpl'] : 'BBeditor'}(array(
					'id'=>$id,
					'name'=>$name,
					'value'=>$text,
					'addon'=>isset($addon['bb']) ? $addon['bb'] : (isset($addon['no']) ? $addon['no'] : array()),
					'smiles'=>$this->smiles,
					'ownbb'=>$this->ownbb,
				));
			break;
			case'ckeditor':
				array_push($GLOBALS['jscripts'],'addons/ckeditor/ckeditor.js');
				$html=Eleanor::Text($name,$text,(isset($addon['ckeditor']) ? $addon['ckeditor'] : array())+array('id'=>$id)).'<script type="text/javascript">//<![CDATA[
$(function(){
	if(typeof CKEDITOR.instances.'.$id.'!="undefined")
		CKEDITOR.instances.'.$id.'.destroy();
	var editor=CKEDITOR.replace("'.$id.'",{language:"'.substr(Language::$main,0,2).'"});
	EDITOR.New(
		editor.name,
		{			Embed:function(type,data)
			{				if(type=="image" && data.src)
					editor.insertElement(CKEDITOR.dom.element.createFromHtml("<img src=\""+data.src+"\" title=\""+(data.title||"")+"\" alt=\""+(data.alt||data.title||"")+"\" />"));
			},
			Insert:function(pre,after,F){				var s=editor.getSelection().getSelectedText();
				if($.isFunction(F))					s=F(s);				editor.insertHtml(pre+s+after);
			},			Get:function(){ return editor.getData(); },
			Set:function(text){ editor.setData(text); },
			Selection:function(){ return editor.getSelection().getSelectedText(); }		}
	);
	editor.on("focus",function(){EDITOR.Active(this.name)});
});//]]></script>';
			break;
			case'tinymce':#Tiny MCE
				array_push($GLOBALS['jscripts'],'addons/tiny_mce/jquery.tinymce.js','addons/tiny_mce/tiny_config.js');
				static$tinyalr=true;
				if($tinyalr)
					$GLOBALS['head'][]='<script type="text/javascript">//<![CDATA[
if(CORE.in_ajax.length)
	CORE.after_ajax.push(EDITOR.tinymce_ready);
else
	EDITOR.tinymce_ready();//]]></script>';
				$tinyalr=false;
				$html=Eleanor::Text($name,$text,array('id'=>$id,'class'=>'tiny_mce_editor'));
			break;
			case'codemirror':
				$GLOBALS['jscripts'][]='addons/codemirror/lib/codemirror.js';
				$GLOBALS['head'][__class__.'-codemirror']='<link rel="stylesheet" href="addons/codemirror/lib/codemirror.css" type="text/css" media="screen" />';
				$mode=isset($addon['codemirror']['type']) ? preg_replace('#[^a-z0-9]+#','',(string)$addon['codemirror']['type']) : '';
				if($mode=='purephp')#Заплатка для "чистого" PHP
				{
					array_push($GLOBALS['jscripts'],'addons/codemirror/mode/php/eleanor.js','addons/codemirror/mode/php/php.js');
					$mode='application/x-httpd-php';
				}
				elseif($mode and is_dir(Eleanor::$root.'addons/codemirror/mode/'.$mode))
				{
					if(file_exists($f='addons/codemirror/mode/'.$mode.'/eleanor.js'))
						$GLOBALS['jscripts'][]=$f;
					$GLOBALS['jscripts'][]='addons/codemirror/mode/'.$mode.'/'.$mode.'.js';
					$mode=Types::MimeTypeByExt($mode);
				}
				else
				{
					array_push($GLOBALS['jscripts'],'addons/codemirror/mode/htmlmixed/eleanor.js','addons/codemirror/mode/htmlmixed/htmlmixed.js');
					$mode='text/html';
				}
				$html=Eleanor::Text($name,$text,array('id'=>$id,'style'=>'width:100%;height:100%')).'<script type="text/javascript">//<![CDATA[
$(function(){
	setTimeout(function(){
		var editor=CodeMirror.fromTextArea(
			$("#'.$id.'").get(0),
			{
				mode:"'.$mode.'",
				lineNumbers:true,
				indentWithTabs:true,
				indentUnit:4,
				matchBrackets:true,
				onFocus:function(){EDITOR.Active("'.$id.'")}
			}
		);
		EDITOR.New(
			"'.$id.'",
			{				Embed:function(type,data)
				{
					if(type=="image" && data.src)
						editor.replaceSelection("<img src=\""+data.src+"\""+(data.title ? " title=\""+data.title+"\"" : "")+" />");
				},
				Insert:function(pre,after,F){					var s=editor.getSelection();
					if($.isFunction(F))
						s=F(s);
					editor.replaceSelection(pre+s+after);
				},
				Set:function(text){ editor.setValue(text); },
				Get:function(){ return editor.getValue(); },
				Selection:function(){ return editor.getSelection(); }
			}
		);
	},50);
});//]]></script>';
			break;
			default:#Без редактора
				$GLOBALS['jscripts'][]='js/eleanor_bb.js';
				$html=Eleanor::Text($name,$text,(isset($addon['no']) ? $addon['no'] : array())+array('id'=>$id,'rows'=>10,'cols'=>50)).'<script type="text/javascript">/*<![CDATA[*/EDITOR.New("'.$id.'",
				{
					Embed:function(type,data)
					{
						if(type=="image" && data.src)
							SetSelectedText($("#'.$id.'"),data.src);
					},
					Insert:function(pre,after,F){ SetSelectedText($("#'.$id.'"),pre,after,F); },
					Get:function(){ return $("#'.$id.'").val(); },
					Set:function(text){ $("#'.$id.'").val(text); }
				}
			);
			$("#'.$id.'").focus(function(){EDITOR.Active("'.$id.'")});//]]></script>';
		}

		$ownbb=array();
		if($this->ownbb)
		{
			$lang=Eleanor::$Language['editor'];
			$ug=Eleanor::GetUserGroups();
			foreach(OwnBB::$bbs as &$bb)
				if($bb['sb'] and (!$bb['gr_use'] or count(array_intersect(explode(',',$bb['gr_use']),$ug))>0))
				{
					if(false!==$p=strpos($bb['tags'],','))
						$bb['tags']=substr($bb['tags'],0,$p);
					$class='OwnBbCode_'.((false===$p=strrpos($bb['handler'],'.')) ? $bb['handler'] : substr($bb['handler'],0,$p));
					if(!class_exists($class,false) and !include(Eleanor::$root.'core/ownbb/'.$bb['handler']))
						continue;
					$ownbb[]=array(
						't'=>$bb['tags'],
						's'=>$class::SINGLE,
						'l'=>isset($lang['ownbb_'.$bb['handler']]) ? $lang['ownbb_'.$bb['handler']] : false,
					);
				}
		}
		return Eleanor::$Template->$tpl($id,$html,$this->smiles ? static::GetSmiles() : array(),$ownbb);
	}

	public static function GetSmiles()#ToDo! To trait & editor_result
	{
		$sm=Eleanor::$Cache->Get('smiles',false);
		if($sm===false)
		{
			$sm=array();
			$R=Eleanor::$Db->Query('SELECT `path`,`emotion`,`show` FROM `'.P.'smiles` WHERE `status`=1 ORDER BY `pos` ASC');
			while($a=$R->fetch_assoc())
			{				$a['emotion']=explode(',,',trim($a['emotion'],','));
				$sm[]=$a;
			}
			Eleanor::$Cache->Put('smiles',$sm,0,false);
		}
		return$sm;
	}
}