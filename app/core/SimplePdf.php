<?php

class SimplePdf
{
    private const PAGE_W = 595.28;
    private const PAGE_H = 841.89;
    private const MARGIN = 48.0;

    /** @var list<string> */
    private array $pages = [];
    /** @var list<array{w:int,h:int,data:string}> */
    private array $images = [];
    private int $page = -1;
    private float $y = 0.0;
    private float $headerHeight = 96.0;

    public function __construct()
    {
        $this->addPage();
    }

    public function addPage(): void
    {
        $this->pages[] = '';
        $this->page = count($this->pages) - 1;
        $this->y = self::PAGE_H - 56;
    }

    /**
     * @param array{
     *   logo?:string,
     *   data?:string,
     *   cliente?:string,
     *   projeto?:string,
     *   status?:string,
     *   valor?:string,
     *   horas?:string,
     *   inicio?:string,
     *   final?:string,
     *   resumo?:string,
     *   contato?:string
     * } $info
     */
    public function composeProposal(array $info): void
    {
        $this->drawLetterhead((string) ($info['logo'] ?? ''));

        $this->y = self::PAGE_H - $this->headerHeight - 28;
        $this->writeTextAt(self::MARGIN, $this->y, 'PROPOSTA COMERCIAL', 14, true, 0.05, 0.05, 0.08);
        $data = (string) ($info['data'] ?? date('d/m/Y'));
        $this->writeTextAt(self::PAGE_W - self::MARGIN - 92, $this->y, 'Data: ' . $data, 10, false, 0.35, 0.35, 0.4);
        $this->y -= 10;
        $this->accentRule();
        $this->y -= 18;

        $col = (self::PAGE_W - (2 * self::MARGIN) - 18) / 2;
        $this->field(self::MARGIN, 'Nome do cliente', (string) ($info['cliente'] ?? '—'), $col);
        $this->field(self::MARGIN + $col + 18, 'Título do projeto', (string) ($info['projeto'] ?? '—'), $col);
        $this->y -= 36;
        $this->field(self::MARGIN, 'Status', (string) ($info['status'] ?? '—'), $col);
        $this->field(self::MARGIN + $col + 18, 'Valor da proposta', (string) ($info['valor'] ?? '—'), $col);
        $this->y -= 36;
        $this->field(self::MARGIN, 'Quantidade de horas', (string) ($info['horas'] ?? '—'), $col);
        $this->field(self::MARGIN + $col + 18, 'Prazo', (string) ($info['inicio'] ?? '—') . ' a ' . (string) ($info['final'] ?? '—'), $col);
        $this->y -= 44;

        $this->writeTextAt(self::MARGIN, $this->y, 'RESUMO DO PROJETO', 8, true, 0.39, 0.4, 0.95);
        $this->y -= 16;
        $this->paragraph((string) ($info['resumo'] ?? ''), 11);

        $this->drawFooter((string) ($info['contato'] ?? ''), $data);
    }

    public function output(string $filename): void
    {
        $pdf = $this->build();
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'proposta.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $safeName . '"');
        header('Content-Length: ' . (string) strlen($pdf));
        header('Cache-Control: private, no-store');
        echo $pdf;
    }

    public function bytes(): string
    {
        return $this->build();
    }

    private function drawLetterhead(string $logoPath): void
    {
        $band = 102.0;
        $this->headerHeight = $band;
        $this->fillRect(0, self::PAGE_H - $band, self::PAGE_W, $band, 0.051, 0.051, 0.059);
        $this->fillRect(0, self::PAGE_H - $band - 4, self::PAGE_W, 4, 0.388, 0.4, 0.945);

        $placed = false;
        if ($logoPath === '' || !is_file($logoPath)) {
            $logoPath = (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2)) . '/public/assets/images/brand/logo-ha.png';
        }
        if (is_file($logoPath)) {
            $imgW = 430.0;
            $imgH = $imgW * (367 / 2000);
            $x = self::MARGIN;
            $y = self::PAGE_H - $band + (($band - $imgH) / 2);
            $placed = $this->drawImage($logoPath, $x, $y, $imgW, $imgH, 13, 13, 15);
        }

