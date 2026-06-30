// Eleanor CMS © 2025 --> https://eleanor-cms.com
(async({template,container,data})=>{
	data=JSON.parse(document.querySelector(data).textContent);

	const app=(await import("./settings.mjs")).default(template,data);

	Vue.createApp(app).mount(container);
})(document.currentScript.dataset);