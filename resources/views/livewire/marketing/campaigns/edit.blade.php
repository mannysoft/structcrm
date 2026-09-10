<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Domains\Marketing\Actions\UpdateMarketingCampaignAction;
use App\Domains\Marketing\Models\MarketingCampaign;

new #[Layout('layouts.app')] class extends Component {
    public string $uuid = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:500')]
    public string $description = '';

    #[Validate('boolean')]
    public bool $is_active = true;

    public array $steps = [];

    public function mount(MarketingCampaign $campaign): void
    {
        $this->authorize('update', $campaign);

        $this->uuid = $campaign->uuid;
        $this->name = $campaign->name;
        $this->description = $campaign->description ?? '';
        $this->is_active = $campaign->is_active;
        $this->steps = $campaign->steps->map(fn ($step) => [
            'id' => $step->id,
            'subject' => $step->subject,
            'delay_days' => $step->delay_days,
            'body' => $step->body,
        ])->all();
    }

    public function save(UpdateMarketingCampaignAction $action): void
    {
        $this->validate();

        $this->validate([
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.subject' => ['required', 'string', 'max:255'],
            'steps.*.delay_days' => ['required', 'integer', 'min:0'],
            'steps.*.body' => ['required', 'string'],
        ]);

        $campaign = MarketingCampaign::where('uuid', $this->uuid)->first();

        abort_unless($campaign, 404);

        $action->execute(
            campaign: $campaign,
            name: $this->name,
            description: $this->description ?: null,
            isActive: $this->is_active,
            steps: $this->steps,
        );

        session()->flash('status', 'Campaign updated.');

        $this->redirect(route('marketing.campaigns.index'), navigate: true);
    }
}; ?>

<div>
    <x-page-header title="Edit Campaign" description="Update this marketing email sequence." />

    <form wire:submit="save" class="mt-6 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-3xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <div class="sm:col-span-4">
                    <x-input-label for="name" value="Name" />
                    <div class="mt-2">
                        <x-text-input type="text" wire:model="name" id="name" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="is_active" value="Status" />
                    <div class="mt-2">
                        <label for="is_active" class="inline-flex items-center">
                            <input wire:model="is_active" id="is_active" type="checkbox" class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500" name="is_active">
                            <span class="ms-2 text-sm text-gray-600">Active</span>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('is_active')" />
                </div>

                <div class="col-span-full">
                    <x-input-label for="description" value="Description" />
                    <div class="mt-2">
                        <x-textarea id="description" wire:model="description" rows="2"></x-textarea>
                    </div>
                    <x-input-error :messages="$errors->get('description')" />
                </div>

                <div class="col-span-full" x-data="{ steps: @entangle('steps') }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium leading-6 text-gray-900">Emails</h3>
                        <button type="button" @click="steps.push({ id: null, subject: '', delay_days: 0, body: '' })" class="text-sm font-semibold text-brand-600 hover:text-brand-500">Add email</button>
                    </div>
                    <div class="mt-4 space-y-4">
                        <template x-for="(step, index) in steps" :key="index">
                            <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                                <div class="flex items-start justify-between">
                                    <h4 class="text-sm font-medium text-gray-700" x-text="'Email ' + (index + 1)"></h4>
                                    <button type="button" @click="steps.splice(index, 1)" class="text-sm text-red-600 hover:text-red-900">Remove</button>
                                </div>
                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-8">
                                        <x-input-label for="subject" value="Subject" />
                                        <div class="mt-2">
                                            <x-text-input x-model="step.subject" placeholder="Subject" />
                                        </div>
                                    </div>
                                    <div class="col-span-4">
                                        <x-input-label for="delay_days" value="Send after (days)" />
                                        <div class="mt-2">
                                            <x-text-input type="number" x-model="step.delay_days" min="0" placeholder="0" />
                                        </div>
                                    </div>
                                    <div class="col-span-12">
                                        <x-input-label for="body" value="Body (HTML)" />
                                        <div class="mt-2">
                                            <x-textarea x-model="step.body" rows="4" placeholder="<p>Hello {name}, …</p>"></x-textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('steps')" />
                    <x-input-error :messages="$errors->get('steps.*.subject')" />
                    <x-input-error :messages="$errors->get('steps.*.delay_days')" />
                    <x-input-error :messages="$errors->get('steps.*.body')" />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-x-3 border-t border-gray-200 bg-gray-50 px-4 py-4 rounded-b-xl sm:px-8">
            <a href="{{ route('marketing.campaigns.index') }}" wire:navigate class="text-sm font-semibold text-gray-900">Cancel</a>
            <x-button type="submit">Save</x-button>
        </div>
    </form>
</div>
