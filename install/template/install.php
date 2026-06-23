<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\Classes\L10n;
use const Eleanor\{CHARSET,SITEDIR};

return new class extends \Eleanor\Basic {
	readonly L10n $l10n;
	readonly string $http;

	protected array $head=[];

	protected const int ENT = \ENT_QUOTES | \ENT_HTML5 | \ENT_SUBSTITUTE | \ENT_DISALLOWED;

	function __construct()
	{
		$this->l10n=new L10n('',__DIR__.'/l10n/');
		$this->http=\basename(__DIR__).'/';
	}

	/** Элемент шаблона. Отображает информацию в рамке с иконкой "ошибка"
	 * @param string $message Текст
	 * @param array $d */
	function Message(string$message,...$d):string
	{
		return<<<HTML
<div class="warning">
	<img src="{$this->http}images/warning.png" alt="">
	<div>
		<h4>{$this->l10n['error']}</h4>
		<p>{$message}</p>
	</div>
	<div class="clr"></div>
</div>
HTML;
	}

	/** Общий шаблон установщика
	 * @param string $title Заголовк
	 * @param int $percent Процент выполнения установки
	 * @param string $navi Навигационная строка
	 * @param string $content Содержимое страницы
	 * @param array ...$d Параметры по умолчанию */
	function index(string$title,int$percent,string$navi,string$content,...$d):string
	{
		$lang=L10n::$code;
		$sitedir=SITEDIR;
		$version=VERSION;
		$head=\join('',$this->head);

		return<<<HTML
<!DOCTYPE html>
<html lang="$lang">
<head>
	<base href="$sitedir">
	<meta charset="utf-8">
	<meta name="robots" content="noindex, follow">
	<title>$title :: Eleanor CMS $version</title>
$head
	<link rel="icon" href="../favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="template/style.css">
</head>
<body class="pagebg">
<main class="wrapper">
	<div class="elh"><div class="elh"><div class="elh">
		<div class="head">
			<h1>Eleanor CMS</h1>
			<div class="version">
				<span><span>{$this->l10n['version']}<b>$version</b></span></span>
			</div>
		</div>
		<div class="process">
			<div class="procline" title="{$this->l10n['progress']}"><img style="width:{$percent}%" src="{$this->http}images/spacer.png" alt="{$percent}%" title="{$percent}%"></div>
			<div class="procinfo"><span>$navi</span></div>
		</div>
	</div></div></div>
	<div class="wpbox">
		<div class="wptop"><b>&nbsp;</b></div>
		<div class="wpmid">
			<div class="wpcont">$content</div>
			<div class="clr"></div>
		</div>
		<div class="wpbtm"><b>&nbsp;</b></div>
	</div>
	<div class="elf"><div class="elf"><div class="elf">
		<div class="copyright">Powered by <a href="https://eleanor-cms.com" target="_blank">Eleanor CMS</a></div>
		<img class="elcd" src="{$this->http}images/spacer.png" alt="">
	</div></div></div>
</main>
</body>
</html>
HTML;
	}

	/** Шаг 1: выбор языка системы
	 * @param array ...$d Параметры по умолчанию */
	function Step1(...$d):string
	{
		$content=<<<HTML
<form class="selectlang" method="post">
	<button type="submit" name="l10n" value="ru" autofocus tabindex="1">
		<img src="{$this->http}images/flags/russian-big.png" alt="Русский" title="Русский">
		<span><b>Выбрать русский</b><br>основным языком системы</span>
	</button>
	<button type="submit" name="l10n" value="en" tabindex="1">
		<img src="{$this->http}images/flags/english-big.png" alt="English" title="English">
		<span><b>Select English</b><br>as main language of the system</span>
	</button>
</form>
HTML;

		return $this->index('Добро пожаловать! / Welcome!',0,'Выберите язык / Choose language',$content,...$d);
	}

	/** Проверка системных требований не пройдена */
	function Problems(array$errors,...$d):string
	{
		$content=<<<'HTML'
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
HTML;

		foreach($errors as $k=>$v)
		{
			if(in_array($k,['NOT_WRITABLE','NOT_EXIST'],true))
				$content.=$this->Message($this->l10n[$k].join('<br>',$v));
			else
				$content.=$this->Message($this->l10n[$v] ?? $v);
		}

		$content.=<<<'HTML'
			</table>
		</form>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
HTML;

		return $this->index($this->l10n['installation_impossible'],0,$this->l10n['problems'],$content,...$d);
	}

	/** Шаг 2: формальное лицензионное соглашение */
	function Step2(...$d):string
	{
		$year=\idate('Y');
		$content=<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<div class="wpcont">
			<form method="post">
				<div class="textarea license">
<p><strong>TL;DR: Do whatever the fuck you want!</strong></p>
<h1>MIT License</h1>
<p>Copyright (c) $year <a href="https://sunvas.online" target="_blank" style="color:black">Alexnader Sunvas</a></p>
<p>Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:</p>
<p>The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.</p>
<p>THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.</p>
				</div>
				<div class="submitline">
					<input type="submit" value="{$this->l10n['back']}" name="back" class="button" tabindex="1">
					<input type="submit" value="{$this->l10n['i_agree']}" name="agree" class="button" tabindex="1" autofocus>
				</div>
			</form>
		</div>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
HTML;

		return $this->index($this->l10n['license'],20,$this->l10n['read_careful'],$content,...$d);
	}

	/** Шаг 3: Настройки подключения к БД */
	function Step3(string$host,string$user,string$pass,string$db,string$title,string$description,string$hcaptcha,string$hsecret,bool$multilang,array$l10ns,string$username,string$password,string$password2,array$errors,...$d):string
	{
		Link('//cdn.jsdelivr.net');

		$data=\compact('host','user','pass','db','title','description','hcaptcha','hsecret','multilang','l10ns','username','password','password2');
		$data=\json_encode($data,JSON);
		$nonce=Nonce();
		$this->head[]=<<<HTML
<script src="//cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.min.js" nonce="$nonce" defer></script>
<script id="app-data" type="application/json">$data</script>
<script nonce="$nonce" src="template/step-3.js" defer data-container="#app" data-template="#app-tpl" data-data="#app-data"></script>
HTML;

		$db_errors='';

		foreach(['MYSQL_CONNECT','MYSQL_LOW'] as $err)
			if(\in_array($err,$errors))
				$db_errors.=$this->Message($this->l10n[$err]);

		$content=<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<div class="wpcont" id="app"></div>
		<script id="app-tpl" type="text/x-template">
			<form method="post">
				<h2 class="subhead">{$this->l10n['db']}</h2>{$db_errors}
				<ul class="reset formfield">
					<li class="ffield">
						<label for="host">{$this->l10n['db_host']}</label>
						<div class="ffdd">
							<input type="text" name="host" class="f_text" tabindex="1" id="host" v-model.lazy="host" autofocus required autocomplete="off">
							<br><small>{$this->l10n['db_host_']}</small>
						</div>
					</li>
					<li class="ffield">
						<label for="db">{$this->l10n['db_name']}</label>
						<div class="ffdd">
							<input type="text" name="db" class="f_text" tabindex="1" id="db" v-model.lazy="db" required autocomplete="off">
							<br><small>{$this->l10n['db-info']}</small>
						</div>
					</li>
					<li class="ffield">
						<label for="user">{$this->l10n['db_user']}</label>
						<div class="ffdd">
							<input type="text" name="user" class="f_text" tabindex="1" id="user" v-model.lazy="user" required autocomplete="username">
						</div>
					</li>
					<li class="ffield">
						<label for="pass">{$this->l10n['db_pass']}</label>
						<div class="ffdd">
							<input type="text" name="pass" class="f_text" tabindex="1" id="pass" v-model.lazy="pass" autocomplete="current-password">
						</div>
					</li>
				</ul>
				<br>
				<h3 class="subhead">{$this->l10n['settings']}</h3>
				<ul class="reset formfield">
					<li class="ffield">
						<label for="title">{$this->l10n['site-name']}</label>
						<div class="ffdd">
							<input type="text" name="title" class="f_text" tabindex="1" id="title" v-model.lazy="title" required>
						</div>
					</li>
					<li class="ffield">
						<label for="description">{$this->l10n['description']}</label>
						<div class="ffdd">
							<textarea name="description" class="f_text" tabindex="1" id="description" v-model.lazy="description"></textarea>
						</div>
					</li>
					<li class="ffield">
						<label for="multi">{$this->l10n['multilang']}</label>
						<div class="ffdd">
							<label>
								<input type="checkbox" name="multilang" tabindex="1" id="multi" v-model="multilang">
								<span>{$this->l10n['multi']}</span>
							</label>
							<br>
							<label>
								<input type="checkbox" name="l10ns[]" tabindex="1" id="l10ns" v-model="l10ns" :disabled="!multilang" v-for="value of translations" :value>
								<span>{$this->l10n['add-l10n']}</span>
							</label>
						</div>
					</li>
					<li class="ffield">
						<label for="hcaptcha">{$this->l10n['hcaptcha']}</label>
						<div class="ffdd">
							<input type="text" name="hcaptcha" class="f_text" tabindex="1" id="hcaptcha" v-model.lazy="hcaptcha" pattern="[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}" autocomplete="off">
						</div>
					</li>
					<li class="ffield">
						<label for="hsecret">{$this->l10n['hsecret']}</label>
						<div class="ffdd">
							<input type="text" name="hsecret" class="f_text" tabindex="1" id="hsecret" v-model.lazy="hsecret" autocomplete="off">
						</div>
					</li>
				</ul>
				<br>
				<h4 class="subhead">{$this->l10n['administrator']}</h4>
				<ul class="reset formfield">
					<li class="ffield">
						<label for="username">{$this->l10n['username']}</label>
						<div class="ffdd">
							<input type="text" name="username" class="f_text" tabindex="1" id="username" v-model.lazy="username" required autocomplete="nickname" maxlength="25">
						</div>
					</li>
					<li class="ffield">
						<label for="p1">{$this->l10n['p1']}</label>
						<div class="ffdd">
							<input type="password" name="password" class="f_text" tabindex="1" id="p1" v-model.lazy="password" required autocomplete="new-password" minlength="10">
						</div>
					</li>
					<li class="ffield">
						<label for="p2">{$this->l10n['p2']}</label>
						<div class="ffdd">
							<input type="password" name="password2" class="f_text" tabindex="1" id="p2" ref="p2" v-model.lazy="password2" required autocomplete="new-password" minlength="10">
						</div>
					</li>
				</ul>
				<div class="submitline">
					<input type="submit" value="{$this->l10n['back']}" name="back" class="button" tabindex="1" form="back-form">
					<input type="submit" value="{$this->l10n['install']}" name="next" class="button" tabindex="1">
				</div>
			</form>
			<form method="post" id="back-form" @submit="Back">
				<input type="hidden" v-for="[name,value] in back" :name :value>
				<input type="hidden" name="l10ns[]" v-if="multilang" v-for="value in l10ns" :value>
			</form>
		</script>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
HTML;

		return $this->index($this->l10n['config'],40,$this->l10n['fill'],$content,...$d);
	}

	protected function StatusResult(array$status,bool$ok):array
	{
		if($ok)
		{
			$this->head['refresh']=<<<'HTML'
<meta http-equiv="refresh" content="2">
HTML;
			$result=<<<HTML
<div class="submitline">{$this->l10n['update']}</div>
HTML;
		}
		else
		{
			Link('//cdn.jsdelivr.net');

			$nonce=Nonce();
			$this->head[]=<<<HTML
<script src="//cdn.jsdelivr.net/npm/jquery@4/dist/jquery.slim.min.js" nonce="$nonce" defer></script>
<script nonce="$nonce">addEventListener('DOMContentLoaded',function(){
	$(document).on("click","span.red",function(){ alert($(this).attr("title")); });
})</script>
HTML;
			$result=$this->Message($this->l10n['queries_error']).<<<HTML
<form method="post">
	<div class="submitline">
		<input type="submit" value="{$this->l10n['back']}" name="back" class="button" tabindex="1">
	</div>
</form>
HTML;
		}

		foreach($status as $k=>&$v)
		{
			$color=$v ? 'red' : 'green';
			$title=$v ? htmlspecialchars(strip_tags($v),self::ENT,CHARSET,false) : 'OK';

			$v=<<<HTML
<span class="$color" title="$title">$k</span>
HTML;
		}
		$status=join(', ',$status);

		return[$status,$result];
	}

	/** Шаг 4: Создание таблиц */
	function Step4(array$status,bool$ok,...$d):string
	{
		[$status,$result]=$this->StatusResult($status,$ok);

		$content=<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<div class="wpcont">
			<div class="information">
				<h4>{$this->l10n['creating']}</h4>
				$status
			</div>
			$result
		</div>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
HTML;

		return $this->index($this->l10n['creating'],60,$this->l10n['installing'],$content,...$d);
	}

	/** Шаг 5: Запись значений */
	function Step5(array$status,bool$ok,...$d):string
	{
		[$status,$result]=$this->StatusResult($status,$ok);

		$content=<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<div class="wpcont">
			<div class="information">
				<h4>{$this->l10n['inserting']}</h4>
				$status
			</div>
			$result
		</div>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
HTML;

		return $this->index($this->l10n['inserting'],80,$this->l10n['installing'],$content,...$d);
	}

	/** Шаг 6: Запись конфигов и финиш */
	function Step6(string$sitedir,...$d):string
	{
		$content=<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<div class="wpcont">
			<div class="information" style="text-align:center">
				<h4 style="color: green;">{$this->l10n['finished']}</h4>
			</div>
			<div class="information">{$this->l10n['finish_text']}</div>
			<div class="submitline">
				<p><a href="$sitedir">{$this->l10n['user-area']}</a></p>
				<p><a href="{$sitedir}admin.php">{$this->l10n['admin-panel']}</a></p>
			</div>
		</div>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
HTML;

		return $this->index($this->l10n['finished'],100,$this->l10n['finish'],$content,...$d);
	}
};