<script setup>
const props = defineProps({
	text: String,
	template: {
		type: Object,
		default: () => ({}),
	},
	timestamp: Number,
});

console.log(props);
</script>

<template>
	<div v-html="$t(props.text, props.template)"></div>
</template>
