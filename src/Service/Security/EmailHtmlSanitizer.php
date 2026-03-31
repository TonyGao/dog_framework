<?php

namespace App\Service\Security;

class EmailHtmlSanitizer
{
    private const FORBIDDEN_ELEMENTS = [
        'script',
        'iframe',
        'object',
        'embed',
        'link',
        'meta',
        'base',
        'form',
        'input',
        'button',
        'textarea',
        'select',
        'option',
    ];

    public function sanitize(?string $html): string
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        // Prepend UTF-8 meta to force detection by DOMDocument
        $htmlForParsing = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"><html><body>' . $html . '</body></html>';
        $dom->loadHTML(
            $htmlForParsing,
            \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD | \LIBXML_NOERROR | \LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach (self::FORBIDDEN_ELEMENTS as $tagName) {
            $this->removeElementsByTagName($dom, $tagName);
        }

        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//*[@*]');
        if ($nodes !== false) {
            foreach ($nodes as $node) {
                if (!$node instanceof \DOMElement) {
                    continue;
                }

                $attributesToRemove = [];
                foreach ($node->attributes as $attribute) {
                    $name = mb_strtolower($attribute->nodeName);
                    $value = trim($attribute->nodeValue);

                    if (str_starts_with($name, 'on')) {
                        $attributesToRemove[] = $attribute->nodeName;
                        continue;
                    }

                    if (in_array($name, ['formaction', 'srcdoc'], true)) {
                        $attributesToRemove[] = $attribute->nodeName;
                        continue;
                    }

                    if (in_array($name, ['href', 'src', 'xlink:href'], true) && !$this->isAllowedUrl($value)) {
                        $attributesToRemove[] = $attribute->nodeName;
                        continue;
                    }

                    if ($name === 'style' && $this->containsDangerousCss($value)) {
                        $attributesToRemove[] = $attribute->nodeName;
                    }
                }

                foreach ($attributesToRemove as $attributeName) {
                    $node->removeAttribute($attributeName);
                }
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body instanceof \DOMElement) {
            return '';
        }

        $sanitizedHtml = '';
        foreach ($body->childNodes as $childNode) {
            $sanitizedHtml .= $dom->saveHTML($childNode);
        }

        return $sanitizedHtml;
    }

    private function removeElementsByTagName(\DOMDocument $dom, string $tagName): void
    {
        while (true) {
            $elements = $dom->getElementsByTagName($tagName);
            if ($elements->length === 0) {
                return;
            }

            $element = $elements->item(0);
            if (!$element instanceof \DOMNode || !$element->parentNode instanceof \DOMNode) {
                return;
            }

            $element->parentNode->removeChild($element);
        }
    }

    private function containsDangerousCss(string $value): bool
    {
        return (bool) preg_match('/expression\s*\(|url\s*\(\s*[\'"]?\s*javascript:|behavior\s*:|@import/i', $value);
    }

    private function isAllowedUrl(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        $decodedValue = html_entity_decode($value, \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
        if (preg_match('/^\s*#/', $decodedValue) === 1) {
            return true;
        }

        if (
            str_starts_with($decodedValue, '/') ||
            str_starts_with($decodedValue, './') ||
            str_starts_with($decodedValue, '../')
        ) {
            return true;
        }

        $parts = parse_url($decodedValue);
        if ($parts === false) {
            return false;
        }

        $scheme = isset($parts['scheme']) ? mb_strtolower($parts['scheme']) : null;
        if ($scheme === null) {
            return true;
        }

        if ($scheme === 'data') {
            return preg_match('/^data:image\/(gif|png|jpe?g|webp|svg\+xml);base64,[a-z0-9+\/=\s]+$/i', $decodedValue) === 1;
        }

        return in_array($scheme, ['http', 'https', 'mailto', 'tel', 'cid'], true);
    }
}
