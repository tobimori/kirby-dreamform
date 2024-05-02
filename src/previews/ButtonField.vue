<script setup>
import { props as blockProps } from "@/utils/block";
import Editable from "@/components/Editable.vue";

const props = defineProps(blockProps);
const emit = defineEmits(["update"]);
</script>

<template>
	<div class="df-field">
		<editable
			tag="div"
			class="df-button-input"
			:placeholder="$t('dreamform.fields.button.label.label')"
			:modelValue="content.label"
			@update:modelValue="emit('update', { ...props.content, label: $event })"
			@dblClick="open"
		/>
	</div>
</template>

<style lang="scss">
.df-button-input {
	outline: none;
	background: var(--color-black);
	color: var(--color-white);
	padding: var(--spacing-2) var(--spacing-3);
	border-radius: var(--rounded-md);
	box-shadow: var(--shadow-sm);
}
</style>
