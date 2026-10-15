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
	<!-- eslint-disable-next-line vue/no-v-html -- values are escaped -->
	<div class="df-log-info-entry" v-html="text"></div>
</template>

<style>
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
