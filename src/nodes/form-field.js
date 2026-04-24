class DreamformFormFieldView {
	constructor(node, view, getPos) {
		this.dom = document.createElement("span")
		this.dom.dataset.dreamformField = true
		this.node = node
		this.view = view
		this.getPos = getPos

		// extract field key from {{ field_key }} format
		this.fieldKey = node.attrs.field
			.replace(/^\{{2}\s*/, "")
			.replace(/\s*\}{2}$/, "")

		// initial label update
		this.updateLabel()

		// handle click to edit
		this.dom.addEventListener("click", () => {
			window.panel.dialog.open("dreamform/form-fields", {
				query: { field: this.fieldKey },
				on: {
					submit: (value) => {
						// extract field key from JSON value
						const fieldData = JSON.parse(value.field)

						// update node with new field key
						view.dispatch(
							view.state.tr.setNodeMarkup(getPos(), null, {
								field: `{{ ${fieldData.key} }}`
							})
						)
						window.panel.dialog.close()
					}
				}
			})
		})
	}

	update(node) {
		// update field key if changed
		const newFieldKey = node.attrs.field
			.replace(/^\{{2}\s*/, "")
			.replace(/\s*\}{2}$/, "")
		if (newFieldKey !== this.fieldKey) {
			this.fieldKey = newFieldKey
			this.updateLabel()
		}
		return true
	}

	updateLabel() {
		const content = window.panel?.content?.version?.("changes") || {}
		const label = this.findFieldLabel(content, this.fieldKey)
		// fall back to the kebab-case key so it matches what the field editor shows
		this.dom.innerText = label || this.fieldKey.replaceAll("_", "-")
	}

	findFieldLabel(content, fieldKey) {
		const blocks =
			content.fields?.flatMap(
				(field) => field.columns?.flatMap((column) => column.blocks || []) || []
			) || []

		// placeholders use snake_case but stored keys use kebab-case, so normalize both sides
		const normalize = (key) => key?.replaceAll("-", "_")
		const normalizedFieldKey = normalize(fieldKey)
		const block = blocks.find(
			(block) => normalize(block.content?.key) === normalizedFieldKey
		)
		return block?.content?.label || null
	}

	stopEvent() {
		return true
	}
}

export default {
	get button() {
		return {
			id: this.name,
			icon: "input-field",
			label: window.panel.$t("dreamform.writerNodes.formField"),
			name: this.name,
			inline: true
		}
	},

	get name() {
		return "dreamformFormField"
	},

	commands({ type }) {
		return () => (state, dispatch) => {
			window.panel.dialog.open("dreamform/form-fields", {
				on: {
					submit: (value) => {
						const { tr, selection } = state
						const pos = selection.$from.pos

						// extract field key from JSON value
						const fieldData = JSON.parse(value.field)
						const fieldKey = fieldData.key

						// create node with only the field key
						const node = type.create({
							field: `{{ ${fieldKey} }}`
						})

						let transaction = tr.replaceSelectionWith(node)
						transaction = transaction.insertText(" ", pos + node.nodeSize)

						// move cursor after space
						transaction = transaction.setSelection(
							state.selection.constructor.near(
								transaction.doc.resolve(pos + node.nodeSize + 1)
							)
						)

						dispatch(transaction.scrollIntoView())
						window.panel.dialog.close()
					}
				}
			})
		}
	},

	get schema() {
		return {
			group: "inline",
			inline: true,
			atom: true,
			attrs: {
				field: { default: "" }
			},
			parseDOM: [
				{
					tag: "span[data-dreamform-field]",
					getAttrs: (dom) => ({
						field: dom.dataset.field || `{{ ${dom.innerText} }}`
					})
				}
			],
			toDOM: (node) => [
				"span",
				{
					"data-dreamform-field": true,
					"data-field": node.attrs.field
				},
				node.attrs.field
			]
		}
	},

	view(node, view, getPos) {
		return new DreamformFormFieldView(node, view, getPos)
	}
}
