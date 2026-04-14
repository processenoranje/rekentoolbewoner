<?php
class Mailer
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $subject, string $message): bool
    {
        $headers = [
            'From: ' . $this->config['from_name'] .
            ' <' . $this->config['from_email'] . '>',
            'Content-Type: text/plain; charset=UTF-8',
        ];

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}