<script setup>
import { computed, ref, watch } from "kirbyuse"
import {
	after,
	autofocus,
	before,
	disabled,
	help,
	icon,
	id,
	label,
	name,
	required
} from "kirbyuse/props"
import { useSyncedSlug } from "@/utils/use-synced-slug"

const props = defineProps({
	formData: {
		type: Object,
		default: () => ({})
	},
	sync: {
		type: String,
		default: "label"
	},
	value: {
		type: String,
		default: ""
	},
	allow: {
		type: String,
		default: "a-zA-Z0-9_"
	},
	path: String,
	wizard: {
		type: [Boolean, Object],
		default: false
	},
	...after,
	...autofocus,
	...before,
	...disabled,
	...help,
	...icon,
	...id,
	...label,
	...name,
	...required
})

const emit = defineEmits(["input"])

const { slug, handleManualInput } = useSyncedSlug({
	initialValue: props.value,
	syncField: props.sync,
	syncSource: props.formData,
	onUpdate: (value) => emit("input", value)
})

const preview = computed(() => {
	if (props.help !== undefined) return props.help
	if (props.path !== undefined) return props.path + slug.value
	return null
})

const fieldProps = computed(() => {
	// eslint-disable-next-line no-unused-vars
	const { formData, sync, ...rest } = props
	return rest
})

// sync external value changes
watch(
	() => props.value,
	(newValue) => {
		slug.value = newValue || ""
	}
)

const input = ref(null)

defineExpose({
	focus: () => input.value?.focus()
})
</script>

<template>
	<k-field
		v-bind="fieldProps"
		:class="['k-slug-field', 'df-field-slug-field']"
		:help="preview"
		:input="id"
	>
		<k-input
			ref="input"
			v-bind="fieldProps"
			:value="slug"
			type="slug"
			@input="handleManualInput"
		/>
	</k-field>
</template>
