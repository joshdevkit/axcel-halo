<?php

namespace Axcel\AxcelCore\Services\Mail;


trait Queueable
{
    /**
     * The name of the queue connection to use.
     */
    public ?string $connection = null;

    /**
     * The name of the queue to use.
     */
    public ?string $queue = null;

    /**
     * The number of seconds to delay the delivery.
     */
    public ?int $delay = null;

    /**
     * The number of times to attempt sending.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait between retries.
     */
    public int $retryAfter = 60;

    /**
     * Set the desired connection for the job.
     */
    public function onConnection(?string $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Set the desired queue for the job.
     */
    public function onQueue(?string $queue): self
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * Set the desired delay for the job.
     */
    public function delay(?int $delay): self
    {
        $this->delay = $delay;
        return $this;
    }

    /**
     * Set the number of times to attempt sending.
     */
    public function tries(int $tries, int $retryAfter = 60): self
    {
        $this->tries = $tries;
        $this->retryAfter = $retryAfter;
        return $this;
    }
}
