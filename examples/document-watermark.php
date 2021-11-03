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

