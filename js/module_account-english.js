CORE.Lang({
	NICK_TOO_LONG:function(n,l)
	{
		return"User name length should not exceed "+n+" character"+(n==1 ? "" : "s")+". Your - "+l+" character"+(l==1 ? "" : "s")+".";
	},
	PASS_TOO_SHORT:function(n,l)
	{
		return"The password should be at least "+n+" character"+(n==1 ? "" : "s")+". Your - "+l+" character"+(l==1 ? "" : "s")+".";
	}
})
