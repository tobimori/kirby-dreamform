<script setup>
import Editable from "@/components/Editable.vue";

const props = defineProps({
	content: Object,
	fieldset: Object,
});

const emit = defineEmits(["update"]);
const update = (value) => emit("update", { ...props.content, ...value });
</script>

<template>
	<div class="df-field-header">
		<div>
			<editable
				tag="div"
				class="df-field-label"
				:placeholder="fieldset.name"
				:class="{ 'is-invalid': !content.label }"
				:modelValue="content.label"
				@update:modelValue="update({ label: $event })"
			/>
			<button
				type="button"
				class="df-field-required"
				:class="{ 'is-active': props.content.required }"
				@click="update({ required: !props.content.required })"
			>
				✶ <span>Required</span>
			</button>
		</div>
		<editable
			tag="code"
			class="df-field-key"
			:class="{ 'is-invalid': !content.key }"
			:slugify="true"
			:placeholder="$t('dreamform.key')"
			:modelValue="content.key"
			@update:modelValue="update({ key: $event })"
		/>
	</div>
</template>

<style lang="scss">
.df-field-header {
	justify-content: space-between;
	font-weight: var(--font-semi);
	margin-bottom: var(--spacing-2);
	line-height: var(--leading-h3);
	display: flex;
	align-items: center;

	&:hover .df-field-required {
		color: var(--color-gray-500);
	}

	.df-field-label.is-invalid {
		color: var(--color-red);
	}
}

.df-field-key {
	color: var(--color-gray-700);
	background: var(--color-gray-200);
	padding: var(--spacing-1) 0.375rem;
	border-radius: var(--input-rounded);
	font-size: var(--text-xs);
	text-align: right;

	&.is-invalid {
		background: var(--color-red);
		color: var(--color-white);
	}
}

.df-field-required {
	padding: 0.125rem;
	color: var(--color-white);
	transition: 100ms color;
	margin-left: var(--spacing-1);

	&.is-active {
		color: var(--color-blue);
	}
}
</style>
