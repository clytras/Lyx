<?php 

return [
	'fonts' => [
		'FontAwesome' => 'font-awesome/font-awesome.css',
		'IconicFill' => 'iconic_fill/iconic_fill.css'
	],
	'depends' => [
		'js' => [
			'lyx' => [
				'web/Application.js' => [
					'utils/Object.js'
				]
			]
		]
	]
];