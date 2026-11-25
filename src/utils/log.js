import { usePanel } from "kirbyuse"

/**
 * Translates a log message to trusted HTML. The template values are
 * escaped, as log values can contain submitted data. Text without a
 * translation (e.g. exception messages) stays a plain string, which
 * `v-safe-html` and `k-box` escape
 */
export function translateLog(key, template = {}) {
	const panel = usePanel()

	if (panel.translation.data[key] === undefined) {
		return key ?? ""
	}

	return panel.th(key, template)
}
