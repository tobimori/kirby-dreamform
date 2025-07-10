import {
	onMounted,
	onUnmounted,
	ref,
	useApp,
	useContent,
	usePanel,
	watch
} from "kirbyuse"

/**
 * Composable for syncing slug values with auto-generation and uniqueness
 *
 * @param {Object} options
 * @param {string} options.initialValue - Initial slug value
 * @param {string} options.syncField - Field to sync from (e.g., 'label')
 * @param {Object} options.syncSource - Source object to watch for sync field changes
 * @param {Function} options.onUpdate - Callback when slug is updated
 * @returns {Object} { slug, shouldAutoGenerate }
 */
export function useSyncedSlug({
	initialValue,
	syncField,
	syncSource,
	onUpdate
}) {
	const app = useApp()
	const panel = usePanel()
	const { currentContent } = useContent()

	const slug = ref(initialValue || "")
	const shouldAutoGenerate = ref(!initialValue)

	// stop auto-generation on publish
	const handlePublish = () => {
		if (slug.value) {
			shouldAutoGenerate.value = false
		}
	}

	onMounted(() => {
		panel.events.on("content.publish", handlePublish)
	})

	onUnmounted(() => {
		panel.events.off("content.publish", handlePublish)
	})

	// get all existing keys from the current content
	const getExistingKeys = () => {
		const keys = []
		const content = currentContent.value

		if (content?.fields && Array.isArray(content.fields)) {
			// iterate through all field layouts
			content.fields.forEach((field) => {
				if (field.columns && Array.isArray(field.columns)) {
					field.columns.forEach((column) => {
						if (column.blocks && Array.isArray(column.blocks)) {
							column.blocks.forEach((block) => {
								// check if this block has a key field
								const key = block.content?.key
								if (key && key !== slug.value) {
									keys.push(key)
								}
							})
						}
					})
				}
			})
		}

		return keys
	}

	// ensure slug is unique by adding suffix if needed
	const ensureUniqueSlug = (baseSlug) => {
		const existingKeys = getExistingKeys()
		let uniqueSlug = baseSlug
		let counter = 2

		while (existingKeys.includes(uniqueSlug)) {
			uniqueSlug = `${baseSlug}_${counter}`
			counter++
		}

		return uniqueSlug
	}

	// watch for sync field changes
	if (syncField && syncSource) {
		watch(
			() => syncSource[syncField],
			(newValue) => {
				if (shouldAutoGenerate.value && newValue) {
					const baseSlug = app.$helper.slug(newValue)
					const uniqueSlug = ensureUniqueSlug(baseSlug)
					slug.value = uniqueSlug
					onUpdate?.(uniqueSlug)
				}
			},
			{ immediate: true }
		)
	}

	// handle manual input
	const handleManualInput = (value) => {
		// disable auto-generation when user manually edits
		shouldAutoGenerate.value = false
		slug.value = value
		onUpdate?.(value)
	}

	return {
		slug,
		shouldAutoGenerate,
		handleManualInput
	}
}
