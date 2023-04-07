<?php

class JobsImporter
{
    private PDO $db;

    private array $files;

    public function __construct(string $host, string $username, string $password, string $databaseName, array $files)
    {
        $this->files = $files;
        
        /* connect to DB */
        try {
            $this->db = new PDO('mysql:host=' . $host . ';dbname=' . $databaseName, $username, $password);
        } catch (Exception $e) {
            die('DB error: ' . $e->getMessage() . "\n");
        }
    }

    public function importJobs(): int
    {
        /* remove existing items */
        $this->db->exec('DELETE FROM job');

        $i = 0;

        if (count($this->files) === 0) {
            throw new Exception("No files to read");
        }

        foreach ($this->files as $file) {
            printMessage("Reading file: ". $file['file']);
            if  ($file['extension'] === 'json') {
                $i += $this->importJson($file['file']);
            } elseif ($file['extension'] === 'xml') {
                $i += $this->importXml($file['file']);
            }
        }
        /* parse XML file */
        return $i;
    }

    private function importXml(string $file): int
    {
        $xml = simplexml_load_file($file);

        $commands = 0;
        /* import each item */
        foreach ($xml->item as $item) {
            $commands += $this->db->exec('INSERT INTO job (reference, title, description, url, company_name, publication) VALUES ('
                . '\'' . addslashes($item->ref) . '\', '
                . '\'' . addslashes($item->title) . '\', '
                . '\'' . addslashes($item->description) . '\', '
                . '\'' . addslashes($item->url) . '\', '
                . '\'' . addslashes($item->company) . '\', '
                . '\'' . addslashes($item->pubDate) . '\')'
            );
        }
        return $commands;
    }

    private function importJson(string $file): int
    {
        $fileContent = json_decode(file_get_contents($file), true);
        $items = $fileContent["offers"];
        $prefix = $fileContent["offerUrlPrefix"];
        $commands = 0;

        foreach ($items as $item) {
            $date = DateTimeImmutable::createFromFormat('D M d H:i:s e Y', $item['publishedDate']);

            $commands += $this->db->exec('INSERT INTO job (reference, title, description, url, company_name, publication) VALUES ('
               . '\''. addslashes($item['reference']) . '\', '
               . '\''. addslashes($item['title']) . '\', '
               . '\''. addslashes($item['description']) . '\', '
               . '\''. addslashes($prefix . $item['urlPath']) . '\', '
               . '\''. addslashes($item['companyname']) . '\', '
               . '\''. $date->format('Y-m-d') . '\')');
        }
        return $commands;
    }
}