<script setup>
import Editable from "@/components/Editable.vue";

const props = defineProps({
	content: Object,
});

const emit = defineEmits(["update"]);
</script>

<template>
	<div class="df-field-error">
		<span>{{ $t("dreamform.common.errorMessage.label") }}:</span>
		<editable
			tag="span"
			:placeholder="$t('dreamform.fields.error.required')"
			:modelValue="content.errormessage"
			@update:modelValue="emit('update', { ...content, errormessage: $event })"
		/>
	</div>
</template>

<style lang="scss">
.df-field-error {
	display: flex;
	align-items: center;
	color: var(--color-black);
	font-size: var(--text-xs);
	color: var(--color-gray-900);
	margin-block-start: var(--spacing-3);

	& > span:not(.df-editable) {
		display: block;
		color: var(--color-gray-600);
		margin-right: var(--spacing-1);
	}
}
</style>
