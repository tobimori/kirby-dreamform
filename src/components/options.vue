<script setup>
import Editable from "@/components/editable.vue"
import { nextTick, onMounted, ref, useApp, watch } from "kirbyuse"

const app = useApp()

const props = defineProps({
	classMod: String,
	useWriter: Boolean,
	writerOptions: Object,
	options: Array
})

// convert options to items, handle vue rendering
const items = ref([])
const setItems = (options) => {
	items.value = options.map((option) => ({
		_id: app.$helper.uuid(),
		...option
	}))
}

onMounted(() => setItems(props.options))
watch(
	() => props.options,
	(options) => setItems(options)
)

// handle updating
const emit = defineEmits(["update"])
const update = (value) => emit("update", value)
const sort = () => update(items.value)

const updateOption = (id, value) => {
	const index = items.value.findIndex((item) => item._id === id)
	items.value[index] = { ...items.value[index], ...value }
	update(items.value)
}

const addOption = () => {
	update([...items.value, { value: "", label: "" }])
	nextTick(() => focusLabel(items.value[items.value.length - 1]._id))
}

const removeOption = (id) => {
	update(items.value.filter((item) => item._id !== id))
	nextTick(() => {
		if (items.value.length) {
			focusValue(items.value[items.value.length - 1]._id)
		}
	})
}

// handle focus
const labelInputs = ref([])
const valueInputs = ref([])

const focusEndOf = (el) => {
	console.log(labelInputs.value, valueInputs.value)
	if (!el) return

	el.focus()
	const range = document.createRange()
	const selection = window.getSelection()
	range.setStart(el, el.childNodes.length)
	range.collapse(true)
	selection.removeAllRanges()
	selection.addRange(range)
}

const focusLabel = (id) => {
	const index = items.value.findIndex((item) => item._id === id)
	focusEndOf(labelInputs.value[index].el)
}

const focusValue = (id) => {
	const index = items.value.findIndex((item) => item._id === id)
	focusEndOf(valueInputs.value[index].el)
}

const focusNextOrAddOption = (id) => {
	const index = items.value.findIndex((item) => item._id === id)

	if (index === items.value.length - 1) {
		addOption()
	} else {
		focusLabel(items.value[index + 1]._id)
	}
}
</script>

<template>
	<div class="df-options-list">
		<k-draggable
			class="df-options-list-draggable"
			handle=".df-option-drag"
			:list="items"
			@sort="sort"
		>
			<div
				v-for="item in items"
				:key="item._id"
				:ref="item._id"
				:class="classMod"
				class="df-option"
			>
				<div class="df-option-inner">
					<span class="df-option-icon"></span>
					<component
						:is="useWriter ? 'k-writer-input' : Editable"
						v-bind="writerOptions"
						ref="labelInputs"
						:toolbar="{ inline: true }"
						class="df-option-label"
						tag="div"
						:class="{ 'is-invalid': !item.label }"
						:placeholder="$t('dreamform.common.label.label')"
						:model-value="item.label"
						:value="item.label"
						@input="updateOption(item._id, { label: $event })"
						@update:modelValue="updateOption(item._id, { label: $event })"
						@backspace="removeOption(item._id)"
						@enter="focusValue(item._id)"
					/>
					<editable
						ref="valueInputs"
						tag="code"
						class="df-option-value"
						:class="{ 'is-invalid': !item.value }"
						:placeholder="$t('dreamform.common.options.value.label')"
						:model-value="item.value"
						@update:modelValue="updateOption(item._id, { value: $event })"
						@backspace="focusLabel(item._id)"
						@enter="focusNextOrAddOption(item._id)"
					/>
				</div>
				<button type="button" class="df-option-drag">
					<k-icon type="sort" />
				</button>
				<button
					type="button"
					class="df-option-remove"
					:aria-label="$t('dreamform.common.options.remove')"
					@click="removeOption(item._id)"
				>
					<k-icon type="trash" />
				</button>
			</div>
		</k-draggable>

		<button
			type="button"
			class="df-option df-option-add-button"
			:class="classMod"
			@click="addOption"
		>
			<span class="df-option-icon"></span>
			<span class="df-option-label">{{
				$t("dreamform.common.options.add")
			}}</span>
		</button>
	</div>
</template>

<style>
.df-options-list,
.df-options-list-draggable {
	display: flex;
	flex-direction: column;
	gap: var(--spacing-2);
	max-width: max-content;
}

.df-option {
	width: 100%;

	&,
	.df-option-inner {
		display: flex;
		align-items: start;
	}

	&.df-option-add-button {
		cursor: pointer;
		opacity: 0.3;
		transition: opacity 0.15s;
		margin-top: 0.125rem;
		max-width: max-content;

		&:hover {
			opacity: 0.75;
		}
	}
}

.df-option-label {
	line-height: var(--leading-snug);
	margin-top: -0.125rem;

	&::before,
	.k-text {
		padding: 0 !important;
	}
}

.df-option-value {
	margin-left: var(--spacing-2);
	color: var(--color-text-dimmed);
	background: var(--menu-color-back);
	padding: var(--spacing-1) 0.375rem;
	border-radius: var(--input-rounded);
	font-size: var(--text-xs);
	text-align: right;
	white-space: nowrap;

	&.is-invalid {
		background: var(--color-red);
		color: var(--color-white);
	}
}

.df-option-remove,
.df-option-drag {
	cursor: pointer;
	opacity: 0;
	transition: opacity 0.15s;
	margin-left: var(--spacing-2);
	flex-shrink: 0;

	.df-option-inner:focus-within + &,
	.df-option:hover & {
		opacity: 0.5;
	}

	&:hover,
	&:focus-visible {
		opacity: 1 !important;
	}
}

.df-option-label.is-invalid {
	color: var(--color-red);
}
</style>
