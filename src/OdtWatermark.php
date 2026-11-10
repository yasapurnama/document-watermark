<?php

namespace Yasapurnama\DocumentWatermark;

use DOMDocument;
use DOMElement;
use DOMXPath;
use ZipArchive;
use RuntimeException;

class OdtWatermark extends Watermark
{
    private const NS_OFFICE   = 'urn:oasis:names:tc:opendocument:xmlns:office:1.0';
    private const NS_STYLE    = 'urn:oasis:names:tc:opendocument:xmlns:style:1.0';
    private const NS_TEXT     = 'urn:oasis:names:tc:opendocument:xmlns:text:1.0';
    private const NS_DRAW     = 'urn:oasis:names:tc:opendocument:xmlns:drawing:1.0';
    private const NS_SVG      = 'urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0';
    private const NS_XLINK    = 'http://www.w3.org/1999/xlink';
    private const NS_FO       = 'urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0';
    private const NS_MANIFEST = 'urn:oasis:names:tc:opendocument:xmlns:manifest:1.0';

    private $zip;
    private $styles;
    private $content;
    private $styleName;
    private $textStyleName;
    private $imagePath;
    private $imageMime;
    private $imageWidth = '50mm';
    private $imageHeight = '10mm';

    public function width(float $width): self
    {
        $this->imageWidth = $width . 'mm';

        return $this;
    }

    public function height(float $height): self
    {
        $this->imageHeight = $height . 'mm';

        return $this;
    }

    /**
     * Uses $this->onlyFirstPage from the parent Watermark class.
     * empty() avoids a warning if the property is not defined there.
     */
    private function isFirstPageOnly(): bool
    {
        return !empty($this->onlyFirstPage);
    }

