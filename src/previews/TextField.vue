<script setup>
import { computed } from "kirbyuse";
import { props as blockProps } from "@/utils/block";
import FieldError from "@/components/FieldError.vue";
import FieldInput from "@/components/FieldInput.vue";
import FieldHeader from "@/components/FieldHeader.vue";

const props = defineProps(blockProps);

const emit = defineEmits(["update", "open"]);
const update = (value) => emit("update", { ...props.content, ...value });
const open = (e) => {
	if (e.target === e.currentTarget) {
		emit("open");
	}
};

const showError = computed(() => {
	// required always needs an error message
	if (props.content.required) {
		return true;
	}

	// fields that could have validation errors
	// without additional fields
	if (props.fieldset.type === "email-field") {
		return true;
	}

	if (
		// number fields with min or max value set
		props.fieldset.type === "number-field" &&
		(props.content.min !== "" || props.content.max !== "")
	) {
		return true;
	}

	return false;
});

const icon = computed(() => {
	if (["title", "text-left"].includes(props.fieldset.icon)) {
		return null;
	}

	if (props.fieldset.icon === "document") {
		return "angle-down";
	}

	return props.fieldset.icon;
});
</script>

<template>
	<div class="df-field" @dblclick="open">
		<field-header
			:requireLabel="true"
			:content="content"
			:fieldset="fieldset"
			@update="update"
		/>
		<field-input :content="content" @update="update" :icon="icon" />
		<field-error v-if="showError" :content="content" @update="update" />
	</div>
</template>

<style lang="scss">
.k-block-type-textarea-field .df-field {
	.df-input {
		max-height: none;

		.df-placeholder {
			align-items: flex-start;
			min-height: 6rem;
			white-space: pre-wrap;
		}
	}
}
</style>
