<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
$projectDir = dirname(__FILE__).'/../../';
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Console Application',

	// preloading 'log' component
	'preload'=>array('log', 'user'),

    'aliases' => array(
        'vendor' => $projectDir.'/vendor',
    ),

	// application components
	'components'=>array(

		// database settings are configured in database.php
		'db'=>require(dirname(__FILE__).'/database.php'),

		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),

	),

    'modules'=>array(
        'user',
    ),

    'commandMap' => array(
        'migrate' => array(
            'class' => 'vendor.yiiext.migrate-command.EMigrateCommand',
            'module' => 'core,user'
        ),
    ),

    'params' => array(
        'composer.callbacks' => array(
            'pre-install' => array('yiic', 'init', 'create'),
            'post-update' => array('yiic', 'migrate'),
            'post-install' => array('yiic', 'migrate'),
        ),
    )
);
