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
	<k-box
		class="df-log-error-entry"
		:text="text"
		theme="negative"
		icon="alert"
		:html="true"
	/>
</template>
