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
        $stmt = $this->pdo->prepare("SELECT content FROM page_content WHERE section_key = ?");
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
        $stmt = $this->pdo->prepare(
            "INSERT INTO page_content (section_key, content) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE content = VALUES(content)"
        );
        $stmt->execute([$sectionKey, $content]);

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
        $stmt = $this->pdo->prepare("SELECT section_key, content, updated_at FROM page_content ORDER BY updated_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>