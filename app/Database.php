<?php
class Database
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        $this->pdo = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );
    }

    public function insertFormData(array $data): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO household_data (postcode, huisnummer, toevoeging, zonnepanelen, preset, verbruik, opwek, data_source, submitted_at)
             VALUES (:postcode, :huisnummer, :toevoeging, :zonnepanelen, :preset, :verbruik, :opwek, :data_source, NOW())"
        );

        $data_source = isset($data['preset']) ? 'preset' : 'custom';

        $stmt->execute([
            'postcode' => $data['postcode'] ?? '',
            'huisnummer' => $data['huisnummer'] ?? '',
            'toevoeging' => $data['toevoeging'] ?? '',
            'zonnepanelen' => isset($data['zonnepanelen']) ? 1 : 0,
            'preset' => $data['preset'] ?? null,
            'verbruik' => (int)($data['verbruik'] ?? 0),
            'opwek' => (int)($data['opwek'] ?? 0),
            'data_source' => $data_source,
        ]);
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}