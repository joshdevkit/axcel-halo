<?php


use App\Core\Application;
use App\Core\Attributes\Carbon;
use App\Core\Http\Redirect;
use App\Core\Http\Response;

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?? $default;
    }
}


if (!function_exists('view')) {
    function view(string $view, array $data = [], ?string $layout = null)
    {
        $engie = Application::getInstance()->make('viewFactory');

        if ($layout) {
            $engie->setLayout($layout);
        }

        return $engie->render($view, $data, true);
    }
}

if (!function_exists('json')) {
    function json(array $data)
    {
        return json_encode($data);
    }
}

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        return rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}




if (!function_exists('now')) {
    function now($timezone = null)
    {
        return Carbon::now($timezone);
    }
}

if (!function_exists('auth')) {
    function auth()
    {
        return app('auth');
    }
}


if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return session()->getCsrfToken();
    }
}

if (!function_exists('csrf')) {
    function csrf(): string
    {
        return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
    }
}

if (!function_exists('public_path')) {
    function public_path($path = '')
    {
        if ($path === 'temp') {
            return sys_get_temp_dir();
        }

        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' .
            ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}



if (!function_exists('asset')) {
    function asset($path)
    {
        $manifestPath = public_path('build/.vite/manifest.json');

        if (!file_exists($manifestPath)) {
            return $path;
        }

        $manifestContent = file_get_contents($manifestPath);
        $manifest = json_decode($manifestContent, true);

        $path = 'frontend/' . $path;

        if (isset($manifest[$path])) {
            return '/build/' . $manifest[$path]['file'];
        }

        return $path;
    }
}


if (!function_exists('class_basename')) {
    function class_basename($class)
    {
        return basename(str_replace('\\', '/', is_object($class) ? get_class($class) : $class));
    }
}


if (!function_exists('vite')) {
    function vite()
    {
        $manifestPath = public_path('build/.vite/manifest.json');
        // dd(file_get_contents($manifestPath));
        if (!file_exists($manifestPath)) {
            return '
            <script type="module" src="http://localhost:5173/@vite/client"></script>
            <link rel="stylesheet" href="http://localhost:5173/frontend/css/app.css">
            <script type="module" src="http://localhost:5173/frontend/js/app.js"></script>';
        }

        $manifestContent = file_get_contents($manifestPath);
        $manifest = json_decode($manifestContent, true);

        $cssFile = isset($manifest['frontend/js/app.js']['css'][0]) ? '/build/' . $manifest['frontend/js/app.js']['css'][0] : 'frontend/css/app.css';
        $jsFile = isset($manifest['frontend/js/app.js']['file']) ? '/build/' . $manifest['frontend/js/app.js']['file'] : 'frontend/js/app.js';

        return '
            <link rel="stylesheet" href="' . $cssFile . '">
            <script type="module" src="' . $jsFile . '"></script>';
    }
}



if (!function_exists('session')) {
    function session($key = null, $subkey = null, $default = null)
    {
        $session = app('session');

        if (is_null($key)) {
            return $session;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $session->set($k, $v);
            }
            return null;
        }

        $data = $session->get($key, $default);

        if ($key === 'old' && is_array($data)) {
            $session->remove('old');
        }

        if (!is_null($subkey) && is_array($data) && (is_string($subkey) || is_int($subkey)) && array_key_exists($subkey, $data)) {
            return $data[$subkey];
        }

        return $data;
    }
}




if (!function_exists('old')) {
    function old(string $key, $default = null)
    {
        $oldInput = session()->get('old', []);
        $value = $oldInput[$key] ?? $default;

        session()->set('old', []);

        return $value;
    }
}

if (!function_exists('request')) {
    /**
     * Get the request instance or a specific request parameter.
     * 
     * @param string|null $key The key to retrieve from request data.
     * @param mixed $default The default value if the key is not found.
     * @return mixed|\App\Core\Request
     */
    function request($key = null, $default = null)
    {
        $request = app('request');

        if (is_null($key)) {
            return $request;
        }

        return $request->get($key, $default);
    }
}



if (!function_exists('redirect')) {
    function redirect($url = null)
    {
        return new Redirect($url);
    }
}




if (!function_exists('response')) {
    function response(): Response
    {
        return new Response();
    }
}

if (!function_exists('app')) {
    /**
     * Get the available container instance or make a binding.
     *
     * @param  string|null  $abstract  Optional abstract type to resolve from the container
     * @param  array  $parameters  Optional parameters to pass to the resolver
     * @return mixed|\App\Core\Application
     */
    function app($abstract = null, array $parameters = [])
    {
        $app = Application::getInstance();

        if (is_null($abstract)) {
            return $app;
        }

        // If parameters are provided, we'll pass them to the container
        return empty($parameters)
            ? $app->get($abstract)
            : $app->make($abstract, $parameters);
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the full path to the storage directory.
     *
     * @param string $path Optional path to append to the storage directory.
     * @return string The full path to the storage directory.
     */
    function storage_path($path = '')
    {
        return rtrim(base_path('storage/'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}


if (!function_exists('back')) {
    function back()
    {
        return redirect()->back();
    }
}
