/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

CORE.Ukrainian={	Plural:function(n,a)
	{		return n%10==1&&n%100!=11?a[0]:(n%10>=2&&n%10<=4&&(n%100<10||n%100>=20)?a[1]:a[2]);	}}