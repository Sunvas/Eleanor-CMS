// Eleanor CMS © 2026 --> https://eleanor-cms.com
(async({template,container,data})=>{
	const
		{groups,...extra}=JSON.parse($(data).text()),
		app=(await import('./settings.js')).default(template,{...extra});

	Vue.createApp({
		template,
		extends:app,
		data:()=>({
			monolingual:true,
			group:groups[0].id,
			groups
		})
	}).mount(container);
})(document.currentScript.dataset);