function obtenerFechaDesdeTexto(texto, tipo){
	if(tipo == 'completa'){
		var reggie = /(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/;		
		var dateArray = reggie.exec(texto); 
		var fecha_texto_formato = '';
		
		var fecha_obj = new Date(
			(+dateArray[1]),
			(+dateArray[2])-1, // Careful, month starts at 0!
			(+dateArray[3]),
			(+dateArray[4]),
			(+dateArray[5]),
			(+dateArray[6])
		);
		
		fecha_texto_formato += (fecha_obj.getDate() > 9) ? fecha_obj.getDate() : '0' + fecha_obj.getDate();
		fecha_texto_formato += '/' + (((fecha_obj.getMonth() + 1) > 9) ? (fecha_obj.getMonth() + 1) : '0' + (fecha_obj.getMonth() + 1));
		fecha_texto_formato += '/' + fecha_obj.getFullYear();
		
		fecha_texto_formato += ' ' + ((fecha_obj.getHours() > 9) ? fecha_obj.getHours() : '0' + fecha_obj.getHours());
		fecha_texto_formato += ':' + ((fecha_obj.getMinutes() > 9) ? fecha_obj.getMinutes() : '0' + fecha_obj.getMinutes());
		fecha_texto_formato += ':' + ((fecha_obj.getSeconds() > 9) ? fecha_obj.getSeconds() : '0' + fecha_obj.getSeconds());
		
		return fecha_texto_formato;
	}
	
	if(tipo == 'simple'){
		var reggie = /(\d{4})-(\d{2})-(\d{2})/;		
		var dateArray = reggie.exec(texto); 
		var fecha_texto_formato = '';
		
		var fecha_obj = new Date(
			(+dateArray[1]),
			(+dateArray[2])-1, // Careful, month starts at 0!
			(+dateArray[3])
		);
		
		fecha_texto_formato += (fecha_obj.getDate() > 9) ? fecha_obj.getDate() : '0' + fecha_obj.getDate();
		fecha_texto_formato += '/' + (((fecha_obj.getMonth() + 1) > 9) ? (fecha_obj.getMonth() + 1) : '0' + (fecha_obj.getMonth() + 1));
		fecha_texto_formato += '/' + fecha_obj.getFullYear();
		
		return fecha_texto_formato;
	}
}