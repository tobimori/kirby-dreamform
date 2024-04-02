<script setup>
/**
 * Editable component
 * we're using a contenteditable to allow inline editing for labels and other
 * text fields in the form builder
 */

import { watch, useApp, usePanel } from "kirbyuse";
import { onMounted, ref } from "kirbyuse";

const props = defineProps({
	tag: String,
	modelValue: String,
	placeholder: String,
	slugify: Boolean,
});

const el = ref();
defineExpose({ el });

const panel = usePanel();
const app = useApp();

// set the initial value on mount
onMounted(() => (el.value.textContent = props.modelValue));

// emit the update to upper component
const emit = defineEmits(["update:modelValue", "backspace", "enter"]);
const handleUpdate = () => {
	// replicate behaviour of kirby slug input
	if (props.slugify && props.modelValue === el.value.textContent.trim()) return;

	// Save the selection range
	const selection = window.getSelection();
	const range = selection.rangeCount > 0 ? selection.getRangeAt(0) : null;
	let cursorPosition = range ? range.startOffset : 0; // Save cursor start position
	const endCursorPosition = range ? range.endOffset : 0; // Save cursor end position

	// Slugify the value
	const value = props.slugify
		? app.$helper.slug(
				el.value.textContent,
				[panel.language.rules ?? panel.$system.slugs, panel.$system.ascii],
				"a-zA-Z0-9_"
		  )
		: el.value.textContent;

	if (value !== el.value.textContent) {
		el.value.textContent = value;

		// Restore the selection range
		if (range) {
			// Create a new range
			const newRange = document.createRange();

			// Adjust cursor position in case the new content is shorter
			cursorPosition = Math.min(cursorPosition, value.length);
			const newCursorPos = Math.min(endCursorPosition, value.length);

			// Set the start and end positions of the range
			newRange.setStart(el.value.firstChild, cursorPosition);
			newRange.setEnd(el.value.firstChild, newCursorPos);

			// Remove any ranges currently selected
			selection.removeAllRanges();
			// Add the new range (which sets the cursor position)
			selection.addRange(newRange);
		}
	}

	emit("update:modelValue", value);
};

// if the model value changes from outside
// update the contenteditable element
watch(
	() => props.modelValue,
	(value) => {
		if (el.value.textContent !== value) el.value.textContent = value;
	}
);

const metaKeyAllowList = [
	"ArrowUp", // move to top
	"ArrowDown", // move to bottom
	"ArrowLeft", // move to start of line
	"ArrowRight", // move to end of line
	"a", // select all
	"c", // copy
	"v", // paste
	"z", // undo
	"x", // cut
	"r", // reload
];

const handleKeyDown = (event) => {
	if (event.key === "Backspace" && !el.value.textContent) {
		event.preventDefault();
		emit("backspace", event);
	}

	if (event.key === "Enter") {
		event.preventDefault();
		emit("enter", event);
	}

	if (
		event.metaKey &&
		!metaKeyAllowList.includes(event.key) // disallow meta key combinations for formatting
	) {
		event.preventDefault();
	}
};

// prevent adding formatting from pasted text
const handlePaste = (event) => {
	event.preventDefault();

	// strip all formatting
	const value = event.clipboardData
		.getData("text/plain")
		.replaceAll("\r\n", " ")
		.replaceAll("\n", " ")
		.replaceAll("\r", " ");

	// insert the stripped text
	document.execCommand("insertText", false, value);
};

const focus = () => el.value.focus();
</script>

<template>
	<component :is="tag" class="df-editable" @click="focus">
		<span
			contenteditable
			@input="handleUpdate"
			@blur="handleUpdate"
			@keydown="handleKeyDown"
			@paste="handlePaste"
			ref="el"
			role="text-box"
		>
		</span>
		<span v-if="!modelValue">{{ placeholder }}</span>
	</component>
</template>

<style lang="scss">
.df-editable {
	position: relative;
	display: inline-block;

	span[contenteditable] + span {
		opacity: 0.65;
		user-select: none;
	}

	span[contenteditable] {
		outline: none;

		&:empty {
			position: absolute;
			min-width: 1px;
		}
	}
}
</style>
