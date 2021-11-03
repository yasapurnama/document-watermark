<?php

namespace Yasapurnama\DocumentWatermark;

abstract class Watermark
{
    protected $documentPath;

    protected $outputDir;

    protected $fileName;

    protected $outputFile;

    protected $text;

    protected $section   = 'footer';

    protected $align     = 'right';

    protected $fontSize  = 10;

    protected $fontColor = '000000';

    function __construct(string $documentPath)
    {
        $this->documentPath = $documentPath;

        $this->setDefault();
    }

    public function setDefault()
    {
        $this->fileName  = basename($this->documentPath);
        $this->outputDir = dirname($this->documentPath) . '/watermark';

        if (!is_dir($this->outputDir))
            mkdir($this->outputDir, 0755, true);
    }

    public function setText(string $text)
    {
        $this->text = $text;

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

    public function alignLeft()
    {
        $this->align = 'left';

        return $this;
    }

    public function alignRight()
    {
        $this->align = 'right';

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

    abstract public function generate();

}