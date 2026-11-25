<script setup>
import Editable from "@/components/editable.vue"
import { useHelpers } from "kirbyuse"
import { nextTick, onMounted, ref, watch } from "vue"

const helpers = useHelpers()

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
		_id: helpers.uuid(),
		...option
	}))
}

// the input to focus after the next options update, as the parent
// can apply the update after the next tick
let pendingFocus = null

onMounted(() => setItems(props.options))
watch(
	() => props.options,
	(options) => {
		setItems(options)

		const focus = pendingFocus
		pendingFocus = null
		const last = items.value.at(-1)
		if (focus && last) {
			nextTick(() => focusInput(focus[last._id]))
		}
	}
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
	pendingFocus = labelInputs
	update([...items.value, { value: "", label: "" }])
}

const removeOption = (id) => {
	pendingFocus = valueInputs
	update(items.value.filter((item) => item._id !== id))
}

// handle focus, refs are keyed by item id as Vue does not keep
// the order of template ref arrays in v-for
const labelInputs = {}
const valueInputs = {}

const focusEndOf = (el) => {
	if (!el) return

	el.focus()
	const range = document.createRange()
	const selection = window.getSelection()
	range.setStart(el, el.childNodes.length)
	range.collapse(true)
	selection.removeAllRanges()
	selection.addRange(range)
}

// editable inputs expose their element, writer inputs a focus method
const focusInput = (input) =>
	input?.el ? focusEndOf(input.el) : input?.focus?.()
const focusLabel = (id) => focusInput(labelInputs[id])
const focusValue = (id) => focusInput(valueInputs[id])

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
						:ref="(input) => (labelInputs[item._id] = input)"
						:toolbar="{ inline: true }"
						class="df-option-label"
						tag="div"
						:class="{ 'is-invalid': !item.label }"
						:placeholder="$t('dreamform.common.label.label')"
						:model-value="item.label"
						:value="item.label"
						@input="updateOption(item._id, { label: $event })"
						@update:model-value="updateOption(item._id, { label: $event })"
						@backspace="removeOption(item._id)"
						@enter="focusValue(item._id)"
					/>
					<editable
						:ref="(input) => (valueInputs[item._id] = input)"
						tag="code"
						class="df-option-value"
						:class="{ 'is-invalid': !item.value }"
						:placeholder="$t('dreamform.common.options.value.label')"
						:model-value="item.value"
						@update:model-value="updateOption(item._id, { value: $event })"
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
		background: var(--color-red-600);
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
	color: var(--color-red-600);
}
</style>
