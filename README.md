# Translation Loader Laravel
This module is created for loading translations from xlsx files to Laravel translation model, and designed to work with 
[spatie/laravel-translation-loader](https://github.com/spatie/laravel-translation-loader) extension.

# Description

Sometimes you need to have possibility to unload yor database strings and translate it to many languages in single file. 
This package was created to unload translations to these languages in single xlsx file and back, from xlsx file to database.

# Translation file structure

| source                 | ru             | en              | another_lang_code |
|------------------------|----------------|-----------------|-------------------|
|Привет, мир!            |Привет, мир!    |Hello, world!    | ...               |
|validator.email_invalid |Неверный e-mail |Email is invalid | ...               |

# Installation
`composer require nolka/translation-loader-laravel`

# Usage

### Unload translations from database
```php
use TranslationLoader\Laravel\Reader\DbReader;
use TranslationLoader\TranslationManager;
use TranslationLoader\Writer\XlsxWriter;

$langs = [
    'ru' => 'Russian',
    'en' => 'English',
];

$manager = new TranslationManager($langs);

$reader = new DbReader();
$exportFile = base_path() . '/to_translate.xlsx';
if (file_exists($exportFile)) {
    unlink($exportFile);
}
$writer = new XlsxWriter($manager, $exportFile);

$manager->copyTranslations($reader, $writer);
```

### Load translations to database

```php
use TranslationLoader\Laravel\Writer\DbWriter;
use TranslationLoader\Reader\XlsxReader;
use TranslationLoader\TranslationManager;

$langs = [
    'ru' => 'Russian',
    'en' => 'English',
];

$manager = new TranslationManager($langs);

$reader = new XlsxReader($manager, base_path() . '/translated_messages.xlsx');
$writer = new DbWriter();


$manager->copyTranslations($reader, $writer);
```
