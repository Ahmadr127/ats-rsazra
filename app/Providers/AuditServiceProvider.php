<?php

namespace App\Providers;

use App\Mail\TemplatedMail;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Queue\Events\JobReleasedAfterException;
use Illuminate\Queue\Events\JobRetryRequested;
use Illuminate\Queue\Events\JobTimedOut;
use Illuminate\Queue\Events\QueueBusy;
use Illuminate\Queue\Events\QueueFailedOver;
use Illuminate\Queue\Events\WorkerStarting;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

/**
 * Wires framework and Eloquent events into the audit stream.
 *
 * Everything is wildcard/observer based, so new models, queued jobs, mailables
 * and custom App\Events classes are captured without touching this file.
 */
class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerModelListeners();
        $this->registerAuthListeners();
        $this->registerQueueListeners();
        $this->registerWorkerListeners();
        $this->registerMailListeners();
        $this->registerScheduleListeners();
        $this->registerCustomEventListeners();
    }

    private function registerModelListeners(): void
    {
        Event::listen('eloquent.created: *', function (string $eventName, array $payload): void {
            $model = $payload[0] ?? null;

            if ($model instanceof Model && ! $this->isExcludedModel($model)) {
                AuditLogger::model('created', $model, null, $model->getAttributes(), 'created');
            }
        });

        Event::listen('eloquent.updated: *', function (string $eventName, array $payload): void {
            $model = $payload[0] ?? null;

            if (! $model instanceof Model || $this->isExcludedModel($model)) {
                return;
            }

            $changes = $model->getChanges();

            if ($changes === []) {
                return;
            }

            // getOriginal() still holds the pre-update values during "updated".
            $original = array_intersect_key($model->getOriginal(), $changes);

            AuditLogger::model('updated', $model, $original, $changes, 'updated');
        });

        Event::listen('eloquent.deleted: *', function (string $eventName, array $payload): void {
            $model = $payload[0] ?? null;

            if ($model instanceof Model && ! $this->isExcludedModel($model)) {
                AuditLogger::model('deleted', $model, $model->getAttributes(), null, 'deleted');
            }
        });
    }

    private function registerAuthListeners(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            AuditLogger::record('auth', 'auth.login', [
                'status' => 'success',
                'severity' => 'info',
                'auditable_type' => Model::class,
                'auditable_id' => $event->user->getAuthIdentifier(),
                'subject' => $event->user->name ?? null,
                'context' => ['guard' => $event->guard, 'remember' => $event->remember],
            ]);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            AuditLogger::record('auth', 'auth.logout', [
                'status' => 'success',
                'severity' => 'info',
                'auditable_type' => Model::class,
                'auditable_id' => $event->user?->getAuthIdentifier(),
                'subject' => $event->user?->name,
                'context' => ['guard' => $event->guard],
            ]);
        });

        Event::listen(Failed::class, function (Failed $event): void {
            AuditLogger::record('auth', 'auth.failed', [
                'status' => 'failed',
                'severity' => 'notice',
                'subject' => $event->credentials['username'] ?? $event->credentials['email'] ?? null,
                'context' => ['guard' => $event->guard],
            ]);
        });

        Event::listen(Lockout::class, function (Lockout $event): void {
            AuditLogger::record('auth', 'auth.lockout', [
                'status' => 'locked',
                'severity' => 'warning',
                'method' => $event->request->method(),
                'url' => $event->request->fullUrl(),
            ]);
        });

        Event::listen(PasswordReset::class, function (PasswordReset $event): void {
            AuditLogger::record('auth', 'auth.password-reset', [
                'status' => 'success',
                'severity' => 'info',
                'auditable_type' => Model::class,
                'auditable_id' => $event->user->getAuthIdentifier(),
                'subject' => $event->user->name ?? null,
            ]);
        });

        Event::listen(Registered::class, function (Registered $event): void {
            AuditLogger::record('auth', 'auth.registered', [
                'status' => 'success',
                'severity' => 'info',
                'auditable_type' => Model::class,
                'auditable_id' => $event->user->getAuthIdentifier(),
                'subject' => $event->user->name ?? null,
            ]);
        });
    }

    private function registerQueueListeners(): void
    {
        Event::listen(JobQueued::class, function (JobQueued $event): void {
            AuditLogger::record('queue', 'job.queued', [
                'status' => 'queued',
                'subject' => static::displayableJob($event->job),
                'context' => [
                    'connection' => $event->connectionName,
                    'queue' => $event->queue,
                    'job_id' => $event->id,
                    'delay' => $event->delay,
                ],
            ]);
        });

        Event::listen(JobProcessing::class, function (JobProcessing $event): void {
            AuditLogger::setMailableContext(static::mailableContextFromJob($event->job));
            AuditLogger::startJobTimer(static::jobTimerKey($event->job));

            AuditLogger::record('queue', 'job.processing', [
                'status' => 'processing',
                'subject' => static::displayableJob($event->job),
                'context' => static::jobContext($event->job),
            ]);
        });

        Event::listen(JobProcessed::class, function (JobProcessed $event): void {
            AuditLogger::record('queue', 'job.processed', [
                'status' => 'processed',
                'subject' => static::displayableJob($event->job),
                'duration_ms' => AuditLogger::jobDuration(static::jobTimerKey($event->job)),
                'context' => static::jobContext($event->job),
            ]);

            AuditLogger::setMailableContext(null);
        });

        Event::listen(JobExceptionOccurred::class, function (JobExceptionOccurred $event): void {
            AuditLogger::record('queue', 'job.exception', [
                'status' => 'exception',
                'severity' => 'warning',
                'subject' => static::displayableJob($event->job),
                'context' => array_merge(static::jobContext($event->job), [
                    'exception' => $event->exception,
                ]),
            ]);
        });

        Event::listen(JobReleasedAfterException::class, function (JobReleasedAfterException $event): void {
            AuditLogger::record('queue', 'job.released', [
                'status' => 'released',
                'severity' => 'warning',
                'subject' => static::displayableJob($event->job),
                'duration_ms' => AuditLogger::jobDuration(static::jobTimerKey($event->job)),
                'context' => array_merge(static::jobContext($event->job), [
                    'backoff' => $event->backoff,
                ]),
            ]);
        });

        Event::listen(JobFailed::class, function (JobFailed $event): void {
            $context = array_merge(static::jobContext($event->job), [
                'exception' => $event->exception,
            ]);

            AuditLogger::record('queue', 'job.failed', [
                'status' => 'failed',
                'severity' => 'error',
                'subject' => static::displayableJob($event->job),
                'duration_ms' => AuditLogger::jobDuration(static::jobTimerKey($event->job)),
                'context' => $context,
            ]);

            $mailable = static::resolveMailable($event->job);

            if ($mailable !== null) {
                AuditLogger::record('mail', 'email.failed', [
                    'status' => 'failed',
                    'severity' => 'error',
                    'subject' => static::mailableSubject($mailable),
                    'context' => array_merge(static::mailableContext($mailable), [
                        'exception' => $event->exception,
                    ]),
                ]);
            }

            AuditLogger::setMailableContext(null);
        });

        Event::listen(JobTimedOut::class, function (JobTimedOut $event): void {
            AuditLogger::record('queue', 'job.timed_out', [
                'status' => 'timed_out',
                'severity' => 'error',
                'subject' => static::displayableJob($event->job),
                'context' => static::jobContext($event->job),
            ]);
        });

        Event::listen(JobRetryRequested::class, function (JobRetryRequested $event): void {
            $payload = $event->payload();

            AuditLogger::record('queue', 'job.retry_requested', [
                'status' => 'retry',
                'severity' => 'warning',
                'subject' => $payload['displayName'] ?? null,
                'context' => [
                    'job_id' => $event->job->id ?? null,
                    'delay' => $event->job->delay ?? null,
                    'payload' => $payload,
                ],
            ]);
        });

        Event::listen(QueueFailedOver::class, function (QueueFailedOver $event): void {
            AuditLogger::record('queue', 'queue.failed_over', [
                'status' => 'failed',
                'severity' => 'error',
                'subject' => $event->connectionName,
                'context' => [
                    'command' => static::displayableJob($event->command),
                    'exception' => $event->exception,
                ],
            ]);
        });

        Event::listen(QueueBusy::class, function (QueueBusy $event): void {
            AuditLogger::record('queue', 'queue.busy', [
                'status' => 'busy',
                'severity' => 'warning',
                'subject' => $event->queue,
                'context' => [
                    'connection' => $event->connection,
                    'queue' => $event->queue,
                    'size' => $event->size ?? null,
                ],
            ]);
        });
    }

    private function registerWorkerListeners(): void
    {
        Event::listen(WorkerStarting::class, function (WorkerStarting $event): void {
            $options = $event->workerOptions;

            AuditLogger::record('worker', 'worker.started', [
                'status' => 'started',
                'subject' => $event->queue,
                'context' => [
                    'connection' => $event->connectionName,
                    'queue' => $event->queue,
                    'name' => $options?->name,
                    'sleep' => $options?->sleep,
                    'timeout' => $options?->timeout,
                    'tries' => $options?->maxTries,
                    'memory' => $options?->memory,
                    'backoff' => $options?->backoff,
                    'rest' => $options?->rest,
                ],
            ]);
        });

        Event::listen(WorkerStopping::class, function (WorkerStopping $event): void {
            AuditLogger::record('worker', 'worker.stopped', [
                'status' => 'stopped',
                'context' => [
                    'status' => $event->status,
                    'name' => $event->workerOptions?->name,
                ],
            ]);
        });
    }

    private function registerMailListeners(): void
    {
        Event::listen(MessageSending::class, function (MessageSending $event): void {
            $key = spl_object_id($event->message);

            AuditLogger::startMailTimer($key);

            AuditLogger::record('mail', 'email.sending', [
                'status' => 'sending',
                'subject' => static::mailSubject($event->message),
                'context' => array_merge(static::mailContext($event->message), [
                    'template_key' => AuditLogger::mailableContext(),
                ]),
            ]);
        });

        Event::listen(MessageSent::class, function (MessageSent $event): void {
            $message = $event->message;
            $key = spl_object_id($message);

            AuditLogger::record('mail', 'email.sent', [
                'status' => 'sent',
                'subject' => static::mailSubject($message),
                'duration_ms' => AuditLogger::mailDuration($key),
                'context' => array_merge(static::mailContext($message), [
                    'template_key' => AuditLogger::mailableContext(),
                    'message_id' => $event->sent->getMessageId(),
                ]),
            ]);
        });
    }

    private function registerScheduleListeners(): void
    {
        Event::listen(ScheduledTaskStarting::class, function (ScheduledTaskStarting $event): void {
            AuditLogger::record('schedule', 'schedule.started', [
                'status' => 'started',
                'subject' => static::taskName($event->task),
                'context' => ['mutex' => $event->task->mutexName()],
            ]);
        });

        Event::listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event): void {
            AuditLogger::record('schedule', 'schedule.finished', [
                'status' => 'finished',
                'subject' => static::taskName($event->task),
                'duration_ms' => (int) round($event->runtime * 1000),
                'context' => ['mutex' => $event->task->mutexName()],
            ]);
        });

        Event::listen(ScheduledTaskFailed::class, function (ScheduledTaskFailed $event): void {
            AuditLogger::record('schedule', 'schedule.failed', [
                'status' => 'failed',
                'severity' => 'error',
                'subject' => static::taskName($event->task),
                'context' => [
                    'mutex' => $event->task->mutexName(),
                    'exception' => $event->exception,
                ],
            ]);
        });

        Event::listen(ScheduledTaskSkipped::class, function (ScheduledTaskSkipped $event): void {
            AuditLogger::record('schedule', 'schedule.skipped', [
                'status' => 'skipped',
                'severity' => 'warning',
                'subject' => static::taskName($event->task),
                'context' => ['mutex' => $event->task->mutexName()],
            ]);
        });
    }

    private function registerCustomEventListeners(): void
    {
        Event::listen('App\Events\*', function (string $eventName, array $payload): void {
            AuditLogger::record('event', 'custom.'.Str::afterLast($eventName, '\\'), [
                'status' => 'dispatched',
                'subject' => $eventName,
                'context' => $payload,
            ]);
        });
    }

    private function isExcludedModel(Model $model): bool
    {
        foreach ((array) config('audit.excluded_models', []) as $class) {
            if ($model instanceof $class) {
                return true;
            }
        }

        return false;
    }

    private static function displayableJob(mixed $job): ?string
    {
        if (is_object($job)) {
            return get_class($job);
        }

        return is_string($job) ? $job : null;
    }

    private static function jobTimerKey(JobContract $job): string
    {
        return implode('|', [
            $job->getConnectionName(),
            $job->getQueue(),
            $job->getJobId(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function jobContext(JobContract $job): array
    {
        return [
            'connection' => $job->getConnectionName(),
            'queue' => $job->getQueue(),
            'job_id' => $job->getJobId(),
            'attempts' => $job->attempts(),
            'resolved' => static::displayableJob(static::resolveJobInstance($job)),
        ];
    }

    private static function resolveJobInstance(JobContract $job): mixed
    {
        if (! method_exists($job, 'getResolvedJob')) {
            return null;
        }

        try {
            return $job->getResolvedJob();
        } catch (Throwable) {
            return null;
        }
    }

    private static function resolveMailable(JobContract $job): ?object
    {
        $resolved = static::resolveJobInstance($job);

        if ($resolved instanceof SendQueuedMailable && isset($resolved->mailable)) {
            return $resolved->mailable;
        }

        return null;
    }

    private static function mailableContextFromJob(JobContract $job): ?string
    {
        try {
            $payload = $job->payload();
        } catch (Throwable) {
            return null;
        }

        $command = $payload['data']['command'] ?? null;

        if (! is_string($command)) {
            return null;
        }

        try {
            $object = unserialize($command, ['allowed_classes' => true]);
        } catch (Throwable) {
            return null;
        }

        if ($object instanceof SendQueuedMailable && $object->mailable instanceof TemplatedMail) {
            return $object->mailable->key;
        }

        return null;
    }

    private static function mailableSubject(object $mailable): ?string
    {
        if ($mailable instanceof TemplatedMail) {
            return $mailable->subject;
        }

        if (isset($mailable->subject) && is_string($mailable->subject)) {
            return $mailable->subject;
        }

        return class_basename($mailable);
    }

    /**
     * @return array<string, mixed>
     */
    private static function mailableContext(object $mailable): array
    {
        $context = [];

        if ($mailable instanceof TemplatedMail) {
            $context['template_key'] = $mailable->key;
        }

        if (isset($mailable->to) && is_array($mailable->to)) {
            $context['to'] = $mailable->to;
        }

        return $context;
    }

    private static function mailSubject(Email $message): ?string
    {
        return $message->getSubject();
    }

    /**
     * @return array<string, mixed>
     */
    private static function mailContext(Email $message): array
    {
        return [
            'to' => static::addresses($message->getTo()),
            'cc' => static::addresses($message->getCc()),
            'from' => static::addresses($message->getFrom()),
        ];
    }

    /**
     * @param  array<Address>  $addresses
     * @return array<int, array<string, string|null>>
     */
    private static function addresses(array $addresses): array
    {
        return array_map(
            fn (Address $address) => ['address' => $address->getAddress(), 'name' => $address->getName()],
            $addresses
        );
    }

    private static function taskName(object $task): string
    {
        if (isset($task->command) && is_string($task->command)) {
            return $task->command;
        }

        if (isset($task->description) && is_string($task->description)) {
            return $task->description;
        }

        return class_basename($task);
    }
}
