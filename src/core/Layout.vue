This is the layout component extended to accomodate multi-step form layouts.
Keep in mind that this component is heavily affected by any Core changes if they
happen and so it's important to keep an eye on the core changes and update this
component accordingly.
https://github.com/getkirby/kirby/blob/main/panel/src/components/Forms/Layouts/Layout.vue
This also means that the plugin does not work if any other plugin modifies the
layout component as well. We can solve this by creating an entire custom field
similar to https://github.com/tobimori/kirby-icon-field.

<template>
	<section
		:data-selected="isSelected"
		class="k-layout"
		:class="{
			'df-layout-page': isPageIndicator,
		}"
		tabindex="0"
		@click="$emit('select')"
	>
		<k-grid class="k-layout-columns" v-if="!isPageIndicator">
			<k-layout-column
				v-for="(column, columnIndex) in columns"
				:key="column.id"
				v-bind="{
					...column,
					endpoints,
					fieldsetGroups,
					fieldsets,
				}"
				@input="
					$emit('updateColumn', {
						column,
						columnIndex,
						blocks: $event,
					})
				"
			/>
		</k-grid>
		<div class="k-layout-columns df-layout-column" v-else>
			<k-icon type="survey" />
			{{ $t("dreamform.form.nextPage") }}
		</div>
		<nav v-if="!disabled" class="k-layout-toolbar">
			<k-button
				v-if="settings && !isPageIndicator"
				:title="$t('settings')"
				class="k-layout-toolbar-button"
				icon="settings"
				@click="openSettings"
			/>

			<k-button
				class="k-layout-toolbar-button"
				icon="angle-down"
				@click="$refs.options.toggle()"
			/>
			<k-dropdown-content ref="options" :options="options" align-x="end" />
			<k-sort-handle />
		</nav>
	</section>
</template>

<script>
export default {
	extends: "k-layout",
	computed: {
		isPageIndicator: {
			get() {
				return (
					this.columns.length === 1 &&
					this.columns[0].width === "dreamform-page"
				);
			},
		},
	},
};
</script>

<style lang="scss">
.df-layout-page {
	display: flex;
	align-items: center;
	overflow: hidden;
	border-radius: var(--rounded-md);
	width: 20rem;

	&,
	&:not(:last-of-type) {
		margin: 1.5rem auto;
	}

	.k-layout-toolbar {
		flex-direction: row;
		width: calc(2 * var(--layout-toolbar-width));
		background: var(--color-white);
	}
}

.df-layout-column {
	min-height: 2rem;
	display: flex;
	align-items: center;
	padding: 0 var(--spacing-2);
	width: 100%;
	padding-right: var(--layout-toolbar-width);
	background: var(--color-gray-100);
	--icon-color: var(--color-gray-600);
	color: var(--color-text-dimmed);
	font-weight: var(--font-semi);
	gap: var(--spacing-2);
}

body:has(.k-page-view[data-template="form"]) {
	// Tighter layout (we don't have layout config options)
	.k-layout-column {
		min-height: 4rem;
	}

	.k-layout-toolbar {
		padding-bottom: 0;
	}
}

.k-layout-df-page .k-layout-column {
	min-height: 4rem;
}
</style>
