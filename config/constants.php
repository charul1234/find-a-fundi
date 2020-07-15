<?php
	return [
		//User Role Ids
		'ROLE_TYPE_SUPERADMIN_ID' => 1,
		'ROLE_TYPE_SEEKER_ID' => 2,
		'ROLE_TYPE_PROVIDER_ID' => 3,

		//Directories Path
		'USERS_UPLOADS_PATH' => '/uploads/users/',
		'PACKAGES_UPLOADS_PATH' => '/uploads/packages/',
		'SETTING_IMAGE_URL' => '/uploads/setting/',

		// Defaults
		'NO_IMAGE_URL' =>'/images/no_image.png',

		// Default Datetiem format
		'DATETIME_FORMAT' => 'd M Y, h:i A',
		'MYSQL_DATETIME_FORMAT' => '%d %b %Y, %h:%i %p',
		'MYSQL_DATE_FORMAT' => '%d %b %Y',
		'DATE_FORMAT' => 'd M Y',

		// Form error class		
		'ERROR_FORM_GROUP_CLASS' => 'has-error border-left-danger',
		'DEFAULT_CURRENCY_SYMBOL' => '$',
		'DEFAULT_WEBSERVICE_PAGINATION_ENDLIMIT' => '10',
	];
?>