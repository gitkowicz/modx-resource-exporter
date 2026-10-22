<?php
/**
 * ============================================================
 * MODX RESOURCE EXPORTER — ЭКСПОРТ РЕСУРСОВ В TXT
 * ============================================================
 *
 * СОВМЕСТИМОСТЬ:
 * MODX Revolution 2.8.x
 *
 * ПРОВЕРЕНО:
 * MODX Revolution 2.9.5
 *
 * MODX Revolution 3.x не заявлен как поддерживаемый.
 *
 * ЗАПУСК:
 * https://ВАШ-САЙТ.RU/export-resources.php?key=ВАШ_СЕКРЕТНЫЙ_КЛЮЧ
 *
 * РЕЗУЛЬТАТ:
 * /public_html/exported_txt/
 *
 * ЛОГ:
 * /public_html/exported_txt/export.log
 *
 * СОХРАНЯЕТ:
 * Содержимое поля content всех неудалённых ресурсов.
 *
 * БАЗУ ДАННЫХ НЕ ИЗМЕНЯЕТ.
 *
 * ВАЖНО:
 * 1. Укажите собственный секретный ключ.
 * 2. Используйте HTTPS.
 * 3. После работы удалите export-resources.php с сервера.
 * 4. Для полной резервной копии отдельно сохраните базу данных.
 *
 * ============================================================
 */

define('MODX_API_MODE', true);
require_once __DIR__ . '/index.php';

// 1. Секретный ключ
$secret = 'ВСТАВЬ_СЮДА_СВОЙ_СЕКРЕТНЫЙ_КЛЮЧ';

if (
    !isset($_GET['key']) ||
    !is_string($_GET['key']) ||
    !hash_equals($secret, $_GET['key'])
) {
    http_response_code(403);
    exit('Access denied');
}

// 2. Каталог экспорта
$exportDir = __DIR__ . '/exported_txt';
$logFile = $exportDir . '/export.log';

// 3. Создаём каталог
if (!is_dir($exportDir) && !mkdir($exportDir, 0755, true)) {
    error_log('ExportResourcesToTxt: не удалось создать ' . $exportDir);
    exit('ОШИБКА: не удалось создать каталог экспорта');
}

if (!is_writable($exportDir)) {
    exit('ОШИБКА: нет прав на запись в каталог экспорта');
}

// 4. Вывод сообщений в браузер и запись в лог
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

function exportLog($message, $logFile)
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;

    echo $line . "\n";

    $written = file_put_contents(
        $logFile,
        $line . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );

    if ($written === false) {
        error_log('ExportResourcesToTxt: не удалось записать лог');
    }

    if (ob_get_level() > 0) {
        @ob_flush();
    }

    flush();
}

// 5. Начало экспорта
exportLog('========================================', $logFile);
exportLog('Запуск экспорта ресурсов MODX', $logFile);
exportLog('Каталог экспорта: ' . $exportDir, $logFile);

$resources = $modx->getCollection('modResource', [
    'deleted' => 0,
]);

$total = count($resources);

exportLog('Найдено ресурсов: ' . $total, $logFile);

$success = 0;
$skipped = 0;
$errors = 0;
$number = 0;

// 6. Обрабатываем ресурсы
foreach ($resources as $resource) {
    $number++;

    $uri = trim((string)$resource->get('uri'), '/');
    $id = $resource->get('id');

    if ($uri === '') {
        $relativePath = 'index.txt';
    } else {
        $parts = explode('/', $uri);

        foreach ($parts as &$part) {
            $part = basename($part);

            $part = preg_replace(
                '/[^a-zA-Z0-9а-яА-ЯёЁ._-]/u',
                '_',
                $part
            );

            if ($part === '' || $part === '.' || $part === '..') {
                $part = '_';
            }
        }
        unset($part);

        $lastIndex = count($parts) - 1;

        $parts[$lastIndex] = preg_replace(
            '/\.[^.]+$/u',
            '',
            $parts[$lastIndex]
        );

        if ($parts[$lastIndex] === '') {
            $parts[$lastIndex] = 'page_' . $id;
        }

        $parts[$lastIndex] .= '.txt';
        $relativePath = implode(DIRECTORY_SEPARATOR, $parts);
    }

    $file = $exportDir . DIRECTORY_SEPARATOR . $relativePath;
    $dir = dirname($file);

    // Создание вложенных каталогов
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        $errors++;
        exportLog(
            "ОШИБКА [$number/$total] Не удалось создать каталог: $relativePath",
            $logFile
        );
        continue;
    }

    // Не перезаписываем существующие файлы
    if (file_exists($file)) {
        $skipped++;
        exportLog(
            "ПРОПУСК [$number/$total] $relativePath — файл уже существует",
            $logFile
        );
        continue;
    }

    // Читаем содержимое ресурса
    $content = (string)$resource->get('content');

    // Сохраняем файл
    $bytes = file_put_contents($file, $content, LOCK_EX);

    if ($bytes === false) {
        $errors++;
        exportLog(
            "ОШИБКА [$number/$total] Не удалось записать: $relativePath",
            $logFile
        );
        continue;
    }

    $success++;

    exportLog(
        "OK [$number/$total] $relativePath — записано байт: $bytes",
        $logFile
    );
}

// 7. Итог
exportLog('----------------------------------------', $logFile);
exportLog('ЭКСПОРТ ЗАВЕРШЁН', $logFile);
exportLog('Всего ресурсов: ' . $total, $logFile);
exportLog('Успешно сохранено: ' . $success, $logFile);
exportLog('Пропущено: ' . $skipped, $logFile);
exportLog('Ошибок: ' . $errors, $logFile);
exportLog('========================================', $logFile);