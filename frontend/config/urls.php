<?php
return [
	// RUTAS PRINCIPALES DEL SITIO
	'' => 'main/index',	
	
	# PARA LAS RUTAS DEL CHAT
	'api/v1/init' => 'api-v1/init',
	'api/v1/send-message' => 'api-v1/send-message',
//	'api/v1/download-file/<id:([A-Za-z0-9-\/]+)>' => 'api-v1/download-file',
	'api/v1/get-keywords-elastic-search' => 'api-v1/get-keywords-elastic-search',
    'api/v1/get-documents-elastic-search' => 'api-v1/get-documents-elastic-search',
    'api/v1/user-inactive' => 'api-v1/user-inactive',
    'api/v1/user-goodbye' => 'api-v1/user-goodbye',
    
    # PARA LAS RUTAS DE LA CUENTA
    'api/v1/login' => 'api-v1/login',
    'api/v1/forgot-password' => 'api-v1/forgot-password',
    'api/v1/register-user' => 'api-v1/register-user',
    'api/v1/edit-user' => 'api-v1/edit-user',
    'api/v1/get-info-user' => 'api-v1/get-info-user',
];
