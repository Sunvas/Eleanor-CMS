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

class AccountSettings
{
	public static function Menu()
	{
		return array(
			'main'=>$GLOBALS['Eleanor']->Url->Construct(array('do'=>'settings'),true,''),
		);
	}

	public static function Content($master=true)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		Eleanor::LoadOptions('user-profile',false);

		#Themes
		$themes=array(''=>$lang['by_default']);
		if(Eleanor::$vars['templates'] and is_array(Eleanor::$vars['templates']))
			foreach(Eleanor::$vars['templates'] as &$v)
			{
				$f=Eleanor::$root.'templates/'.$v.'.settings.php';
				if(!file_exists($f))
					continue;
				$a=include($f);
				$name=is_array($a) && isset($a['name']) ? $a['name'] : $v;
				$themes[$v]=$name;
			}
		#[E] Themes

		$post=false;
		$avatar=array(
			'id'=>Eleanor::$Login->GetUserValue('id'),
			'type'=>'uploadimage',
			'name'=>'a',
			'default'=>'',
			'bypost'=>&$post,
			'options'=>array(
				'types'=>array('png','jpeg','jpg','bmp','gif'),
				'path'=>Eleanor::$uploads.'/avatars/',
				'max_size'=>Eleanor::$vars['avatar_bytes'],
				'max_image_size'=>Eleanor::$vars['avatar_size'],
				'filename'=>function($a)
				{
					return isset($a['id']) ? 'av-'.$a['id'].strrchr($a['filename'],'.') : $a['filename'];
				},
				'deleted'=>false,
			),
		);
		$controls=array(
			$lang['siteopts'],
			'full_name'=>array(
				'title'=>$lang['full_name'],
				'descr'=>'',
				'type'=>'input',
				'bypost'=>&$post,
				'options'=>array(
					'htmlsafe'=>true,
				),
			),
			'language'=>Eleanor::$vars['multilang'] ? array(
				'title'=>$lang['lang'],
				'descr'=>'',
				'type'=>'select',
				'bypost'=>&$post,
				'options'=>array(
					'callback'=>function() use ($lang)
					{
						$a=array(''=>$lang['by_default']);
						foreach(Eleanor::$langs as $k=>&$v)
							$a[$k]=$v['name'];
						return$a;
					},
				),
			) : false,
			'theme'=>count($themes)>2 ? array(
				'title'=>$lang['theme'],
				'descr'=>'',
				'type'=>'select',
				'bypost'=>&$post,
				'options'=>array(
					'options'=>$themes,
				),
			) : false,
			'editor'=>array(
				'title'=>$lang['editor'],
				'descr'=>'',
				'type'=>'select',
				'bypost'=>&$post,
				'options'=>array(
					'callback'=>function() use ($lang)
					{
						return array(''=>$lang['by_default'])+$GLOBALS['Eleanor']->Editor->editors;
					},
				),
			),
			'staticip'=>array(
				'title'=>$lang['staticip'],
				'descr'=>$lang['staticip_'],
				'type'=>'check',
				'bypost'=>&$post,
			),
			'timezone'=>array(
				'title'=>$lang['timezone'],
				'descr'=>'',
				'type'=>'select',
				'bypost'=>&$post,
				'options'=>array(
					'callback'=>function($a) use ($lang)
					{
						return Eleanor::Option($lang['by_default'],'',in_array('',$a['value'],'')).Types::TimeZonesOptions($a['value']);
					},
				),
			),
			$lang['personal'],
			'gender'=>array(
				'title'=>$lang['gender'],
				'descr'=>'',
				'type'=>'select',
				'bypost'=>&$post,
				'options'=>array(
					'options'=>array(-1=>$lang['nogender'],$lang['female'],$lang['male']),
				),
			),
			'bio'=>array(
				'title'=>$lang['bio'],
				'descr'=>'',
				'type'=>'text',
				'bypost'=>&$post,
				'options'=>array(
					'htmlsafe'=>true,
				),
			),
			'interests'=>array(
				'title'=>$lang['interests'],
				'descr'=>'',
				'type'=>'text',
				'bypost'=>&$post,
				'options'=>array(
					'htmlsafe'=>true,
				),
			),
			'location'=>array(
				'title'=>$lang['location'],
				'descr'=>$lang['location_'],
				'type'=>'input',
				'bypost'=>&$post,
				'options'=>array(
					'htmlsafe'=>true,
				),
			),
			'site'=>array(
				'title'=>$lang['site'],
				'descr'=>$lang['site_'],
				'type'=>'input',
				'save'=>function($a,$Obj)
				{
					if($a['value'] and !Strings::CheckUrl($a['value']))
						$Obj->errors[]='SITE_ERROR';
					else
						return$a['value'];
				},
				'bypost'=>&$post,
				'options'=>array(
					'type'=>'url',
					'htmlsafe'=>false,
				),
			),
			'signature'=>array(
				'title'=>$lang['signature'],
				'descr'=>'',
				'type'=>'editor',
				'bypost'=>&$post,
			),
			$lang['connect'],
			'jabber'=>array(
				'title'=>'Jabber',
				'descr'=>'',
				'type'=>'input',
				'bypost'=>&$post,
				'options'=>array(
					'htmlsafe'=>true,
				),
			),
			'skype'=>array(
				'title'=>'Skype',
				'descr'=>'',
				'type'=>'input',
				'bypost'=>&$post,
				'options'=>array(
					'htmlsafe'=>true,
				),
			),
			'icq'=>array(
				'title'=>'ICQ',
				'descr'=>'',
				'type'=>'input',
				'save'=>function($a,$Obj)
				{
					$v=preg_replace('#[^0-9]+#','',$a['value']);
					if($v and !isset($v[4]))
						$Obj->errors[]='SHORT_ICQ';
					return$v;
				},
				'bypost'=>&$post,
				'options'=>array(
					'htmlsafe'=>true,
				),
			),
			'vk'=>array(
				'title'=>$lang['vk'],
				'descr'=>$lang['vk_'],
				'type'=>'input',
				'save'=>array(__class__,'SaveVK'),
				'bypost'=>&$post,
				'options'=>array(
					'htmlsafe'=>true,
				),
			),
			'facebook'=>array(
				'title'=>'Facebook',
				'descr'=>'',
				'type'=>'input',
				'save'=>array(__class__,'SaveVK'),
				'bypost'=>&$post,
				'options'=>array(
					'htmlsafe'=>true,
				),
			),
			'twitter'=>array(
				'title'=>'Twitter',
				'descr'=>$lang['twitter_'],
				'type'=>'input',
				'bypost'=>&$post,
				'options'=>array(
					'htmlsafe'=>true,
				),
			),
		);
		$saved=false;
		if($master and $_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
		{
			$C=new Controls;
			$C->arrname=array();
			$C->throw=false;
			$values=$C->SaveControls($controls);

			$C->arrname=array('avatar');
			$oldavatar=Eleanor::$Login->GetUserValue(array('avatar_location','avatar_type'),false);
			$atype=isset($_POST['_atype']) ? $_POST['_atype'] : false;

			if($atype=='upload')
				$av=$C->SaveControl($avatar+array('value'=>$oldavatar['avatar_type']=='upload' && $oldavatar['avatar_location'] ? Eleanor::$uploads.'/avatars/'.$oldavatar['avatar_location'] : ''));
			else
				$av=isset($_POST['avatar_location']) ? (string)$_POST['avatar_location'] : '';

			if($atype=='upload' and $av)
				$atype=strpos($av,'://')===false ? 'upload' : 'url';
			else
				$atype=$av ? 'local' : '';

			if(($atype=='upload' or $atype=='local') and $av and !is_file(Eleanor::$root.$av))
				$C->errors[]='AVATAR_NOT_EXISTS';

			if($atype=='local' and $av)
				$av=preg_replace('#^images/avatars/#','',$av);

			if($C->errors)
			{
				$post=true;
				return static::Edit($controls,$avatar,$C->errors);
			}

			UserManager::Update($values);

			if($atype=='upload')
				$av=basename($av);
			if($oldavatar['avatar_location']!=$av or $oldavatar['avatar_type']!=$atype)
			{
				if($oldavatar['avatar_type']=='upload' and $oldavatar['avatar_location'] and $oldavatar['avatar_location']!=$av)
					Files::Delete(Eleanor::$root.Eleanor::$uploads.'/avatars/'.$oldavatar['avatar_location']);
				UserManager::Update(array('avatar_location'=>$av,'avatar_type'=>$atype));
				Eleanor::$Login->SetUserValue(array(
					'avatar_location'=>$av,
					'avatar_type'=>$atype,
				));
			}
			$saved=true;
		}
		return static::Edit($controls,$avatar,array(),$saved);
	}

