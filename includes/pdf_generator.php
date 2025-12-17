<?php

/**
 * Simple PDF Generator - Working Version
 * Using correct PDF syntax
 */
class PDFGenerator
{
    private $buffer;
    private $page;
    private $n;
    private $offsets;

    public $pageWidth = 595.28;
    public $pageHeight = 841.89;
    public $margin = 42.52;

    private $pages = [];
    private $currentPage = 0;

    public function __construct()
    {
        $this->buffer = '';
        $this->page = 0;
        $this->n = 2;
        $this->offsets = [];
        $this->pages = [];
        $this->addPage();
    }

    public function addPage()
    {
        $this->page++;
        $this->currentPage = $this->page;
        $this->pages[$this->page] = '';
    }

    private function _out($s)
    {
        $this->pages[$this->page] .= $s . "\n";
    }

    public function addText($x, $y, $txt, $size = 12, $style = 'normal')
    {
        $txt = $this->_escape($txt);
        $font = ($style === 'bold') ? '/F2' : '/F1';

        $s = sprintf('BT %s %d Tf %.2f %.2f Td (%s) Tj ET', $font, $size, $x, $y, $txt);
        $this->_out($s);
    }

    public function addCenteredText($y, $txt, $size = 12, $style = 'normal')
    {
        $w = strlen($txt) * $size * 0.5;
        $x = ($this->pageWidth - $w) / 2;
        $this->addText($x, $y, $txt, $size, $style);
    }

    public function addLine($x1, $y1, $x2, $y2)
    {
        $this->_out(sprintf('%.2f w %.2f %.2f m %.2f %.2f l S', 0.5, $x1, $y1, $x2, $y2));
    }

    public function addRect($x, $y, $w, $h, $fill = false)
    {
        $op = $fill ? 'f' : 'S';
        $this->_out(sprintf('%.2f %.2f %.2f %.2f re %s', $x, $y, $w, $h, $op));
    }

    public function setGray($g)
    {
        $this->_out(sprintf('%.3f g', $g));
    }

    public function addTable($headers, $data, $x, $y, $colWidths)
    {
        $rowHeight = 20;
        $fontSize = 9;
        $totalWidth = array_sum($colWidths);

        $this->setGray(0.9);
        $this->addRect($x, $y - $rowHeight, $totalWidth, $rowHeight, true);
        $this->setGray(0);

        $currentX = $x;
        foreach ($headers as $i => $header) {
            $this->addText($currentX + 2, $y - 14, $header, $fontSize, 'bold');
            $currentX += $colWidths[$i];
        }

        $this->addRect($x, $y - $rowHeight, $totalWidth, $rowHeight, false);
        $y -= $rowHeight;

        foreach ($data as $row) {
            if ($y < $this->margin + 30) {
                $this->addPage();
                $y = $this->pageHeight - $this->margin - 30;

                $this->setGray(0.9);
                $this->addRect($x, $y - $rowHeight, $totalWidth, $rowHeight, true);
                $this->setGray(0);

                $currentX = $x;
                foreach ($headers as $i => $header) {
                    $this->addText($currentX + 2, $y - 14, $header, $fontSize, 'bold');
                    $currentX += $colWidths[$i];
                }

                $this->addRect($x, $y - $rowHeight, $totalWidth, $rowHeight, false);
                $y -= $rowHeight;
            }

            $currentX = $x;
            foreach ($row as $i => $cell) {
                $text = $this->_truncate($cell, $colWidths[$i], $fontSize);
                $this->addText($currentX + 2, $y - 14, $text, $fontSize);
                $currentX += $colWidths[$i];
            }

            $this->addRect($x, $y - $rowHeight, $totalWidth, $rowHeight, false);

            $currentX = $x;
            foreach ($colWidths as $width) {
                $this->addLine($currentX, $y, $currentX, $y - $rowHeight);
                $currentX += $width;
            }
            $this->addLine($currentX, $y, $currentX, $y - $rowHeight);

            $y -= $rowHeight;
        }

        return $y;
    }

    private function _truncate($text, $maxWidth, $fontSize)
    {
        $text = (string) $text;
        $maxChars = floor($maxWidth / ($fontSize * 0.5)) - 2;
        if (strlen($text) > $maxChars) {
            return substr($text, 0, $maxChars - 3) . '...';
        }
        return $text;
    }

    private function _escape($s)
    {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace('(', '\\(', $s);
        $s = str_replace(')', '\\)', $s);
        $s = str_replace("\r", '\\r', $s);
        $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        return $s;
    }

