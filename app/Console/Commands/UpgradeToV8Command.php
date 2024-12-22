<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpgradeToV8Command extends Command
{
    protected $signature = 'app:migrate-to-v8';

    protected $description = 'Migrate Mailcoach data to v8';

    public function handle(): void
    {
        Schema::table('mailcoach_transactional_mail_log_items', function (Blueprint $table): void {
            $table->string('mail_name')->nullable()->after('mailable_class');

            $table->index(['mail_name', 'created_at'], 'mail_name_index');
        });

        Schema::table('mailcoach_tags', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('name');
        });
    }
}
