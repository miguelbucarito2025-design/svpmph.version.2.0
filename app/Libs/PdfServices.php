<?php

declare(sytict_types=1);

namespace DevCore\Services;

use Dompdf\Dompdf; // Traído automáticamente por Composer

class PdfService
{
    public function generate(string $html)
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("documento.pdf");
    }
}
