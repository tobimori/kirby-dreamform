<script setup>
const props = defineProps({
	text: String,
	template: {
		type: Object,
		default: () => ({}),
	},
});
</script>

<template>
	<div
		class="df-log-info-entry"
		v-html="$t(props.text, props.template, props.text)"
	></div>
</template>

<style lang="scss">
.df-log-info-entry {
	padding: var(--spacing-2) var(--spacing-3);
	background: var(--color-white);
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
	line-height: var(--leading-normal);
	margin-bottom: 0.125rem;
	width: 100%;
}
</style>
