<script setup>
import { computed } from "kirbyuse"
const props = defineProps({
	column: {
		default: () => ({}),
		type: Object
	},
	field: {
		default: () => ({}),
		type: Object
	},
	value: {
		type: Object,
		default: () => ({
			type: "dynamic",
			field: null,
			value: null
		})
	}
})

const currentField = computed(() =>
	props.field.options.find((field) => field.id === props.value.field)
)
</script>

<template>
	<div class="k-link-field-preview" :class="$options.class">
		<div
			v-if="value.type === 'dynamic' && currentField"
			class="k-tag df-dynamic-field-tag"
		>
			<k-icon :type="currentField.icon" />
			<span class="k-tag-text">
				{{ currentField.label }}
			</span>
		</div>
		<div v-else-if="value.type === 'static'" :class="$options.class">
			{{ value.value }}
		</div>
	</div>
</template>

<style>
.df-dynamic-field-tag.k-tag {
	max-width: max-content;

	.k-icon {
		margin-inline-start: 0.125rem;
		width: 1.5rem;
		height: 1.5rem;
		padding: 0.1875rem;
		margin-inline-end: -0.25rem;
	}
}
</style>
