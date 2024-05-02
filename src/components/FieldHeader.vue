<script setup>
import Editable from "@/components/Editable.vue";

const props = defineProps({
	content: Object,
	fieldset: Object,
	requireLabel: Boolean,
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
				:class="{ 'is-invalid': !content.label && requireLabel }"
				:modelValue="content.label"
				@update:modelValue="update({ label: $event })"
			/>
			<button
				type="button"
				class="df-field-required"
				:class="{ 'is-active': props.content.required }"
				@click="update({ required: !props.content.required })"
			>
				✶ <span>{{ $t("dreamform.common.required.label") }}</span>
			</button>
		</div>
		<div class="df-field-key">
			<editable
				tag="code"
				:class="{ 'is-invalid': !content.key }"
				:slugify="true"
				:placeholder="$t('dreamform.common.key.label')"
				:modelValue="content.key"
				@update:modelValue="update({ key: $event })"
			/>
			<k-icon type="key" />
		</div>
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

	&:hover .df-field-required:not(.is-active) {
		color: var(--color-gray-500);
	}

	.df-field-label.is-invalid {
		color: var(--color-red);
	}
}

.df-field-key {
	color: var(--color-gray-700);
	background: var(--color-gray-200);
	padding: 0.125rem var(--spacing-1);
	border-radius: var(--input-rounded);
	font-size: var(--text-xs);
	text-align: right;
	display: flex;
	gap: var(--spacing-1);
	align-items: center;

	&.is-invalid {
		background: var(--color-red);
		color: var(--color-white);
	}
}

.df-field-required {
	padding: 0.125rem;
	color: var(--color-white);
	transition: color 0.15s;
	margin-left: var(--spacing-1);

	&.is-active {
		color: var(--color-blue);

		&:hover {
			color: var(--color-blue-500);
		}
	}
}
</style>
