<script setup>
import { computed } from "kirbyuse"
import { ref, usePanel } from "kirbyuse"
import {
	autofocus,
	disabled,
	help,
	id,
	label,
	name,
	required,
	type
} from "kirbyuse/props"

const emit = defineEmits(["input"])

const props = defineProps({
	...disabled,
	...help,
	...id,
	...autofocus,
	...label,
	...name,
	...type,
	...required,
	options: {
		type: Array,
		default: () => []
	},
	value: {
		type: Object,
		default: () => ({
			type: "dynamic",
			field: null,
			value: null
		})
	}
})

const { $t } = usePanel()

const changeType = (type) => {
	emit("input", {
		type,
		value: props.value?.value ?? null,
		field: props.value?.field ?? null
	})
}

const typeOptions = [
	{
		id: "dynamic",
		icon: "input-cursor-move",
		label: $t("dreamform.fromField")
	},
	{
		id: "static",
		icon: "status-draft",
		label: $t("dreamform.static")
	}
]

const currentType = computed(
	() =>
		typeOptions.find((type) => type.id === props.value.type) ?? typeOptions[0]
)

const currentField = computed(() =>
	props.options.find((field) => field.id === props.value.field)
)

const staticIsInvalid = ref(props.invalid ?? false)
const isInvalid = computed(() => {
	if (!props.required) {
		return false
	}

	if (currentType.value.id === "static") {
		return staticIsInvalid.value
	}

	return !currentField.value
})

// element refs
const types = ref(null)
const fields = ref(null)
</script>

<template>
	<k-field v-bind="props" :input="id" class="df-dynamic-field">
		<k-input v-bind="props" :invalid="isInvalid" :icon="false">
			<div class="k-link-input-header df-dynamic-field-input-header">
				<!-- Type selector -->
				<k-button
					class="k-link-input-toggle"
					:disabled="props.disabled"
					:dropdown="!props.disabled && typeOptions.length > 1"
					:icon="currentType.icon"
					variant="filled"
					@click="types.toggle()"
				>
					{{ currentType.label }}
				</k-button>
				<k-dropdown-content
					ref="types"
					:options="
						typeOptions.map((obj) => ({
							...obj,
							click: () => changeType(obj.id)
						}))
					"
				/>

				<!-- Input -->
				<div v-if="currentType.id === 'dynamic'" style="display: contents">
					<k-button
						class="df-dynamic-field-input-toggle"
						:class="{ 'is-empty': !currentField }"
						:disabled="props.disabled || props.options.length === 0"
						:dropdown="!props.disabled && props.options.length > 0"
						:icon="currentField?.icon ?? 'box'"
						variant="default"
						@click="fields.toggle()"
					>
						{{
							props.options.length > 0
								? (currentField?.label ?? $t("dreamform.common.emptyField"))
								: $t("dreamform.common.noFields")
						}}
					</k-button>
					<k-dropdown-content
						v-if="props.options.length > 0"
						ref="fields"
						:options="
							props.options.map((obj) => ({
								...obj,
								label: obj.type ? `${obj.label} (${obj.type})` : obj.label,
								click: () => emit('input', { type: 'dynamic', field: obj.id })
							}))
						"
					/>
					<k-button
						v-if="currentField"
						icon="cancel-small"
						:aria-label="$t('dreamform.common.clearField')"
						@click="emit('input', { type: 'dynamic', field: null })"
					/>
				</div>

				<k-text-input
					v-else
					:id="id"
					ref="input"
					:disabled="props.disabled"
					:value="props.value?.value ?? ''"
					:required="props.required"
					@invalid="staticIsInvalid = !!$event"
					@input="emit('input', { type: 'static', value: $event })"
				/>
			</div>
		</k-input>
	</k-field>
</template>

<style>
.df-dynamic-field-input-header {
	grid-template-columns: max-content minmax(0, 1fr) max-content;
	padding-inline-end: 0.25rem;
}

.df-dynamic-field-input-toggle.k-button {
	--button-height: var(--height-sm);
	--button-rounded: var(--rounded-sm);
	justify-content: start;

	&:not(.is-empty) .k-button-arrow {
		display: none;
	}

	&.is-empty {
		margin-inline-end: -0.25rem;
		--button-color-text: var(--color-gray-700);

		.k-button-arrow {
			margin-inline-start: auto;
		}
	}
}
</style>
