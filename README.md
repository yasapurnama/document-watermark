
# Document Watermark

[![Latest Stable Version](http://poser.pugx.org/yasapurnama/document-watermark/v)](https://packagist.org/packages/yasapurnama/document-watermark)
[![Total Downloads](http://poser.pugx.org/yasapurnama/document-watermark/downloads)](https://packagist.org/packages/yasapurnama/document-watermark)
[![License](http://poser.pugx.org/yasapurnama/document-watermark/license)](https://packagist.org/packages/yasapurnama/document-watermark)


Generate text watermark on PDF and Word documents


## Installation

Install document-watermark via composer

```bash
  $ composer require yasapurnama/document-watermark
```

## Examples
Generate word document watermark. Using default section `footer`, text align `right`, font color `000000` and font size `10` 
```php
$wordWatermark = WatermarkFactory::load(__DIR__ . '/files/word-sample.docx')
                            ->setText('Last update on ' . date('m/d/Y'))
                            ->generate();
```
![example-word-watermark](https://user-images.githubusercontent.com/12730759/151197618-4cc9131d-cdd7-404a-bb03-aa2c390accbc.png)

Generate PDF watermark with custom options
```php
$pdfWatermark  = WatermarkFactory::load(__DIR__ . '/files/pdf-sample.pdf')
                            ->subDirectory('sub-dir')
                            ->setText('Last update on ' . date('m/d/Y'))
                            ->sectionHeader()
                            ->alignRight()
                            ->fontSize(9)
                            ->fontColor('ff0000')
                            ->generate();
```
![example-pdf-watermark](https://user-images.githubusercontent.com/12730759/151197877-e94c4087-cf92-4dad-beab-a7951cf42ca3.png)


### Image watermark

Generate word document watermark using image at footer, with page margins x=1 y=1, align right, only in first page

```php
$wordWatermark = WatermarkFactory::load(__DIR__ . '/files/word-sample.docx')
                            ->outputFile(__DIR__ . '/files/watermark/word-sample.docx')
                            ->setImage(__DIR__ . '/files/stamp.png')
                            ->sectionFooter(1, 1)
                            ->alignRight()
                            ->onlyFirstPage()
                            ->generate();
```

Generate pdf document watermark using image with default values

```php
$pdfWatermark  = WatermarkFactory::load(__DIR__ . '/files/pdf-sample.pdf')
    ->outputFile(__DIR__ . '/files/watermark/sub-dir/pdf-sample.pdf')
    ->setImage(__DIR__ . '/files/stamp.png')
    ->generate();
```






## Contribute
Just submit pull request, your contributions are always welcomed!
## Credits
This project was inspired by [ajaxray's](https://github.com/ajaxray) and using [markpdf](https://github.com/ajaxray/markpdf) as depedencies.