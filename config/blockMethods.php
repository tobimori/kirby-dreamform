<?php

use Kirby\Cms\Collection;

return [
	'toFormField' => function (Collection $fields) {
		return $fields->findByKey($this->id());
	}
];
