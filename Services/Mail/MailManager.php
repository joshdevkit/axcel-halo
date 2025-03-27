<?php

namespace App\Core\Services\Mail;

use App\Core\Autloader\Config;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class MailManager
{
    private static $instance;
    private Mailer $mailer;
    private Config $config;
    private array $globalFrom = [];

    private function __construct(Config $config)
    {
        $this->config = $config;
        $dsn = $this->buildDsn();
        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);

        // Set global from address if configured
        $fromEmail = $this->config->get('mail.from.address');
        $fromName = $this->config->get('mail.from.name');

        if ($fromEmail) {
            $this->globalFrom = [
                'email' => $fromEmail,
                'name' => $fromName ?? ''
            ];
        }
    }

    private function buildDsn(): string
    {
        $mailer = $this->config->get('mail.default', 'smtp');
        $mailConfig = $this->config->get("mail.mailers.{$mailer}", []);

        $driver = $mailConfig['transport'];
        $host = $mailConfig['host'];
        $port = $mailConfig['port'];
        $username = $mailConfig['username'];
        $password = $mailConfig['password'];
        $encryption = $mailConfig['encryption'];

        // Ensure username and password are not empty
        if (empty($username) || empty($password)) {
            throw new \Exception("SMTP authentication failed: Username or password is missing.");
        }

        return "{$driver}://{$username}:{$password}@{$host}:{$port}?encryption={$encryption}";
    }

    public static function getInstance(?Config $config = null): self
    {
        if (is_null(self::$instance)) {
            if (is_null($config)) {
                throw new \InvalidArgumentException('Config is required for the first initialization');
            }
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    public function send(Mailable $mailable): void
    {
        $email = $this->buildEmail($mailable);
        $this->mailer->send($email);
    }

    public function queue(Mailable $mailable): void
    {
        // In a real implementation, you would add this to a queue system
        // For now, we'll just send it directly
        $this->send($mailable);
    }

    private function buildEmail(Mailable $mailable): Email
    {
        // Get mail data
        $mailable->build();
        $envelope = $mailable->envelope();
        $content = $mailable->content();
        $attachments = $mailable->attachments();

        // Create email
        $email = new Email();

        // Set from
        if (!empty($mailable->from)) {
            $email->from(new Address($mailable->from['email'], $mailable->from['name'] ?? ''));
        } elseif (!empty($this->globalFrom)) {
            $email->from(new Address($this->globalFrom['email'], $this->globalFrom['name'] ?? ''));
        }

        // Set recipients
        foreach ($mailable->to as $recipient) {
            $email->addTo(new Address($recipient['email'], $recipient['name'] ?? ''));
        }

        if (!empty($mailable->cc)) {
            foreach ($mailable->cc as $recipient) {
                $email->addCc(new Address($recipient['email'], $recipient['name'] ?? ''));
            }
        }

        if (!empty($mailable->bcc)) {
            foreach ($mailable->bcc as $recipient) {
                $email->addBcc(new Address($recipient['email'], $recipient['name'] ?? ''));
            }
        }

        if (!empty($mailable->replyTo)) {
            foreach ($mailable->replyTo as $recipient) {
                $email->addReplyTo(new Address($recipient['email'], $recipient['name'] ?? ''));
            }
        }

        // Set subject from envelope
        $email->subject($envelope->subject);

        // Set content
        if ($content->view) {
            $html = app()->get('viewFactory')->make($content->view, $mailable->viewData)->getContent();
            $email->html($html);
        }

        if ($content->text) {
            // get the plain text view
            $text = app()->get('viewFactory')->make($content->text, $mailable->viewData)->getContent();
            $email->text($text);
        }

        // Add attachments
        foreach ($attachments as $attachment) {
            if ($attachment->path) {
                $email->attachFromPath($attachment->path, $attachment->name, $attachment->mime);
            } elseif ($attachment->data) {
                $email->attach($attachment->data, $attachment->name, $attachment->mime);
            }
        }

        return $email;
    }
}
