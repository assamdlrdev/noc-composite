<?php

namespace App\Services;

use App\Models\NocAuditLog;
use Illuminate\Support\Collection;

class ApplicationWorkflowService
{
    public function getWorkflowStatus(string $appno): array
    {
        $logs = NocAuditLog::where('appno', $appno)
            ->orderBy('action_datetime')
            ->get();

        if ($logs->isEmpty()) {
            return [
                'exists' => false,
                'message' => 'No workflow history found for this application.'
            ];
        }

        $latest = $logs->last();

        $pendingQuery = $this->hasPendingQuery($appno);

        return [
            'exists' => true,

            'appno' => $appno,

            'current' => [
                'stage' => $this->getCurrentStage($logs),
                'status' => $this->getCurrentStatus($latest),
                'action' => $latest->action_code,
                'action_description' => $latest->action_description,
                'action_at' => $latest->action_datetime,
            ],

            'workflow' => $this->getWorkflowState($logs),

            'query' => [
                'pending' => $pendingQuery,
            ],

            'permissions' => [
                'can_forward' => !$pendingQuery,
                'can_revert' => !$pendingQuery,
                'can_reject' => !$pendingQuery,
                'can_issue' => !$pendingQuery,
                'can_respond_to_query' => $pendingQuery,
            ],

            'tabs' => $this->getTabs(
                $logs,
                $pendingQuery
            ),
        ];
    }

    private function getCurrentStage(Collection $logs): string
    {
        $latest = $logs->last();

        $action = $latest->action_code;

        return match ($action) {

            'lra_received',
            'co_sent_back_to_lra' => 'LRA',

            'lra_forwarded_to_co',
            'co_forwarded_asst',
            'asst_forwarded_co',
            'lra_forwarded_co_query',
            'ast_forwarded_co_query',
            'lra_forwarded_co_rejected',
            'ast_forwarded_co_rejected' => 'CO',

            'co_forwarded_to_adc',
            'adc_reverted_to_co',
            'adc_query',
            'adc_rejected' => 'ADC',

            'adc_forwarded_to_dc',
            'dc_query',
            'dc_rejected',
            'dc_forwarded_to_js',
            'dc_noc_issued' => 'DC',

            'js_rejected' => 'JS',

            default => 'UNKNOWN',
        };
    }

    private function getCurrentStatus(NocAuditLog $latest): string
    {
        return $latest->application_status ?? 'UNKNOWN';
    }

    private function getWorkflowState(Collection $logs): array
    {
        return [
            'lra' => [
                'completed' => $this->hasAction($logs, [
                    'lra_forwarded_to_co',
                ]),
                'reverted' => $this->hasAction($logs, [
                    'co_sent_back_to_lra',
                ]),
            ],

            'co_initial' => [
                'completed' => $this->hasAction($logs, [
                    'co_forwarded_asst',
                    'co_forwarded_to_adc',
                ]),
                'reverted' => $this->hasAction($logs, [
                    'adc_reverted_to_co',
                ]),
            ],

            'da' => [
                'completed' => $this->hasAction($logs, [
                    'asst_forwarded_co',
                ]),
            ],

            'adc' => [
                'completed' => $this->hasAction($logs, [
                    'adc_forwarded_to_dc',
                ]),
                'reverted' => $this->hasAction($logs, [
                    'adc_reverted_to_co',
                ]),
            ],

            'dc' => [
                'completed' => $this->hasAction($logs, [
                    'dc_forwarded_to_js',
                    'dc_noc_issued',
                ]),
                'reverted' => $this->hasAction($logs, [
                    'dc_reverted_to_adc',
                ]),
            ],

            'js' => [
                'completed' => $this->hasAction($logs, [
                    'dc_forwarded_to_js',
                ]),
            ],

            'final' => [
                'issued' => $this->hasAction($logs, [
                    'dc_noc_issued',
                ]),
                'rejected' => $this->hasAction($logs, [
                    'lra_forwarded_co_rejected',
                    'ast_forwarded_co_rejected',
                    'adc_rejected',
                    'dc_rejected',
                    'js_rejected',
                ]),
                'cancelled' => $this->hasAction($logs, [
                    'noc_cancelled',
                ]),
            ],
        ];
    }

    private function getTabs(
        Collection $logs,
        bool $pendingQuery
    ): array {
        $final = $this->hasAction($logs, [
            'dc_noc_issued',
            'noc_cancelled',
            'lra_forwarded_co_rejected',
            'ast_forwarded_co_rejected',
            'adc_rejected',
            'dc_rejected',
            'js_rejected',
        ]);

        return [
            'lra' => true,

            'co' => $this->hasAction($logs, [
                'lra_forwarded_to_co',
                'co_forwarded_asst',
                'asst_forwarded_co',
                'co_forwarded_to_adc',
            ]),

            'da' => $this->hasAction($logs, [
                'co_forwarded_asst',
                'asst_forwarded_co',
            ]),

            'adc' => $this->hasAction($logs, [
                'co_forwarded_to_adc',
                'adc_forwarded_to_dc',
                'adc_reverted_to_co',
            ]),

            'dc' => $this->hasAction($logs, [
                'adc_forwarded_to_dc',
                'dc_forwarded_to_js',
                'dc_noc_issued',
            ]),

            'js' => $this->hasAction($logs, [
                'dc_forwarded_to_js',
            ]),

            'query' => $pendingQuery,

            'final' => $final,
        ];
    }

    private function hasAction(
        Collection $logs,
        array $actions
    ): bool {
        return $logs->contains(function ($log) use ($actions) {
            return in_array($log->action_code, $actions, true);
        });
    }

    private function hasPendingQuery(string $appno): bool
    {
        /*
         * Replace this with your existing query model/table.
         *
         * Example:
         *
         * return ApplicationQuery::where('appno', $appno)
         *     ->where('status', 'PENDING')
         *     ->exists();
         */

        return false;
    }
}