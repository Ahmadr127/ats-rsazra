<?php

namespace App\Console\Commands;

use App\Mail\TemplatedMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SmtpTestCommand extends Command
{
    protected $signature = 'ats:smtp-test
        {email : Recipient address for the test email}
        {--sync : Send synchronously instead of via the queue (worker)}
        {--from= : Override From address}
        {--from-name= : Override From name}';

    protected $description = 'Push a test email through the queue (worker) to verify full mail delivery.';

    public function handle(): int
    {
        $to = $this->argument('email');
        $mailer = config('mail.default');
        $queueConnection = config('queue.default');

        $this->line('Queue      : '.$queueConnection);
        $this->line('Mailer     : '.$mailer);
        $this->line('Host       : '.config("mail.mailers.{$mailer}.host", '—').':'.config("mail.mailers.{$mailer}.port", '—'));
        $this->line('From       : '.($this->option('from') ?? config('mail.from.address')));
        $this->line('Recipient  : '.$to);

        try {
            if ($this->option('sync')) {
                Mail::mailer($mailer)->raw('Test email dari perintah smtp-test ATS RS Azra. Waktu kirim: '.now(), function ($message) use ($to): void {
                    $message->to($to)->subject('Test SMTP ATS RS Azra '.now()->format('Y-m-d H:i:s'));
                    if ($from = $this->option('from')) {
                        $message->from($from, $this->option('from-name') ?: null);
                    }
                });

                $this->info('OK — email test terkirim langsung (sync) tanpa exception.');
                $this->line('Cek inbox/spam penerima '.$to.' untuk memastikan.');

                return self::SUCCESS;
            }

            Mail::to($to)->queue(new TemplatedMail(
                'Test SMTP ATS RS Azra '.now()->format('Y-m-d H:i:s'),
                'Test email dari perintah ats:smtp-test ATS RS Azra. Waktu dikeluarkan: '.now(),
                'smtp-test',
            ));

            $this->info('OK — email test masuk ke antrian "'.$queueConnection.'" (queue: jobs).');
            $this->line('Sekarang jalankan worker untuk mengirimnya:');
            $this->line('  php artisan queue:work --stop-when-empty');
            $this->line('Kalau gagal, cek failed_jobs / laravel.log untuk detail exception SMTP.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('GAGAL — '.get_class($e));
            $this->error($e->getMessage());
            $this->line('Periksa QUEUE_CONNECTION & MAIL_* di .env (default: database + smtp).');

            return self::FAILURE;
        }
    }
}