        if (!$placed) {
            $this->writeTextAt(self::MARGIN, self::PAGE_H - 48, 'H. A Consultoria & Desenvolvimento', 16, true, 1, 1, 1);
            $this->writeTextAt(self::MARGIN, self::PAGE_H - 66, 'Solucoes em tecnologia para o seu negocio', 9, false, 0.7, 0.72, 0.95);
        }
    }

    private function drawFooter(string $contato, string $data): void
    {
        $h = 42.0;
        $this->fillRect(0, 0, self::PAGE_W, $h, 0.051, 0.051, 0.059);
        $this->fillRect(0, $h, self::PAGE_W, 3, 0.388, 0.4, 0.945);
        $line = $contato !== '' ? $contato : 'H. A Consultoria & Desenvolvimento';
        $this->writeTextAt(self::MARGIN, 24, $line, 8, false, 0.78, 0.78, 0.82);
        $this->writeTextAt(self::MARGIN, 12, 'Documento gerado em ' . $data . '.', 7, false, 0.55, 0.55, 0.6);
    }

    private function field(float $x, string $label, string $value, float $width): void
    {
        $this->writeTextAt($x, $this->y, mb_strtoupper($label, 'UTF-8'), 7, true, 0.39, 0.4, 0.95);
        $max = (int) max(12, floor($width / 5.6));
        $chunks = $this->wrap($value === '' ? '—' : $value, $max);
        $this->writeTextAt($x, $this->y - 13, $chunks[0] ?? '—', 11, true, 0.08, 0.08, 0.1);
    }

    private function paragraph(string $text, float $size): void
    {
        $maxChars = (int) max(20, floor((self::PAGE_W - (2 * self::MARGIN)) / ($size * 0.5)));
        $paragraphs = preg_split("/\r\n|\n|\r/", $text);
        if ($paragraphs === false) {
            $paragraphs = [$text];
        }

        foreach ($paragraphs as $para) {
            $chunks = $this->wrap($para, $maxChars);
            if ($chunks === []) {
                $chunks = [''];
            }
            foreach ($chunks as $chunk) {
                $this->ensureSpace($size + 6);
                $this->writeTextAt(self::MARGIN, $this->y, $chunk === '' ? ' ' : $chunk, $size, false, 0.12, 0.12, 0.14);
                $this->y -= $size + 6;
            }
        }
    }

    private function accentRule(): void
    {
        $y = $this->y - 6;
        $this->pages[$this->page] .= sprintf(
            "0.388 0.400 0.945 RG 1.5 w %.2F %.2F m %.2F %.2F l S\n",
            self::MARGIN,
            $y,
            self::PAGE_W - self::MARGIN,
            $y
        );
        $this->y -= 8;
    }

    private function fillRect(float $x, float $y, float $w, float $h, float $r, float $g, float $b): void
    {
        $this->pages[$this->page] .= sprintf(
            "%.3F %.3F %.3F rg %.2F %.2F %.2F %.2F re f\n",
            $r,
            $g,
            $b,
            $x,
            $y,
            $w,
            $h
        );
    }

    private function ensureSpace(float $needed): void
    {
        if ($this->y - $needed < 56) {
            $this->addPage();
            $this->y = self::PAGE_H - 56;
        }
    }

    /**
     * @return list<string>
     */
    private function wrap(string $text, int $max): array
    {
        $text = trim($text);
        if ($text === '') {
            return [''];
        }

        $words = preg_split('/\s+/u', $text) ?: [$text];
        $lines = [];
        $current = '';
        foreach ($words as $word) {
            $trial = $current === '' ? $word : $current . ' ' . $word;
            if ($this->len($trial) > $max && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $trial;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    private function len(string $text): int
    {
        return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
    }

    private function writeTextAt(
        float $x,
        float $y,
        string $text,
        float $size,
        bool $bold,
        float $r,
        float $g,
        float $b
    ): void {
        $font = $bold ? 'F2' : 'F1';
        $escaped = $this->escape($this->toWinAnsi($text));
        $this->pages[$this->page] .= sprintf(
            "%.3F %.3F %.3F rg BT /%s %.2F Tf %.2F %.2F Td (%s) Tj ET\n",
            $r,
            $g,
            $b,
            $font,
            $size,
            $x,
            $y,
            $escaped
        );
    }

    private function drawImage(
        string $path,
        float $x,
        float $y,
        float $w,
        float $h,
        int $bgR,
        int $bgG,
        int $bgB
    ): bool {
        if (!function_exists('imagecreatefromstring')) {
            return false;
        }

        $raw = @file_get_contents($path);
        if ($raw === false) {
            return false;
        }

        $src = @imagecreatefromstring($raw);
        if ($src === false) {
            return false;
        }

        $iw = imagesx($src);
        $ih = imagesy($src);
        $dst = imagecreatetruecolor($iw, $ih);
        if ($dst === false) {
            imagedestroy($src);
            return false;
        }

        $bg = imagecolorallocate($dst, $bgR, $bgG, $bgB);
        imagefilledrectangle($dst, 0, 0, $iw, $ih, $bg);
        imagecopy($dst, $src, 0, 0, 0, 0, $iw, $ih);
        ob_start();
        imagejpeg($dst, null, 88);
        $jpeg = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);

        if (!is_string($jpeg) || $jpeg === '') {
            return false;
        }

        $this->images[] = [
            'w' => $iw,
            'h' => $ih,
            'data' => $jpeg,
        ];
        $n = count($this->images);
        $this->pages[$this->page] .= sprintf("q %.2F 0 0 %.2F %.2F %.2F cm /Im%d Do Q\n", $w, $h, $x, $y, $n);

        return true;
    }

    private function escape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function toWinAnsi(string $utf8): string
    {
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $utf8);
            if ($converted !== false) {
                return $converted;
            }
        }
        if (function_exists('mb_convert_encoding')) {
            $converted = @mb_convert_encoding($utf8, 'Windows-1252', 'UTF-8');
            if (is_string($converted)) {
                return $converted;
            }
        }

        return preg_replace('/[^\x20-\x7E]/', '?', $utf8) ?? $utf8;
    }

    private function build(): string
    {
        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        $contentIds = [];
        $pageIds = [];
        $next = 3;
        foreach ($this->pages as $ignored) {
            $contentIds[] = $next;
            $pageIds[] = $next + 1;
            $next += 2;
        }
        $font1 = $next;
        $font2 = $next + 1;
        $imageStart = $next + 2;

        $xObjects = '';
        foreach ($this->images as $i => $ignoredImage) {
            $xObjects .= ' /Im' . ($i + 1) . ' ' . ($imageStart + $i) . ' 0 R';
        }
        $xObjectDict = $xObjects !== '' ? ' /XObject <<' . $xObjects . ' >>' : '';

        $kids = [];
        foreach ($pageIds as $pageId) {
            $kids[] = $pageId . ' 0 R';
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($pageIds) . ' >>';

        foreach ($this->pages as $index => $stream) {
            if ($stream === '' || $stream[-1] !== "\n") {
                $stream .= "\n";
            }
            $cid = $contentIds[$index];
            $pid = $pageIds[$index];
            $objects[$cid] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';
            $objects[$pid] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Contents %d 0 R /Resources << /Font << /F1 %d 0 R /F2 %d 0 R >>%s >> >>',
                self::PAGE_W,
                self::PAGE_H,
                $cid,
                $font1,
                $font2,
                $xObjectDict
            );
        }

        $objects[$font1] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[$font2] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        foreach ($this->images as $i => $image) {
            $id = $imageStart + $i;
            $objects[$id] = sprintf(
                "<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length %d >>\nstream\n%s\nendstream",
                $image['w'],
                $image['h'],
                strlen($image['data']),
                $image['data']
            );
        }

        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }

        $maxId = (int) max(array_keys($objects));
        $startxref = strlen($pdf);
        $pdf .= 'xref' . "\n0 " . ($maxId + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($id = 1; $id <= $maxId; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }
        $pdf .= 'trailer' . "\n<< /Size " . ($maxId + 1) . " /Root 1 0 R >>\nstartxref\n" . $startxref . "\n%%EOF";

        return $pdf;
    }
}
