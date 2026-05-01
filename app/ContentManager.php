<?php
declare(strict_types=1);

class ContentManager
{
    private PDO $pdo;
    private string $cacheDir;
    private int $cacheTTL = 3600; // 1 hour in seconds

    public function __construct(array $dbConfig, string $cacheDir = null)
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database'],
            $dbConfig['charset']
        );

        $this->pdo = new PDO(
            $dsn,
            $dbConfig['username'],
            $dbConfig['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $this->cacheDir = $cacheDir ?? dirname(__DIR__) . '/cache';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function getContent(string $sectionKey): string
    {
        $cacheFile = $this->getCacheFilePath($sectionKey);

        // Check cache first
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $this->cacheTTL)) {
            return file_get_contents($cacheFile);
        }

        // Fetch from database
        $stmt = $this->pdo->prepare("SELECT content FROM page_content WHERE section_key = ? AND active = 1");
        $stmt->execute([$sectionKey]);
        $result = $stmt->fetchColumn();

        if ($result === false) {
            return '';
        }

        // Cache the content
        file_put_contents($cacheFile, $result);

        return $result;
    }

    public function setContent(string $sectionKey, string $content): void
    {
        // Sanitize and validate content
        $sanitizedContent = $this->sanitizeContent($content);

        if (!$this->validateContent($sanitizedContent)) {
            throw new InvalidArgumentException('Content contains potentially malicious code and cannot be saved.');
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO page_content (section_key, content) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE content = VALUES(content)"
        );
        $stmt->execute([$sectionKey, $sanitizedContent]);

        // Invalidate cache
        $cacheFile = $this->getCacheFilePath($sectionKey);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    private function getCacheFilePath(string $sectionKey): string
    {
        return $this->cacheDir . '/' . md5($sectionKey) . '.cache';
    }

    public function clearCache(string $sectionKey = null): void
    {
        if ($sectionKey === null) {
            array_map('unlink', glob($this->cacheDir . '/*.cache'));
        } else {
            $cacheFile = $this->getCacheFilePath($sectionKey);
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        }
    }

    public function getAllContent(): array
    {
        $stmt = $this->pdo->prepare("SELECT section_key, content, updated_at, active FROM page_content ORDER BY updated_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleActive(string $sectionKey): bool
    {
        $stmt = $this->pdo->prepare("UPDATE page_content SET active = 1 - active WHERE section_key = ?");
        $stmt->execute([$sectionKey]);
        $success = $stmt->rowCount() > 0;
        if ($success) {
            // Clear cache for this section
            $this->clearCache($sectionKey);
        }
        return $success;
    }

    /**
     * Sanitize HTML content to prevent XSS attacks
     */
    private function sanitizeContent(string $content): string
    {
        // Allow only safe HTML tags and attributes
        $allowedTags = [
            'p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'a', 'span', 'div', 'img', 'table', 'thead', 'tbody',
            'tr', 'th', 'td', 'blockquote', 'code', 'pre', 'input'
        ];

        $allowedAttributes = [
            'href', 'target', 'rel', 'alt', 'src', 'width', 'height', 'style',
            'class', 'id', 'title', 'colspan', 'rowspan', 'type', 'min', 'value',
            'name', 'readonly'
        ];

        // Use DOMDocument for proper HTML sanitization
        $dom = new DOMDocument();
        $dom->loadHTML('<div>' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $this->sanitizeDOMNode($dom->documentElement, $allowedTags, $allowedAttributes);

        // Extract the inner HTML
        $result = '';
        foreach ($dom->documentElement->childNodes as $node) {
            $result .= $dom->saveHTML($node);
        }

        return $result;
    }

    /**
     * Recursively sanitize DOM nodes
     */
    private function sanitizeDOMNode(DOMNode $node, array $allowedTags, array $allowedAttributes): void
    {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tagName = strtolower($node->nodeName);

            // Remove disallowed tags
            if (!in_array($tagName, $allowedTags)) {
                $node->parentNode->removeChild($node);
                return;
            }

            // Remove disallowed attributes
            $attributesToRemove = [];
            foreach ($node->attributes as $attr) {
                if (!in_array(strtolower($attr->name), $allowedAttributes)) {
                    $attributesToRemove[] = $attr->name;
                } else {
                    // Sanitize attribute values
                    $attr->value = $this->sanitizeAttributeValue($attr->name, $attr->value);
                }
            }

            foreach ($attributesToRemove as $attrName) {
                $node->removeAttribute($attrName);
            }
        }

        // Recursively sanitize child nodes
        $childNodes = [];
        foreach ($node->childNodes as $child) {
            $childNodes[] = $child;
        }

        foreach ($childNodes as $child) {
            $this->sanitizeDOMNode($child, $allowedTags, $allowedAttributes);
        }
    }

    /**
     * Sanitize attribute values
     */
    private function sanitizeAttributeValue(string $attrName, string $value): string
    {
        switch (strtolower($attrName)) {
            case 'href':
                // Only allow safe URLs
                if (preg_match('/^(https?:\/\/|mailto:|tel:)/i', $value)) {
                    return $value;
                }
                return '#'; // Replace unsafe URLs with safe placeholder

            case 'src':
                // Only allow safe image sources
                if (preg_match('/^(https?:\/\/|data:image\/(png|jpg|jpeg|gif|webp);base64,)/i', $value)) {
                    return $value;
                }
                return ''; // Remove unsafe image sources

            case 'style':
                // Remove dangerous CSS properties
                $value = preg_replace('/(expression|javascript|vbscript|on\w+)/i', '', $value);
                return $value;

            default:
                // Remove any JavaScript event handlers or dangerous content
                $value = preg_replace('/(javascript|vbscript|on\w+):/i', '', $value);
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Validate content for potentially malicious patterns
     */
    private function validateContent(string $content): bool
    {
        // Check for dangerous patterns
        $dangerousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/<form/i',
            '/<meta/i',
            '/expression\s*\(/i',
            '/eval\s*\(/i',
            '/document\./i',
            '/window\./i',
            '/location\./i'
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return false;
            }
        }

        return true;
    }
}
?>
