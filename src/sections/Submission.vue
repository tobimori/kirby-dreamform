<script setup>
import { useSection, ref, useApp, usePanel } from "kirbyuse";
import { section } from "kirbyuse/props";

import EntryBase from "@/components/log/EntryBase.vue";

const props = defineProps(section);

const app = useApp();
const panel = usePanel();

const didLoad = ref(false);
const isSpam = ref(false);
const isPartial = ref(false);
const log = ref([]);

const loadSection = async () => {
	const { load } = useSection();
	const response = await load({
		parent: props.parent,
		name: props.name,
	});

	didLoad.value = true;
	isSpam.value = response.isSpam;
	isPartial.value = response.isPartial;
	log.value = response.log;
	console.log(log.value);
};

const toggleSpam = () => {
	app.$dialog(
		`submission/${props.parent.split("/")[2]}/mark-as-${
			isSpam.value ? "ham" : "spam"
		}`,
		{
			on: {
				success(res) {
					panel.dialog.close();
					panel.notification.success(res.message);
					loadSection();
				},
			},
		}
	);
};

const runActions = () => {
	app.$dialog(`submission/${props.parent.split("/")[2]}/run-actions`, {
		on: {
			success(res) {
				panel.dialog.close();
				panel.notification.success(res.message);
				loadSection();
			},
		},
	});
};

const exists = (type) => app.$helper.isComponent(`df-log-${type}-entry`);

loadSection();
</script>

<template>
	<k-section :headline="$t('dreamform.submission')" v-if="didLoad">
		<k-button
			icon="play"
			size="xs"
			slot="options"
			variant="filled"
			@click="runActions"
		>
			{{ $t("dreamform.submission.runActions.button") }}
		</k-button>
		<div class="df-submission-section">
			<div class="df-stat" v-if="!isPartial">
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
		<div class="df-submission-section" v-if="!isPartial">
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
			<template v-for="entry in log">
				<EntryBase
					v-if="exists(entry.type) || entry.type === 'none'"
					:key="entry.id"
					:template="entry.data?.template"
					:timestamp="entry.timestamp"
					:title="entry.title"
					:icon="entry.icon"
				>
					<component
						v-if="entry.type !== 'none'"
						:is="`df-log-${entry.type}-entry`"
						v-bind="entry.data"
					/>
				</EntryBase>
				<k-box
					v-else
					:key="`${entry.id}-error`"
					:text="$t('dreamform.submission.error.logType', { type: entry.type })"
					icon="alert"
					theme="negative"
				/>
			</template>
		</div>
	</k-section>
</template>

<style lang="scss">
.df-submission-section {
	background: var(--color-white);
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
	line-height: var(--leading-normal);
	margin-bottom: 0.125rem;

	.k-button {
		padding: var(--spacing-4) var(--spacing-5);
		width: 100%;
		justify-content: flex-start;
		border-radius: var(--rounded-sm);
		gap: var(--spacing-1);
	}
}

.df-stat {
	padding: var(--spacing-3) var(--spacing-6);
	line-height: var(--leading-tight);

	&-value {
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
}

.df-submission-log {
	margin-top: var(--spacing-8);
}
</style>
