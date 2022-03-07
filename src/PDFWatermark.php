<?php

namespace Yasapurnama\DocumentWatermark;

use Yasapurnama\PhpShellRotation\Shell;
use Yasapurnama\PhpShellRotation\Shell\System;

class PDFWatermark extends Watermark
{
    public function generate()
    {
        $cmd    = $this->getCommand();
        $result = $this->exec($cmd);

        if (!$result || !file_exists($this->outputFile))
            $this->outputFile = '';

        return $this->outputFile;
    }

    private function getFontSizeOption()
    {
        return '-s ' . $this->fontSize;
    }

    private function getOpacityOption()
    {
        return '-o ' . $this->opacity;
    }

    private function getFontColorOption()
    {
        return '-l ' . $this->fontColor;
    }

    private function getPositionOption()
    {
        if ($this->section == 'header')
            return $this->getHeaderPosition($this->align);

        return $this->getFooterPosition($this->align);
    }

    private function getHeaderPosition($align)
    {
        if ($align == 'left')
            return sprintf(
                '-x %s -y %s',
                $this->x,
                $this->y,
            );

        return sprintf(
            '-x -%s -y %s',
            $this->x,
            $this->y
        );
    }

    private function getFooterPosition($align)
    {
        if ($align == 'left')
            return sprintf(
                '-x %s -y -%s',
                $this->x,
                $this->y,
            );

        return sprintf(
            '-x -%s -y -%s',
            $this->x,
            $this->y
        );
    }

    private function getBinExecutable()
    {
        $binFile = realpath(__DIR__ . '/../bin/markpdf_linux-amd64');

        if (PHP_OS == 'Windows' || PHP_OS == 'WINNT') {
            $binFile = realpath(__DIR__ . '/../bin/markpdf_windows-386.exe');
        } else if (PHP_OS == 'Darwin') {
            $binFile = realpath(__DIR__ . '/../bin/markpdf_darwin-amd64');
        }

        if (!is_file($binFile))
            throw new \Exception('Markpdf binary not found');

        return $binFile;
    }

    private function getCommand()
    {
        if (!$this->outputFile) {
            $this->outputFile = $this->outputDir . '/' . $this->fileName;
        }

        if ($this->image) {
            return sprintf(
                '%s %s %s %s %s %s',
                $this->getBinExecutable(),
                $this->documentPath,
                $this->image,
                $this->outputFile,
                $this->getPositionOption(),
                $this->getOpacityOption()
            );
        }

        return sprintf(
            '%s %s "%s" %s %s %s %s',
            $this->getBinExecutable(),
            $this->documentPath,
            $this->text,
            $this->outputFile,
            $this->getFontColorOption(),
            $this->getFontSizeOption(),
            $this->getPositionOption()
        );
    }

    private function exec($cmd)
    {
        $shell  = new Shell();
        $output = $shell->exec($cmd);

        return $shell->getStatus() === 0;
    }
}
