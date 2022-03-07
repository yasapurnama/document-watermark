<?php

namespace Yasapurnama\DocumentWatermark;

abstract class Watermark
{
    protected $documentPath;

    protected $outputDir;

    protected $fileName;

    protected $outputFile;

    protected $text;

    protected $image;

    protected $opacity   = 1.0;

    protected $section   = 'header';

    protected $align     = 'left';

    protected $x         = 30;

    protected $y         = 25;

    protected $fontSize  = 10;

    protected $fontColor = '000000';

    protected $onlyFirstPage = false;

    function __construct(string $documentPath)
    {
        $this->documentPath = $documentPath;
        $this->fileName     = basename($this->documentPath);
        $this->outputDir    = dirname($this->documentPath);
    }

    public function outputFile(string $outputFile)
    {
        $this->outputFile = $outputFile;

        return $this;
    }

    public function setText(string $text)
    {
        $this->text = $text;
        $this->image = null;

        return $this;
    }

    public function setImage(string $image)
    {
        $this->image = $image;
        $this->text = null;

        return $this;
    }

    public function opacity(float $opacity)
    {
        $this->opacity = $opacity;

        return $this;
    }

    public function sectionHeader()
    {
        $this->section = 'header';

        return $this;
    }

    public function sectionFooter()
    {
        $this->section = 'footer';

        return $this;
    }

    public function alignLeft(int $x = null, int $y = null)
    {
        $this->align = 'left';
        if ($x) $this->x = $x;
        if ($y) $this->y = $y;

        return $this;
    }

    public function alignRight(int $x = null, int $y = null)
    {
        $this->align = 'right';
        if ($x) $this->x = $x;
        if ($y) $this->y = $y;

        return $this;
    }

    public function fontSize(int $fontSize)
    {
        $this->fontSize = $fontSize;

        return $this;
    }

    public function fontColor(string $fontColor)
    {
        $this->fontColor = $fontColor;

        return $this;
    }

    public function subDirectory(string $directory)
    {
        $this->outputDir = $this->outputDir . '/' . $directory;

        if (!is_dir($this->outputDir))
            mkdir($this->outputDir, 0755, true);

        return $this;
    }

    public function onlyFirstPage()
    {
        $this->onlyFirstPage = true;

        return $this;
    }

    abstract public function generate();
}
