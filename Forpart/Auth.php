<?php

namespace App\Core\Forpart;

/**
 * Class Auth
 *
 * This class serves as a static proxy to the "auth" service, allowing
 * easy access to authentication-related methods, such as logging in,
 * logging out, and checking the user's authentication status.
 *
 * @package App\Core\Forpart
 *
 * @method static bool attempt(string $email, string $password) Attempt to authenticate a user
 * @method static void login(User $user) Log in a user and store user session
 * @method static void logout() Log out the current authenticated user
 * @method static bool check() Check if a user is authenticated
 * @method static User|null user() Get the currently authenticated user
 * @method static int|null id() Get the ID of the currently authenticated user
 * @method static bool guest() Check if the current user is a guest (not authenticated)
 */
class Auth extends Forpart
{
    /**
     * Get the accessor name for the "auth" service.
     *
     * @return string
     */
    protected static function getForpartAccessor()
    {
        return 'auth';
    }
}
