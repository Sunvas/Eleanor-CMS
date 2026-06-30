/** Create EditorJS instance
 * @url https://github.com/codex-team/editor.js/blob/next/types/configs/editor-config.d.ts */
export default async function(div,extra={},extra_tools={}){
	const{lang}=document.documentElement;

	if(lang!=="en")
		await import(`./editorjs-${lang}.mjs`).then(({i18n})=>Object.assign(extra,{i18n}),e=>console.error(e));

	return new EditorJS({
		holder: div,
		autofocus:true,
		minHeight:10,
		placeholder:div.dataset.placeholder,
		tools: {
			code: CodeTool,
			embed: Embed,
			header: Header,
			List: {
				class: EditorjsList,
				inlineToolbar: true,
				config: {
					defaultStyle: "unordered"
				},
			},
			quote: Quote,
			raw: RawTool,
			...extra_tools
		},
		...extra,
	});
};