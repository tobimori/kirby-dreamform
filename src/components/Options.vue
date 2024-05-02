<script setup>
import { ref, onMounted, watch, useApp, nextTick } from "kirbyuse";
import Editable from "@/components/Editable.vue";

const app = useApp();

const props = defineProps({
	classMod: String,
	options: Array,
});

// convert options to items, handle vue rendering
const items = ref([]);
const setItems = (options) => {
	items.value = options.map((option) => ({
		_id: app.$helper.uuid(),
		...option,
	}));
};

onMounted(() => setItems(props.options));
watch(
	() => props.options,
	(options) => setItems(options)
);

// handle updating
const emit = defineEmits(["update"]);
const update = (value) => emit("update", value);
const sort = () => update(items.value);

const updateOption = (id, value) => {
	const index = items.value.findIndex((item) => item._id === id);
	items.value[index] = { ...items.value[index], ...value };
	update(items.value);
};

const addOption = () => {
	update([...items.value, { value: "", label: "" }]);
	nextTick(() => focusLabel(items.value[items.value.length - 1]._id));
};

const removeOption = (id) => {
	update(items.value.filter((item) => item._id !== id));
	nextTick(() => {
		if (items.value.length) {
			focusValue(items.value[items.value.length - 1]._id);
		}
	});
};

// handle focus
const labelInputs = ref([]);
const valueInputs = ref([]);

const focusEndOf = (el) => {
	el.focus();
	const range = document.createRange();
	const selection = window.getSelection();
	range.setStart(el, el.childNodes.length);
	range.collapse(true);
	selection.removeAllRanges();
	selection.addRange(range);
};

const focusLabel = (id) => {
	const index = items.value.findIndex((item) => item._id === id);
	focusEndOf(labelInputs.value[index].el);
};

const focusValue = (id) => {
	const index = items.value.findIndex((item) => item._id === id);
	focusEndOf(valueInputs.value[index].el);
};

const focusNextOrAddOption = (id) => {
	const index = items.value.findIndex((item) => item._id === id);

	if (index === items.value.length - 1) {
		addOption();
	} else {
		focusLabel(items.value[index + 1]._id);
	}
};
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
				:class="classMod"
				class="df-option"
				:ref="item._id"
			>
				<div class="df-option-inner">
					<span class="df-option-icon"></span>
					<editable
						tag="div"
						class="df-option-label"
						:class="{ 'is-invalid': !item.label }"
						:placeholder="$t('dreamform.common.label.label')"
						:modelValue="item.label"
						@update:modelValue="updateOption(item._id, { label: $event })"
						@backspace="removeOption(item._id)"
						@enter="focusValue(item._id)"
						ref="labelInputs"
					/>
					<editable
						tag="code"
						class="df-option-value"
						:class="{ 'is-invalid': !item.value }"
						:placeholder="$t('dreamform.common.options.value.label')"
						:modelValue="item.value"
						@update:modelValue="updateOption(item._id, { value: $event })"
						@backspace="focusLabel(item._id)"
						@enter="focusNextOrAddOption(item._id)"
						ref="valueInputs"
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

<style lang="scss">
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
	&-inner {
		display: flex;
		align-items: center;
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

.df-option-value {
	margin-left: var(--spacing-2);
	color: var(--color-gray-700);
	background: var(--color-gray-200);
	padding: var(--spacing-1) 0.375rem;
	border-radius: var(--input-rounded);
	font-size: var(--text-xs);
	text-align: right;

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
