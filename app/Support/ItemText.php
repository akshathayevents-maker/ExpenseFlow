<?php

namespace App\Support;

use Normalizer;

/**
 * Pure text helpers for inventory item names (English, Tamil, Tanglish).
 * Single source for every normalised/phonetic key stored in the database.
 *
 *  normalize()  lowercase, drop pack sizes ("25kg") and punctuation, keep Tamil letters AND vowel signs.
 *  phonetic()   spelling-tolerant key for Latin text: dhal=dal=daal, toor=tur, paruppu=parupu,
 *               sakkarai=sakarai. Tamil script is left untouched.
 *  skeleton()   consonant skeleton of one Latin word (parpu=paruppu -> "prp"); fuzzy tier only.
 */
final class ItemText
{
    private const LATIN_RULES = [
        // aspirated / digraph consonants
        'zh' => 'l', 'dh' => 'd', 'th' => 't', 'bh' => 'b', 'gh' => 'g', 'kh' => 'k',
        'ph' => 'p', 'sh' => 's', 'ch' => 'c', 'ck' => 'k', 'q' => 'k', 'w' => 'v',
        // long vowels
        'aa' => 'a', 'ee' => 'i', 'ii' => 'i', 'oo' => 'u', 'uu' => 'u',
    ];

    public static function normalize(?string $text): string
    {
        $t = (string) $text;
        if (class_exists(Normalizer::class)) {
            $t = Normalizer::normalize($t, Normalizer::FORM_C) ?: $t;
        }
        $t = mb_strtolower($t);
        $units = implode('|', array_map(fn ($u) => preg_quote($u, '/'), array_merge(
            array_keys(config('inventory.units', [])),
            array_keys(config('inventory.unit_aliases', []))
        )));
        if ($units !== '') {
            // pack sizes such as "25kg", "1 l", "500 gm"
            $t = preg_replace('/\b\d+(?:[.,]\d+)?\s*(?:'.$units.')\b/u', ' ', $t);
        }
        // keep letters + combining marks (Tamil vowel signs are \p{M}); everything else is a separator
        $t = preg_replace('/[^\p{L}\p{M}]+/u', ' ', $t);

        return trim(preg_replace('/\s+/u', ' ', $t));
    }

    public static function phonetic(?string $text): string
    {
        $words = explode(' ', self::normalize($text));

        return trim(implode(' ', array_map([self::class, 'phoneticWord'], $words)));
    }

    public static function phoneticWord(string $w): string
    {
        if ($w === '' || ! self::isLatin($w)) {
            return $w;
        }
        $w = strtr($w, self::LATIN_RULES);
        $w = preg_replace('/(.)\1+/u', '$1', $w);   // double letters: sakkarai -> sakarai
        if (strlen($w) > 3) {
            $w = preg_replace('/h$/', '', $w);      // trailing h: "dalh" -> "dal"
        }

        return $w;
    }

    public static function skeleton(string $phoneticWord): string
    {
        if (! self::isLatin($phoneticWord) || mb_strlen($phoneticWord) < 3) {
            return $phoneticWord;
        }
        $first = $phoneticWord[0];
        $rest = preg_replace('/[aeiouy]/', '', substr($phoneticWord, 1));

        return preg_replace('/(.)\1+/', '$1', $first.$rest);
    }

    /** Space-free form so "பாசிப்பருப்பு" equals "பாசி பருப்பு" and "toordal" equals "toor dal". */
    public static function compact(string $key): string
    {
        return str_replace(' ', '', $key);
    }

    public static function isLatin(string $w): bool
    {
        return (bool) preg_match('/^[a-z]+$/', $w);
    }

    public static function hasTamil(?string $text): bool
    {
        return (bool) preg_match('/\p{Tamil}/u', (string) $text);
    }
}
