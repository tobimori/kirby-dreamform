<script setup>
import { props as blockProps } from "@/utils/block";
import Editable from "../utils/Editable.vue";

const props = defineProps(blockProps);
const emit = defineEmits(["update"]);
</script>

<template>
	<editable
		tag="div"
		class="df-button-input"
		:placeholder="$t('dreamform.submit')"
		:modelValue="content.label"
		@update:modelValue="emit('update', { ...props.content, label: $event })"
		@dblClick="open"
	/>
</template>

<style lang="scss">
.k-block-type-button-field {
	padding: var(--spacing-3) var(--spacing-4);
	height: 100%;
	display: flex;
	align-items: center;
}

.df-button-input {
	outline: none;
	background: var(--color-black);
	color: var(--color-white);
	padding: var(--spacing-2) var(--spacing-3);
	border-radius: var(--rounded-md);
	box-shadow: var(--shadow-sm);
}
</style>
