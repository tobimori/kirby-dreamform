<script setup>
import { props as blockProps } from "@/utils/block";
import FieldHeader from "@/components/FieldHeader.vue";
import Options from "@/components/Options.vue";
import FieldError from "@/components/FieldError.vue";
import { computed } from "kirbyuse";

const props = defineProps(blockProps);

const emit = defineEmits(["update", "open"]);
const update = (value) => emit("update", { ...props.content, ...value });
const open = (e) => {
	if (e.target === e.currentTarget) emit("open");
};

const useWriter = computed(
	() =>
		props.fieldset.type === "radio-field" ||
		props.fieldset.type === "checkbox-field",
);
</script>

<template>
	<div class="df-field" @dblclick="open">
		<field-header
			:content="content"
			:fieldset="fieldset"
			@update="update"
			:minAsRequired="fieldset.type === 'checkbox-field'"
		/>
		<options
			:classMod="{
				'is-radio': fieldset.type === 'radio-field',
				'is-checkbox': fieldset.type === 'checkbox-field',
			}"
			:useWriter="useWriter"
			:writerOptions="
				useWriter ? fieldset.tabs.field.fields.options.fields.label : {}
			"
			:options="content.options"
			@update="update({ options: $event })"
		/>
		<field-error v-if="content.required" :content="content" @update="update" />
	</div>
</template>

<style lang="scss">
.df-option.is-checkbox,
.df-option.is-radio {
	.df-option-icon {
		display: block;
		width: 1rem;
		height: 1rem;
		border: 1px solid var(--choice-color-border);
		margin-right: var(--spacing-2);
		background: var(--color-white);
		box-shadow: var(--shadow-sm);
		flex-shrink: 0;
	}

	&.k-sortable-ghost {
		outline: none;
		box-shadow: none;

		.df-option-icon {
			background: var(--color-gray-100);
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
