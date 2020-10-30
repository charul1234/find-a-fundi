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
		'TIME_FORMAT' => 'h:i A',

		// Form error class		
		'ERROR_FORM_GROUP_CLASS' => 'has-error border-left-danger',
		'DEFAULT_CURRENCY_SYMBOL' => '$',
		'DEFAULT_WEBSERVICE_PAGINATION_ENDLIMIT' => '10',
		'SCREEN_NAME1' => 'screen1',
		'SCREEN_NAME2' => 'screen2',
		'PAYMENT_STATUS_REQUESTED' => 'requested',
		'PAYMENT_STATUS_QUOTED' => 'quoted',
		'PAYMENT_STATUS_ACCEPTED' => 'accepted',
		'PAYMENT_STATUS_COMPLETED' => 'completed',
		'PAYMENT_STATUS_DECLINED' => 'declined',
		'JOB_STATUS_LOST' => 'lost',
		'PAYMENT_STATUS_PENDING' => 'pending',
		'PAYMENT_STATUS_SUCCESS' => 'success',
		'JOB_STATUS_NOT_STARTED' => 'not started',
		'JOB_STATUS_STARTED' => 'started',
		'JOB_STATUS_COMPLETED' => 'completed',

		'DEVICE_TYPE_ANDROID'=>'android',
        'DEVICE_TYPE_IOS'=>'ios',

        'NOTIFICATION_VERIFY_ACCOUNT_SUBJECT'=>'Account Verified',
        'NOTIFICATION_VERIFY_ACCOUNT_MESSAGE'=>'Account is verified. you can login with credential.',
        
        'NOTIFICATION_JOB_QUOTED_SUBJECT'=>'Job Qouted',
        'NOTIFICATION_JOB_QUOTED_MESSAGE'=>'Job Qouted done',

        'NOTIFICATION_NEW_JOB_SUBJECT'=>'New Job',
        'NOTIFICATION_NEW_JOB_MESSAGE'=>'New Job available',

        'NOTIFICATION_AFTER_PAYMENT_SUBJECT'=>'Payment done.',
        'NOTIFICATION_AFTER_PAYMENT_MESSAGE'=>'we got payment from seeker, you can schedule job.',

        'NOTIFICATION_NEW_BOOKING_HOURLY_SUBJECT'=>'New job',
        'NOTIFICATION_NEW_BOOKING_HOURLY_MESSAGE'=>'New hourly job available.',

        'NOTIFICATION_NEW_BOOKING_PACKAGE_SUBJECT'=>'New job',
        'NOTIFICATION_NEW_BOOKING_PACKAGE_MESSAGE'=>'New package job available.',

        'NOTIFICATION_NEW_BOOKING_REQUEST_SUBJECT'=>'New job',
        'NOTIFICATION_NEW_BOOKING_REQUEST_MESSAGE'=>'New request job available.',
        
	];
?>