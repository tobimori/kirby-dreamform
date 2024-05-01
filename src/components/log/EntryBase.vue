<script setup>
import { formatDate } from "@/utils/date";

const props = defineProps({
	title: String,
	template: {
		type: Object,
		default: () => ({}),
	},
	icon: String,
	timestamp: Number,
});

const date = formatDate(props.timestamp);
</script>

<template>
	<div class="df-log-entry">
		<k-icon :type="props.icon" class="df-log-entry-icon" />
		<span class="df-log-entry-line"></span>
		<div class="df-log-entry-content">
			<div class="df-log-entry-heading">
				<span>{{ $t(props.title, props.template, props.title) }}</span>
				<span>{{ date }}</span>
			</div>
			<div class="df-log-entry-details">
				<slot></slot>
			</div>
		</div>
	</div>
</template>

<style lang="scss">
.df-log-entry {
	display: flex;
	align-items: stretch;
	position: relative;
	padding-left: 1.75rem;
	margin-top: var(--spacing-3);

	&:not(:last-child) {
		margin-bottom: var(--spacing-6);
	}

	&-heading {
		white-space: nowrap;
		color: var(--color-gray-700);
		display: flex;
		gap: var(--spacing-1);
		margin-bottom: var(--spacing-2);
		flex-wrap: wrap;

		> span:first-child {
			color: var(--color-black);
		}
	}

	&:last-child &-line {
		display: none;
	}

	&-line {
		width: 0.0625rem;
		background: var(--color-gray-400);
		position: absolute;
		top: 1.25rem;
		left: 0.5rem;
		bottom: -1rem;
	}

	&-icon {
		position: absolute;
		z-index: 2;
		inset-block-start: -0.125rem;
		inset-inline-start: 0;
		color: var(--color-gray-700);
	}

	&-content {
		width: 100%;
	}
}
</style>
