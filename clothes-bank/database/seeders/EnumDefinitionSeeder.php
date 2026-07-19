<?php

namespace Database\Seeders;

use App\Models\EnumDefinition;
use Illuminate\Database\Seeder;

class EnumDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHousingStatus();
        $this->seedGender();
        $this->seedFdaStatus();
        $this->seedHousingReferralOutcome();
    }

    private function seedHousingStatus(): void
    {
        $items = [
            ['key' => 'unknown', 'label' => 'Unknown'],
            ['key' => 'rough-sleeper', 'label' => 'Rough sleeper'],
            ['key' => 'hostel', 'label' => 'Hostel'],
            ['key' => 'housed', 'label' => 'Housed'],
            ['key' => 'temporary', 'label' => 'Temporary accommodation'],
            ['key' => 'sofa-surfing', 'label' => 'Sofa surfing'],
        ];

        $this->upsert('housing_status', $items);
    }

    private function seedGender(): void
    {
        $items = [
            ['key' => 'male', 'label' => 'Male'],
            ['key' => 'female', 'label' => 'Female'],
            ['key' => 'other', 'label' => 'Other'],
            ['key' => 'prefer-not-to-say', 'label' => 'Prefer not to say'],
        ];

        $this->upsert('gender', $items);
    }

    private function seedFdaStatus(): void
    {
        $items = [
            ['key' => 'yes', 'label' => 'Yes'],
            ['key' => 'no', 'label' => 'No'],
            ['key' => 'appealing', 'label' => 'Appealing'],
            ['key' => 'unknown', 'label' => 'Unknown'],
        ];

        $this->upsert('fda_status', $items);
    }

    private function seedHousingReferralOutcome(): void
    {
        $items = [
            ['key' => 'swep', 'label' => 'SWEP'],
            ['key' => 'hostel-bed-provided', 'label' => 'Hostel bed provided'],
            ['key' => 'no-availability', 'label' => 'No availability'],
            ['key' => 'no-duty-of-care', 'label' => 'No duty of care'],
            ['key' => 'appealing', 'label' => 'Currently Appealing'],
            ['key' => 'left-before-outcome', 'label' => 'Left before outcome'],
            ['key' => 'declined', 'label' => 'Declined offered accomodation'],
            ['key' => 'no-response', 'label' => 'No response'],
            ['key' => 'other', 'label' => 'Other'],
        ];

        $this->upsert('housing_referral_outcome', $items);
    }

    private function upsert(string $group, array $items): void
    {
        foreach ($items as $item) {
            EnumDefinition::updateOrCreate(
                [
                    'group' => $group,
                    'key' => $item['key'],
                ],
                [
                    'label' => $item['label'],
                    'active' => true,
                ]
            );
        }
    }
}
