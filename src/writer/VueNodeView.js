import Vue from "vue";
import MyCustomComponent from "./MyCustomComponent.vue";

export default class CustomNodeView {
	constructor(node, view, getPos) {
		this.node = node;
		this.view = view;
		this.getPos = getPos;

		this.dom = document.createElement("span"); // Placeholder element
		this.mountVueComponent();
	}

	mountVueComponent() {
		const ComponentClass = Vue.extend(MyCustomComponent);
		const instance = new ComponentClass({
			propsData: {
				node: this.node,
				updateAttrs: this.updateAttrs.bind(this),
				isEditable: this.view.editable,
			},
		});

		instance.$mount();
		this.dom.appendChild(instance.$el);
	}

	updateAttrs(attrs) {
		const transaction = this.view.state.tr.setNodeMarkup(this.getPos(), null, {
			...this.node.attrs,
			...attrs,
		});
		this.view.dispatch(transaction);
	}

	// Ensure the component is destroyed when the node view is removed
	destroy() {
		if (this.vueComponent) {
			this.vueComponent.$destroy();
		}
	}
}