    private function _newobj()
    {
        $this->n++;
        $this->offsets[$this->n] = strlen($this->buffer);
        $this->buffer .= $this->n . ' 0 obj' . "\n";
    }

    private function _putstream($s)
    {
        $this->buffer .= 'stream' . "\n" . $s . "\n" . 'endstream' . "\n";
    }

    public function output($name = 'doc.pdf')
    {
        if ($this->page == 0) {
            $this->addPage();
        }

        $this->buffer = "%PDF-1.3\n";

        $nb = $this->page;
        $n = $this->n;

        for ($i = 1; $i <= $nb; $i++) {
            $this->_newobj();
            $this->buffer .= '<<' . "\n";
            $this->buffer .= '/Type /Page' . "\n";
            $this->buffer .= '/Parent 1 0 R' . "\n";
            $this->buffer .= sprintf('/MediaBox [0 0 %.2f %.2f]', $this->pageWidth, $this->pageHeight) . "\n";
            $this->buffer .= '/Contents ' . ($this->n + 1) . ' 0 R' . "\n";
            $this->buffer .= '/Resources 2 0 R' . "\n";
            $this->buffer .= '>>' . "\n";
            $this->buffer .= 'endobj' . "\n";

            $p = $this->pages[$i];
            $this->_newobj();
            $this->buffer .= '<<' . "\n";
            $this->buffer .= '/Length ' . strlen($p) . "\n";
            $this->buffer .= '>>' . "\n";
            $this->_putstream($p);
            $this->buffer .= 'endobj' . "\n";
        }

        $this->offsets[1] = strlen($this->buffer);
        $this->buffer .= '1 0 obj' . "\n";
        $this->buffer .= '<<' . "\n";
        $this->buffer .= '/Type /Pages' . "\n";
        $kids = '';
        for ($i = 0; $i < $nb; $i++) {
            $kids .= (3 + 2 * $i) . ' 0 R ';
        }
        $this->buffer .= '/Kids [' . $kids . ']' . "\n";
        $this->buffer .= '/Count ' . $nb . "\n";
        $this->buffer .= '>>' . "\n";
        $this->buffer .= 'endobj' . "\n";

        $this->offsets[2] = strlen($this->buffer);
        $this->buffer .= '2 0 obj' . "\n";
        $this->buffer .= '<<' . "\n";
        $this->buffer .= '/Font << /F1 ' . ($this->n + 1) . ' 0 R /F2 ' . ($this->n + 2) . ' 0 R >>' . "\n";
        $this->buffer .= '>>' . "\n";
        $this->buffer .= 'endobj' . "\n";

        $this->_newobj();
        $this->buffer .= '<<' . "\n";
        $this->buffer .= '/Type /Font' . "\n";
        $this->buffer .= '/BaseFont /Helvetica' . "\n";
        $this->buffer .= '/Subtype /Type1' . "\n";
        $this->buffer .= '>>' . "\n";
        $this->buffer .= 'endobj' . "\n";

        $this->_newobj();
        $this->buffer .= '<<' . "\n";
        $this->buffer .= '/Type /Font' . "\n";
        $this->buffer .= '/BaseFont /Helvetica-Bold' . "\n";
        $this->buffer .= '/Subtype /Type1' . "\n";
        $this->buffer .= '>>' . "\n";
        $this->buffer .= 'endobj' . "\n";

        $this->_newobj();
        $this->buffer .= '<<' . "\n";
        $this->buffer .= '/Type /Catalog' . "\n";
        $this->buffer .= '/Pages 1 0 R' . "\n";
        $this->buffer .= '>>' . "\n";
        $this->buffer .= 'endobj' . "\n";

        $o = strlen($this->buffer);
        $this->buffer .= 'xref' . "\n";
        $this->buffer .= '0 ' . ($this->n + 1) . "\n";
        $this->buffer .= '0000000000 65535 f ' . "\n";
        for ($i = 1; $i <= $this->n; $i++) {
            $this->buffer .= sprintf('%010d 00000 n ', $this->offsets[$i]) . "\n";
        }

        $this->buffer .= 'trailer' . "\n";
        $this->buffer .= '<<' . "\n";
        $this->buffer .= '/Size ' . ($this->n + 1) . "\n";
        $this->buffer .= '/Root ' . $this->n . ' 0 R' . "\n";
        $this->buffer .= '>>' . "\n";
        $this->buffer .= 'startxref' . "\n";
        $this->buffer .= $o . "\n";
        $this->buffer .= '%%EOF';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . strlen($this->buffer));
        echo $this->buffer;
        exit;
    }
}
