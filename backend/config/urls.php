<?php
return [
    '' => 'main/index',
	'ingresar'=> 'main/ingresar',
	'recuperar'=> 'main/recuperar-clave',
	'salir'=> 'main/salir',
	
	'mi-cuenta'=> 'usuarios/mi-cuenta',
	'cambiar-clave'=> 'usuarios/cambiar-clave',
	
	// PARA EL MODULO DE NOTIFICACIONES
	'notificaciones'=> 'notificaciones/index',
	'notificaciones/listado-ajax' => 'notificaciones/listado-ajax',
	'notificaciones/agregar'=> 'notificaciones/agregar',
	'notificaciones/editar/<id:\d+>'=> 'notificaciones/editar',
	'notificaciones/eliminar-ajax'=> 'notificaciones/eliminar-ajax',
	
	// PARA EL MODULO DE NOTIFICACIONES EMAILS
	'notificacionesemails'=> 'notificaciones-emails/index',
	'notificacionesemails/listado-ajax' => 'notificaciones-emails/listado-ajax',
	'notificacionesemails/agregar'=> 'notificaciones-emails/agregar',
	'notificacionesemails/editar/<id:\d+>'=> 'notificaciones-emails/editar',
	'notificacionesemails/eliminar-ajax'=> 'notificaciones-emails/eliminar-ajax',
	'notificacionesemails/generador-emails'=> 'notificaciones-emails/generador-emails',
	'notificacionesemails/enviar-emails'=> 'notificaciones-emails/enviar-emails',
	
	// PARA EL MODULO DE USUARIOS
	'usuarios'=> 'usuarios/index',
	'usuarios/listado-ajax' => 'usuarios/listado-ajax',
	'usuarios/agregar'=> 'usuarios/agregar',
	'usuarios/editar/<id:\d+>'=> 'usuarios/editar',
	'usuarios/eliminar-ajax'=> 'usuarios/eliminar-ajax',
	
	// PARA EL MODULO DE PARAMETROS
	'parametros'=> 'parametros/index',
	'parametros/listado-ajax' => 'parametros/listado-ajax',
	'parametros/agregar'=> 'parametros/agregar',
	'parametros/editar/<id:\d+>'=> 'parametros/editar',
	'parametros/eliminar-ajax'=> 'parametros/eliminar-ajax',
	
	
	/*****************************/
	/** PARA LA APLICACION WEB ***/
	/*****************************/
	
	// PARA EL MODULO DE ESTADOS
	'web/estados'=> 'web/estados/index',
	'web/estados/listado-ajax' => 'web/estados/listado-ajax',
	'web/estados/agregar'=> 'web/estados/agregar',
	'web/estados/agregar-procesar'=> 'web/estados/agregar-procesar',
	'web/estados/editar/<id:\d+>'=> 'web/estados/editar',
	'web/estados/eliminar-ajax'=> 'web/estados/eliminar-ajax',
	
	// PARA EL MODULO DE ACCIONES
	'web/acciones'=> 'web/acciones/index',
	'web/acciones/listado-ajax' => 'web/acciones/listado-ajax',
	'web/acciones/agregar'=> 'web/acciones/agregar',
	'web/acciones/agregar-procesar'=> 'web/acciones/agregar-procesar',
	'web/acciones/editar/<id:\d+>'=> 'web/acciones/editar',
	'web/acciones/eliminar-ajax'=> 'web/acciones/eliminar-ajax',
	'web/acciones/agregar-ajax'=> 'web/acciones/agregar-ajax',
	'web/acciones/editar-ajax'=> 'web/acciones/editar-ajax',
	
	// PARA EL MODULO DE MENSAJES
	'web/mensajes'=> 'web/mensajes/index',
	'web/mensajes/listado-ajax' => 'web/mensajes/listado-ajax',
	'web/mensajes/agregar'=> 'web/mensajes/agregar',
	'web/mensajes/agregar-procesar'=> 'web/mensajes/agregar-procesar',
	'web/mensajes/editar/<id:\d+>'=> 'web/mensajes/editar',
	'web/mensajes/eliminar-ajax'=> 'web/mensajes/eliminar-ajax',
    
    // PARA EL MODULO DE ESTADOS MENSAJES
    'web/estadosmensajes'=> 'web/estados-mensajes/index',
    'web/estadosmensajes/listado-ajax' => 'web/estados-mensajes/listado-ajax',
    'web/estadosmensajes/agregar'=> 'web/estados-mensajes/agregar',
    'web/estadosmensajes/agregar-procesar'=> 'web/estados-mensajes/agregar-procesar',
    'web/estadosmensajes/editar/<id:\d+>'=> 'web/estados-mensajes/editar',
    'web/estadosmensajes/eliminar-ajax'=> 'web/estados-mensajes/eliminar-ajax',
    'web/estadosmensajes/agregar-ajax'=> 'web/estados-mensajes/agregar-ajax',
    'web/estadosmensajes/editar-ajax'=> 'web/estados-mensajes/editar-ajax',
    
	// PARA EL MODULO DE MEDICOS
    'web/medicos'=> 'web/medicos/index',
    'web/medicos/listado-ajax' => 'web/medicos/listado-ajax',
    'web/medicos/agregar'=> 'web/medicos/agregar',
    'web/medicos/agregar-procesar'=> 'web/medicos/agregar-procesar',
    'web/medicos/editar/<id:\d+>'=> 'web/medicos/editar',
    'web/medicos/eliminar-ajax'=> 'web/medicos/eliminar-ajax',
    
    // PARA EL MODULO DE HISTORIALES
    'web/historiales'=> 'web/historiales/index',
    'web/historiales/listado-ajax' => 'web/historiales/listado-ajax',
    'web/historiales/agregar'=> 'web/historiales/agregar',
    'web/historiales/agregar-procesar'=> 'web/historiales/agregar-procesar',
    'web/historiales/editar/<id:\d+>'=> 'web/historiales/editar',
    'web/historiales/visualizar/<id:\d+>'=> 'web/historiales/visualizar',
    'web/historiales/eliminar-ajax'=> 'web/historiales/eliminar-ajax',
	
    // PARA EL MODULO DE TRADUCCIONES TURCO
    'web/traduccionesturco'=> 'web/traducciones-turco/index',
    'web/traduccionesturco/listado-ajax' => 'web/traducciones-turco/listado-ajax',
    'web/traduccionesturco/agregar'=> 'web/traducciones-turco/agregar',
    'web/traduccionesturco/agregar-procesar'=> 'web/traducciones-turco/agregar-procesar',
    'web/traduccionesturco/editar/<id:\d+>'=> 'web/traducciones-turco/editar',
    'web/traduccionesturco/eliminar-ajax'=> 'web/traducciones-turco/eliminar-ajax',
    
    // PARA EL MODULO DE LIBROS
    'web/libros'=> 'web/libros/index',
    'web/libros/listado-ajax' => 'web/libros/listado-ajax',
    'web/libros/agregar'=> 'web/libros/agregar',
    'web/libros/agregar-procesar'=> 'web/libros/agregar-procesar',
    'web/libros/editar/<id:\d+>'=> 'web/libros/editar',
    'web/libros/eliminar-ajax'=> 'web/libros/eliminar-ajax',
	
];
