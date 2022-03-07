
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
                            ->subDirectory('watermark')
                            ->setText('Last update on ' . date('m/d/Y'))
                            ->generate();
```
![example-word-watermark](https://user-images.githubusercontent.com/12730759/151197618-4cc9131d-cdd7-404a-bb03-aa2c390accbc.png)

Generate PDF watermark with custom options
```php
$pdfWatermark  = WatermarkFactory::load(__DIR__ . '/files/pdf-sample.pdf')
                            ->subDirectory('watermark')
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
                            ->outputFile(__DIR__ . '/files/watermark/word-image-stamp-custom.docx')
                            ->setImage(__DIR__ . '/files/stamp.png')
                            ->sectionFooter(1, 1)
                            ->alignRight()
                            ->onlyFirstPage()
                            ->generate();
```
![example-word-image-stamp](https://user-images.githubusercontent.com/12730759/157050800-04308e49-e981-4a1d-aaea-fd42ae033584.png)

Generate pdf document watermark using image with default values

```php
$pdfWatermark  = WatermarkFactory::load(__DIR__ . '/files/pdf-sample.pdf')
    ->outputFile(__DIR__ . '/files/watermark/pdf-image-stamp.pdf')
    ->setImage(__DIR__ . '/files/stamp.png')
    ->generate();
```
![example-pdf-image-stamp](https://user-images.githubusercontent.com/12730759/157051029-83e69c08-f8e7-4d48-b8f8-0e1097bc1c8f.png)


## Contribute
Just submit pull request, your contributions are always welcomed!
## Credits
This project was inspired by [ajaxray's](https://github.com/ajaxray) and using [markpdf](https://github.com/ajaxray/markpdf) as depedencies.