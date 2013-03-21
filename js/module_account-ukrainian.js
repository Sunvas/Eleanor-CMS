CORE.AddScript("js/ukrainian.js",function(){
	CORE.Lang({
		NICK_TOO_LONG:function(n,l)
		{
			return"Довжина імені користувача не повинна перевищувати "+n+CORE.Ukrainian.Plural(n,[" символ"," символи"," символів"])+". Ви ввели "+l+CORE.Ukrainian.Plural(l,[" символ."," символи."," символів."]);
		},
		PASS_TOO_SHORT:function(n,l)
		{
			return"Довжина пароля повинна бути мінімум "+n+CORE.Ukrainian.Plural(n,[" символ"," символи"," символів"])+". Ви ввели тільки "+l+CORE.Ukrainian.Plural(l,[" символ."," символи."," символів."]);
		}
	})
});