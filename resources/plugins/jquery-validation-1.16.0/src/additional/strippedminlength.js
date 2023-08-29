// TODO check if value starts with <, otherwise don't try stripping anything
$.validator.addMethod( "strippedminlength", function( value, element, param ) {
	return $( value ).text().length >= param;
}, $.validator.format( "Informe no minímo {0} caracteres" ) );
