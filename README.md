# MODX Resource Exporter v1.0

A simple PHP utility for exporting MODX Revolution resources into individual TXT files.

Простая PHP-утилита для экспорта ресурсов MODX Revolution в отдельные TXT-файлы.

## Features / Возможности

- 📄 Export resources to TXT files / Экспорт ресурсов в TXT-файлы
- 📁 Preserve URL directory structure / Сохранение структуры URL в каталогах
- 📊 Export progress and status messages / Отображение прогресса и статуса экспорта
- 📝 Detailed export log / Подробный лог экспорта
- 🛡️ Secret key protection / Защита секретным ключом

## Export Location / Где хранятся файлы

Exported files are stored in the `exported_txt` folder next to the script.

Экспортированные файлы сохраняются в папке `exported_txt` рядом со скриптом.

```
public_html/
├── export-resources.php
└── exported_txt/
    ├── index.txt
    ├── about.txt
    ├── contacts.txt
    └── export.log
```

## Usage / Использование

### English

1. Upload `export-resources.php` to your MODX root directory.
2. Set your secret key in the script.
3. Open the following URL in your browser:

   ```
   https://example.com/export-resources.php?key=YOUR_SECRET_KEY
   ```

4. Download the `exported_txt` folder using FTP.

### Русский

1. Загрузите `export-resources.php` в корневую папку сайта MODX.
2. Укажите секретный ключ в скрипте.
3. Откройте ссылку запуска в браузере.
4. Скачайте папку `exported_txt` через FTP.

## Logging / Логирование

Export results are recorded in `export.log`.

Результаты экспорта записываются в файл `export.log`.

## Requirements / Требования

- MODX Revolution 2.x
- Compatible PHP version

### Tested on / Протестировано на:

- MODX Revolution 2.9.5

> ⚠️ Compatibility with other versions has not been fully tested.
> Совместимость с другими версиями полностью не проверялась.

## Notes / Примечания

- The exporter saves only the resource content field. It does not modify the database or create a full website backup.
  Экспортируется только поле `content` ресурсов. Скрипт не изменяет базу данных и не создаёт полную резервную копию сайта.
- Use HTTPS and remove the PHP script from the server after exporting.
  Используйте HTTPS и удаляйте PHP-скрипт с сервера после завершения экспорта.
