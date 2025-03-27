<?php

namespace App\Core;

use App\Core\Auth\AuthManager;
use App\Core\Autloader\Config;
use App\Core\Autloader\EnvLoader;
use App\Core\Bus\Container;
use App\Core\Routing\Router;
use App\Core\Middleware\MiddlewareRegistry;
use App\Core\Eloquent\Database\Database;
use App\Core\Exceptions\NativeException;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\HasherPackage;
use App\Core\Services\Mail\MailManager;
use App\Core\Session\SessionManager;
use App\Core\View\ViewFactory;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;
use Whoops\Run;

class Application extends Container
{
    private static ?Application $instance = null;
    private Run $whoops;
    private string $basePath;

    private function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        static::$instance = $this;

        $this->configure();
    }

    /**
     * Create a new application instance
     * 
     * @param string $basePath
     * @return self
     */
    public static function create(string $basePath): self
    {
        return new self($basePath);
    }

    /**
     * Get the application instance
     * 
     * @return self
     */
    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            throw new \RuntimeException("Application has not been initialized. Call Application::create() first.");
        }

        return static::$instance;
    }

    /**
     * Configure the application
     * 
     * @return void
     */
    private function configure(): void
    {
        new EnvLoader();
        $this->bind(Request::class, fn() => Request::createFromGlobals());
        $this->bind('configPath', fn() => $this->basePath . '/config/', true);
        $this->bind('framework', fn() => $this->basePath . '/framework/', true);
        $this->bind('config', fn($container) => new Config($container), true);
        $this->bind('database', fn() => Database::getInstance(), true);
        $this->bind('viewPath', fn() => $this->basePath . '/frontend/views/', true);
        $this->bind('viewFactory', fn($container) => new ViewFactory($container), true);
        $this->bind('session', fn() => SessionManager::getInstance(), true);
        $this->bind('auth', fn() => AuthManager::getInstance(), true);
        $this->bind('mail', fn($container) => MailManager::getInstance($container->get('config')), true);
        $this->bind('directory', fn() => $this->basePath, true);
        $this->bind('hash', fn() => new HasherPackage(), true);
        $this->bind('requestStack', fn($container) => $container->get('session')->getRequestStack(), true);
        $this->bind('csrf', function ($container) {
            return new CsrfTokenManager(
                new UriSafeTokenGenerator(),
                new SessionTokenStorage($container->get('requestStack'))
            );
        }, true);
    }

    /**
     * Load route files
     * 
     * @param string|null $web Web routes file path
     * @param string|null $api API routes file path
     * @return self
     */
    public function loadRoutes(?string $web = null, ?string $api = null): self
    {
        $router = new Router($this);
        $this->bind('router', fn() => $router, true);

        if ($web !== null) {
            $router->load(base_path($web));
        }

        if ($api !== null) {
            $router->load(base_path($api));
        }

        return $this;
    }

    /**
     * Register middlewares with the application
     * 
     * @param array $middlewares
     * @return self
     */
    public function registerMiddlewares(array $middlewares): self
    {
        foreach ($middlewares as $name => $class) {
            MiddlewareRegistry::register($name, $class);
        }
        return $this;
    }

    /**
     * Boot the application
     * 
     * @return self
     */
    public function boot(): self
    {
        $this->whoops = $this->setupWhoops();
        $this->whoops->register();
        return $this;
    }

    /**
     * Run the application
     * 
     * @return void
     */
    public function run(): void
    {
        try {
            $this->bind('request', fn() => Request::capture(), true);
            $request = $this->make(Request::class);
            $router = $this->make('router');

            try {
                $response = $router->dispatch($request);
            } catch (\Exception $routerException) {
                throw new \RuntimeException(
                    $routerException->getMessage(),
                    $routerException->getCode(),
                    $routerException
                );
            }

            if (!$response instanceof Response) {
                $response = new Response($response);
            }

            $response->send();
        } catch (\Throwable $exception) {
            $this->handleException($exception);
        }
    }

    /**
     * Handle an exception
     * 
     * @param \Throwable $exception
     * @return void
     */
    private function handleException(\Throwable $exception): void
    {
        throw $exception;
    }

    /**
     * Set up the Whoops error handler
     * 
     * @return \Whoops\Run
     */
    private function setupWhoops(): \Whoops\Run
    {
        $whoops = new \Whoops\Run;

        // Check debug mode
        $isDebugMode = env('APP_DEBUG', false);
        if (is_string($isDebugMode)) {
            $isDebugMode = strtolower($isDebugMode) === 'true';
        }

        if ($isDebugMode) {
            $handler = new \Whoops\Handler\PrettyPageHandler();
            $handler->setPageTitle('Application Error');
            $whoops->pushHandler($handler);
        } else {
            $whoops->pushHandler(function ($exception) {
                $exc = new NativeException();
                echo $exc->render();
                return \Whoops\Handler\Handler::QUIT;
            });
        }
        return $whoops;
    }
}
