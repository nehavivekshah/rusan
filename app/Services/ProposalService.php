<?php

namespace App\Services;

use App\Models\Proposals;
use App\Models\Proposal_items;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProposalService extends BaseService
{
    /**
     * Convert currency strings like "₹100.00" to float.
     */
    public function convertCurrencyStringToNumber($currencyString)
    {
        if (empty($currencyString)) return 0.0;
        $cleanString = preg_replace('/[^0-9.]+/', '', $currencyString);
        return (float) $cleanString;
    }

    /**
     * Process proposal items, calculate taxes, and save.
     */
    public function processItems(Proposals $proposal, array $items)
    {
        // If updating, clear old items (simulating controller logic)
        Proposal_items::where('proposal_id', $proposal->id)->delete();

        foreach ($items as $row) {
            $itemName = $row['item_name'] ?? '';
            $descr = $row['description'] ?? '';
            $qty = !empty($row['quantity']) ? (int) $row['quantity'] : 1;
            $rate = !empty($row['rate']) ? (float) $row['rate'] : 0;

            if (!$itemName && !$descr && $qty <= 0 && $rate <= 0) {
                continue;
            }

            $cgst = $sgst = $igst = $vat = 0.0;
            $taxAmountTotal = 0.0;

            foreach ($row['tax_percentage'] ?? [] as $entry) {
                [$code, $percent] = explode(':', $entry);
                $percent = (float) $percent;

                switch (strtolower($code)) {
                    case '0': $cgst = $percent; break;
                    case '1': $sgst = $percent; break;
                    case '2': $igst = $percent; break;
                    case '3': $vat = $percent; break;
                }

                $taxAmountTotal += ($rate * $qty) * ($percent / 100);
            }

            $lineSubtotal = $rate * $qty;
            $lineTotal = $lineSubtotal + $taxAmountTotal;

            $proposalItem = new Proposal_items();
            $proposalItem->proposal_id = $proposal->id;
            $proposalItem->item_name = $itemName;
            $proposalItem->description = $descr;
            $proposalItem->quantity = $qty;
            $proposalItem->rate = $rate;
            $proposalItem->cgst_percent = $cgst;
            $proposalItem->sgst_percent = $sgst;
            $proposalItem->igst_percent = $igst;
            $proposalItem->vat_percent = $vat;
            $proposalItem->item_total_amount = $lineTotal;
            $proposalItem->save();
        }
    }

    /**
     * Create a follow-up task for sent proposals.
     */
    public function createProposalFollowUpTask(Proposals $proposal)
    {
        $task = new Task();
        $task->cid = $proposal->cid ?? Auth::user()->cid;
        $task->uid = Auth::id();
        $task->title = "Proposal Follow-up: " . $proposal->subject;
        $task->des = "Proposal was sent. Coordinate with client for feedback in 48 hours.";
        $task->status = '3'; // Pending/Follow-up
        $task->label = '#17a2b8';
        return $task->save();
    }
}
