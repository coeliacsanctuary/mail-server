<?php

namespace App\Livewire\Overrides;

use Exception;
use Illuminate\Support\Facades\Http;

class CampaignDeliveryComponent extends \Spatie\Mailcoach\Livewire\Campaigns\CampaignDeliveryComponent
{
    public function schedule()
    {
        parent::schedule();

        try {
            Http::coeliac()->post('/api/mailcoach-schedule?key' . config('services.coeliac.key'), [
                'time' => $this->scheduled_at_date,
            ]);
        } catch (Exception) {
            notifyWarning('Campaign has been scheduled, but could not prepare a planned wake up call on the main website');
        }
    }
}
