<script setup>
import { computed, onMounted, onUnmounted, ref } from "kirbyuse"

const props = defineProps({
	template: {
		type: Object,
		default: () => ({})
	},
	from: String,
	subject: String,
	body: String
})

const body = props.body.replaceAll("\n", "<br>").replaceAll("———", "<hr>")

const isExpanded = ref(false)
const uuid = ref(Math.random().toString(36).substring(2))

const contentRef = ref(null)
const height = ref(0)

const updateHeight = () => {
	height.value = contentRef.value?.clientHeight + 24
}

onMounted(() => {
	updateHeight()
	window.addEventListener("resize", updateHeight)
})

onUnmounted(() => {
	window.removeEventListener("resize", updateHeight)
})

const meta = computed(() => [
	{ key: "subject", value: props.subject },
	{ key: "to", value: props.template.to },
	{ key: "from", value: props.from }
])
</script>

<template>
	<div
		class="df-log-email-entry"
		:class="{ 'is-expanded': isExpanded }"
		:style="{ '--height': height + 'px' }"
	>
		<div
			:id="uuid"
			ref="contentRef"
			class="df-log-email-entry-content"
			:aria-hidden="!isExpanded"
		>
			<div
				v-for="{ key, value } in meta"
				:key="key"
				class="df-log-email-entry-meta"
			>
				<span class="df-log-email-entry-meta-label">
					{{ $t(`dreamform.actions.email.log.${key}`) }}
				</span>
				<span class="df-log-email-entry-meta-value" :data-type="key">{{
					value
				}}</span>
			</div>
			<p class="df-log-email-entry-body" v-html="body"></p>
		</div>
		<k-button
			type="button"
			class="df-log-email-entry-expand"
			:aria-expanded="isExpanded"
			:aria-controls="uuid"
			variant="filled"
			size="xs"
			:dropdown="true"
			@click="isExpanded = !isExpanded"
		>
			{{
				$t(`dreamform.actions.email.log.${isExpanded ? "collapse" : "expand"}`)
			}}
		</k-button>
	</div>
</template>

<style>
.df-log-email-entry {
	overflow: hidden;
	width: 100%;
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
	background: var(--item-color-back);
	line-height: var(--leading-normal);
	position: relative;
	max-height: 14rem;

	@media (prefers-reduced-motion: no-preference) {
		transition: max-height 0.25s ease-out;
		will-change: height;
	}

	&.is-expanded {
		max-height: var(--height);

		&::before {
			opacity: 0;
		}

		.df-log-email-entry-expand .k-icon {
			transform: rotate(180deg);
		}
	}

	&::before {
		content: "";
		background: linear-gradient(
			to bottom,
			transparent,
			rgb(from var(--item-color-back) r g b / 20%),
			var(--item-color-back)
		);
		position: absolute;
		pointer-events: none;
		inset: 0;
		transition: all 0.15s ease-in-out;
	}

	hr {
		border-top: 1px solid
			light-dark(var(--color-gray-300), var(--color-gray-900));
		margin-bottom: -1.25rem;
	}
}

.df-log-email-entry-body {
	padding: var(--spacing-4) var(--spacing-3);
	color: var(--color-text);
}

.df-log-email-entry-meta {
	padding-block: var(--spacing-2);
	padding-inline: var(--spacing-3);
	display: grid;
	grid-template-columns: auto 1fr;
	border-block-end: 1px solid
		light-dark(var(--color-gray-300), var(--color-gray-900));
	white-space: nowrap;
	overflow: hidden;
}

.df-log-email-entry-meta-value {
	text-overflow: ellipsis;
	overflow: hidden;
	max-width: max-content;
	display: block;
	line-height: var(--leading-normal);
	margin-inline-start: var(--spacing-2);
	color: var(--color-text);

	&:not([data-type="subject"]) {
		background: var(--field-color-back);
		border-radius: 9999px;
		padding-inline: var(--spacing-2);
	}
}

.df-log-email-entry-meta-label {
	color: var(--color-text-dimmed);
}

.df-log-email-entry-expand {
	position: absolute;
	inset: auto var(--spacing-3) var(--spacing-3) auto;
	z-index: 20;
	display: flex;
	color: var(--color-text-dimmed);
	gap: var(--spacing-1);
	align-items: center;
	background: light-dark(var(--color-white), var(--color-gray-800));
}
</style>
