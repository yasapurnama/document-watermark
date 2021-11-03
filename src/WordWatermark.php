<?php

namespace Yasapurnama\DocumentWatermark;

use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\PhpWord;

class WordWatermark extends Watermark
{
    public function generate()
    {
        $phpWord   = WordIOFactory::load($this->documentPath);
        $container = $this->getContainer($phpWord);

        $container->addPreserveText(
            $this->text,
            ['size'  => $this->fontSize, 'color' => $this->fontColor],
            ['align' => $this->align],
        );

        $this->outputFile = $this->outputDir . '/' . $this->fileName;
        $output           = $phpWord->save($this->outputFile, 'Word2007');

        if (!$output || !file_exists($this->outputFile))
            $this->outputFile = '';

        return $this->outputFile;
    }

    private function getContainer(PhpWord $phpWord)
    {
        $sections = $phpWord->getSections();
        $section  = $sections[0];

        if ($this->section == 'header')
            return $section->addHeader();

        return $section->addFooter();
    }
}