<?php

namespace SicrediAPI\Mappers;

use SicrediAPI\Domain\Webhook as WebhookDomain;

class Webhook
{
    public static function mapCreateWebhook(WebhookDomain $webhook)
    {
        $base = [
            'eventos' => $webhook->getEvents(),
            'url' => $webhook->getUrl(),
            'urlStatus' => $webhook->getUrlStatus(),
            'contratoStatus' => $webhook->getContractStatus(),
            'nomeResponsavel' => $webhook->getResponsibleName(),
            'email' => $webhook->getEmail(),
            'telefone' => $webhook->getPhone(),
        ];

        // Remove campos vazios
        return array_filter($base, function ($value) {
            return !empty($value);
        });
    }
}
