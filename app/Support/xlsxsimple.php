<?php

namespace App\Support;

use ZipArchive;

/**
 * Generador mínimo de archivos .xlsx (sin dependencias externas; requiere la extensión PHP "zip").
 * Soporta varias hojas, estilos básicos, anchos de columna y fila de encabezado fija.
 */
class XlsxSimple
{
    // Estilos disponibles (índices de cellXfs en styles.xml)
    public const S_HEADER = 1;  // encabezado: blanco, negrita, fondo oscuro
    public const S_BOLD   = 2;  // negrita
    public const S_MONEY  = 3;  // $#,##0.00
    public const S_TITLE  = 4;  // título grande
    public const S_MONEYB = 5;  // $#,##0.00 en negrita
    public const S_INT    = 6;  // entero
    public const S_PCT    = 7;  // 0.0"%"
    public const S_NOTE   = 8;  // nota gris cursiva

    private array $sheets = [];

    /** @param array $rows  filas; cada celda es un escalar o ['v' => valor, 's' => estilo] */
    public function sheet(string $name, array $rows, array $widths = [], bool $freezeFirstRow = false): self
    {
        $name = preg_replace('/[\[\]\:\*\?\/\\\\]/', '', $name);
        $this->sheets[] = [
            'name'   => mb_substr($name === '' ? 'Hoja' : $name, 0, 31),
            'rows'   => $rows,
            'widths' => $widths,
            'freeze' => $freezeFirstRow,
        ];
        return $this;
    }

    public static function money($n): array { return ['v' => (float) $n, 's' => self::S_MONEY]; }
    public static function moneyB($n): array { return ['v' => (float) $n, 's' => self::S_MONEYB]; }
    public static function int($n): array { return ['v' => (int) $n, 's' => self::S_INT]; }
    public static function pct($n): array { return ['v' => (float) $n, 's' => self::S_PCT]; }
    public static function head(string $t): array { return ['v' => $t, 's' => self::S_HEADER]; }
    public static function bold($t): array { return ['v' => $t, 's' => self::S_BOLD]; }
    public static function title(string $t): array { return ['v' => $t, 's' => self::S_TITLE]; }
    public static function note(string $t): array { return ['v' => $t, 's' => self::S_NOTE]; }

    /** Escribe el archivo en $path. */
    public function save(string $path): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('La extensión PHP "zip" no está habilitada (activa extension=zip en php.ini) para generar archivos Excel.');
        }
        if (empty($this->sheets)) {
            $this->sheet('Hoja1', [['']]);
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo Excel temporal.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());
        foreach ($this->sheets as $i => $s) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $this->sheetXml($s));
        }
        $zip->close();
    }

    // ------------------------------------------------------------------ XML

    private const XML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

    private function esc($v): string
    {
        $v = (string) $v;
        // quita caracteres de control que XML no admite
        $v = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $v) ?? '';
        return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function colName(int $i): string
    {
        $n = '';
        for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
            $n = chr(65 + ($i - 1) % 26) . $n;
        }
        return $n;
    }

    private function contentTypes(): string
    {
        $o = self::XML . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        foreach ($this->sheets as $i => $_) {
            $o .= '<Override PartName="/xl/worksheets/sheet' . ($i + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        return $o . '</Types>';
    }

    private function rootRels(): string
    {
        return self::XML . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbook(): string
    {
        $o = self::XML . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        foreach ($this->sheets as $i => $s) {
            $o .= '<sheet name="' . $this->esc($s['name']) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }
        return $o . '</sheets></workbook>';
    }

    private function workbookRels(): string
    {
        $o = self::XML . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        foreach ($this->sheets as $i => $_) {
            $o .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
        }
        $o .= '<Relationship Id="rId' . (count($this->sheets) + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        return $o . '</Relationships>';
    }

    private function styles(): string
    {
        return self::XML . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="2">'
            .   '<numFmt numFmtId="164" formatCode="&quot;$&quot;#,##0.00"/>'
            .   '<numFmt numFmtId="165" formatCode="0.0&quot;%&quot;"/>'
            . '</numFmts>'
            . '<fonts count="5">'
            .   '<font><sz val="11"/><name val="Calibri"/></font>'
            .   '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            .   '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .   '<font><b/><sz val="14"/><name val="Calibri"/></font>'
            .   '<font><i/><sz val="10"/><color rgb="FF64748B"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            .   '<fill><patternFill patternType="none"/></fill>'
            .   '<fill><patternFill patternType="gray125"/></fill>'
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FF0F172A"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="9">'
            .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .   '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            .   '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .   '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            .   '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .   '<xf numFmtId="164" fontId="1" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1"/>'
            .   '<xf numFmtId="1" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            .   '<xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            .   '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function sheetXml(array $s): string
    {
        $o = self::XML . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        if ($s['freeze']) {
            $o .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
        }

        if (!empty($s['widths'])) {
            $o .= '<cols>';
            foreach ($s['widths'] as $i => $w) {
                $o .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . (float) $w . '" customWidth="1"/>';
            }
            $o .= '</cols>';
        }

        $o .= '<sheetData>';
        foreach ($s['rows'] as $r => $row) {
            $o .= '<row r="' . ($r + 1) . '">';
            foreach (array_values($row) as $c => $cell) {
                $style = 0;
                $val = $cell;
                if (is_array($cell)) {
                    $val = $cell['v'] ?? '';
                    $style = (int) ($cell['s'] ?? 0);
                }
                if ($val === null || $val === '') {
                    continue; // celda vacía: no se escribe
                }
                $ref = $this->colName($c) . ($r + 1);
                $sAttr = $style ? ' s="' . $style . '"' : '';
                if (is_int($val) || is_float($val)) {
                    $o .= '<c r="' . $ref . '"' . $sAttr . '><v>' . $val . '</v></c>';
                } else {
                    $o .= '<c r="' . $ref . '"' . $sAttr . ' t="inlineStr"><is><t xml:space="preserve">' . $this->esc($val) . '</t></is></c>';
                }
            }
            $o .= '</row>';
        }
        return $o . '</sheetData></worksheet>';
    }
}