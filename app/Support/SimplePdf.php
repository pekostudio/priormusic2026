<?php

namespace App\Support;

class SimplePdf
{
    /**
     * @param  list<string>  $lines
     */
    public static function make(array $lines): string
    {
        $pages = array_chunk($lines, 44);
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
        ];
        $pageObjectIds = [];
        $contentObjectIds = [];

        foreach ($pages as $pageIndex => $pageLines) {
            $pageObjectIds[] = 3 + ($pageIndex * 3);
            $contentObjectIds[] = 5 + ($pageIndex * 3);
        }

        $objects[] = '<< /Type /Pages /Kids ['.implode(' ', array_map(fn (int $id): string => "{$id} 0 R", $pageObjectIds)).'] /Count '.count($pages).' >>';

        foreach ($pages as $pageIndex => $pageLines) {
            $pageObjectId = $pageObjectIds[$pageIndex];
            $fontObjectId = $pageObjectId + 1;
            $contentObjectId = $contentObjectIds[$pageIndex];
            $stream = self::stream($pageLines);

            $objects[$pageObjectId - 1] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 {$fontObjectId} 0 R >> >> /Contents {$contentObjectId} 0 R >>";
            $objects[$fontObjectId - 1] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>';
            $objects[$contentObjectId - 1] = "<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream";
        }

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[$index + 1] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($index = 1; $index <= count($objects); $index++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$index]);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF\n";
    }

    /**
     * @param  list<string>  $lines
     */
    private static function stream(array $lines): string
    {
        $content = "BT\n/F1 10 Tf\n50 750 Td\n14 TL\n";

        foreach ($lines as $line) {
            $content .= '('.self::escape(mb_strimwidth($line, 0, 110, '...')).") Tj\nT*\n";
        }

        return $content.'ET';
    }

    private static function escape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
