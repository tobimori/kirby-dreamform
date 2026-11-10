<script setup>
import { computed, ref, useApp, usePanel, useSection } from "kirbyuse"
import { section } from "kirbyuse/props"

const props = defineProps(section)

const STORAGE_KEY = "kirby$dreamform$license$banner"

const state = ref("active")
const local = ref(false)
const isClosed = ref(window.sessionStorage.getItem(STORAGE_KEY) === "true")

const message = computed(() => {
	if (["expired", "revoked"].includes(state.value))
		return `dreamform.license.${state.value}`
	return local.value ? "dreamform.license.cta" : "dreamform.license.demoMode"
})

const loadSection = async () => {
	const { load } = useSection()
	const response = await load({
		parent: props.parent,
		name: props.name
	})

	state.value = response.state
	local.value = response.local
}

const close = () => {
	window.sessionStorage.setItem(STORAGE_KEY, "true")
	isClosed.value = true
}

const app = useApp()
const panel = usePanel()
const openDialog = () => {
	app.$dialog("dreamform/activate", {
		on: {
			success(t) {
				panel.dialog.close()
				panel.notification.success(t.message)
				loadSection()
			}
		}
	})
}

loadSection()
</script>

<template>
	<k-section
		v-if="state !== 'active' && (!local || !isClosed)"
		class="df-license-section"
	>
		<div class="df-license-section-wrapper">
			<a
				href="https://www.andkindness.com/dreamform"
				target="_blank"
				class="df-logo"
			>
				<k-icon type="dreamform" class="" />
				<h1>DreamForm</h1>
			</a>
			<h2 v-text="$t(message)"></h2>
		</div>
		<a href="https://www.andkindness.com/buy?plugin=dreamform" target="_blank">
			{{ $t("dreamform.license.buy") }}
		</a>
		<k-button-group layout="collapsed">
			<k-button
				size="sm"
				theme="info"
				variant="filled"
				icon="key"
				@click="openDialog()"
			>
				{{ $t("dreamform.license.activate") }}
			</k-button>
			<k-button
				v-if="local"
				size="sm"
				theme="info"
				variant="filled"
				icon="cancel-small"
				:title="$t('close')"
				@click="close()"
			/>
		</k-button-group>
	</k-section>
</template>

<style>
.df-logo {
	display: flex;
	align-items: center;
	color: #1b4493;
	margin-right: 1rem;
	font-weight: var(--font-semi);

	.k-icon {
		width: 1rem;
		height: 1rem;
		margin-right: 0.5rem;
	}
}

.df-license-section {
	background: var(--color-blue-300);
	padding: var(--spacing-1) var(--spacing-1);
	border-radius: var(--rounded-lg);
	justify-content: space-between;
	color: var(--color-black);

	a:not(.df-logo) {
		display: block;
		color: var(--color-blue-800);
		text-decoration: underline;
		text-decoration-color: currentColor;
		text-underline-offset: 0.125rem;
		margin-right: 0.75rem;
		margin-left: auto;
	}

	.k-button-group[data-layout="collapsed"] > .k-button {
		--theme-color-border: var(--color-blue-300);
	}
}

.df-license-section,
.df-license-section-wrapper {
	display: flex;
	align-items: center;
}

.df-license-section-wrapper {
	padding: var(--spacing-1);
}
</style>
