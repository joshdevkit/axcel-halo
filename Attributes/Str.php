<?php

namespace Axcel\AxcelCore\Attributes;

class Str
{
    /**
     * Get the class basename.
     *
     * @param string $class The fully qualified class name
     * @return string The class name without the namespace
     */
    public static function classBasename($class)
    {
        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Convert a string to snake_case.
     *
     * @param string $value
     * @return string
     */
    public static function snake($value)
    {
        $value = preg_replace('/[A-Z]/', '_$0', $value);
        return strtolower(ltrim($value, '_'));
    }

    /**
     * Convert a string to camelCase.
     *
     * @param string $value
     * @return string
     */
    public static function camel($value)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value))));
    }

    /**
     * Pluralize a given word.
     *
     * @param string $word
     * @return string
     */
    public static function plural($word)
    {
        if (preg_match('/(s|x|z|ch|sh)$/i', $word)) {
            return $word . 'es';
        }

        return $word . 's';
    }

    /**
     * Singularize a given word.
     *
     * @param string $word
     * @return string
     */
    public static function singular($word)
    {
        // Handle words ending in 'ies' (e.g., 'flies' -> 'fly')
        if (preg_match('/ies$/i', $word)) {
            return preg_replace('/ies$/i', 'y', $word);
        }

        // Handle words ending in 'es' (e.g., 'boxes' -> 'box', 'buses' -> 'bus')
        if (preg_match('/(s|x|z|ch|sh)es$/i', $word)) {
            return preg_replace('/(s|x|z|ch|sh)es$/i', '$1', $word);
        }

        // Handle words ending in 's' (e.g., 'cats' -> 'cat', 'dogs' -> 'dog', 'users' -> 'user')
        // Note: We need to handle "s" separately to remove only plural forms like "users"
        if (preg_match('/s$/i', $word) && !preg_match('/ss$/i', $word)) {
            return preg_replace('/s$/i', '', $word);
        }

        // Handle words ending in 'ves' (e.g., 'wolves' -> 'wolf')
        if (preg_match('/ves$/i', $word)) {
            return preg_replace('/ves$/i', 'f', $word);
        }

        // Return the word unchanged if it doesn't match any of the above patterns
        return $word;
    }



    /**
     * Capitalize the first letter of each word in a string (title case).
     *
     * @param string $value
     * @return string
     */
    public static function titleCase($value)
    {
        return ucwords(strtolower($value));
    }

    /**
     * Convert a string to kebab-case.
     *
     * @param string $value
     * @return string
     */
    public static function kebab($value)
    {
        return strtolower(preg_replace('/[A-Z]/', '-$0', lcfirst($value)));
    }

    /**
     * Check if a string starts with a given substring.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }

    /**
     * Check if a string ends with a given substring.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }
}
