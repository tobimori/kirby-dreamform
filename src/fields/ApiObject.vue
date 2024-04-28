<script setup>
import { watch } from "kirbyuse";
import { ref, useApi } from "kirbyuse";
import {
	autofocus,
	after,
	before,
	disabled,
	icon,
	invalid,
	label,
	name,
	type,
	required,
} from "kirbyuse/props";

const props = defineProps({
	formData: {
		type: Object,
		default: () => ({}),
	},
	sync: String,
	api: String,
	empty: String,
	value: [String, Object],
	novalidate: {
		type: Boolean,
		default: false,
	},
	config: Object,
	endpoints: Object,
	...autofocus,
	...after,
	...before,
	...disabled,
	...icon,
	...invalid,
	...label,
	...name,
	...type,
	...required,
});
const emit = defineEmits(["input"]);

const fields = ref([]);

const api = useApi();
const loadRemoteFields = async () => {
	if (!props.formData[props.sync]) return;

	const response = await api.get(
		`/dreamform/object/mailchimp/${props.endpoints.model}/${
			props.formData[props.sync]
		}`
	);

	fields.value = Object.fromEntries(
		Object.entries(response).map(([key, value]) => [
			key,
			// hack to not show the "static/dynamic" toggles in the field mapping object preview
			{ ...value, saveable: key === "tags" ? false : value.saveable },
		])
	);
};

watch(
	() => props.formData[props.sync],
	() => loadRemoteFields()
);

loadRemoteFields();
</script>

<template>
	<k-object-field
		v-bind="props"
		:fields="fields"
		@input="emit('input', $event)"
	/>
</template>
