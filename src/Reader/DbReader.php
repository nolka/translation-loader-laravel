<?php

declare(strict_types=1);

namespace TranslationLoader\Laravel\Reader;

use TranslationLoader\Reader\TranslationReaderInterface;
use TranslationLoader\Data\DataRow;
use Generator;

/**
 * Used for reading translations from laravel language_lines table
 * @package TranslationLoader\Laravel\Reader
 */
class DbReader implements TranslationReaderInterface
{
    /** @var string Model class which is used to store translations */
    public string $modelClass = '\App\Models\LanguageLine';

    /** @var string */
    protected string $filePath;

    /**
     * DbWriter constructor.
     * @param string|null $modelClass
     */
    public function __construct(?string $modelClass = null)
    {
        if (!empty($modelClass)) {
            $this->modelClass = $modelClass;
        }
    }

    /**
     * @inheritDoc
     */
    public function read(): Generator
    {
        $modelClass = $this->modelClass;
        $count = 10;
        $page = 1;

        do {
            $results = $modelClass::query()->forPage($page, $count)->get();

            $countResults = $results->count();

            if ($countResults == 0) {
                break;
            }

            foreach ($results as $result) {
                foreach ($this->buildDataRows($result->toArray()) as $dataRow) {
                    yield $dataRow;
                }
            }

            unset($results);

            $page++;
        } while ($countResults == $count);
    }

    /**
     * Generates translatable structures, yielding it
     * @param array $result
     * @return Generator
     */
    public function buildDataRows(array $result): Generator
    {
        foreach ($result['text'] as $langCode => $text) {
            $dataRow = new DataRow();
            $dataRow->sourceLangCode = static::CELL_SOURCE_NAME;
            if ($result['group'] == '*') {
                $dataRow->sourceValue = $result['key'];
            } else {
                $dataRow->sourceValue = "{$result['group']}.{$result['key']}";
            }
            $dataRow->destLangCode = $langCode;
            $dataRow->destValue = $text;
            yield $dataRow;
        }
    }
}
