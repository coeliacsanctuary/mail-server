<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Mailcoach\Domain\Automation\Models\AutomationMail;
use Spatie\Mailcoach\Domain\Campaign\Models\Campaign;
use Spatie\Mailcoach\Domain\ConditionBuilder\Enums\ComparisonOperator;
use Spatie\Mailcoach\Domain\TransactionalMail\Models\TransactionalMail;
use Spatie\Mailcoach\Domain\TransactionalMail\Models\TransactionalMailLogItem;
use Throwable;

class UpgradeToV7Command extends Command
{
    protected $signature = 'app:migrate-to-v7';

    protected $description = 'Migrate Mailcoach data to v7';

    public function handle(): void
    {
        Schema::table('', function (Blueprint $table): void {
            Schema::table('mailcoach_segments', function (Blueprint $table): void {
                $table->json('stored_conditions')->nullable()->after('email_list_id');
            });

            Schema::table('mailcoach_campaigns', function (Blueprint $table): void {
                $table->boolean('disable_webview')->default(false)->after('show_publicly');

                $table->after('summary_mail_sent_at', function (Blueprint $table): void {
                    $table->integer('split_test_wait_time_in_minutes')->nullable();
                    $table->integer('split_test_split_size_percentage')->nullable();
                    $table->timestamp('split_test_started_at')->nullable();
                    $table->foreignId('split_test_winning_content_item_id')->nullable();
                });
            });

            Schema::table('mailcoach_sends', function (Blueprint $table): void {
                $table->foreignId('content_item_id')
                    ->nullable()
                    ->after('transport_message_id');
            });

            Schema::table('mailcoach_transactional_mail_log_items', function (Blueprint $table): void {
                $table->text('subject')->nullable()->change();
                $table->boolean('fake')->default(false)->after('attachments');
            });

            Schema::create('mailcoach_content_items', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();

                $table->morphs('model');

                $table->string('from_email')->nullable();
                $table->string('from_name')->nullable();

                $table->string('reply_to_email')->nullable();
                $table->string('reply_to_name')->nullable();

                $table->string('subject')->nullable();

                $table->unsignedBigInteger('template_id')->nullable();
                $table->longText('html')->nullable();
                $table->longText('structured_html')->nullable();
                $table->longText('email_html')->nullable();
                $table->longText('webview_html')->nullable();

                $table->string('mailable_class')->nullable();
                $table->json('mailable_arguments')->nullable();

                $table->boolean('utm_tags')->default(false);
                $table->string('utm_source')->nullable();
                $table->string('utm_medium')->nullable();
                $table->string('utm_campaign')->nullable();
                $table->boolean('add_subscriber_tags')->default(false);
                $table->boolean('add_subscriber_link_tags')->default(false);

                $table->timestamp('all_sends_created_at')->nullable();
                $table->timestamp('all_sends_dispatched_at')->nullable();

                $table->integer('sent_to_number_of_subscribers')->default(0);
                $table->integer('open_count')->default(0);
                $table->integer('unique_open_count')->default(0);
                $table->integer('open_rate')->default(0);
                $table->integer('click_count')->default(0);
                $table->integer('unique_click_count')->default(0);
                $table->integer('click_rate')->default(0);
                $table->integer('unsubscribe_count')->default(0);
                $table->integer('unsubscribe_rate')->default(0);
                $table->integer('bounce_count')->default(0);
                $table->integer('bounce_rate')->default(0);
                $table->timestamp('statistics_calculated_at')->nullable();

                $table->timestamps();
            });

            Schema::create('mailcoach_links', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table
                    ->foreignId('content_item_id')
                    ->constrained('mailcoach_content_items')
                    ->cascadeOnDelete();

                $table->string('url', 2048);
                $table->integer('click_count')->default(0);
                $table->integer('unique_click_count')->default(0);
                $table->nullableTimestamps();
            });

            Schema::create('mailcoach_clicks', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();

                $table
                    ->foreignId('send_id')
                    ->constrained('mailcoach_sends')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('link_id')
                    ->constrained('mailcoach_links')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('subscriber_id')
                    ->nullable()
                    ->constrained('mailcoach_subscribers')
                    ->cascadeOnDelete();

                $table->nullableTimestamps();
            });

