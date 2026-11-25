<script setup>
import { useHelpers, usePanel } from "kirbyuse"

import EntryBase from "@/components/log/entry-base.vue"

defineOptions({ inheritAttrs: false })

const props = defineProps({
	path: String,
	isSpam: Boolean,
	isPartial: Boolean,
	log: {
		type: Array,
		default: () => []
	}
})

// the default dialog success handler shows the message and reloads the view
const panel = usePanel()
const toggleSpam = () =>
	panel.dialog.open(
		`submission/${props.path}/mark-as-${props.isSpam ? "ham" : "spam"}`
	)
const runActions = () =>
	panel.dialog.open(`submission/${props.path}/run-actions`)

const helpers = useHelpers()
const exists = (type) => helpers.isComponent(`df-log-${type}-entry`)
</script>

<template>
	<k-field
		:input="false"
		:label="$t('dreamform.submission')"
		class="df-submission-field"
	>
		<template #options>
			<k-button icon="play" size="xs" variant="filled" @click="runActions">
				{{ $t("dreamform.submission.runActions.button") }}
			</k-button>
		</template>
		<div class="df-submission-section">
			<div v-if="!isPartial" class="df-stat">
				{{ $t("dreamform.submission.markedAs").split("…")[0] }}
				<span
					class="df-stat-value"
					:class="isSpam ? 'is-negative' : 'is-positive'"
				>
					<k-icon :type="isSpam ? 'spam' : 'shield-check'"></k-icon>
					{{ $t("dreamform.submission." + (isSpam ? "spam" : "ham")) }}
				</span>
				{{ $t("dreamform.submission.markedAs").split("…")[1] }}
			</div>
			<div v-else class="df-stat">
				<span class="df-stat-value">
					<k-icon type="circle-half"></k-icon>
					{{ $t("dreamform.submission.partial") }}
				</span>
			</div>
		</div>
		<div v-if="!isPartial" class="df-submission-section">
			<k-button
				type="button"
				variant="dimmed"
				size="sm"
				icon="angle-right"
				:theme="isSpam ? 'positive' : 'error'"
				@click="toggleSpam"
			>
				{{
					$t(
						isSpam
							? "dreamform.submission.reportAsHam.button"
							: "dreamform.submission.reportAsSpam.button"
					)
				}}
			</k-button>
		</div>
		<div class="df-submission-log">
			<template v-for="entry in log" :key="entry.id">
				<EntryBase
					v-if="exists(entry.type) || entry.type === 'none'"
					:template="entry.data?.template"
					:timestamp="entry.timestamp"
					:title="entry.title"
					:icon="entry.icon"
				>
					<component
						:is="`df-log-${entry.type}-entry`"
						v-if="entry.type !== 'none'"
						v-bind="entry.data"
					/>
				</EntryBase>
				<k-box
					v-else
					:text="$t('dreamform.submission.error.logType', { type: entry.type })"
					icon="alert"
					theme="negative"
				/>
			</template>
		</div>
	</k-field>
</template>

<style>
.df-submission-section {
	background: var(--item-color-back);
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
	line-height: var(--leading-normal);
	margin-bottom: 0.125rem;

	.k-button {
		padding: var(--spacing-4) 1.25rem;
		width: 100%;
		justify-content: flex-start;
		border-radius: var(--rounded-sm);
		gap: var(--spacing-1);
	}
}

.df-stat {
	padding: var(--spacing-3) var(--spacing-6);
	line-height: var(--leading-tight);
}

.df-stat-value {
	white-space: pre;
	font-weight: var(--font-semi);
	margin-right: -0.25rem;

	.k-icon {
		display: inline-block;
		--icon-size: 1rem;
		vertical-align: text-bottom;
		color: var(--color-blue-600);
		margin-right: 0.125rem;
	}

	&.is-positive,
	&.is-positive .k-icon {
		color: var(--color-green-700);
	}

	&.is-negative {
		color: var(--color-red-600);

		.k-icon {
			color: var(--color-red-700);
		}
	}
}

.df-submission-log {
	margin-top: var(--spacing-8);
}
</style>
