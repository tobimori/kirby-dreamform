export function formatDate(timestamp) {
	const locale = window.panel.user.language;
	const rtf = new Intl.RelativeTimeFormat(locale, { numeric: "auto" });

	const diff = (new Date().getTime() - timestamp * 1000) / 1000;
	const units = [
		{ unit: "year", seconds: 365 * 24 * 60 * 60 },
		{ unit: "month", seconds: 30 * 24 * 60 * 60 },
		{ unit: "day", seconds: 24 * 60 * 60 },
		{ unit: "hour", seconds: 60 * 60 },
		{ unit: "minute", seconds: 60 },
	];

	for (const { unit, seconds } of units) {
		const value = Math.floor(diff / seconds);
		if (value > 0) {
			return rtf.format(0 - value, unit);
		}
	}

	return window.panel.$t("dreamform.justNow");
}
