<script setup>
import { translateLog } from "@/utils/log"

const props = defineProps({
	text: String,
	template: {
		type: Object,
		default: () => ({})
	}
})

const text = translateLog(props.text, props.template)
</script>

<template>
	<div v-safe-html="text" class="df-log-info-entry"></div>
</template>

<style>
.df-log-info-entry {
	padding: var(--spacing-2) var(--spacing-3);
	background: var(--item-color-back);
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
	line-height: var(--leading-normal);
	margin-bottom: 0.125rem;
	width: 100%;
}
</style>
