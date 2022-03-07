<?php

use Yasapurnama\DocumentWatermark\WatermarkFactory;

include 'vendor/autoload.php';

$wordWatermark = WatermarkFactory::load(__DIR__ . '/files/word-sample.docx')
                            ->setText('Last update on ' . date('m/d/Y'))
                            ->generate();

$pdfWatermark  = WatermarkFactory::load(__DIR__ . '/files/pdf-sample.pdf')
                            ->subDirectory('sub-dir')
                            ->setText('Last update on ' . date('m/d/Y'))
                            ->sectionHeader()
                            ->alignRight()
                            ->fontSize(9)
                            ->fontColor('ff0000')
                            ->generate();

$pdfWatermark  = WatermarkFactory::load(__DIR__ . '/files/pdf-sample.pdf')
    ->outputFile(__DIR__ . '/files/watermark/sub-dir/pdf-sample.pdf')
    ->setImage(__DIR__ . '/files/stamp.png')
    ->generate();

$pdfWatermark  = WatermarkFactory::load(__DIR__ . '/files/pdf-sample.pdf')
    ->outputFile(__DIR__ . '/files/watermark/sub-dir/pdf-sample.pdf')
    ->setImage(__DIR__ . '/files/stamp.png')
    ->sectionFooter(5, 5)
    ->alignRight()
    ->opacity(0.1)
    ->generate();

$wordWatermark = WatermarkFactory::load(__DIR__ . '/files/word-sample.docx')
    ->outputFile(__DIR__ . '/files/watermark/word-sample.docx')
    ->setImage(__DIR__ . '/files/stamp.png')
    ->generate();

$wordWatermark = WatermarkFactory::load(__DIR__ . '/files/word-sample.docx')
    ->outputFile(__DIR__ . '/files/watermark/word-sample.docx')
    ->setImage(__DIR__ . '/files/stamp.png')
    ->sectionFooter(1, 1)
    ->alignRight()
    ->onlyFirstPage()
    ->generate();


