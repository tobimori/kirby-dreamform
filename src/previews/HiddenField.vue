<script setup>
import { props as blockProps } from "@/utils/block";
import Editable from "@/components/Editable.vue";

const props = defineProps(blockProps);

const emit = defineEmits(["update", "open"]);
</script>

<template>
	<div class="df-field">
		<div
			class="df-input df-hidden-input"
			:class="{ 'is-invalid': !content.key }"
		>
			<k-icon type="hidden" />
			<editable
				tag="code"
				class="df-hidden-key"
				:slugify="true"
				:placeholder="$t('dreamform.fields.hidden.placeholder')"
				:modelValue="content.key"
				@update:modelValue="emit('update', { ...props.content, key: $event })"
			/>
		</div>
	</div>
</template>

<style lang="scss">
.df-hidden-key {
	font-size: var(--text-xs);
	line-height: var(--leading-h3);
	white-space: nowrap;
	max-width: 100%;
	overflow: hidden;
	text-overflow: ellipsis;

	.k-icon + & {
		margin-left: var(--spacing-3);
		width: 100%;
	}
}

.df-hidden-input {
	display: flex;
	align-items: center;
	padding-left: var(--spacing-3) !important;

	&.is-invalid {
		outline-color: var(--color-red);
	}
}
</style>
