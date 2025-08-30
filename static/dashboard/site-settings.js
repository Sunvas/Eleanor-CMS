// Eleanor CMS © 2025 --> https://eleanor-cms.com
(async({template,container,data})=>{
	data=JSON.parse($(data).text());

	const app=(await import('./settings.js')).default(template,data);

	Vue.createApp(app).mount(container);
})(document.currentScript.dataset);