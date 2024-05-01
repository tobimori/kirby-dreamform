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
	<k-box
		class="df-log-error-entry"
		:text="$t(props.text, props.template, props.text)"
		theme="negative"
		icon="alert"
		:html="true"
	/>
</template>
