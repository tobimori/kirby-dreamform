<script setup>
import { usePanel } from "kirbyuse"
import { ref, watch } from "vue"
import {
	after,
	autofocus,
	before,
	disabled,
	icon,
	label,
	name,
	required,
	type
} from "kirbyuse/props"

const props = defineProps({
	formData: {
		type: Object,
		default: () => ({})
	},
	sync: String,
	//api: String,
	empty: String,
	value: [String, Object],
	novalidate: {
		type: Boolean,
		default: false
	},
	config: Object,
	endpoints: Object,
	...autofocus,
	...after,
	...before,
	...disabled,
	...icon,
	...label,
	...name,
	...type,
	...required
})
const emit = defineEmits(["input"])

const fields = ref([])

const panel = usePanel()
const loadRemoteFields = async () => {
	if (!props.formData[props.sync]) return

	// the model endpoint starts with a slash, e.g. `/pages/forms+contact`
	const model = props.endpoints.model.replace(/^\//, "")
	const response = await panel.get(
		`dreamform/mailchimp/${model}/lists/${props.formData[props.sync]}/fields`
	)

	fields.value = Object.fromEntries(
		Object.entries(response).map(([key, value]) => [
			key,
			// hack to not show the "static/dynamic" toggles in the field mapping object preview
			{ ...value, saveable: key === "tags" ? false : value.saveable }
		])
	)
}

watch(
	() => props.formData[props.sync],
	() => loadRemoteFields()
)

loadRemoteFields()
</script>

<template>
	<k-object-field
		v-bind="props"
		:fields="fields"
		@input="emit('input', $event)"
	/>
</template>