	public static function SaveVK($a)
	{
		return preg_replace('#[^a-z0-9_\.-]+/#','',$a['value']);
	}

	protected static function Edit($controls,$avatar,$errors=array(),$saved=false)
	{
		$names=array('avatar_type','avatar_location');
		foreach($controls as $k=>&$control)
			if(is_array($control))
				$names[]=$k;

		$values=Eleanor::$Login->GetUserValue($names,false);
		if($errors)
		{
			$values['_aupload']=isset($_POST['_atype']) && $_POST['_atype']=='upload';
			$values['avatar_location']=isset($_POST['avatar_location']) ? (string)$_POST['avatar_location'] : '';
		}
		else
		{
			if($values['avatar_type']=='local' and $values['avatar_location'])
				$values['avatar_location']='images/avatars/'.$values['avatar_location'];
			$values['_aupload']=$values['avatar_type']!='local';

			$al=$values['avatar_location'] ? ($values['_aupload'] && strpos($values['avatar_location'],'://')===false ? Eleanor::$uploads.'/avatars/' : '').$values['avatar_location'] : '';
			if($values['_aupload'])
			{
				$avatar['value']=$al;
				$values['avatar_location']='';
			}
			else
				$values['avatar_location']=$al;
		}

		foreach($values as $k=>&$v)
			if(isset($controls[$k]))
				$controls[$k]['value']=$v;
		$C=new Controls;
		$C->arrname=array();
		$values=$C->DisplayControls($controls)+$values;

		$C->arrname=array('avatar');
		$avatar=$C->DisplayControl($avatar);

		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$GLOBALS['title'][]=$lang['settings'];
		return Eleanor::$Template->AcOptions($controls,$values,$avatar,$errors,$saved);
	}
}