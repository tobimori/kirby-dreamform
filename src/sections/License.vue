<script setup>
import { ref, useApp, usePanel, useSection } from "kirbyuse";
import { section } from "kirbyuse/props";

const props = defineProps(section);

const activated = ref(true);
const local = ref(false);

const loadSection = async () => {
	const { load } = useSection();
	const response = await load({
		parent: props.parent,
		name: props.name,
	});

	activated.value = response.activated;
	local.value = response.local;
};

const app = useApp();
const panel = usePanel();
const openDialog = () => {
	app.$dialog("dreamform/activate", {
		on: {
			success(t) {
				panel.dialog.close();
				panel.notification.success(t.message);
				loadSection();
			},
		},
	});
};

loadSection();
</script>

<template>
	<k-section class="df-license-section" v-if="!activated">
		<div class="df-license-section-wrapper">
			<a
				href="https://plugins.andkindness.com/dreamform"
				target="_blank"
				class="df-logo"
			>
				<k-icon type="dreamform" class="" />
				<h1>DreamForm</h1>
			</a>
			<h2
				v-text="
					$t(local ? 'dreamform.license.cta' : 'dreamform.license.demoMode')
				"
			></h2>
		</div>
		<a href="https://plugins.andkindness.com/dreamform/pricing" target="_blank">
			{{ $t("dreamform.license.buy") }}
		</a>
		<k-button
			size="sm"
			theme="info"
			variant="filled"
			icon="key"
			@click="openDialog()"
		>
			{{ $t("dreamform.license.activate") }}
		</k-button>
	</k-section>
</template>

<style lang="scss">
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

	&,
	&-wrapper {
		display: flex;
		align-items: center;
	}

	&-wrapper {
		padding: var(--spacing-1);
	}

	a:not(.df-logo) {
		display: block;
		color: var(--color-blue-800);
		text-decoration: underline;
		text-decoration-color: currentColor;
		text-underline-offset: 0.125rem;
		margin-right: 0.75rem;
		margin-left: auto;
	}
}
</style>
