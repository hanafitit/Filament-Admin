<?php

namespace App\Support\Uploads;

class OrderAttachmentUpload
{
    public static function acceptedFileTypes(): array
    {
        return config('uploads.order_attachments.accepted_file_types', []);
    }

    public static function acceptedExtensions(): array
    {
        return config('uploads.order_attachments.accepted_extensions', []);
    }

    public static function configuredMaxKilobytes(): int
    {
        return max(1, (int) config('uploads.order_attachments.max_size_kb', 50 * 1024));
    }

    public static function effectiveMaxKilobytes(): int
    {
        $serverLimit = static::serverMaxKilobytes();

        if ($serverLimit === null) {
            return static::configuredMaxKilobytes();
        }

        return min(static::configuredMaxKilobytes(), $serverLimit);
    }

    public static function uploadTooLargeMessage(): string
    {
        return sprintf(
            'Файл слишком большой. Максимальный размер: %s.',
            static::formatKilobytes(static::effectiveMaxKilobytes()),
        );
    }

    public static function invalidTypeMessage(): string
    {
        return sprintf(
            'Недопустимый формат файла. Разрешены: %s.',
            implode(', ', static::acceptedExtensions()),
        );
    }

    public static function helperText(): string
    {
        return sprintf(
            'Разрешены файлы %s. Максимальный размер: %s.',
            implode(', ', static::acceptedExtensions()),
            static::formatKilobytes(static::effectiveMaxKilobytes()),
        );
    }

    private static function serverMaxKilobytes(): ?int
    {
        $limits = array_filter([
            static::normalizeIniSizeToKilobytes(ini_get('upload_max_filesize')),
            static::normalizeIniSizeToKilobytes(ini_get('post_max_size')),
        ]);

        if ($limits === []) {
            return null;
        }

        return min($limits);
    }

    private static function normalizeIniSizeToKilobytes(string|false $value): ?int
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        $multiplier = match ($unit) {
            'g' => 1024 * 1024,
            'm' => 1024,
            'k' => 1,
            default => 1 / 1024,
        };

        $kilobytes = (int) ceil($number * $multiplier);

        return $kilobytes > 0 ? $kilobytes : null;
    }

    private static function formatKilobytes(int $kilobytes): string
    {
        if ($kilobytes >= 1024 * 1024) {
            return rtrim(rtrim(number_format($kilobytes / (1024 * 1024), 2, '.', ''), '0'), '.').' GB';
        }

        if ($kilobytes >= 1024) {
            return rtrim(rtrim(number_format($kilobytes / 1024, 2, '.', ''), '0'), '.').' MB';
        }

        return $kilobytes.' KB';
    }
}
