<script setup>
import Editable from "@/components/editable.vue"
import { props as blockProps } from "@/utils/block"

const props = defineProps(blockProps)
const emit = defineEmits(["update"])
</script>

<template>
	<div class="df-field">
		<editable
			tag="div"
			class="df-button-input"
			:placeholder="$t('dreamform.fields.button.label.label')"
			:model-value="content.label"
			@update:modelValue="emit('update', { ...props.content, label: $event })"
			@dblClick="open"
		/>
	</div>
</template>

<style>
.df-button-input {
	outline: none;
	background: light-dark(var(--color-black), var(--color-white));
	color: light-dark(var(--color-white), var(--color-black));
	padding: var(--spacing-2) var(--spacing-3);
	border-radius: var(--rounded-md);
	box-shadow: var(--shadow-sm);
}
</style>
