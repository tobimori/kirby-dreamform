<script setup>
import { useSection, ref, useApp, usePanel } from "kirbyuse";
import { section } from "kirbyuse/props";

const props = defineProps(section);

const app = useApp();
const panel = usePanel();

const didLoad = ref(false);
const isSpam = ref(false);
const isPartial = ref(false);

const loadSection = async () => {
	const { load } = useSection();
	const response = await load({
		parent: props.parent,
		name: props.name,
	});

	didLoad.value = true;
	isSpam.value = response.isSpam;
	isPartial.value = response.isPartial;
};

const toggleSpam = () => {
	app.$dialog(`submission/${props.parent.split("/")[2]}/mark-as-${isSpam.value ? 'ham' : 'spam'}`, {
		on: {
			success(res) {
				panel.dialog.close();
				panel.notification.success(res.message);
				loadSection();
			}
		}
	})
}

loadSection();
</script>

<template>
	<k-section :headline="$t('dreamform.submission')" v-if="didLoad">
		<div class="df-submission-section" >
			<div class="df-stat" v-if="!isPartial">
				{{ $t("dreamform.submission-marked-as").split("<>status</>")[0] }}
				<span class="df-stat-value" :class="isSpam ? 'is-negative' : 'is-positive'">
					<k-icon :type="isSpam ? 'spam' : 'shield-check'" />
					{{ $t(isSpam ? "dreamform.spam" : "dreamform.ham") }}
				</span>
				{{ $t("dreamform.submission-marked-as").split("<>status</>")[1] }}
			</div>
			<div class="df-stat" v-else>
				<span class="df-stat-value">
					<k-icon type="circle-half" />
					{{ $t("dreamform.partial-submission") }}
				</span>
			</div>
		</div>
		<div class="df-submission-section" v-if="!isPartial">
			<k-button type="button" variant="dimmed" size="sm" icon="angle-right" :theme="isSpam ? 'positive' : 'error'"
				@click="toggleSpam">
				{{ $t(isSpam ? "dreamform.report-as-ham" : "dreamform.report-as-spam") }}
			</k-button>
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

		&.is-positive, &.is-positive .k-icon {
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
</style>
