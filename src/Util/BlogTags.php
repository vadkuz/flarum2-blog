<?php

namespace V17Development\FlarumBlog\Util;

final class BlogTags
{
    /**
     * Parse the `blog_tags` setting (stored like "1|2|3") to a clean list of tag IDs.
     *
     * @return int[]
     */
    public static function parseTagIds(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $ids = [];

        foreach (explode('|', $value) as $part) {
            $part = trim($part);

            if ($part === '' || !ctype_digit($part)) {
                continue;
            }

            $ids[] = (int) $part;
        }

        // Keep stable ordering while removing duplicates.
        $ids = array_values(array_unique($ids));

        return $ids;
    }
}

