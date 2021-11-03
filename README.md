
# Document Watermark

[![Latest Stable Version](http://poser.pugx.org/yasapurnama/document-watermark/v)](https://packagist.org/packages/yasapurnama/document-watermark)
[![Total Downloads](http://poser.pugx.org/yasapurnama/document-watermark/downloads)](https://packagist.org/packages/yasapurnama/document-watermark)
[![License](http://poser.pugx.org/yasapurnama/document-watermark/license)](https://packagist.org/packages/yasapurnama/document-watermark)


Generate text watermark on PDF and Word documents


## Installation

Install document-watermark with npm

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

## Contribute
Just submit pull request, your contributions are always welcomed!
## Credits
This project was inspired by [ajaxray's](https://github.com/ajaxray) and using [markpdf](https://github.com/ajaxray/markpdf) as depedencies.