<?php
    spl_autoload_register(function($class){
        if($class == 'tFPDF')
            require_once 'Monitoring.Common'.DIRECTORY_SEPARATOR.
                'API'.DIRECTORY_SEPARATOR.
                'Libraries'.DIRECTORY_SEPARATOR.
                'FPDF-v1.81'.DIRECTORY_SEPARATOR.
                'tfpdf.php';
        else
        {
            $path = preg_replace('/^Common/', 'Monitoring.Common',
                preg_replace('/^API/', 'Monitoring.API',
                    str_replace('\\', DIRECTORY_SEPARATOR, $class)));

            require_once DIRECTORY_SEPARATOR . $path . '.php';
        }
    });