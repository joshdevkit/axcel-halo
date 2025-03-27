<?php

namespace App\Core\View;

use Axcel\Core\Bus\Container;

class ViewFactory
{
    private string $viewPath;
    private ?string $layout = null;
    private array $sections = [];
    private string $currentSection = '';
    private string $content = '';
    private array $sharedData = [];

    public function __construct(Container $container)
    {
        $this->viewPath = $container->make('viewPath');
    }

    /**
     * Render a view file with optional data.
     */
    public function render(string $view, array $data = [], bool $return = false): string
    {
        $viewFile = $this->resolveViewPath($view);

        if (!file_exists($viewFile)) {
            throw new \Exception("View file '{$viewFile}' not found.");
        }

        $data = array_merge($this->sharedData, $this->escapeData($data));
        extract($data);

        ob_start();
        require $viewFile;
        $this->content = ob_get_clean();

        return $this->layout ? $this->renderLayout() : $this->content;
    }

    /**
     * Create a new instance and render a view.
     */
    public function make(string $view, array $data = []): self
    {
        $clone = clone $this;
        $clone->content = $clone->render($view, $data);
        return $clone;
    }

    /**
     * Get the rendered content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set shared data available in all views.
     */
    public function withData(array $data): self
    {
        $this->sharedData = array_merge($this->sharedData, $this->escapeData($data));
        return $this;
    }

    /**
     * Set the layout for the view.
     */
    public function extends(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Start a new section.
     */
    /**
     * Start a new section or directly set a value.
     */
    public function section(string $name, ?string $content = null): void
    {
        if ($content !== null) {
            $this->sections[$name] = $content;
        } else {
            if (!empty($this->currentSection)) {
                throw new \Exception("Cannot start a new section '{$name}' before closing '{$this->currentSection}'.");
            }
            $this->currentSection = $name;
            ob_start();
        }
    }


    /**
     * End and save the current section.
     */
    public function endSection(): void
    {
        if (empty($this->currentSection)) {
            throw new \Exception("No active section to close.");
        }

        $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = '';
    }

    /**
     * Display a previously captured section.
     */
    public function yield(string $name): void
    {
        echo $this->sections[$name] ?? '';
    }

    /**
     * Clear all stored sections.
     */
    public function clearSections(): void
    {
        $this->sections = [];
    }

    /**
     * Check if a view file exists.
     */
    public function exists(string $view): bool
    {
        return file_exists($this->resolveViewPath($view));
    }

    /**
     * Render the layout file.
     */
    private function renderLayout(): string
    {
        $layoutFile = $this->resolveViewPath($this->layout);

        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout file '{$layoutFile}' not found.");
        }

        ob_start();
        require $layoutFile;
        return ob_get_clean();
    }

    /**
     * Resolve the full path of a view file.
     */
    private function resolveViewPath(string $view): string
    {
        return "{$this->viewPath}" . str_replace('.', '/', $view) . ".php";
    }

    /**
     * Escape output to prevent XSS.
     */
    private function escapeData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->escapeData($value);
            } elseif (is_string($value)) {
                $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        return $data;
    }
}