    public function generate()
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new RuntimeException('PHP ZipArchive extension is required.');
        }

        if (!is_file($this->documentPath)) {
            throw new RuntimeException('Input ODT document not found.');
        }

        if (!$this->text && !$this->image) {
            throw new RuntimeException('Please provide watermark text or image.');
        }

        $output = $this->outputFile ?: (
            $this->outputDir . '/' . $this->fileName
        );

        $directory = dirname($output);

        if (!is_dir($directory) &&
            !mkdir($directory, 0755, true) &&
            !is_dir($directory)) {
            throw new RuntimeException('Cannot create output directory.');
        }

        $temporary = tempnam($directory, '.odt-watermark-');

        if ($temporary === false) {
            throw new RuntimeException('Cannot create temporary file.');
        }

        $zip = new \ZipArchive();
        $opened = false;

        try {
            if (!copy($this->documentPath, $temporary)) {
                throw new RuntimeException('Cannot copy input ODT file.');
            }

            $result = $zip->open($temporary);

            if ($result !== true) {
                throw new RuntimeException(
                    'Cannot open ODT archive. ZIP error: ' . $result
                );
            }

            $opened = true;
            $this->zip = $zip;

            $content = $zip->getFromName('content.xml');
            $styles = $zip->getFromName('styles.xml');

            if ($content === false || $styles === false) {
                throw new RuntimeException('Invalid ODT document.');
            }

            $this->styles = $this->loadXml($styles);

            if ($this->image) {
                $this->imagePath = $this->addImage($this->image);
            }

            $this->createStyles();

            if ($this->isFirstPageOnly()) {
                // Keep whitespace intact: content.xml holds the real text.
                $this->content = $this->loadXml($content, true);
                $this->insertFirstPageWatermark();

                if (!$zip->addFromString(
                    'content.xml',
                    $this->content->saveXML()
                )) {
                    throw new RuntimeException('Cannot update content.xml.');
                }
            } else {
                $this->insertWatermark();
            }

            if (!$zip->addFromString(
                'styles.xml',
                $this->styles->saveXML()
            )) {
                throw new RuntimeException('Cannot update styles.xml.');
            }

            if ($this->image) {
                $this->updateManifest();
            }

            if (!$zip->close()) {
                $opened = false;
                throw new RuntimeException('Cannot save ODT archive.');
            }

            $opened = false;

            if (!copy($temporary, $output)) {
                throw new RuntimeException('Cannot write output ODT file.');
            }

            $this->outputFile = $output;

            return $output;
        } finally {
            if ($opened) {
                $zip->close();
            }

            unset($this->zip);

            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }

    /**
     * Load XML without network access.
     */
    private function loadXml(string $xml, bool $keepBlanks = false): DOMDocument
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->preserveWhiteSpace = true;
        $document->formatOutput = false;

        $options = LIBXML_NONET;

        if (!$keepBlanks) {
            $options |= LIBXML_NOBLANKS;
        }

        $previous = libxml_use_internal_errors(true);

        try {
            if (!$document->loadXML($xml, $options)) {
                throw new RuntimeException('Invalid ODT XML.');
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return $document;
    }

    /**
     * Generate unique graphic and text styles.
     *
     * All pages:   automatic styles in styles.xml (used by master-page header/footer).
     * First page:  common styles in styles.xml (office:styles), because the
     *              frame lives in content.xml, which can only use common styles
     *              from styles.xml (not its automatic styles).
     */
    private function createStyles(): void
    {
        $xpath = new DOMXPath($this->styles);
        $xpath->registerNamespace('office', self::NS_OFFICE);
        $xpath->registerNamespace('style', self::NS_STYLE);

        $path = $this->isFirstPageOnly()
            ? '/office:document-styles/office:styles'
            : '/office:document-styles/office:automatic-styles';

        $target = $xpath->query($path)->item(0);

        if (!$target) {
            throw new RuntimeException('ODT styles section not found.');
        }

        $suffix = substr(sha1(uniqid('', true)), 0, 10);

        $this->styleName = 'WatermarkGraphic' . $suffix;
        $this->textStyleName = 'WatermarkText' . $suffix;

        // Graphic style.
        $graphic = $this->styles->createElementNS(
            self::NS_STYLE,
            'style:style'
        );

        $graphic->setAttributeNS(self::NS_STYLE, 'style:name', $this->styleName);
        $graphic->setAttributeNS(self::NS_STYLE, 'style:family', 'graphic');

        $properties = $this->styles->createElementNS(
            self::NS_STYLE,
            'style:graphic-properties'
        );

        $properties->setAttributeNS(self::NS_STYLE, 'style:wrap', 'run-through');
        $properties->setAttributeNS(self::NS_STYLE, 'style:run-through', 'foreground');

        $properties->setAttributeNS(
            self::NS_STYLE,
            'style:horizontal-pos',
            $this->align === 'right' ? 'right' : 'left'
        );
        $properties->setAttributeNS(self::NS_STYLE, 'style:horizontal-rel', 'page');

        $properties->setAttributeNS(
            self::NS_STYLE,
            'style:vertical-pos',
            $this->section === 'footer' ? 'bottom' : 'top'
        );
        $properties->setAttributeNS(self::NS_STYLE, 'style:vertical-rel', 'page');

        if ($this->image) {
            $horizontalMargin = $this->align === 'right'
                ? 'fo:margin-right'
                : 'fo:margin-left';
            $verticalMargin = $this->section === 'footer'
                ? 'fo:margin-bottom'
                : 'fo:margin-top';

            $properties->setAttributeNS(
                self::NS_FO,
                $horizontalMargin,
                $this->x . 'mm'
            );
            $properties->setAttributeNS(
                self::NS_FO,
                $verticalMargin,
                $this->y . 'mm'
            );
        }

        $properties->setAttributeNS(
            self::NS_DRAW,
            'draw:opacity',
            round(max(0, min(1, $this->opacity)) * 100) . '%'
        );
        $properties->setAttributeNS(self::NS_DRAW, 'draw:fill', 'none');
        $properties->setAttributeNS(self::NS_DRAW, 'draw:stroke', 'none');

        $graphic->appendChild($properties);
        $target->appendChild($graphic);

        // Text style.
        if ($this->text) {
            $textStyle = $this->styles->createElementNS(
                self::NS_STYLE,
                'style:style'
            );

            $textStyle->setAttributeNS(
                self::NS_STYLE,
                'style:name',
                $this->textStyleName
            );
            $textStyle->setAttributeNS(self::NS_STYLE, 'style:family', 'text');

            $textProperties = $this->styles->createElementNS(
                self::NS_STYLE,
                'style:text-properties'
            );

            $color = ltrim($this->fontColor, '#');

            if (!preg_match('/^[0-9a-fA-F]{6}$/', $color)) {
                throw new RuntimeException('Invalid watermark font color.');
            }

            if ($this->fontSize <= 0) {
                throw new RuntimeException('Font size must be positive.');
            }

            $textProperties->setAttributeNS(
                self::NS_FO,
                'fo:font-size',
                $this->fontSize . 'pt'
            );
            $textProperties->setAttributeNS(
                self::NS_FO,
                'fo:color',
                '#' . $color
            );

            $textStyle->appendChild($textProperties);
            $target->appendChild($textStyle);
        }
    }

    /**
     * Add watermark to every master page (all pages).
     */
    private function insertWatermark(): void
    {
        $xpath = new DOMXPath($this->styles);

        $xpath->registerNamespace('office', self::NS_OFFICE);
        $xpath->registerNamespace('style', self::NS_STYLE);

        $masterPages = $xpath->query(
            '/office:document-styles/office:master-styles/style:master-page'
        );

        if (!$masterPages || $masterPages->length === 0) {
            throw new RuntimeException('No ODT master pages found.');
        }

        foreach ($masterPages as $masterPage) {
            $elementName = $this->section === 'footer'
                ? 'footer'
                : 'header';

            // ODF master pages use style:header and style:footer,
            // NOT office:header / office:footer.
            $elements = $xpath->query(
                './style:' . $elementName,
                $masterPage
            );

            if ($elements->length > 0) {
                $container = $elements->item(0);
            } else {
                $container = $this->styles->createElementNS(
                    self::NS_STYLE,
                    'style:' . $elementName
                );

                // Insert the header/footer before other master-page
                // children so the master-page element order stays valid.
                $masterPage->insertBefore(
                    $container,
                    $masterPage->firstChild
                );
            }

            $paragraph = $this->styles->createElementNS(
                self::NS_TEXT,
                'text:p'
            );

            $paragraph->appendChild($this->createFrame($this->styles, false));

            $container->appendChild($paragraph);
        }
    }

    /**
     * Add the watermark to the first page only.
     *
     * The frame is anchored to page 1 inside the first paragraph of the body
     * (text:anchor-type="page" + text:anchor-page-number="1"). This does not
     * depend on master pages, headers/footers or the number of pages.
     */
    private function insertFirstPageWatermark(): void
    {
        $xpath = new DOMXPath($this->content);

        $xpath->registerNamespace('office', self::NS_OFFICE);
        $xpath->registerNamespace('text', self::NS_TEXT);

        $body = '/office:document-content/office:body/office:text';

        $text = $xpath->query($body)->item(0);

        if (!$text) {
            throw new RuntimeException('ODT text body not found.');
        }

        // First paragraph or heading directly inside office:text.
        $target = $xpath->query(
            '(' . $body . '/text:p | ' . $body . '/text:h)[1]'
        )->item(0);

        if (!$target) {
            $target = $this->content->createElementNS(
                self::NS_TEXT,
                'text:p'
            );
            $text->appendChild($target);
        }

        $frame = $this->createFrame($this->content, true);

        $target->insertBefore($frame, $target->firstChild);
    }

    /**
     * Create a native ODF drawing frame in the given document.
     */
    private function createFrame(DOMDocument $document, bool $firstPage): DOMElement
    {
        $frame = $document->createElementNS(
            self::NS_DRAW,
            'draw:frame'
        );

        $frame->setAttributeNS(
            self::NS_DRAW,
            'draw:style-name',
            $this->styleName
        );

        $frame->setAttributeNS(
            self::NS_DRAW,
            'draw:name',
            'DocumentWatermark'
        );

        $frame->setAttributeNS(
            self::NS_TEXT,
            'text:anchor-type',
            'page'
        );

        if ($firstPage) {
            $frame->setAttributeNS(
                self::NS_TEXT,
                'text:anchor-page-number',
                '1'
            );
        }

        $frame->setAttributeNS(
            self::NS_DRAW,
            'draw:layer',
            'foreground'
        );

        $frame->setAttributeNS(
            self::NS_DRAW,
            'draw:z-index',
            '2147483647'
        );

        if ($this->image) {
            $this->appendImage($frame);
        } else {
            $this->appendText($frame);
        }

        return $frame;
    }

    /**
     * Insert text into the drawing frame.
     */
    private function appendText(DOMElement $frame): void
    {
        $document = $frame->ownerDocument;

        $frame->setAttributeNS(self::NS_SVG, 'svg:width', '150mm');
        $frame->setAttributeNS(self::NS_SVG, 'svg:height', '20mm');

        $textBox = $document->createElementNS(
            self::NS_DRAW,
            'draw:text-box'
        );

        $paragraph = $document->createElementNS(
            self::NS_TEXT,
            'text:p'
        );

        $span = $document->createElementNS(
            self::NS_TEXT,
            'text:span'
        );

        $span->setAttributeNS(
            self::NS_TEXT,
            'text:style-name',
            $this->textStyleName
        );

        $span->appendChild($document->createTextNode($this->text));

        $paragraph->appendChild($span);
        $textBox->appendChild($paragraph);
        $frame->appendChild($textBox);
    }

    /**
     * Insert image reference into the drawing frame.
     */
    private function appendImage(DOMElement $frame): void
    {
        $document = $frame->ownerDocument;

        $frame->setAttributeNS(self::NS_SVG, 'svg:width', $this->imageWidth);
        $frame->setAttributeNS(self::NS_SVG, 'svg:height', $this->imageHeight);

        $image = $document->createElementNS(
            self::NS_DRAW,
            'draw:image'
        );

        $image->setAttributeNS(self::NS_XLINK, 'xlink:href', $this->imagePath);
        $image->setAttributeNS(self::NS_XLINK, 'xlink:type', 'simple');
        $image->setAttributeNS(self::NS_XLINK, 'xlink:show', 'embed');
        $image->setAttributeNS(self::NS_XLINK, 'xlink:actuate', 'onLoad');

        $frame->appendChild($image);
    }

    /**
     * Embed watermark image in ODT package.
     */
    private function addImage(string $image): string
    {
        if (!$this->zip instanceof \ZipArchive) {
            throw new RuntimeException('ODT ZIP archive is not initialized.');
        }

        if (!is_file($image)) {
            throw new RuntimeException('Watermark image not found.');
        }

        $info = @getimagesize($image);

        if (!$info || !in_array($info['mime'], [
            'image/png',
            'image/jpeg',
            'image/gif',
        ], true)) {
            throw new RuntimeException(
                'Watermark must be a PNG, JPEG, or GIF image.'
            );
        }

        $contents = file_get_contents($image);

        if ($contents === false) {
            throw new RuntimeException('Cannot read watermark image.');
        }

        $extensions = [
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_GIF  => 'gif',
        ];

        if (!isset($extensions[$info[2]])) {
            throw new RuntimeException('Unsupported watermark image type.');
        }

        $path = 'Pictures/watermark_' .
            sha1($contents) . '.' . $extensions[$info[2]];

        if ($this->zip->locateName($path) === false &&
            !$this->zip->addFromString($path, $contents)) {
            throw new RuntimeException('Cannot embed watermark image.');
        }

        $this->imageMime = $info['mime'];

        return $path;
    }

    /**
     * Register embedded image in META-INF/manifest.xml.
     */
    private function updateManifest(): void
    {
        $xml = $this->zip->getFromName('META-INF/manifest.xml');

        if ($xml === false) {
            throw new RuntimeException('ODT manifest not found.');
        }

        $manifest = $this->loadXml($xml);

        $root = $manifest->documentElement;

        $entry = $manifest->createElementNS(
            self::NS_MANIFEST,
            'manifest:file-entry'
        );

        $entry->setAttributeNS(
            self::NS_MANIFEST,
            'manifest:full-path',
            $this->imagePath
        );

        $entry->setAttributeNS(
            self::NS_MANIFEST,
            'manifest:media-type',
            $this->imageMime
        );

        $root->appendChild($entry);

        if (!$this->zip->addFromString(
            'META-INF/manifest.xml',
            $manifest->saveXML()
        )) {
            throw new RuntimeException('Cannot update ODT manifest.');
        }
    }
}
