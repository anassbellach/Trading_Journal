<?php
// ─── StoreTradeRequest ────────────────────────────────────────────────────────
namespace App\Http\Requests\Trade;

use Illuminate\Foundation\Http\FormRequest;

class StoreTradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id'        => ['required', 'integer', 'exists:accounts,id'],
            'ticker'            => ['required', 'string', 'max:20'],
            'direction'         => ['required', 'in:long,short'],
            'status'            => ['sometimes', 'in:open,closed,cancelled'],
            'entry_price'       => ['required', 'numeric', 'min:0'],
            'exit_price'        => ['nullable', 'numeric', 'min:0'],
            'stop_loss'         => ['nullable', 'numeric', 'min:0'],
            'take_profit'       => ['nullable', 'numeric', 'min:0'],
            'position_size'     => ['required', 'numeric', 'min:0.0001'],
            'commission'        => ['nullable', 'numeric', 'min:0'],
            'risk_pct'          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'session'           => ['required', 'in:asian,london,new_york,overnight,pre_market'],
            'strategy_id'       => ['nullable', 'integer', 'exists:strategies,id'],
            'opened_at'         => ['required', 'date'],
            'closed_at'         => ['nullable', 'date', 'after_or_equal:opened_at'],
            'psychology_rating' => ['nullable', 'integer', 'min:1', 'max:10'],
            'psychology_notes'  => ['nullable', 'string', 'max:2000'],
            'mistakes'          => ['nullable', 'array'],
            'mistakes.*'        => ['string', 'max:100'],
            'notes'             => ['nullable', 'string', 'max:5000'],
            'tags'              => ['nullable', 'array'],
            'tags.*'            => ['integer', 'exists:tags,id'],
            'screenshots'       => ['nullable', 'array', 'max:5'],
            'screenshots.*'     => ['image', 'max:8192'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.exists'  => 'Geselecteerde account bestaat niet.',
            'strategy_id.exists' => 'Geselecteerde strategie bestaat niet.',
            'entry_price.required' => 'Entry prijs is verplicht.',
            'ticker.required'    => 'Voer een instrument in.',
        ];
    }
}
