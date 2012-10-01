/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
CORE.AcRegister={	max_name:15,	module:"",

	nameerrors:[],
	CheckName:function(name,F)
	{		var th=this;		if(typeof th.nameerrors[name]!="undefined")
			F(th.nameerrors[name]);
		else if(name.length>this.max_name)
			F(CORE.lang.NICK_TOO_LONG(this.max_name,name.length));
		else
			CORE.Ajax(
				{
					module:this.module,
					language:CORE.language,
					"do":"register",
					event:"login",
					name:name
				},
				function(error)
				{
					th.nameerrors[name]=error;
					F(error);
				}
			);
	},

	emailserrors:[],
	CheckEmail:function(email,F)
	{
		var th=this;
		if(typeof th.emailserrors[email]!="undefined")
			F(th.emailserrors[email]);
		else
			CORE.Ajax(
				{
					module:this.module,
					language:CORE.language,
					"do":"register",
					event:"email",
					email:email
				},
				function(error)
				{
					th.emailserrors[email]=error;
					F(error);
				}
			);
	}}