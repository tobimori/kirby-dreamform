<script setup>
import FieldError from "@/components/field-error.vue"
import FieldHeader from "@/components/field-header.vue"
import Options from "@/components/options.vue"
import { props as blockProps } from "@/utils/block"
import { computed } from "kirbyuse"

const props = defineProps(blockProps)

const emit = defineEmits(["update", "open"])
const update = (value) => emit("update", { ...props.content, ...value })
const open = (e) => {
	if (e.target === e.currentTarget) emit("open")
}

const useWriter = computed(
	() =>
		props.fieldset.type === "radio-field" ||
		props.fieldset.type === "checkbox-field"
)
</script>

<template>
	<div class="df-field" @dblclick="open">
		<field-header
			:content="content"
			:fieldset="fieldset"
			:min-as-required="fieldset.type === 'checkbox-field'"
			@update="update"
		/>
		<options
			:class-mod="{
				'is-radio': fieldset.type === 'radio-field',
				'is-checkbox': fieldset.type === 'checkbox-field'
			}"
			:use-writer="useWriter"
			:writer-options="
				useWriter ? fieldset.tabs.field.fields.options.fields.label : {}
			"
			:options="content.options"
			@update="update({ options: $event })"
		/>
		<field-error v-if="content.required" :content="content" @update="update" />
	</div>
</template>

<style>
.df-option.is-checkbox,
.df-option.is-radio {
	.df-option-icon {
		display: block;
		width: 1rem;
		height: 1rem;
		border: 1px solid var(--input-color-border);
		margin-right: var(--spacing-2);
		background: light-dark(var(--color-white), var(--color-gray-700));
		box-shadow: var(--shadow-sm);
		flex-shrink: 0;
	}

	&.k-sortable-ghost {
		outline: none;
		box-shadow: none;

		.df-option-icon {
			background: light-dark(var(--color-gray-100), var(--color-gray-800));
			outline: 2px solid var(--color-focus);
			box-shadow: var(--shadow-md);
		}
	}
}
.df-option.is-radio {
	.df-option-icon {
		border-radius: 999px;
	}
}

.df-option.is-checkbox {
	.df-option-icon {
		border-radius: var(--choice-rounded);
	}
}
</style>
