<?php

namespace App\Http\Resources\Trade;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'account_id'       => $this->account_id,
            'account'          => $this->whenLoaded('account', fn () => [
                'id'   => $this->account->id,
                'name' => $this->account->name,
            ]),
            'ticker'           => $this->ticker,
            'direction'        => $this->direction,
            'status'           => $this->status,
            'entry_price'      => (float) $this->entry_price,
            'exit_price'       => $this->exit_price !== null ? (float) $this->exit_price : null,
            'stop_loss'        => $this->stop_loss !== null ? (float) $this->stop_loss : null,
            'take_profit'      => $this->take_profit !== null ? (float) $this->take_profit : null,
            'position_size'    => (float) $this->position_size,
            'commission'       => (float) $this->commission,
            'risk_amount'      => $this->risk_amount !== null ? (float) $this->risk_amount : null,
            'risk_pct'         => $this->risk_pct !== null ? (float) $this->risk_pct : null,
            'pnl'              => $this->pnl !== null ? (float) $this->pnl : null,
            'pnl_pct'          => $this->pnl_pct !== null ? (float) $this->pnl_pct : null,
            'rr_ratio'         => $this->rr_ratio !== null ? (float) $this->rr_ratio : null,
            'is_win'           => $this->is_win,
            'session'          => $this->session,
            'strategy_id'      => $this->strategy_id,
            'strategy'         => $this->whenLoaded('strategy', fn () => $this->strategy ? [
                'id'    => $this->strategy->id,
                'name'  => $this->strategy->name,
                'color' => $this->strategy->color,
            ] : null),
            'tags'             => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($t) => [
                'id'    => $t->id,
                'name'  => $t->name,
                'color' => $t->color,
            ])),
            'screenshots'      => $this->whenLoaded('screenshots', fn () => $this->screenshots->map(fn ($s) => [
                'id'   => $s->id,
                'url'  => asset('storage/' . $s->path),
                'type' => $s->type,
            ])),
            'opened_at'        => $this->opened_at?->toIso8601String(),
            'closed_at'        => $this->closed_at?->toIso8601String(),
            'duration_seconds' => $this->duration_seconds,
            'psychology_rating'=> $this->psychology_rating,
            'psychology_notes' => $this->psychology_notes,
            'mistakes'         => $this->mistakes ?? [],
            'notes'            => $this->notes,
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
