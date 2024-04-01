import { disabled, id, section } from "kirbyuse/props";

export const props = {
	fieldset: section.fieldset,
	...disabled,
	...id,
	endpoints: {
		default: () => ({}),
		type: [Array, Object],
	},
	content: Object,
};
