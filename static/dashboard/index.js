async function J(r)
{
	return r.ok ? r.json() : Promise.reject(r);
}

//Sidebar
L.then(()=>{
	const
		narrow="sidebar-narrow-unfoldable",
		sidebar=$("#sidebar")
			.toggleClass("hide",!localStorage.getItem("sidebar"))
			.toggleClass(narrow,!!localStorage.getItem("sidebar-narrow"))
			.on("shown.coreui.sidebar",()=>localStorage.setItem("sidebar",1))
			.on("hidden.coreui.sidebar",()=>localStorage.removeItem("sidebar")),
		SB=a=>coreui.Sidebar.getInstance(sidebar[0])[a]();

	$(document).on('swiped-left',()=>SB("hide"));
	$("button.header-toggler").on("click",()=>SB("toggle"));

	$("button.sidebar-toggler").on("click",()=>{
		sidebar.toggleClass(narrow);
		sidebar.hasClass(narrow) ? localStorage.setItem("sidebar-narrow",1) : localStorage.removeItem("sidebar-narrow");
	});
});

//Theme selector
L.then(()=>{
	const
		a="data-coreui-theme",
		d=localStorage.getItem("dark"),
		h=$("html");

	//Restore previously saved
	h.attr(a,d ? "dark" : "light");
	$(`#theme-selector button[name=${d ? 'dark' : 'light'}]`).addClass("active");

	//Switch
	$(document).on("click","#theme-selector button:not(.active)",function(){
		$("#theme-selector button").removeClass("active");

		const dark=$(this).addClass("active").attr("name")=="dark";

		h.attr(a,dark ? "dark" : "light");
		dark ? localStorage.setItem("dark",1) : localStorage.removeItem("dark");
	});
});

//ToolTip
L.then(()=>$('[data-coreui-toggle="tooltip"]').each((i,el)=>new coreui.Tooltip(el)));

//Nav links
L.then(()=>$(`nav a.nav-link[href='${location.pathname}']`).addClass("active"));

//Sign out
L.then(()=>$("#sign-out").on("click",()=>fetch(location.pathname,{method:"delete",headers:{accept:"application/json"}}).then(J).then(r=>r.ok ? location.reload() : alert(r.error))));

//Cron
if(document.currentScript.dataset.cron)
	(function F(){ fetch("cron.php").then(async r=>r.status==200 ? setTimeout(F,1000*(await r.text())) : 0); })();