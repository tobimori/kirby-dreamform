<script setup>
import { props as blockProps } from "@/utils/block";
import FieldHeader from "@/components/FieldHeader.vue";
import Options from "@/components/Options.vue";
import FieldError from "@/components/FieldError.vue";
import FieldInput from "@/components/FieldInput.vue";

const props = defineProps(blockProps);

const emit = defineEmits(["update", "open"]);
const update = (value) => emit("update", { ...props.content, ...value });
const open = (e) => {
	if (e.target === e.currentTarget) emit("open");
};
</script>

<template>
	<div class="df-field df-select-field" @dblclick="open">
		<field-header
			:requireLabel="true"
			:content="content"
			:fieldset="fieldset"
			@update="update"
		/>
		<field-input :content="content" @update="update" icon="angle-down" />
		<options
			classMod="is-select"
			:options="content.options"
			@update="update({ options: $event })"
		/>
		<field-error v-if="content.required" :content="content" @update="update" />
	</div>
</template>

<style lang="scss">
.df-select-field .df-options-list {
	margin-top: var(--spacing-2);
}
.df-option.is-select {
	.df-option-inner,
	&.df-option-add-button {
		display: flex;
		align-items: center;
		box-shadow: var(--shadow-sm);
		background: var(--color-white);
		outline: 1px solid var(--color-gray-200);
		padding: var(--spacing-1) var(--spacing-1) var(--spacing-1) var(--spacing-2);
		border-radius: var(--input-rounded);
		font-variant-numeric: tabular-nums;
		justify-content: space-between;
		line-height: var(--input-leading);
		overflow: hidden;
	}

	.df-option-value {
		border-radius: var(--rounded-sm);
	}

	&.df-option-add-button {
		margin-top: 0;
		color: var(--color-gray-600);
		opacity: 0.75;
		transition: color 0.15s, opacity 0.15s;
		min-height: 1.75rem;
		padding-right: var(--spacing-2);

		&:hover {
			color: var(--color-gray-800);
			opacity: 1;
		}
	}

	&.k-sortable-ghost {
		outline: none;
		box-shadow: none;

		.df-option-inner {
			background: var(--color-gray-100);
			outline: 2px solid var(--color-focus);
			box-shadow: var(--shadow-md);
		}
	}
}
</style>
