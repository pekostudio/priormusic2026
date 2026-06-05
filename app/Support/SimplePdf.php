<?php

namespace App\Support;

class SimplePdf
{
    private const float PageWidth = 842;

    private const float PageHeight = 595;

    private const float Margin = 22;

    /**
     * @param  list<array<string, string|int>>  $rows
     */
    public static function makeLithuanianUsageReport(string $period, array $rows, string $providedBy): string
    {
        $columns = [
            ['label' => 'Eil. Nr.', 'width' => 26, 'key' => 'number'],
            ['label' => "Fonogramos\n(muzikinio kurinio)\npavadinimas", 'width' => 135, 'key' => 'title'],
            ['label' => "solistas (-ai),\nvokalistas (-ai)", 'width' => 48, 'key' => 'soloists'],
            ['label' => "instrumentalistas (-ai),\npritariantysis vokalistas (-ai)", 'width' => 105, 'key' => 'performers'],
            ['label' => "Muzikos\nautorius (-iai)", 'width' => 105, 'key' => 'music_authors'],
            ['label' => "Teksto\nautorius (-iai)", 'width' => 48, 'key' => 'text_authors'],
            ['label' => "Albumo pavadinimas\n(is kokio CD, DVD ar kt.\npanaudotas kurinys)", 'width' => 80, 'key' => 'album'],
            ['label' => "Fonogramos gamintojo\npavadinimas\n(leiblas)", 'width' => 90, 'key' => 'label'],
            ['label' => 'min', 'width' => 36, 'key' => 'minutes'],
            ['label' => 'sek', 'width' => 36, 'key' => 'seconds'],
            ['label' => "Laidos\ntransliavimu\n(kartojimu)\nskaicius", 'width' => 46, 'key' => 'broadcast_count'],
            ['label' => "Fonogramos\npanaudojimo budas:\nkoncertas,\nfonine muzika,\nvinjete ar\nskirtukas", 'width' => 43, 'key' => 'usage_type'],
        ];

        $rowChunks = array_chunk($rows, 20);
        $rowChunks = $rowChunks === [] ? [[]] : $rowChunks;
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
        ];
        $pageObjectIds = [];
        $contentObjectIds = [];

        foreach ($rowChunks as $pageIndex => $pageRows) {
            $pageObjectIds[] = 3 + ($pageIndex * 3);
            $contentObjectIds[] = 5 + ($pageIndex * 3);
        }

        $objects[] = '<< /Type /Pages /Kids ['.implode(' ', array_map(fn (int $id): string => "{$id} 0 R", $pageObjectIds)).'] /Count '.count($rowChunks).' >>';

        foreach ($rowChunks as $pageIndex => $pageRows) {
            $pageObjectId = $pageObjectIds[$pageIndex];
            $fontObjectId = $pageObjectId + 1;
            $contentObjectId = $contentObjectIds[$pageIndex];
            $stream = self::tableStream($period, $pageRows, $columns, $providedBy, $pageIndex + 1, count($rowChunks));

            $objects[$pageObjectId - 1] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.self::PageWidth.' '.self::PageHeight."] /Resources << /Font << /F1 {$fontObjectId} 0 R >> >> /Contents {$contentObjectId} 0 R >>";
            $objects[$fontObjectId - 1] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
            $objects[$contentObjectId - 1] = '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream";
        }

