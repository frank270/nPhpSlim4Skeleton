<?php

namespace App\Services;

class I18nService
{
    protected array $translations = [];
    protected string $locale = 'zh-TW';
    protected string $langPath;

    public function __construct(string $langPath)
    {
        $this->langPath = $langPath;
        // 預設載入 zh-TW
        $this->loadTranslations('zh-TW');
    }

    public function loadTranslations(string $locale): void
    {
        $this->locale = $locale;
        $file = $this->langPath . '/' . $locale . '/layout.php';
        
        if (file_exists($file)) {
            $this->translations = require $file;
        } else {
            $this->translations = [];
        }
    }

    public function t(string $key, array $replace = []): string
    {
        $value = $this->get($key);

        foreach ($replace as $k => $v) {
            $value = str_replace(':' . $k, $v, $value);
        }

        return $value;
    }

    protected function get(string $key): string
    {
        $keys = explode('.', $key);
        $value = $this->translations;

        foreach ($keys as $segment) {
            if (isset($value[$segment])) {
                $value = $value[$segment];
            } else {
                return $key; // 如果找不到，回傳 key 本身
            }
        }

        return is_string($value) ? $value : $key;
    }
}
