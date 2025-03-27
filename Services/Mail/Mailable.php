<?php

namespace Axcel\AxcelCore\Services\Mail;

abstract class Mailable
{
    public array $to = [];
    public array $cc = [];
    public array $bcc = [];
    public array $replyTo = [];
    public array $from = [];
    public array $viewData = [];

    abstract public function envelope(): Envelope;
    abstract public function content(): Content;

    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this;
    }

    public function to($address, $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $email => $recipient) {
                if (is_string($email)) {
                    $this->to[] = ['email' => $email, 'name' => $recipient];
                } else {
                    $this->to[] = ['email' => $recipient, 'name' => null];
                }
            }
        } else {
            $this->to[] = ['email' => $address, 'name' => $name];
        }

        return $this;
    }

    public function cc($address, $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $email => $recipient) {
                if (is_string($email)) {
                    $this->cc[] = ['email' => $email, 'name' => $recipient];
                } else {
                    $this->cc[] = ['email' => $recipient, 'name' => null];
                }
            }
        } else {
            $this->cc[] = ['email' => $address, 'name' => $name];
        }

        return $this;
    }

    public function bcc($address, $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $email => $recipient) {
                if (is_string($email)) {
                    $this->bcc[] = ['email' => $email, 'name' => $recipient];
                } else {
                    $this->bcc[] = ['email' => $recipient, 'name' => null];
                }
            }
        } else {
            $this->bcc[] = ['email' => $address, 'name' => $name];
        }

        return $this;
    }

    public function replyTo($address, $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $email => $recipient) {
                if (is_string($email)) {
                    $this->replyTo[] = ['email' => $email, 'name' => $recipient];
                } else {
                    $this->replyTo[] = ['email' => $recipient, 'name' => null];
                }
            }
        } else {
            $this->replyTo[] = ['email' => $address, 'name' => $name];
        }

        return $this;
    }

    public function from($address, $name = null): self
    {
        $this->from = ['email' => $address, 'name' => $name];
        return $this;
    }

    public function with($key, $value = null): self
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }

        return $this;
    }
}

class Envelope
{
    public string $subject;

    public function __construct(string $subject)
    {
        $this->subject = $subject;
    }
}

class Content
{
    public ?string $view = null;
    public ?string $text = null;

    public function __construct(?string $view = null, ?string $text = null)
    {
        $this->view = $view;
        $this->text = $text;
    }
}

class Attachment
{
    public ?string $path = null;
    public ?string $data = null;
    public string $name;
    public string $mime;

    public static function fromPath(string $path, ?string $name = null, ?string $mime = null): self
    {
        $instance = new self();
        $instance->path = $path;
        $instance->name = $name ?? basename($path);
        $instance->mime = $mime ?? mime_content_type($path);
        return $instance;
    }

    public static function fromData(string $data, string $name, string $mime): self
    {
        $instance = new self();
        $instance->data = $data;
        $instance->name = $name;
        $instance->mime = $mime;
        return $instance;
    }
}
