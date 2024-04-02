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
		<field-header :content="content" :fieldset="fieldset" @update="update" />
		<div class="df-file-upload" @click="open">
			<k-icon type="upload" />
			<span>{{ $t("toolbar.button.file.upload") }}</span>
		</div>
		<field-error v-if="showError" :content="content" @update="update" />
	</div>
</template>

<style lang="scss">
.df-file-upload {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: var(--spacing-2);
	color: var(--input-color-placeholder);
	padding: var(--spacing-4) var(--spacing-2);
	border-radius: var(--input-rounded);
	border: 1.5px dashed var(--input-color-border);

	& > * {
		pointer-events: none;
	}
}
</style>
