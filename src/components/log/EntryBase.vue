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
				<span
					v-html="
						$t(props.title, props.template, encodeURIComponent(props.title))
					"
				></span>
				<span> • {{ date }}</span>
			</div>
			<div class="df-log-entry-details" v-if="$slots.default">
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
	margin-top: var(--spacing-2);

	&:not(:last-child) {
		margin-bottom: var(--spacing-6);
	}

	&-heading {
		color: var(--color-gray-700);
		gap: var(--spacing-1);
		line-height: var(--leading-normal);

		strong {
			font-weight: 400;
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
		inset: 1.625rem auto -1.125rem 0.5rem;
	}

	&-icon {
		position: absolute;
		z-index: 2;
		inset-block-start: 0.125rem;
		inset-inline-start: 0;
		color: var(--color-gray-700);
	}

	&-content {
		width: 100%;
	}

	&-details {
		margin-block-start: var(--spacing-2);
	}
}
</style>
