<?php
class FormHandler
{
    private Database $db;
    private Mailer $mailer;

    public function __construct(Database $db, Mailer $mailer)
    {
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function handle(array $post): void
    {
        error_log(date('Y-m-d H:i:s') . ' - Handling form');
        $this->db->insertFormData($post);
        error_log(date('Y-m-d H:i:s') . ' - Inserted');

        // Skipped mail for now
        // $this->mailer->send(
        //     $post['email'],
        //     'Your form submission',
        //     "Thank you! We received your data."
        // );
    }
}