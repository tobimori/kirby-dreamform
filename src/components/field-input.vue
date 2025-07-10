<script setup>
import Editable from "@/components/editable.vue"

const props = defineProps({
	icon: String,
	content: Object,
	placeholder: String
})

const emit = defineEmits(["update"])
</script>

<template>
	<div class="df-input">
		<editable
			tag="div"
			class="df-placeholder"
			:placeholder="placeholder ?? $t('dreamform.common.placeholder.label')"
			:model-value="content.placeholder"
			@update:modelValue="emit('update', { ...content, placeholder: $event })"
		/>
		<k-icon v-if="icon" :type="icon" />
	</div>
</template>

<style>
.df-input {
	display: flex;
	position: relative;
	color: var(--input-color-placeholder);
	background-color: var(--input-color-back);
	box-shadow: var(--shadow-sm);
	outline: 1px solid var(--input-color-border);
	padding: 0.65rem var(--spacing-2);
	border-radius: var(--input-rounded);
	font-variant-numeric: tabular-nums;
	justify-content: space-between;
	line-height: var(--input-leading);
	overflow: hidden;
	align-items: center;
	max-height: var(--input-height);
}

.df-placeholder {
	display: flex;
	align-items: center;
	white-space: nowrap;
	max-width: calc(100% - 2rem);

	&.df-editable span {
		overflow: hidden;
		text-overflow: ellipsis;
		width: 100%;
	}

	& + .k-icon {
		margin-left: var(--spacing-2);
	}
}
</style>
