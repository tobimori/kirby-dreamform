<template>
	<k-dialog
		v-bind="$props"
		class="k-layout-selector"
		:size="selector?.size ?? 'medium'"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', value)"
	>
		<h3 class="k-label">{{ label }}</h3>
		<k-navigate
			:style="{ '--columns': Number(selector?.columns ?? 3) }"
			axis="x"
			class="k-layout-selector-options"
		>
			<button
				v-for="(columns, layoutIndex) in layouts"
				:key="layoutIndex"
				:aria-current="value === columns"
				:aria-label="columns.join(',')"
				:value="columns"
				class="k-layout-selector-option"
				@click="$emit('input', columns)"
			>
				<k-grid v-if="columns[0] !== 'dreamform-page'" aria-hidden>
					<k-column
						v-for="(column, columnIndex) in columns"
						:key="columnIndex"
						:width="column"
					/>
				</k-grid>
				<k-grid v-else>
					<div class="df-layout-selector-page">
						<k-icon type="survey" />
						<span>
							{{ $t("dreamform.form.newPage") }}
						</span>
					</div>
				</k-grid>
			</button>
		</k-navigate>
	</k-dialog>
</template>

<script>
export default {
	extends: "k-layout-selector",
};
</script>

<style lang="scss">
.df-layout-selector-page {
	grid-column: span 12;
	background: var(--color-gray-200);
	height: 100%;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: var(--spacing-2);
	color: var(--color-text-dimmed);
}
</style>
