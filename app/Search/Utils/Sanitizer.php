<?php

namespace App\Search\Utils;

final readonly class Sanitizer
{
    /**
     * Sanitize a search term for use in FULLTEXT searches.
     */
    public static function searchTerm(string $term): string
    {
        // Remove MySQL boolean operators that could be used maliciously
        $term = preg_replace('/[-+<>()~*"]/', ' ', $term);

        // Remove multiple spaces
        $term = preg_replace('/\s+/', ' ', $term);

        // Trim and add wildcard
        $term = trim($term).'*';

        return $term;
    }
}
