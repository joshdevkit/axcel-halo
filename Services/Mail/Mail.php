<?php

namespace Axcel\AxcelCore\Services\Mail;

use Closure;

class Mail
{
    protected static array $to = [];
    protected static array $cc = [];
    protected static array $bcc = [];
    protected static array $replyTo = [];
    protected static ?Closure $viewDataCallback = null;

    public static function to($address, $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $email => $recipient) {
                if (is_string($email)) {
                    self::$to[] = ['email' => $email, 'name' => $recipient];
                } else {
                    self::$to[] = ['email' => $recipient, 'name' => null];
                }
            }
        } else {
            self::$to[] = ['email' => $address, 'name' => $name];
        }

        return new static();
    }

    public static function cc($address, $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $email => $recipient) {
                if (is_string($email)) {
                    self::$cc[] = ['email' => $email, 'name' => $recipient];
                } else {
                    self::$cc[] = ['email' => $recipient, 'name' => null];
                }
            }
        } else {
            self::$cc[] = ['email' => $address, 'name' => $name];
        }

        return new static();
    }

    public static function bcc($address, $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $email => $recipient) {
                if (is_string($email)) {
                    self::$bcc[] = ['email' => $email, 'name' => $recipient];
                } else {
                    self::$bcc[] = ['email' => $recipient, 'name' => null];
                }
            }
        } else {
            self::$bcc[] = ['email' => $address, 'name' => $name];
        }

        return new static();
    }

    public static function replyTo($address, $name = null): self
    {
        if (is_array($address)) {
            foreach ($address as $email => $recipient) {
                if (is_string($email)) {
                    self::$replyTo[] = ['email' => $email, 'name' => $recipient];
                } else {
                    self::$replyTo[] = ['email' => $recipient, 'name' => null];
                }
            }
        } else {
            self::$replyTo[] = ['email' => $address, 'name' => $name];
        }

        return new static();
    }

    public static function send(Mailable $mailable): void
    {
        self::applyForpartDataToMailable($mailable);
        app()->get('mail')->send($mailable);
        self::resetStaticProperties();
    }

    public static function queue(Mailable $mailable): void
    {
        self::applyForpartDataToMailable($mailable);
        app()->get('mail')->queue($mailable);
        self::resetStaticProperties();
    }

    public static function later(int $delay, Mailable $mailable): void
    {
        self::applyForpartDataToMailable($mailable);
        // In a real implementation, this would handle delayed sending
        // For now, we'll just queue it
        app()->get('mail')->queue($mailable);
        self::resetStaticProperties();
    }

    public static function raw(string $text, $callback): void
    {
        // Create a simple raw text email
        $mailable = new RawMailable($text);

        // Apply callback to configure mailable
        call_user_func($callback, $mailable);

        // Send the email
        app()->get('mail')->send($mailable);
    }

    public static function mailer(string $mailer = null)
    {
        return new static();
    }

    protected static function applyForpartDataToMailable(Mailable $mailable): void
    {
        if (!empty(self::$to)) {
            foreach (self::$to as $recipient) {
                $mailable->to($recipient['email'], $recipient['name']);
            }
        }

        if (!empty(self::$cc)) {
            foreach (self::$cc as $recipient) {
                $mailable->cc($recipient['email'], $recipient['name']);
            }
        }

        if (!empty(self::$bcc)) {
            foreach (self::$bcc as $recipient) {
                $mailable->bcc($recipient['email'], $recipient['name']);
            }
        }

        if (!empty(self::$replyTo)) {
            foreach (self::$replyTo as $recipient) {
                $mailable->replyTo($recipient['email'], $recipient['name']);
            }
        }

        if (self::$viewDataCallback instanceof Closure) {
            call_user_func(self::$viewDataCallback, $mailable);
        }
    }

    protected static function resetStaticProperties(): void
    {
        self::$to = [];
        self::$cc = [];
        self::$bcc = [];
        self::$replyTo = [];
        self::$viewDataCallback = null;
    }
}

class RawMailable extends Mailable
{
    protected $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notification'
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.raw'
        );
    }

    public function build()
    {
        return $this->with('content', $this->content);
    }
}
