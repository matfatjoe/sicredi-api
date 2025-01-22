<?php

namespace SicrediAPI\Resources;

use SicrediAPI\Domain\Webhook as WebhookDomain;
use SicrediAPI\Mappers\Webhook as WebhookMapper;

class Webhook extends ResourceAbstract
{
    public function create(WebhookDomain $webhook): WebhookDomain
    {
        $payload = WebhookMapper::mapCreateWebhook($webhook);

        $payload = array_merge(
            $payload,
            [
                'cooperativa' => $this->apiClient->getCooperative(),
                'posto' => $this->apiClient->getPost(),
                'codBeneficiario' => $this->apiClient->getBeneficiaryCode()
            ]
        );

        $response = $this->post('/cobranca/boleto/v1/webhook/contrato', [
            'json' => $payload,
        ]);

        return $webhook;
    }    
}