            Schema::create('mailcoach_opens', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();

                $table
                    ->foreignId('send_id')
                    ->constrained('mailcoach_sends')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('content_item_id')
                    ->nullable()
                    ->constrained('mailcoach_content_items')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('subscriber_id')
                    ->nullable()
                    ->constrained('mailcoach_subscribers')
                    ->cascadeOnDelete();

                $table->nullableTimestamps();
            });

            Schema::create('mailcoach_unsubscribes', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();

                $table
                    ->foreignId('content_item_id')
                    ->constrained('mailcoach_content_items')
                    ->cascadeOnDelete();

                $table
                    ->foreignId('subscriber_id')
                    ->constrained('mailcoach_subscribers')
                    ->cascadeOnDelete();

                $table->timestamps();
            });

            Schema::create('mailcoach_subscriber_exports', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('status');

                $table
                    ->foreignId('email_list_id')
                    ->nullable()
                    ->constrained('mailcoach_email_lists')
                    ->cascadeOnDelete();

                $table->json('filters')->nullable();
                $table->integer('exported_subscribers_count')->default(0);
                $table->text('errors')->nullable();
                $table->timestamps();
            });

            Schema::create('mailcoach_suppressions', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('email')->unique();
                $table->string('reason');
                $table->timestamps();
            });
        });

        $this->migrateSegments();
        $this->migrateCampaigns();
        $this->migrateAutomationMails();
        $this->migrateTransactionalMails();
        $this->migrateTransactionalMailLogItems();
        $this->migrateSends();
        $this->migrateCampaignOpens();
        $this->migrateCampaignLinks();
        $this->migrateCampaignUnsubscribes();
        $this->migrateAutomationMailOpens();
        $this->migrateAutomationMailLinks();
        $this->migrateAutomationMailUnsubscribes();
        $this->migrateTransactionalMailOpens();
        $this->migrateTransactionalMailClicks();
    }

    public function migrateSegments(): void
    {
        $this->getOutput()->writeln('Migrating segments');
        $this->getOutput()->progressStart(DB::table('mailcoach_segments')->count());

        DB::table('mailcoach_segments')->lazyById()->each(function ($segment): void {
            $positiveTags = DB::table('mailcoach_positive_segment_tags')->where('segment_id', $segment->id)->get();
            $negativeTags = DB::table('mailcoach_negative_segment_tags')->where('segment_id', $segment->id)->get();

            $storedConditions = [];

            if ($positiveTags->count()) {
                $storedConditions[] = [
                    'value' => $positiveTags->pluck('tag_id')->values()->toArray(),
                    'condition_key' => 'subscriber_tags',
                    'comparison_operator' => $segment->all_positive_tags_required
                        ? ComparisonOperator::All
                        : ComparisonOperator::In,
                ];
            }

            if ($negativeTags->count()) {
                $storedConditions[] = [
                    'value' => $negativeTags->pluck('tag_id')->values()->toArray(),
                    'condition_key' => 'subscriber_tags',
                    'comparison_operator' => $segment->all_negative_tags_required
                        ? ComparisonOperator::None
                        : ComparisonOperator::NotIn,
                ];
            }

            DB::table('mailcoach_segments')
                ->where('id', $segment->id)
                ->update([
                    'stored_conditions' => json_encode($storedConditions),
                ]);

            $this->getOutput()->progressAdvance();
        });

        $this->getOutput()->progressFinish();
    }

    public function migrateCampaigns(): void
    {
        $this->getOutput()->writeln('Migrating campaigns');
        $this->getOutput()->progressStart(DB::table('mailcoach_campaigns')->count());

        DB::table('mailcoach_campaigns')->lazyById()->each(function ($row): void {
            DB::table('mailcoach_content_items')->insert([
                'uuid' => Str::uuid(),
                'model_type' => (new Campaign())->getMorphClass(),
                'model_id' => $row->id,
                'from_email' => $row->from_email ?? null,
                'from_name' => $row->from_name ?? null,

                'reply_to_email' => $row->reply_to_email ?? null,
                'reply_to_name' => $row->reply_to_name ?? null,

                'subject' => $row->subject ?? null,
                'template_id' => $row->template_id ?? null,

                'html' => $row->html ?? null,
                'structured_html' => $row->structured_html ?? null,
                'email_html' => $row->email_html ?? null,
                'webview_html' => $row->webview_html ?? null,

                'mailable_class' => $row->mailable_class ?? null,
                'mailable_arguments' => $row->mailable_arguments ?? null,

                'utm_tags' => $row->utm_tags ?? false,
                'add_subscriber_tags' => $row->add_subscriber_tags ?? false,
                'add_subscriber_link_tags' => $row->add_subscriber_link_tags ?? false,

                'sent_to_number_of_subscribers' => $row->sent_to_number_of_subscribers,
                'open_count' => $row->open_count,
                'unique_open_count' => $row->unique_open_count,
                'open_rate' => $row->open_rate,
                'click_count' => $row->click_count,
                'unique_click_count' => $row->unique_click_count,
                'click_rate' => $row->click_rate,
                'unsubscribe_count' => $row->unsubscribe_count,
                'unsubscribe_rate' => $row->unsubscribe_rate,
                'bounce_count' => $row->bounce_count,
                'bounce_rate' => $row->bounce_rate,
                'statistics_calculated_at' => $row->statistics_calculated_at ?? null,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);

            $this->getOutput()->progressAdvance();
        });

        $this->getOutput()->progressFinish();
    }

    public function migrateAutomationMails(): void
    {
        $this->getOutput()->writeln('Migrating automation mails');
        $this->getOutput()->progressStart(DB::table('mailcoach_automation_mails')->count());

        DB::table('mailcoach_automation_mails')->lazyById()->each(function ($row): void {
            DB::table('mailcoach_content_items')->insert([
                'uuid' => Str::uuid(),
                'model_type' => (new AutomationMail())->getMorphClass(),
                'model_id' => $row->id,
                'from_email' => $row->from_email ?? null,
                'from_name' => $row->from_name ?? null,

                'reply_to_email' => $row->reply_to_email ?? null,
                'reply_to_name' => $row->reply_to_name ?? null,

                'subject' => $row->subject ?? null,
                'template_id' => $row->template_id ?? null,

                'html' => $row->html ?? null,
                'structured_html' => $row->structured_html ?? null,
                'email_html' => $row->email_html ?? null,
                'webview_html' => $row->webview_html ?? null,

                'mailable_class' => $row->mailable_class ?? null,
                'mailable_arguments' => $row->mailable_arguments ?? null,

                'utm_tags' => $row->utm_tags ?? false,
                'add_subscriber_tags' => $row->add_subscriber_tags ?? false,
                'add_subscriber_link_tags' => $row->add_subscriber_link_tags ?? false,

                'sent_to_number_of_subscribers' => $row->sent_to_number_of_subscribers,
                'open_count' => $row->open_count,
                'unique_open_count' => $row->unique_open_count,
                'open_rate' => $row->open_rate,
                'click_count' => $row->click_count,
                'unique_click_count' => $row->unique_click_count,
                'click_rate' => $row->click_rate,
                'unsubscribe_count' => $row->unsubscribe_count,
                'unsubscribe_rate' => $row->unsubscribe_rate,
                'bounce_count' => $row->bounce_count,
                'bounce_rate' => $row->bounce_rate,
                'statistics_calculated_at' => $row->statistics_calculated_at ?? null,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);

            $this->getOutput()->progressAdvance();
        });

        $this->getOutput()->progressFinish();
    }

    public function migrateTransactionalMails(): void
    {
        $this->getOutput()->writeln('Migrating transactional mails');
        $this->getOutput()->progressStart(DB::table('mailcoach_transactional_mails')->count());

        DB::table('mailcoach_transactional_mails')
            ->lazyById()
            ->each(function ($row): void {
                DB::table('mailcoach_content_items')->insert([
                    'uuid' => $row->uuid,
                    'model_id' => $row->id,
                    'model_type' => (new TransactionalMail())->getMorphClass(),
                    'subject' => $row->subject,
                    'template_id' => $row->template_id,
                    'html' => $row->body,
                    'structured_html' => $row->structured_html,
                ]);

                $this->getOutput()->progressAdvance();
            });

        $this->getOutput()->progressFinish();
    }

    public function migrateTransactionalMailLogItems(): void
    {
        $this->getOutput()->writeln('Migrating transactional mail log items');
        $this->getOutput()->progressStart(DB::table('mailcoach_transactional_mail_log_items')->count());

        DB::table('mailcoach_transactional_mail_log_items')
            ->lazyById(100)
            ->each(function ($row): void {
                DB::table('mailcoach_content_items')->insert([
                    'uuid' => $row->uuid,
                    'model_id' => $row->id,
                    'model_type' => (new TransactionalMailLogItem())->getMorphClass(),
                    'subject' => $row->subject,
                    'html' => $row->body,
                    'structured_html' => $row->structured_html,
                    'mailable_class' => $row->mailable_class,
                ]);

                $this->getOutput()->progressAdvance();
            });

        $this->getOutput()->progressFinish();
    }

    public function migrateSends(): void
    {
        $this->getOutput()->writeln('Migrating sends');
        $this->getOutput()->progressStart(DB::table('mailcoach_sends')->whereNull('content_item_id')->count());

        DB::table('mailcoach_sends')
            ->whereNull('content_item_id')
            ->lazyById()
            ->each(function ($row): void {
                $contentItem = match (true) {
                    null !== $row->campaign_id => DB::table('mailcoach_content_items')->where('model_id', $row->campaign_id)->where('model_type', (new Campaign())->getMorphClass())->first(),
                    null !== $row->automation_mail_id => DB::table('mailcoach_content_items')->where('model_id', $row->automation_mail_id)->where('model_type', (new AutomationMail())->getMorphClass())->first(),
                    null !== $row->transactional_mail_log_item_id => DB::table('mailcoach_content_items')->where('model_id', $row->transactional_mail_log_item_id)->where('model_type', (new TransactionalMailLogItem())->getMorphClass())->first(),
                    default => false,
                };

                if ($contentItem) {
                    DB::table('mailcoach_sends')->where('id', $row->id)->update(['content_item_id' => $contentItem->id]);
                }

                $this->getOutput()->progressAdvance();
            });

        $this->getOutput()->progressFinish();
    }

    public function migrateCampaignOpens(): void
    {
        $this->getOutput()->writeln('Migrating campaign opens');
        $this->getOutput()->progressStart(DB::table('mailcoach_campaign_opens')->count());

        DB::table('mailcoach_campaign_opens')
            ->join('mailcoach_content_items', 'mailcoach_content_items.model_id', '=', 'mailcoach_campaign_opens.campaign_id')
            ->select('mailcoach_campaign_opens.*', DB::raw('mailcoach_content_items.id as content_item_id'))
            ->where('mailcoach_content_items.model_type', (new Campaign())->getMorphClass())
            ->orderBy('mailcoach_campaign_opens.id')
            ->lazy()
            ->each(function ($row): void {
                DB::table('mailcoach_opens')->insert([
                    'uuid' => $row->uuid,
                    'send_id' => $row->send_id,
                    'content_item_id' => $row->content_item_id,
                    'subscriber_id' => $row->subscriber_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);

                $this->getOutput()->progressAdvance();
            });

        $this->getOutput()->progressFinish();
    }

    public function migrateCampaignLinks(): void
    {
        $this->getOutput()->writeln('Migrating campaign links');
        $this->getOutput()->progressStart(DB::table('mailcoach_campaign_links')->count());

        DB::table('mailcoach_campaign_links')
            ->join('mailcoach_content_items', 'mailcoach_content_items.model_id', '=', 'mailcoach_campaign_links.campaign_id')
            ->where('mailcoach_content_items.model_type', (new Campaign())->getMorphClass())
            ->select('mailcoach_campaign_links.*', DB::raw('mailcoach_content_items.id as content_item_id'))
            ->orderBy('mailcoach_campaign_links.id')
            ->lazy()
            ->each(function ($row): void {
                $linkId = DB::table('mailcoach_links')->insertGetId([
                    'uuid' => $row->uuid,
                    'content_item_id' => $row->content_item_id,
                    'url' => $row->url,
                    'click_count' => $row->click_count,
                    'unique_click_count' => $row->unique_click_count,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);

                DB::table('mailcoach_campaign_clicks')
                    ->where('campaign_link_id', $row->id)
                    ->lazyById()
                    ->each(function ($click) use ($linkId): void {
                        DB::table('mailcoach_clicks')->insert([
                            'uuid' => $click->uuid,
                            'send_id' => $click->send_id,
                            'link_id' => $linkId,
                            'subscriber_id' => $click->subscriber_id,
                            'created_at' => $click->created_at,
                            'updated_at' => $click->updated_at,
                        ]);
                    });

                $this->getOutput()->progressAdvance();
            });

        $this->getOutput()->progressFinish();
    }

    public function migrateCampaignUnsubscribes(): void
    {
        $this->getOutput()->writeln('Migrating campaign unsubscribes');
        $this->getOutput()->progressStart(DB::table('mailcoach_campaign_unsubscribes')->count());

        DB::table('mailcoach_campaign_unsubscribes')
            ->join('mailcoach_content_items', 'mailcoach_content_items.model_id', '=', 'mailcoach_campaign_unsubscribes.campaign_id')
            ->where('mailcoach_content_items.model_type', (new Campaign())->getMorphClass())
            ->select('mailcoach_campaign_unsubscribes.*', DB::raw('mailcoach_content_items.id as content_item_id'))
            ->orderBy('mailcoach_campaign_unsubscribes.id')
            ->lazy()
            ->each(function ($row): void {
                DB::table('mailcoach_unsubscribes')->insert([
                    'uuid' => $row->uuid,
                    'content_item_id' => $row->content_item_id,
                    'subscriber_id' => $row->subscriber_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);

                $this->getOutput()->progressAdvance();
            });

        $this->getOutput()->progressFinish();
    }

    public function migrateAutomationMailOpens(): void
    {
        $this->getOutput()->writeln('Migrating automation mail opens');
        $this->getOutput()->progressStart(DB::table('mailcoach_automation_mail_opens')->count());

        DB::table('mailcoach_automation_mail_opens')
            ->join('mailcoach_content_items', 'mailcoach_content_items.model_id', '=', 'mailcoach_automation_mail_opens.automation_mail_id')
            ->select('mailcoach_automation_mail_opens.*', DB::raw('mailcoach_content_items.id as content_item_id'))
            ->where('mailcoach_content_items.model_type', (new AutomationMail())->getMorphClass())
            ->orderBy('mailcoach_automation_mail_opens.id')
            ->lazy()
            ->each(function ($row): void {
                try {
                    DB::table('mailcoach_opens')->insert([
                        'uuid' => $row->uuid,
                        'send_id' => $row->send_id,
                        'content_item_id' => $row->content_item_id,
                        'subscriber_id' => $row->subscriber_id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                } catch (Throwable) {
                }

                $this->getOutput()->progressAdvance();
            });

        $this->getOutput()->progressFinish();
    }

    public function migrateAutomationMailLinks(): void
    {
        $this->getOutput()->writeln('Migrating automation mail links');
        $this->getOutput()->progressStart(DB::table('mailcoach_automation_mail_links')->count());

        DB::table('mailcoach_automation_mail_links')
            ->join('mailcoach_content_items', 'mailcoach_content_items.model_id', '=', 'mailcoach_automation_mail_links.automation_mail_id')
            ->where('mailcoach_content_items.model_type', (new AutomationMail())->getMorphClass())
            ->select('mailcoach_automation_mail_links.*', DB::raw('mailcoach_content_items.id as content_item_id'))
            ->orderBy('mailcoach_automation_mail_links.id')
            ->lazy()
            ->each(function ($row): void {
                $linkId = DB::table('mailcoach_links')->insertGetId([
                    'uuid' => $row->uuid,
                    'content_item_id' => $row->content_item_id,
                    'url' => $row->url,
                    'click_count' => $row->click_count,
                    'unique_click_count' => $row->unique_click_count,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);

                DB::table('mailcoach_automation_mail_clicks')
                    ->where('automation_mail_link_id', $row->id)
                    ->lazyById()
                    ->each(function ($click) use ($linkId): void {
                        DB::table('mailcoach_clicks')->insert([
                            'uuid' => $click->uuid,
                            'send_id' => $click->send_id,
                            'link_id' => $linkId,
                            'subscriber_id' => $click->subscriber_id,
                            'created_at' => $click->created_at,
                            'updated_at' => $click->updated_at,
                        ]);
                    });

                $this->getOutput()->progressAdvance();
            });

        $this->getOutput()->progressFinish();
    }

    public function migrateAutomationMailUnsubscribes(): void
    {
        $this->getOutput()->writeln('Migrating automation mail unsubscribes');
        $this->getOutput()->progressStart(DB::table('mailcoach_automation_mail_unsubscribes')->count());

        DB::table('mailcoach_automation_mail_unsubscribes')
            ->join('mailcoach_content_items', 'mailcoach_content_items.model_id', '=', 'mailcoach_automation_mail_unsubscribes.automation_mail_id')
            ->where('mailcoach_content_items.model_type', (new AutomationMail())->getMorphClass())
            ->select('mailcoach_automation_mail_unsubscribes.*', DB::raw('mailcoach_content_items.id as content_item_id'))
            ->orderBy('mailcoach_automation_mail_unsubscribes.id')
            ->lazy()
            ->each(function ($row): void {
                DB::table('mailcoach_unsubscribes')->insert([
                    'uuid' => $row->uuid,
                    'content_item_id' => $row->content_item_id,
                    'subscriber_id' => $row->subscriber_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);

                $this->getOutput()->progressAdvance();
            });

        $this->getOutput()->progressFinish();
    }

    public function migrateTransactionalMailOpens(): void
    {
        $this->getOutput()->writeln('Migrating transactional mail opens');
        $this->getOutput()->progressStart(DB::table('mailcoach_transactional_mail_opens')->count());

        DB::table('mailcoach_transactional_mail_opens')
            ->lazyById()
            ->each(function ($row): void {
                try {
                    DB::table('mailcoach_opens')->insert([
                        'uuid' => $row->uuid,
                        'send_id' => $row->send_id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                } catch (Throwable) {
                }

                $this->getOutput()->progressAdvance();
            });

        $this->getOutput()->progressFinish();
    }

    public function migrateTransactionalMailClicks(): void
    {
        $this->getOutput()->writeln('Migrating transactional_mail_clicks');
        $this->getOutput()->progressStart(DB::table('mailcoach_transactional_mail_clicks')->count());

        DB::table('mailcoach_transactional_mail_clicks')
            ->join('mailcoach_sends', 'mailcoach_sends.id', '=', 'mailcoach_transactional_mail_clicks.send_id')
            ->select('mailcoach_transactional_mail_clicks.*', 'mailcoach_sends.content_item_id')
            ->orderBy('mailcoach_transactional_mail_clicks.id')
            ->lazy()
            ->each(function ($row): void {
                $existing = DB::table('mailcoach_links')
                    ->where('content_item_id', $row->content_item_id)
                    ->where('url', $row->url)
                    ->first()
                    ?->id;

                if ($existing) {
                    DB::table('mailcoach_links')->where('id', $existing)->increment('click_count');
                } else {
                    $existing = DB::table('mailcoach_links')->insertGetId([
                        'uuid' => Str::uuid(),
                        'content_item_id' => $row->content_item_id,
                        'url' => $row->url,
                        'click_count' => 1,
                        'unique_click_count' => 1,
                    ]);
                }

                DB::table('mailcoach_clicks')->insert([
                    'uuid' => $row->uuid,
                    'send_id' => $row->send_id,
                    'link_id' => $existing,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);

                $this->getOutput()->progressAdvance();
            });

        $this->getOutput()->progressFinish();
    }
}
