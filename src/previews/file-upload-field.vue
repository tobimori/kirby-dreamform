<script setup>
import { props as blockProps } from "@/utils/block";
import FieldError from "@/components/FieldError.vue";
import FieldHeader from "@/components/FieldHeader.vue";

const props = defineProps(blockProps);

const emit = defineEmits(["update", "open"]);
const update = (value) => emit("update", { ...props.content, ...value });
const open = (e) => {
	if (e.target === e.currentTarget) {
		emit("open");
	}
};
</script>

<template>
	<div class="df-field" @dblclick="open">
		<field-header
			:requireLabel="true"
			:content="content"
			:fieldset="fieldset"
			@update="update"
		/>
		<div class="df-file-upload" @click="open">
			<k-icon type="upload" />
			<span>{{ $t("toolbar.button.file.upload") }}</span>
		</div>
		<field-error
			v-if="
				content.required ||
				content.maxsize !== '' ||
				content.allowedtypes.length > 0
			"
			:content="content"
			@update="update"
		/>
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
