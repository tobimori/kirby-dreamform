<?php

return [
	'dreamform-api-object' => [
		'extends' => 'object',
		'props' => [
			// Unset inherited props
			'fields' => null,

			// reload when the following field changes
			'sync' => function (string $sync = null) {
				return $sync;
			},

			// fetch field setup from the API
			'api' => function (string $api = null) {
				return $api;
			}
		]
	],
];
