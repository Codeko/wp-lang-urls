<?php
/*
    Necesitamos que "ar" no sea rtl (Ver tarea #NHH-550)
    porque ya no es árabe, sino argentino.
    Quitamos todos los idiomas de ese fitro retornando un array vacío
*/


function ar_is_argentine_filter()
{
    add_filter( 'wpml_rtl_languages_codes',  'filter_wpml_rtl_languages_codes');
}

function filter_wpml_rtl_languages_codes($array_codes){
    return [];
}



