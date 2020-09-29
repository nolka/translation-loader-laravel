<?php

namespace TranslationLoader\Laravel\Writer;

use TranslationLoader\Data\DataRow;
use TranslationLoader\Writer\TranslationWriterInterface;

/**
 * Используется для записи переводов в базу
 * @package TranslationLoader\Laravel\Writer
 */
class DbWriter implements TranslationWriterInterface
{
    /** @var string Model class which is used to store translations */
    public string $modelClass = '\App\Models\LanguageLine';

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
    public function write(DataRow $dataRow): bool
    {
        $modelClass = $this->modelClass;
        $translation = $modelClass::firstOrNew(['key' => $this->prepareTranslation($dataRow->sourceValue), 'group' => $this->parseGroup($dataRow->sourceValue)]);
        $translation->setTranslation($dataRow->destLangCode, $dataRow->destValue);
        if ($translation->saveOrFail()) {
            $translation->flushGroupCache();
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function finalize(): void
    {
    }

    /**
     * Подготовить фразу для записи в базу. Если это литерал, то от него отрезается название группы, и возвращается сам литерал
     * @param string $value
     * @return string
     */
    public function prepareTranslation(string $value): string
    {
        if ($this->isLiteral($value)) {
            return mb_substr($value, mb_strpos($value, '.') + 1);
        }
        return $value;
    }

    /**
     * Парсит название группы
     * @param string $value
     * @return string
     */
    public function parseGroup(string $value): string
    {
        if ($this->isLiteral($value)) {
            return mb_substr($value, 0, mb_strpos($value, '.'));
        }
        return '*';
    }

    /**
     * Проверяет, является ли строка литералом
     * @param string $value
     * @return bool
     */
    public function isLiteral(string $value): bool
    {
        return str_contains($value, '.') && !str_contains($value, ' ');
    }
}
