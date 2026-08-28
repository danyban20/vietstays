<?php

namespace App\Support;

use Illuminate\Support\Str;
use RuntimeException;

class LegacySqlParser
{
    public function __construct(
        protected string $sqlPath,
    ) {}

    public static function default(): self
    {
        return new self(base_path('db/vietstays.sql'));
    }

    public function sqlPath(): string
    {
        return $this->sqlPath;
    }

    public function contents(): string
    {
        if (! is_readable($this->sqlPath)) {
            throw new RuntimeException("Legacy SQL dump not found at [{$this->sqlPath}].");
        }

        return file_get_contents($this->sqlPath);
    }

    /**
     * @return array<string, string> table name => CREATE TABLE SQL
     */
    public function extractCreateTableStatements(string $tablePrefix = 'vv_'): array
    {
        $statements = [];
        $pattern = '/CREATE TABLE `('.preg_quote($tablePrefix, '/').'[^`]+)`\s*\((.*?)\)\s*ENGINE=/si';

        if (preg_match_all($pattern, $this->contents(), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $table = $match[1];
                $statements[$table] = "CREATE TABLE `{$table}` ({$match[2]}) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            }
        }

        return $statements;
    }

    /**
     * @return list<string>
     */
    public function extractCreateTableNames(string $tablePrefix = 'vv_'): array
    {
        return array_keys($this->extractCreateTableStatements($tablePrefix));
    }

    /**
     * @return array<string, list<string>> table name => INSERT statements
     */
    public function extractInsertStatements(string $tablePrefix = 'vv_'): array
    {
        $statements = [];
        $contents = $this->contents();
        $pattern = '/INSERT INTO `('.preg_quote($tablePrefix, '/').'[^`]+)`/i';

        if (! preg_match_all($pattern, $contents, $matches, PREG_OFFSET_CAPTURE)) {
            return $statements;
        }

        foreach ($matches[1] as $index => $tableMatch) {
            $table = $tableMatch[0];
            $start = $matches[0][$index][1];
            $statementEnd = $this->findStatementTerminator($contents, $start);

            if ($statementEnd === null) {
                continue;
            }

            $statement = substr($contents, $start, $statementEnd - $start + 1);
            $statements[$table] ??= [];
            $statements[$table][] = $statement;
        }

        return $statements;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function parseGyhPosts(string $postType): array
    {
        $posts = [];

        foreach ($this->extractInsertStatements('gyh_')['gyh_posts'] ?? [] as $statement) {
            foreach ($this->extractInsertValues($statement) as $row) {
                if (($row[20] ?? null) !== $postType) {
                    continue;
                }

                $posts[(int) $row[0]] = [
                    'ID' => (int) $row[0],
                    'post_author' => (int) $row[1],
                    'post_date' => $row[2],
                    'post_title' => $row[5],
                    'post_status' => $row[7],
                    'post_name' => $row[11],
                    'post_modified' => $row[14] ?? $row[2],
                    'post_parent' => (int) $row[17],
                    'post_type' => $row[20],
                ];
            }
        }

        return $posts;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function parseGyhPostmeta(): array
    {
        $meta = [];

        foreach ($this->extractInsertStatements('gyh_')['gyh_postmeta'] ?? [] as $statement) {
            foreach ($this->extractInsertValues($statement) as $row) {
                $postId = (int) $row[1];
                $key = $row[2];
                $value = $row[3];

                if (Str::startsWith($key, '_')) {
                    continue;
                }

                $meta[$postId][$key] = $value;
            }
        }

        return $meta;
    }

    /**
     * @return array<string, string|null>
     */
    public function parseGyhOptions(array $names = []): array
    {
        $options = [];

        foreach ($this->extractInsertStatements('gyh_')['gyh_options'] ?? [] as $statement) {
            foreach ($this->extractInsertValues($statement) as $row) {
                $name = $row[1] ?? null;

                if ($name === null) {
                    continue;
                }

                if ($names !== [] && ! in_array($name, $names, true)) {
                    continue;
                }

                $options[$name] = $row[2] ?? null;
            }
        }

        return $options;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function parseGyhUsers(): array
    {
        $users = [];

        foreach ($this->extractInsertStatements('gyh_')['gyh_users'] ?? [] as $statement) {
            foreach ($this->extractInsertValues($statement) as $row) {
                $users[] = [
                    'ID' => (int) $row[0],
                    'user_login' => $row[1],
                    'user_pass' => $row[2],
                    'user_email' => $row[4],
                    'user_registered' => $row[6],
                    'display_name' => $row[9],
                ];
            }
        }

        return $users;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function parseGyhUsermeta(): array
    {
        $meta = [];

        foreach ($this->extractInsertStatements('gyh_')['gyh_usermeta'] ?? [] as $statement) {
            foreach ($this->extractInsertValues($statement) as $row) {
                $userId = (int) $row[1];
                $meta[$userId][$row[2]] = $row[3];
            }
        }

        return $meta;
    }

    /**
     * @return list<array<int, string|null>>
     */
    public function extractInsertValues(string $insertStatement): array
    {
        if (! preg_match('/INSERT INTO `[^`]+`(?:\s*\([^)]+\))?\s+VALUES\s+(.*)\s*;?\s*$/is', $insertStatement, $match)) {
            return [];
        }

        return $this->parseSqlTuples(rtrim($match[1], ";\r\n"));
    }

    /**
     * @return list<array<int, string|null>>
     */
    public function parseSqlTuples(string $payload): array
    {
        $rows = [];
        $length = strlen($payload);
        $index = 0;

        while ($index < $length) {
            while ($index < $length && in_array($payload[$index], [',', ' ', "\r", "\n", "\t"], true)) {
                $index++;
            }

            if ($index >= $length) {
                break;
            }

            if ($payload[$index] !== '(') {
                break;
            }

            [$values, $index] = $this->parseSqlTuple($payload, $index);
            $rows[] = $values;
        }

        return $rows;
    }

    /**
     * @return array{0: array<int, string|null>, 1: int}
     */
    protected function parseSqlTuple(string $payload, int $index): array
    {
        $values = [];
        $index++;
        $current = '';
        $inString = false;
        $escaped = false;
        $length = strlen($payload);

        while ($index < $length) {
            $char = $payload[$index];

            if ($inString) {
                if ($escaped) {
                    $current .= $char;
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === "'") {
                    $inString = false;
                } else {
                    $current .= $char;
                }

                $index++;

                continue;
            }

            if ($char === "'") {
                $inString = true;
                $index++;

                continue;
            }

            if ($char === ',') {
                $values[] = $this->normalizeSqlValue($current);
                $current = '';
                $index++;

                continue;
            }

            if ($char === ')') {
                $values[] = $this->normalizeSqlValue($current);
                $index++;

                if ($index < $length && $payload[$index] === ',') {
                    $index++;
                }

                break;
            }

            $current .= $char;
            $index++;
        }

        return [$values, $index];
    }

    protected function normalizeSqlValue(string $value): ?string
    {
        $value = trim($value);

        if ($value === '' || strtoupper($value) === 'NULL') {
            return null;
        }

        return $value;
    }

    protected function findStatementTerminator(string $contents, int $start): ?int
    {
        $length = strlen($contents);
        $inString = false;
        $escaped = false;

        for ($index = $start; $index < $length; $index++) {
            $char = $contents[$index];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === "'") {
                    $inString = false;
                }

                continue;
            }

            if ($char === "'") {
                $inString = true;

                continue;
            }

            if ($char === ';') {
                return $index;
            }
        }

        return null;
    }

    public function unserializeMeta(?string $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (Str::startsWith($value, 'a:') || Str::startsWith($value, 's:') || Str::startsWith($value, 'i:')) {
            $unserialized = @unserialize($value, ['allowed_classes' => false]);

            return $unserialized === false && $value !== 'b:0;' ? null : $unserialized;
        }

        return $value;
    }
}
