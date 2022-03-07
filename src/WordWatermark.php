<?php

namespace Yasapurnama\DocumentWatermark;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Image;
use PhpOffice\PhpWord\Element\Footer;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;

class WordWatermark extends Watermark
{
    public function generate()
    {
        $phpWord   = WordIOFactory::load($this->documentPath);
        $container = $this->getContainer($phpWord);

        if ($this->image) {
            $container->addImage(
                $this->image,
                array_merge(
                    array(
                        'wrappingStyle'    => Image::WRAPPING_STYLE_BEHIND,
                        'positioning'      => Image::POSITION_ABSOLUTE,
                        'posHorizontalRel' => Image::POSITION_RELATIVE_TO_PAGE,
                        'posVerticalRel'   => Image::POSITION_RELATIVE_TO_PAGE,
                    ),
                    $this->setAxis()
                )
            );
        }

        if ($this->text) {
            $container->addPreserveText(
                $this->text,
                ['size'  => $this->fontSize, 'color' => $this->fontColor],
                ['align' => $this->align],
            );
        }

        if (!$this->outputFile) {
            $this->outputFile = $this->outputDir . '/' . $this->fileName;
        }

        $output = $phpWord->save($this->outputFile, 'Word2007');

        if (!$output || !file_exists($this->outputFile))
            $this->outputFile = '';

        return $this->outputFile;
    }

    private function getContainer(PhpWord $phpWord)
    {
        $sections = $phpWord->getSections();
        $section  = $sections[0];

        if ($this->section == 'header') {
            return $section->addHeader($this->getFirstPageOption());
        }

        return $section->addFooter($this->getFirstPageOption());
    }

    private function getFirstPageOption()
    {
        if ($this->onlyFirstPage) return Footer::FIRST;

        return Footer::AUTO;
    }

    private function setAxis()
    {
        if ($this->section == 'header') {
            if ($this->align == 'left') {
                return array(
                    'posVertical'    => Image::POSITION_VERTICAL_TOP,
                    'posHorizontal'    => Image::POSITION_HORIZONTAL_LEFT,
                    'wrapDistanceTop' => $this->y,
                    'wrapDistanceLeft' => $this->x
                );
            }
            return array(
                'posVertical'    => Image::POSITION_VERTICAL_TOP,
                'posHorizontal'    => Image::POSITION_HORIZONTAL_RIGHT,
                'wrapDistanceTop' => $this->y,
                'wrapDistanceRight' => $this->x
            );
        }
        if ($this->align == 'left') {
            return array(
                'posVertical'    => Image::POSITION_VERTICAL_BOTTOM,
                'posHorizontal'    => Image::POSITION_HORIZONTAL_LEFT,
                'wrapDistanceBottom' => $this->y,
                'wrapDistanceLeft' => $this->x
            );
        }
        return array(
            'posVertical'    => Image::POSITION_VERTICAL_BOTTOM,
            'posHorizontal'    => Image::POSITION_HORIZONTAL_RIGHT,
            'wrapDistanceRight' => $this->x,
            'wrapDistanceBottom' => $this->y
        );
    }
}
