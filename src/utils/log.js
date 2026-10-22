import { useApp, usePanel } from "kirbyuse"

/**
 * Translates a log message to HTML. Kirby inserts template values without
 * escaping them, and log values can contain submitted data. Text without a
 * translation (e.g. exception messages) is escaped as plain text
 */
export function translateLog(key, template = {}) {
	const { $esc } = useApp()

	const escape = (value) => {
		if (typeof value === "string") return $esc(value)
		if (value && typeof value === "object") {
			return Object.fromEntries(
				Object.entries(value).map(([k, v]) => [k, escape(v)])
			)
		}
		return value
	}

	return usePanel().t(key, escape(template)) ?? $esc(key ?? "")
}
