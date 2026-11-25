<script setup>
import { usePanel } from "kirbyuse"

defineOptions({ inheritAttrs: false })

defineProps({
	activated: Boolean,
	local: Boolean
})

// the default dialog success handler shows the message and reloads the view
const panel = usePanel()
const openDialog = () => panel.dialog.open("dreamform/activate")
</script>

<template>
	<div v-if="!activated" class="df-license-field">
		<div class="df-license-field-wrapper">
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
	</div>
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

.df-license-field {
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
}

.df-license-field,
.df-license-field-wrapper {
	display: flex;
	align-items: center;
}

.df-license-field-wrapper {
	padding: var(--spacing-1);
}
</style>
