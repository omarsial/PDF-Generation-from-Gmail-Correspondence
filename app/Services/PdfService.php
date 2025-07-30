<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    public function generateLargePdf($content, $filename)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($content);
        $dompdf->setPaper('A4', 'portrait');
        
        $dompdf->render();
        
        file_put_contents($filename, $dompdf->output());
        
        return $filename;
    }
}