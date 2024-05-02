<script setup>
import Editable from "@/components/Editable.vue";

const props = defineProps({
	icon: String,
	content: Object,
});

const emit = defineEmits(["update"]);
</script>

<template>
	<div class="df-input">
		<editable
			tag="div"
			class="df-placeholder"
			:placeholder="$t('dreamform.common.placeholder.label')"
			:modelValue="content.placeholder"
			@update:modelValue="emit('update', { ...content, placeholder: $event })"
		/>
		<k-icon v-if="icon" :type="icon" />
	</div>
</template>

<style lang="scss">
.df-input {
	display: flex;
	position: relative;
	color: var(--color-gray-700);
	box-shadow: var(--shadow-sm);
	outline: 1px solid var(--color-gray-200);
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
