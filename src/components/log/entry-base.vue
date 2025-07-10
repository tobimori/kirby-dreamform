<script setup>
import { formatDate } from "@/utils/date"

const props = defineProps({
	title: String,
	template: {
		type: Object,
		default: () => ({})
	},
	icon: String,
	timestamp: Number
})

const date = formatDate(props.timestamp)
</script>

<template>
	<div class="df-log-entry">
		<k-icon :type="props.icon" class="df-log-entry-icon" />
		<span class="df-log-entry-line"></span>
		<div class="df-log-entry-content">
			<div class="df-log-entry-heading">
				<span v-html="$t(props.title, props.template, props.title)"></span>
				<span> • {{ date }}</span>
			</div>
			<div v-if="$slots.default" class="df-log-entry-details">
				<slot></slot>
			</div>
		</div>
	</div>
</template>

<style>
.df-log-entry {
	display: flex;
	align-items: stretch;
	position: relative;
	padding-left: 1.75rem;
	margin-top: var(--spacing-2);

	&:not(:last-child) {
		margin-bottom: var(--spacing-6);
	}

	&:last-child .df-log-entry-line {
		display: none;
	}
}

.df-log-entry-heading {
	color: var(--color-text-dimmed);
	gap: var(--spacing-1);
	line-height: var(--leading-normal);

	strong {
		font-weight: 400;
		color: var(--color-text);
	}
}

.df-log-entry-line {
	width: 0.0625rem;
	background: var(--input-color-border);
	position: absolute;
	inset: 1.625rem auto -1.125rem 0.5rem;
}

.df-log-entry-icon {
	position: absolute;
	z-index: 2;
	inset-block-start: 0.125rem;
	inset-inline-start: 0;
	color: var(--color-text-dimmed);
}

.df-log-entry-content {
	width: 100%;
}

.df-log-entry-details {
	margin-block-start: var(--spacing-2);
}
</style>