        return self::document($objects);
    }

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
            $objects[$contentObjectId - 1] = '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream";
        }

        return self::document($objects);
    }

    /**
     * @param  array<int, string>  $objects
     */
    private static function document(array $objects): string
    {
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
     * @param  list<array<string, string|int>>  $rows
     * @param  list<array{label: string, width: int, key: string}>  $columns
     */
    private static function tableStream(string $period, array $rows, array $columns, string $providedBy, int $page, int $pages): string
    {
        $content = '';
        $x = self::Margin;
        $y = self::PageHeight - 18;

        $content .= self::text('FONOGRAMU AR JU KOPIJU PANAUDOJIMO ATASKAITA', self::PageWidth / 2, $y, 11, 'center', true);
        $content .= self::text("uz {$period}", self::PageWidth / 2, $y - 15, 10, 'center', true);
        $content .= self::text('PASTABA: Butina uzpildyti visus lenteles laukus!', $x, $y - 31, 8, 'left', true, '1 0 0');

        $tableTop = $y - 36;
        $headerHeight = 66;
        $rowHeight = 18;
        $tableWidth = array_sum(array_column($columns, 'width'));

        $content .= self::filledRect($x, $tableTop - $headerHeight, $tableWidth, $headerHeight, '0.75 0.75 0.75');
        $cursorX = $x;

        foreach ($columns as $column) {
            $content .= self::cell($cursorX, $tableTop - $headerHeight, $column['width'], $headerHeight);
            $content .= self::wrappedText($column['label'], $cursorX + 2, $tableTop - 13, $column['width'] - 4, 6.7, 7.3, 'center', true);
            $cursorX += $column['width'];
        }

        $cursorY = $tableTop - $headerHeight;

        foreach ($rows as $row) {
            $cursorX = $x;
            $cursorY -= $rowHeight;

            foreach ($columns as $column) {
                $value = (string) ($row[$column['key']] ?? '');
                $content .= self::cell($cursorX, $cursorY, $column['width'], $rowHeight);
                $content .= self::wrappedText($value, $cursorX + 2, $cursorY + $rowHeight - 7, $column['width'] - 4, 7.1, 7.2, 'center');
                $cursorX += $column['width'];
            }
        }

        while (count($rows) < 20) {
            $cursorX = $x;
            $cursorY -= $rowHeight;

            foreach ($columns as $column) {
                $content .= self::cell($cursorX, $cursorY, $column['width'], $rowHeight);
                $cursorX += $column['width'];
            }

            $rows[] = [];
        }

        $content .= self::filledRect($x + 26, 13, 135, 14, '0.75 0.75 0.75');
        $content .= self::cell($x + 26, 13, 135, 14);
        $content .= self::cell($x + 161, 13, 170, 14);
        $content .= self::text('Informacija pateike:', $x + 93.5, 18, 8, 'center', true);
        $content .= self::text($providedBy, $x + 246, 18, 8, 'center', true);
        $content .= self::text("{$page}/{$pages}", self::PageWidth - self::Margin, 12, 7, 'right');

        return $content;
    }

    private static function cell(float $x, float $y, float $width, float $height): string
    {
        return sprintf("%.2F %.2F %.2F %.2F re S\n", $x, $y, $width, $height);
    }

    private static function filledRect(float $x, float $y, float $width, float $height, string $rgb): string
    {
        return "q {$rgb} rg ".sprintf('%.2F %.2F %.2F %.2F re f', $x, $y, $width, $height)."\nQ\n";
    }

    private static function wrappedText(string $value, float $x, float $y, float $width, float $fontSize, float $lineHeight, string $align = 'left', bool $bold = false): string
    {
        $maxChars = max(3, (int) floor($width / ($fontSize * 0.48)));
        $lines = explode("\n", wordwrap($value, $maxChars, "\n", true));
        $content = '';

        foreach (array_slice($lines, 0, 7) as $index => $line) {
            $content .= self::text($line, $x + ($align === 'center' ? $width / 2 : 0), $y - ($index * $lineHeight), $fontSize, $align, $bold);
        }

        return $content;
    }

    private static function text(string $value, float $x, float $y, float $size, string $align = 'left', bool $bold = false, string $rgb = '0 0 0'): string
    {
        $value = self::normalizeText($value);
        $adjustedX = match ($align) {
            'center' => $x - (mb_strlen($value) * $size * 0.24),
            'right' => $x - (mb_strlen($value) * $size * 0.48),
            default => $x,
        };

        $renderingMode = $bold ? '2 Tr 0.3 w' : '0 Tr';

        return "BT\n{$rgb} rg\n/F1 {$size} Tf\n{$renderingMode}\n".sprintf('%.2F %.2F Td', $adjustedX, $y)."\n(".self::escape($value).") Tj\nET\n";
    }

    private static function normalizeText(string $value): string
    {
        $value = strtr($value, [
            'Ą' => 'A',
            'Č' => 'C',
            'Ę' => 'E',
            'Ė' => 'E',
            'Į' => 'I',
            'Š' => 'S',
            'Ų' => 'U',
            'Ū' => 'U',
            'Ž' => 'Z',
            'ą' => 'a',
            'č' => 'c',
            'ę' => 'e',
            'ė' => 'e',
            'į' => 'i',
            'š' => 's',
            'ų' => 'u',
            'ū' => 'u',
            'ž' => 'z',
        ]);

        return mb_convert_encoding($value, 'Windows-1252', 'UTF-8');
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
