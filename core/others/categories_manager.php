<?php
/*
	Copyright © Eleanor CMS, developed by Alexander Sunvas*, interface created by Rumin Sergey.
	For details, visit the web site http://eleanor-cms.ru, emails send to support@eleanor-cms.ru .
	*Pseudonym
*/
if(!defined('CMS'))die;
class Categories_Manager extends Categories
{
	public
		$pp='c_',#Префикс всех параметров динамических ссылок, генерируемых данным классом
		$table,#Имя основной таблицы категорий
		$template='CategoriesManager',#Название класса оформления
		$ondelete,#Callback функции, вызывамая при удалении категории, первым параметром передается массив всех удалямых категорий
		$post=false,#Флаг bypost контролов
		$controls=false,#Контролы категорий
		$Language;#Языковой объект

	/**
	 * Конструктор менеджера категорий
	 * @param string $l Путь к языковому файлу
	 */
	public function __construct($l='categories_manager-*.php')
	{
		$this->Language=new Language;
		$this->Language->queue[]=$l;
	}

	/**
	 * Показ содержимого менеджера категорий
	 * @return Template
	 */
	public function Show()
	{
		$El=Eleanor::getInstance();
		$El->module['links_categories']=array(
			'list'=>$El->Url->Prefix(),
			'add'=>$El->Url->Construct(array($this->pp.'do'=>'add'))
		);

		if($this->template)
			Eleanor::$Template->queue[]=$this->template;
		if(isset($_GET[$this->pp.'edit']))
			return$_SERVER['REQUEST_METHOD']=='POST' && Eleanor::$our_query ? $this->Save((int)$_GET[$this->pp.'edit']) : $this->AddEdit((int)$_GET[$this->pp.'edit']);
		elseif(isset($_GET[$this->pp.'delete']))
		{
			$id=(int)$_GET[$this->pp.'delete'];
			$R=Eleanor::$Db->Query('SELECT `title`,`parent`,`parents`,`pos` FROM `'.$this->table.'` LEFT JOIN `'.$this->table.'_l` USING(`id`) WHERE `id`='.$id.' AND `language` IN (\'\',\''.Language::$main.'\') LIMIT 1');
			if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
				return GoAway(true);
			$error='';
			do
			{
				if(!isset($_POST['ok']))
					break;

				$ids=array($id);
				$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$this->table.'` WHERE `parents` LIKE \''.$a['parents'].$id.',%\'');
				while($temp=$R->fetch_assoc())
					$ids[]=$temp['id'];

				if(is_callable($this->ondelete))
					try
					{
						call_user_func($this->ondelete,$ids);
					}
					catch(EE $E)
					{
						$error=$E->getMessage();
						break;
					}

				$ids=Eleanor::$Db->In($ids);
				Eleanor::$Db->Delete($this->table,'`id`'.$ids);
				Eleanor::$Db->Delete($this->table.'_l','`id`'.$ids);
				Eleanor::$Db->Update($this->table,array('!pos'=>'`pos`-1'),'`pos`>'.$a['pos'].' AND `parent`=\''.$a['parent'].'\'');
				Eleanor::$Db->Delete(P.'drafts','`key`=\''.get_class($this).'-'.Eleanor::$Login->GetUserValue('id').'-'.$id.'\' LIMIT 1');

				Eleanor::$Cache->Lib->DeleteByTag($this->table);
				GoAway(empty($_POST['back']) ? true : $_POST['back']);
			}while(false);

			$GLOBALS['title'][]=$this->Language['delc'];
			if(isset($_GET['noback']))
				$back='';
			else
				$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
			return Eleanor::$Template->CMDelete($a,$back,$error);
		}
		elseif(isset($_GET[$this->pp.'up']))
		{
			$id=(int)$_GET[$this->pp.'up'];
			$R=Eleanor::$Db->Query('SELECT `parents`,`pos` FROM `'.$this->table.'` WHERE `id`='.$id.' LIMIT 1');
			if($R->num_rows==0 or !Eleanor::$our_query)
				return GoAway();
			list($parents,$posit)=$R->fetch_row();
			$R=Eleanor::$Db->Query('SELECT COUNT(`parents`),`pos` FROM `'.$this->table.'` WHERE `pos`=(SELECT MAX(`pos`) FROM `'.$this->table.'` WHERE `pos`<'.$posit.' AND `parents`=\''.$parents.'\') AND `parents`=\''.$parents.'\'');
			list($cnt,$np)=$R->fetch_row();
			if($cnt>0)
			{
				if($cnt>1 or $np+1!=$posit)
				{
					$this->Optimize($parents);
					$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.$this->table.'` WHERE `id`='.$id.' LIMIT 1');
					list($posit)=$R->fetch_row();
				}
				Eleanor::$Db->Update($this->table,array('!pos'=>'`pos`+1'),'`pos`='.--$posit.' AND `parents`=\''.$parents.'\' LIMIT 1');
				Eleanor::$Db->Update($this->table,array('!pos'=>'`pos`-1'),'`id`='.$id.' AND `parents`=\''.$parents.'\' LIMIT 1');
			}
			GoAway(false,301,'cat'.$id);
		}
		elseif(isset($_GET[$this->pp.'down']))
		{
			$id=(int)$_GET[$this->pp.'down'];
			$R=Eleanor::$Db->Query('SELECT `parents`,`pos` FROM `'.$this->table.'` WHERE `id`='.$id.' LIMIT 1');
			if($R->num_rows==0 or !Eleanor::$our_query)
				return GoAway();
			list($parents,$posit)=$R->fetch_row();
			$R=Eleanor::$Db->Query('SELECT COUNT(`parents`),`pos` FROM `'.$this->table.'` WHERE `pos`=(SELECT MIN(`pos`) FROM `'.$this->table.'` WHERE `pos`>'.$posit.' AND `parents`=\''.$parents.'\') AND `parents`=\''.$parents.'\'');
			list($cnt,$np)=$R->fetch_row();
			if($cnt>0)
			{
				if($cnt>1 or $np-1!=$posit)
				{
					$this->Optimize($parents);
					$R=Eleanor::$Db->Query('SELECT `pos` FROM `'.$this->table.'` WHERE `id`='.$id.' LIMIT 1');
					list($posit)=$R->fetch_row();
				}
				Eleanor::$Db->Update($this->table,array('!pos'=>'`pos`-1'),'`pos`='.++$posit.' AND `parents`=\''.$parents.'\' LIMIT 1');
				Eleanor::$Db->Update($this->table,array('!pos'=>'`pos`+1'),'`id`='.$id.' AND `parents`=\''.$parents.'\' LIMIT 1');
			}
			GoAway(false,301,'cat'.$id);
		}
		elseif(isset($_GET[$this->pp.'do']))
			switch($_GET[$this->pp.'do'])
			{
				case'add':
					if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
						return$this->Save(0);
					return$this->AddEdit(0);
				case'restore':
					$p='';
					if(isset($_GET[$this->pp.'parent']))
					{
						$R=Eleanor::$Db->Query('SELECT `id`,`parents` FROM `'.$El->module['config']['t'].'` WHERE `id`='.(int)$_GET['id'].' LIMIT 1');
						if(list($id,$p)=$R->fetch_row())
							$p.=$id.',';
					}
					return$this->Optimize($p);
				break;
				case'draft':
					$id=isset($_POST['_draft']) ? (int)$_POST['_draft'] : 0;
					unset($_POST['_draft'],$_POST['back']);
					Eleanor::$Db->Replace(P.'drafts',array('key'=>get_class($this).'-'.Eleanor::$Login->GetUserValue('id').'-'.$id,'value'=>serialize($_POST)));
					Eleanor::$content_type='text/plain';
					Start('');
					echo'ok';
					return false;
			}
		return$this->Manager();
	}

	/**
	 * Получение контролов категорий по умолчанию
	 * @return array Массив контролов
	 */
	public function Controls()
	{
		$THIS=$this;#PHP 5.4 Убрать этот костыль
		return array(
			'parent'=>array(
				'title'=>$this->Language['parent'],
				'descr'=>'',
				'type'=>'select',
				'bypost'=>&$this->post,
				'options'=>array(
					'exclude'=>0,
					'callback'=>function($a)use($THIS)
					{
						if(!isset($THIS->dump))
							$THIS->Init($THIS->table);
						return Eleanor::Option('&mdash;',0,in_array(0,$a['value']),array(),2).$THIS->GetOptions($a['value'],$a['options']['exclude']);
					},
					'extra'=>array(
						'tabindex'=>1
					),
				),
			),
			'parents'=>array(
				'type'=>'',
				'descr'=>'',
				'bypost'=>&$this->post,
				'options'=>array(
					'content'=>false,
					'save'=>function($a,$Obj,$controls)use($THIS)
					{
						$R=Eleanor::$Db->Query('SELECT `id`,`parents` FROM `'.$THIS->table.'` WHERE `id`='.(int)$controls['parent'].' LIMIT 1');
						if($a=$R->fetch_assoc())
							return$a['parents'] ? $a['parents'].$a['id'].',' : $a['id'].',';
						return'';
					},
				),
			),
			'title'=>array(
				'title'=>$this->Language['name'],
				'descr'=>'',
				'type'=>'input',
				'check'=>function($value)use($THIS)
				{
					$errors=array();
					if(Eleanor::$vars['multilang'])
						foreach($value as $k=>&$v)
						{
							if($v=='')
								$errors[]=$THIS->Language['EMPTY_TITLE']($k);
						}
					elseif($value=='')
						$errors[]=$THIS->Language['EMPTY_TITLE']();
					return$errors;
				},
				'bypost'=>&$this->post,
				'multilang'=>Eleanor::$vars['multilang'],
				'options'=>array(
					'htmlsafe'=>true,
					'extra'=>array(
						'tabindex'=>2
					),
				),
			),
			'description'=>array(
				'title'=>$this->Language['descr'],
				'descr'=>'',
				'type'=>'editor',
				'bypost'=>&$this->post,
				'multilang'=>Eleanor::$vars['multilang'],
				'options'=>array(
					'htmlsafe'=>true,
				),
				'extra'=>array(
					'no'=>array('tabindex'=>3)
				),
			),
			'meta_title'=>array(
				'title'=>'Window title',
				'descr'=>'',
				'type'=>'input',
				'bypost'=>&$this->post,
				'multilang'=>Eleanor::$vars['multilang'],
				'options'=>array(
					'htmlsafe'=>true,
					'extra'=>array(
						'tabindex'=>4,
						'maxlength'=>150,
					),
				),
			),
			'meta_descr'=>array(
				'title'=>'Meta description',
				'descr'=>$this->Language['meta_descr'],
				'type'=>'input',
				'bypost'=>&$this->post,
				'multilang'=>Eleanor::$vars['multilang'],
				'options'=>array(
					'htmlsafe'=>true,
					'extra'=>array(
						'tabindex'=>5,
						'maxlength'=>150,
					),
				),
			),
			'uri'=>array(
				'title'=>'URI',
				'descr'=>'',
				'type'=>'input',
				'bypost'=>&$this->post,
				'multilang'=>Eleanor::$vars['multilang'],
				'options'=>array(
					'htmlsafe'=>true,
					'extra'=>array(
						'tabindex'=>6,
						'onfocus'=>'if(!$(this).val())$(this).val( $(\'[name=\\\'\'+ $(this).prop("name").replace("uri","title") +\'\\\']\').val() )',
					),
				),
			),
			'image'=>array(
				'title'=>$this->Language['picture'],
				'descr'=>'',
				'type'=>'select',
				'bypost'=>&$this->post,
				'options'=>array(
					'callback'=>function($a)use($THIS)
					{
						$path=Eleanor::$root.$THIS->imgfolder;
						$sel=Eleanor::Option('&mdash;','',in_array('',$a['value']),array(),2);
						$files=glob($path.'*.{jpg,jpeg,bmp,ico,gif,png}',GLOB_BRACE | GLOB_MARK);
						foreach($files as $v)
						{
							if(substr($v,-1)==DIRECTORY_SEPARATOR)
								continue;
							$v=basename($v);
							$sel.=Eleanor::Option($v,false,in_array($v,$a['value']));
						}
						return$sel;
					},
					'extra'=>array(
						'tabindex'=>7,
						'data-path'=>$this->imgfolder,
						'id'=>'image',
					),
				),
				'append'=>'<script type="text/javascript">//<![CDATA[
				$(function(){
					$("#image").change(function(){
						var val=$(this).val();
						if(val)
							$("#preview").prop("src",$(this).data("path")+val).closest("tr").show();
						else
							$("#preview").prop("src","images/spacer.png").closest("tr").hide();
					}).change();
				})
				//]]></script>'
			),
			'preview'=>array(
				'title'=>$this->Language['preview'],
				'descr'=>'',
				'type'=>'',
				'options'=>array(
					'content'=>'<img src="images/spacer.png" id="preview" />',
				),
			),
			'pos'=>array(
				'title'=>$this->Language['pos'],
				'descr'=>$this->Language['pos_'],
				'type'=>'input',
				'bypost'=>&$this->post,
				'options'=>array(
					'htmlsafe'=>true,
					'extra'=>array(
						'tabindex'=>8,
					),
				),
			),
		);
	}

	/**
	 * Получение списка всех категорий
	 * @return Template
	 */
	protected function Manager()
	{
		$GLOBALS['title'][]=$this->Language['list'];
		$parent=isset($_GET[$this->pp.'parent']) ? (int)$_GET[$this->pp.'parent'] : 0;

		$items=$subitems=$navi=$where=$qs=array();
		$El=Eleanor::getInstance();
		if($parent>0)
		{
			$qs['']['parent']=$parent;
			$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.$this->table.'` WHERE `id`='.$parent.' LIMIT 1');
			list($parents)=$R->fetch_row();
			$parents.=$parent;
			$temp=array();
			$R=Eleanor::$Db->Query('SELECT `id`,`title` FROM `'.$this->table.'` INNER JOIN `'.$this->table.'_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `id` IN ('.$parents.')');
			while($a=$R->fetch_assoc())
				$temp[$a['id']]=$a['title'];
			$navi[0]=array('title'=>$this->Language['list'],'_a'=>$El->Url->Prefix());
			foreach(explode(',',$parents) as $v)
				if(isset($temp[$v]))
					$navi[$v]=array('title'=>$temp[$v],'_a'=>$v==$parent ? false : $El->Url->Construct(array($this->pp.'parent'=>$v)));
			$El->module['links_categories']['add']=$El->Url->Construct(array($this->pp.'do'=>'add',$this->pp.'parent'=>$parent));
		}

		$R=Eleanor::$Db->Query('SELECT COUNT(`parent`) FROM `'.$this->table.'` WHERE `parent`='.$parent);
		list($cnt)=$R->fetch_row();

		$page=isset($_GET[$this->pp.'page']) ? (int)$_GET[$this->pp.'page'] : 1;
		if($page<=0)
			$page=1;
		if(isset($_GET[$this->pp.'new-pp']) and 4<$pp=(int)$_GET[$this->pp.'new-pp'])
			Eleanor::SetCookie('per-page',$pp);
		else
			$pp=abs((int)Eleanor::GetCookie('per-page'));
		if($pp<5 or $pp>500)
			$pp=50;
		$offset=abs(($page-1)*$pp);
		if($cnt and $offset>=$cnt)
			$offset=max(0,$cnt-$pp);
		$sort=isset($_GET[$this->pp.'sort']) ? (string)$_GET[$this->pp.'sort'] : '';
		if(!in_array($sort,array('id','title','pos')))
			$sort='';
		$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET[$this->pp.'so']) ? (string)$_GET[$this->pp.'so'] : 'asc';
		if($so!='desc')
			$so='asc';
		if($sort)
			$qs+=array('sort'=>$sort,'so'=>$so);
		else
			$sort='pos';
		$qs+=array('sort'=>false,'so'=>false);

		if($cnt>0)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`title`,`parent`,`image`,`pos` FROM `'.$this->table.'` INNER JOIN `'.$this->table.'_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `parent`='.$parent.' ORDER BY `'.$sort.'` '.$so.' LIMIT '.$offset.','.$pp);
			while($a=$R->fetch_assoc())
			{
				$a['_aedit']=$El->Url->Construct(array($this->pp.'edit'=>$a['id']));
				$a['_adel']=$El->Url->Construct(array($this->pp.'delete'=>$a['id']));
				$a['_aparent']=$El->Url->Construct(array($this->pp.'parent'=>$a['id']));
				$a['_aup']=$a['pos']>1 ? $El->Url->Construct(array($this->pp.'up'=>$a['id'])) : false;
				$a['_adown']=$a['pos']<$cnt ? $El->Url->Construct(array($this->pp.'down'=>$a['id'])) : false;
				$a['_aaddp']=$El->Url->Construct(array($this->pp.'do'=>'add',$this->pp.'parent'=>$a['id']));

				if($a['image'])
					$a['image']=$this->imgfolder.$a['image'];

				$subitems[]=$a['id'];
				$items[$a['id']]=array_slice($a,1);
			}
		}

		if($subitems)
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`parent`,`title` FROM `'.$this->table.'` INNER JOIN `'.$this->table.'_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `parent`'.Eleanor::$Db->In($subitems).' ORDER BY `pos` ASC');
			$subitems=array();
			while($a=$R->fetch_assoc())
				$subitems[$a['parent']][$a['id']]=$a['title'];

			foreach($subitems as &$v)
			{
				asort($v,SORT_STRING);
				foreach($v as $kk=>&$vv)
					$vv=array(
						'title'=>$vv,
						'_aedit'=>$El->Url->Construct(array($this->pp.'edit'=>$kk)),
					);
			}
		}

		$THIS=$this;#PHP 5.4 убрать костыль!
		$links=array(
			'sort_title'=>$El->Url->Construct(array_merge($qs,array($this->pp.'sort'=>'title',$this->pp.'so'=>$qs['sort']=='title' && $qs['so']=='asc' ? 'desc' : 'asc'))),
			'sort_pos'=>$El->Url->Construct(array_merge($qs,array($this->pp.'sort'=>'pos',$this->pp.'so'=>$qs['sort']=='pos' && $qs['so']=='asc' ? 'desc' : 'asc'))),
			'sort_id'=>$El->Url->Construct(array_merge($qs,array($this->pp.'sort'=>'id',$this->pp.'so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
			'form_items'=>$El->Url->Construct($qs+array($this->pp.'page'=>$page)),
			'pp'=>function($n) use ($El,$qs,$THIS){ return$El->Url->Construct($qs+array($THIS->pp.'new-pp'=>$n)); },
			'first_page'=>$El->Url->Construct($qs),
			'pages'=>function($n)use($El,$qs){ return$El->Url->Construct($qs+array('page'=>$n)); },
		);
		return Eleanor::$Template->CMList($items,$subitems,$navi,$cnt,$pp,$qs,$page,$links);
	}

	/**
	 * Получение формы правки категории
	 * @return Template
	 */
	protected function AddEdit($id,$errors=array())
	{
		if(!$this->controls)
			$this->controls=$this->Controls();
		$values=array('_onelang'=>false,'parent'=>array('value'=>isset($_GET[$this->pp.'parent']) ? (int)$_GET[$this->pp.'parent'] : 0));
		if($id)
		{
			$this->controls['parent']['options']['exclude']=$id;
			if(!$errors)
			{
				$R=Eleanor::$Db->Query('SELECT * FROM `'.$this->table.'` WHERE id='.$id.' LIMIT 1');
				if(!$a=$R->fetch_assoc())
					return GoAway(true);
				foreach($a as $k=>&$v)
					if(isset($this->controls[$k]))
						$values[$k]['value']=$v;
				$R=Eleanor::$Db->Query('SELECT * FROM `'.$this->table.'_l` WHERE `id`='.$id);
				while($a=$R->fetch_assoc())
					if(!Eleanor::$vars['multilang'] and (!$a['language'] or $a['language']==Language::$main))
					{
						foreach($a as $k=>&$v)
							if(isset($this->controls[$k]))
								$values[$k]['value']=$v;
						if(!$a['language'])
							break;
					}
					elseif(!$a['language'] and Eleanor::$vars['multilang'])
					{
						foreach($a as $k=>&$v)
							if(isset($this->controls[$k]))
								$values[$k]['value'][Language::$main]=$v;
						$values['_onelang']=true;
						break;
					}
					elseif(Eleanor::$vars['multilang'] and isset(Eleanor::$langs[$a['language']]))
						foreach($a as $k=>&$v)
							if(isset($this->controls[$k]))
								$values[$k]['value'][$a['language']]=$v;
				if(Eleanor::$vars['multilang'])
				{
					if(!isset($values['_onelang']))
						$values['_onelang']=false;
					$values['_langs']=isset($values['title']['value']) ? array_keys($values['title']['value']) : array();
				}
			}
			$GLOBALS['title'][]=$this->Language['editing'];
		}
		else
		{
			$GLOBALS['title'][]=$this->Language['adding'];
			if(Eleanor::$vars['multilang'])
			{
				$values['_onelang']=true;
				$values['_langs']=array_keys(Eleanor::$langs);
			}
		}

		$hasdraft=false;
		if(!$errors and !isset($_GET[$this->pp.'nodraft']))
		{
			$R=Eleanor::$Db->Query('SELECT `value` FROM `'.P.'drafts` WHERE `key`=\''.get_class().'-'.Eleanor::$Login->GetUserValue('id').'-'.$id.'\' LIMIT 1');
			if($draft=$R->fetch_row() and $draft[0])
			{
				$hasdraft=true;
				$_POST+=(array)unserialize($draft[0]);
				$errors=true;
			}
		}

		if($errors)
		{
			if($errors===true)
				$errors=array();
			$this->post=true;
			if(Eleanor::$vars['multilang'])
			{
				$values['_onelang']=isset($_POST['_onelang']);
				$values['_langs']=isset($_POST['_langs']) ? (array)$_POST['_langs'] : array(Language::$main);
			}
		}

		if(isset($_GET[$this->pp.'noback']))
			$back='';
		else
			$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

		$U=Eleanor::getInstance()->Url;
		$C=new Controls;
		$values=$C->DisplayControls($this->controls,$values)+$values;
		$links=array(
			'delete'=>$id ? $U->Construct(array($this->pp.'delete'=>$id,$this->pp.'noback'=>1)) : false,
			'nodraft'=>$hasdraft ? $U->Construct(array($this->pp.'do'=>$id ? false : 'add',$this->pp.'edit'=>$id ? $id : false,$this->pp.'nodraft'=>1)) : false,
			'draft'=>$U->Construct(array($this->pp.'do'=>'draft')),
		);
		return Eleanor::$Template->CMAddEdit($id,$this->controls,$values,$errors,$back,$links);
	}

	/**
	 * Сохранение категории
	 */
	protected function Save($id,$redir=true)
	{
		if(!$this->controls)
			$this->controls=$this->Controls();

		if(Eleanor::$vars['multilang'] and !isset($_POST['_onelang']))
		{
			$langs=isset($_POST['_langs']) ? (array)$_POST['_langs'] : array();
			$langs=array_intersect(array_keys(Eleanor::$langs),$langs);
			if(!$langs)
				$langs=array(Language::$main);
		}
		else
			$langs=array('');

		$C=new Controls;
		$C->langs=$langs;
		$C->throw=false;
		try
		{
			$values=$C->SaveControls($this->controls);
		}
		catch(EE$E)
		{
			return$this->AddEdit($id,array('ERROR'=>$E->getMessage()));
		}
		$errors=$C->errors;

		$errors=array();
		foreach($this->controls as $k=>&$v)
			if(isset($v['check']) and is_callable($v['check']))
				$errors+=call_user_func($v['check'],$values[$k]);

		if($errors)
			return$this->AddEdit($id,$errors);

		$lv=$lvalues=array();
		if(Eleanor::$vars['multilang'])
		{
			foreach($this->controls as $k=>&$v)
				if(is_array($v) and !empty($v['multilang']))
					$lv[]=$k;
			foreach($lv as &$v)
			{
				$lvalues[$v]=$values[$v];
				unset($values[$v]);
			}
		}
		else
		{
			foreach($this->controls as $k=>&$v)
				if(is_array($v) and isset($v['multilang']))
					$lv[]=$k;
			foreach($lv as &$v)
			{
				$lvalues[$v]=array(''=>$values[$v]);
				unset($values[$v]);
			}
		}

		$El=Eleanor::getInstance();
		foreach($lvalues['uri'] as $k=>&$v)
		{
			if($v=='')
				$v=htmlspecialchars_decode($lvalues['title'][$k],ELENT);
			$v=$El->Url->Filter($v,$k);
			$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$this->table.'` INNER JOIN `'.$this->table.'_l` USING(`id`) WHERE `uri`='.Eleanor::$Db->Escape($v).' AND `parent`='.Eleanor::$Db->Escape($values['parent']).' AND `language`=\''.$k.'\''.($id ? ' AND `id`!='.$id : '').' LIMIT 1');
			if($R->num_rows>0)
				$v=null;
		}

		Eleanor::$Db->Delete(P.'drafts','`key`=\''.get_class($this).'-'.Eleanor::$Login->GetUserValue('id').'-'.$id.'\' LIMIT 1');
		if($id)
		{
			if($values['parent']>0)
			{
				$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.$this->table.'` WHERE `id`='.(int)$values['parent'].' LIMIT 1');
				if(!list($parents)=$R->fetch_row() or $parents and strpos(','.$parents,','.$id.',')!==false)
					return$this->AddEdit($id,array('ERROR_PARENT'=>'Error parent'));
			}

			$R=Eleanor::$Db->Query('SELECT `parent`,`parents`,`pos` FROM `'.$this->table.'` WHERE `id`='.$id.' LIMIT 1');
			if(!list($parent,$parents,$pos)=$R->fetch_row())
				return GoAway();

			$values['pos']=(int)$values['pos'];
			if($values['pos']<=0)
				$values['pos']=1;
			if($pos!=$values['pos'])
			{
				Eleanor::$Db->Update($this->table,array('!pos'=>'`pos`-1'),'`pos`>'.$pos.' AND `parent`=\''.$parent.'\'');
				Eleanor::$Db->Update($this->table,array('!pos'=>'`pos`+1'),'`pos`>='.$values['pos'].' AND `parent`=\''.$values['parent'].'\'');
			}
			if($parent!=$values['parent'])
				Eleanor::$Db->Update($this->table,array('!parents'=>'REPLACE(`parents`,\''.$parents.'\',\''.$values['parents'].'\')'),'`parents` LIKE \''.$parents.$id.',%\'');
			Eleanor::$Db->Update($this->table,$values,'id='.$id.' LIMIT 1');
			Eleanor::$Db->Delete($this->table.'_l','`id`='.$id.' AND `language`'.Eleanor::$Db->In($langs,true));

			#Помним, что в таблице категорий могут быть еще и сторонние поля, как, например на форуме (количество сообщений, количество тем). Эти поля нужно сохранить.
			$othf=array();
			$R=Eleanor::$Db->Query('SELECT * FROM `'.$this->table.'_l` WHERE `id`='.$id);
			while($a=$R->fetch_assoc())
				$othf[$a['language']]=array_slice($a,2);

			foreach($langs as &$v)
			{
				$values=array(
					'id'=>$id,
					'language'=>$v,
				);
				foreach($lv as &$f)
					$values[$f]=array_key_exists($v,$lvalues[$f]) ? $lvalues[$f][$v] : '';

				if(isset($othf[$v]))
					$values+=$othf[$v];

				Eleanor::$Db->Replace($this->table.'_l',$values);
			}
		}
		else
		{
			if($values['pos']=='')
			{
				$R=Eleanor::$Db->Query('SELECT MAX(`pos`) FROM `'.$this->table.'` WHERE `parent`=\''.$values['parent'].'\'');
				list($pos)=$R->fetch_row();
				$values['pos']=$pos===null ? 1 : $pos+1;
			}
			else
			{
				if($values['pos']<=0)
					$values['pos']=1;
				Eleanor::$Db->Update($this->table,array('!pos'=>'`pos`+1'),'`pos`>='.(int)$values['pos'].' AND `parent`=\''.$values['parent'].'\'');
			}
			$id=Eleanor::$Db->Insert($this->table,$values);
			$values=array('id'=>array())+array_combine($lv,array_fill(0,count($lv),array()));
			foreach($langs as &$v)
			{
				$values['id'][]=$id;
				$values['language'][]=$v;
				foreach($lv as &$f)
					$values[$f][]=isset($lvalues[$f][$v]) ? $lvalues[$f][$v] : '';
			}
			Eleanor::$Db->Insert($this->table.'_l',$values);
		}
		Eleanor::$Cache->Lib->DeleteByTag($this->table);
		if($redir)
			GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}

	/**
	 * Оптимизация положений категорий, все позиции приводятся к корректному виду
	 * @param string $p Идентификатор родителей категории (parents)
	 */
	public function Optimize($p='')
	{
		$R=Eleanor::$Db->Query('SELECT `id`,`pos` FROM `'.$this->table.'` WHERE `parents`=\''.$p.'\' ORDER BY `pos` ASC');
		$cnt=1;
		while($a=$R->fetch_assoc())
		{
			if($a['pos']!=$cnt)
				Eleanor::$Db->Update($this->table,array('pos'=>$cnt),'`id`='.$a['id'].' LIMIT 1');
			++$cnt;
		}
	}
}