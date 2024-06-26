<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\FollowUpLeadViewSetp;
use App\Models\FollowingLead;
use App\Models\CampaignTag;

class MarketingLeadExport implements FromCollection, WithHeadings {

    public function __construct($data) {
        $this->data = $data;
    }

    public function collection() {
        return collect($this->data);
    }

    public function headings(): array {
        $followUpLeadViewSetp = [];
        $followUpLeadViewSetp[] = 'Homeowner Name';
        $followUpLeadViewSetp[] = 'Homeowner Address';
        $followUpLeadViewSetp[] = 'Lead';
        $followUpLeadViewSetp[] = 'Investor';
        $followUpLeadViewSetp[] = 'Notes and Actions';
        $followUpLeadViewSetp[] = 'Investor Notes';
        $followUpLeadViewSetp[] = 'Appt Email';
        $followUpLeadViewSetp[] = 'Appt Phone';
        $followUpLeadViewSetp[] = 'Marketing Email';
        $followUpLeadViewSetp[] = 'Marketing Address';

        $tags = CampaignTag::where('is_show_marketing', '=', 1)->get();
        foreach ($tags as $tag) {
            $followUpLeadViewSetp[] = $tag->tag_name;
        }
        return $followUpLeadViewSetp;
    }

}
