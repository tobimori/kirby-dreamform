<script setup>
import { computed } from "kirbyuse";
import { props as blockProps } from "@/utils/block";
import Editable from "../utils/Editable.vue";

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
</script>

<template>
	<div class="df-text-field" @dblclick="open">
		<div class="df-field-label">
			<div>
				<editable
					tag="div"
					class="df-field-label-input"
					:placeholder="fieldset.name"
					:class="{ 'is-invalid': !content.label }"
					:modelValue="content.label"
					@update:modelValue="update({ label: $event })"
				/>
				<button
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
		<div class="df-input">
			<editable
				tag="div"
				class="df-placeholder"
				:placeholder="$t('dreamform.placeholder')"
				:modelValue="content.placeholder"
				@update:modelValue="update({ placeholder: $event })"
			/>
			<k-icon v-if="fieldset.icon === 'email'" :type="fieldset.icon" />
		</div>
		<div class="df-error" v-if="showError">
			<span>{{ $t("dreamform.error-message") }}:</span>
			<editable
				tag="span"
				:placeholder="$t('dreamform.error-message-default')"
				:modelValue="content.errorMessage"
				@update:modelValue="update({ errorMessage: $event })"
			/>
		</div>
	</div>
</template>

<style lang="scss">
// overrides for certain blockks
.k-block-type-textarea-field .df-text-field {
	.df-input {
		max-height: none;

		.df-placeholder {
			align-items: flex-start;
			min-height: 6rem;
			white-space: pre-wrap;
		}
	}
}

// base styles
.df-text-field {
	padding: var(--spacing-3) var(--spacing-4);
	height: 100%;

	.df-error {
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
		max-height: var(--input-height);

		.df-placeholder + .k-icon {
			margin-left: var(--spacing-2);
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
		}
	}
}

.df-field-label {
	display: flex;
	justify-content: space-between;
	font-weight: var(--font-semi);
	margin-bottom: var(--spacing-2);
	line-height: var(--leading-h3);
	align-items: center;

	&:hover {
		.df-field-required {
			color: var(--color-gray-500);
		}
	}

	.df-field-label-input.is-invalid {
		color: var(--color-red);
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

	.df-field-key {
		color: var(--color-gray-700);
		background: var(--color-gray-200);
		padding: var(--spacing-1) 0.375rem;
		border-radius: var(--input-rounded);
		font-size: var(--text-xs);

		&.is-invalid {
			background: var(--color-red);
			color: var(--color-white);
		}
	}
}
</style>
