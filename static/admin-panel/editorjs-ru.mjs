/** EditorJS Russian l10n.
 * @url https://github.com/codex-team/editor.js/blob/next/example/example-i18n.html */
export const i18n={messages:{
/**
 * Other below: translation of different UI components of the editor.js core
 */
"ui": {
	"blockTunes": {
		"toggler": {
			"Click to tune": "Нажмите, чтобы настроить",
				"or drag to move": "или перетащите"
		},
	},
	"inlineToolbar": {
		"converter": {
			"Convert to": "Конвертировать в"
		}
	},
	"toolbar": {
		"toolbox": {
			"Add": "Добавить",
		}
	},
	"popover": {
		"Filter": "Поиск",
		"Nothing found": "Ничего не найдено",
		/**
		 * Translation of "Convert To"  at the Block Tunes Popover
		 */
		"Convert to": "Конвертировать в",
	}
},

/**
 * Section for translation Tool Names: both block and inline tools
 */
"toolNames": {
	"Text": "Параграф",
	"Heading": "Заголовок",
	"Ordered List": "Нумерованный список",
	"Unordered List": "Маркированный список",
	"Attachment": "Вложение",
	"Checklist": "Чеклист",
	"Quote": "Цитата",
	"Code": "Код",
	"Raw HTML": "HTML-фрагмент",
	"Link": "Ссылка",
	"Marker": "Маркер",
	"Bold": "Полужирный",
	"Italic": "Курсив",
	"InlineCode": "Моноширинный",
	"Image": "Картинка",
},

/**
 * Section for passing translations to the external tools classes
 */
"tools": {
	/**
	 * Link is the internal Inline Tool
	 */
	"link": {
		"Add a link": "Вставьте ссылку"
	},
	/**
	 * The "stub" is an internal block tool, used to fit blocks that does not have the corresponded plugin
	 */
	"stub": {
		'The block can not be displayed correctly.': 'Блок не может быть отображен'
	},
	"image": {
		"Caption": "Подпись",
		"Select an Image": "Выберите файл",
		"With border": "Добавить рамку",
		"Stretch image": "Растянуть",
		"With background": "Добавить подложку",
	},
	"code": {
		"Enter a code": "Код",
	},
	"header": {
		"Heading 1": "Заголовок 1",
		"Heading 2": "Заголовок 2",
		"Heading 3": "Заголовок 3",
		"Heading 4": "Заголовок 4",
		"Heading 5": "Заголовок 5",
		"Heading 6": "Заголовок 6",
	},
	"paragraph": {
		"Enter something": "Введите текст"
	},
	"list": {
		"Ordered": "Нумерованный",
		"Unordered": "Маркированный",
		"Checklist": "Чеклист",
	},
	/**
	 * Translation of "Convert To"  at the Inline Toolbar hint
	 */
	"convertTo": {
		"Convert to": "Конвертировать в"
	},
},

/**
 * Section allows to translate Block Tunes
 */
"blockTunes": {
	/**
	 * Each subsection is the i18n dictionary that will be passed to the corresponded Block Tune plugin
	 * The name of a plugin should be equal the name you specify in the 'tunes' section for that plugin
	 *
	 * Also, there are few internal block tunes: "delete", "moveUp" and "moveDown"
	 */
	"delete": {
		"Delete": "Удалить",
		"Click to delete": "Подтвердить удаление"
	},
	"moveUp": {
		"Move up": "Переместить вверх"
	},
	"moveDown": {
		"Move down": "Переместить вниз"
	},
},
}};