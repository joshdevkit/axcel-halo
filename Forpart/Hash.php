<?php

namespace App\Core\Forpart;

/**
 * @method static array info(string $hashedValue)
 * @method static string make(string $value, array $options = [])
 * @method static bool check(string $value, string $hashedValue, array $options = [])
 * @method static bool needsRehash(string $hashedValue, array $options = [])
 * @method static bool verifyConfiguration(string $value)
 * @method static void setRounds(int $rounds)
 */
class Hash extends Forpart
{
    /**
     * Get the accessor name for the "hash" service.
     *
     * @return string
     */
    protected static function getForpartAccessor()
    {
        return 'hash';
    }
}
