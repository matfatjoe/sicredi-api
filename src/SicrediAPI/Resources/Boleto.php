<?php

namespace SicrediAPI\Resources;

use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use SicrediAPI\Domain\Boleto\Boleto as BoletoDomain;
use SicrediAPI\Domain\Boleto\Liquidation;
use SicrediAPI\Domain\Boleto\PaymentInformation;
use SicrediAPI\Mappers\Boleto as BoletoMapper;

class Boleto extends ResourceAbstract
{
    public function create(BoletoDomain $boleto): BoletoDomain
    {
        try {
            $payload = BoletoMapper::mapCreateBoleto($boleto);

            $response = $this->post('/cobranca/boleto/v1/boletos', [
                'json' => $payload,
                'headers' => [
                    'cooperativa' => $this->apiClient->getCooperative(),
                    'posto' => $this->apiClient->getPost(),
                ]
            ]);

            $paymentInformation = PaymentInformation::fromArray($response);

            $boleto->setPaymentInformation($paymentInformation);

            if ($boleto->getOurNumber() === null) {
                $boleto->setOurNumber($paymentInformation->getOurNumber());
            }

            return $boleto;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $bodyContent = json_decode(
                $response->getBody()->getContents(),
                true
            );

            if (isset($bodyContent['message']) && !empty($bodyContent['message'])) {
                $exception = new \Exception($bodyContent['message']);
            } else {
                $exception = $e;
            }
            throw $exception;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function query(string $ourNumber): BoletoDomain
    {
        try {
            $response = $this->get('/cobranca/boleto/v1/boletos/', [
                'query' => [
                    'codigoBeneficiario' => $this->apiClient->getBeneficiaryCode(),
                    'nossoNumero' => $ourNumber,
                ],
                'headers' => [
                    'cooperativa' => $this->apiClient->getCooperative(),
                    'posto' => $this->apiClient->getPost(),
                ]
            ]);

            $boleto = BoletoMapper::mapFromQuery($response);

            return $boleto;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw $e;
        }
    }

    public function patchInstruction(
        string $ourNumber,
        string $endpointSuffix,
        array $payload = []
    ) {
        try {
            $url = $this->buildInstructionUrl($ourNumber, $endpointSuffix);

            $options = [
                'headers' => [
                    'codigoBeneficiario' => $this->apiClient->getBeneficiaryCode(),
                    'cooperativa' => $this->apiClient->getCooperative(),
                    'posto' => $this->apiClient->getPost(),
                ],
            ];

            if (empty($payload)) {
                $options['body'] = '{}';
            } else {
                $options['json'] = $payload;
            }

            $response = $this->patch($url, $options);

            return $response;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function buildInstructionUrl(string $ourNumber, string $endpointSuffix): string
    {
        $baseUrl = "/cobranca/boleto/v1/boletos/{$ourNumber}";
        return $baseUrl . $endpointSuffix;
    }


    /**
     * Returns the Boletos liquidated in a specific day.
     * This method returns an instance of Meta\Paginator, which is an iterable object.
     * Upon reaching the end of the page, the next page is automatically fetched.
     * Beware of the performance implications of this method. Memory usage will increase as more pages are fetched.
     *
     * @param DateTime $day
     * @return Meta\Paginator
     * @throws GuzzleException
     */
    public function queryDailyLiquidations(\DateTime $day)
    {
        $liquidations = new Meta\Paginator($this, function ($page) use ($day) {
            return $this->getDailyLiquidationsByPage($day, $page);
        }, function ($items) {
            return BoletoMapper::mapFromQueryDailyLiquidations($items);
        });

        return $liquidations;
    }

    /**
     * Returns the Boletos liquidated in a specific day
     * @param DateTime $day
     * @return Liquidation[]
     * @throws GuzzleException
     */
    private function getDailyLiquidationsByPage(\DateTime $day, int $page = 0)
    {
        $response = $this->get('/cobranca/boleto/v1/boletos/liquidados/dia', [
            'query' => [
                'codigoBeneficiario' => $this->apiClient->getBeneficiaryCode(),
                'dia' => $day->format('d/m/Y'),
                'pagina' => $page,
            ],
            'headers' => [
                'cooperativa' => $this->apiClient->getCooperative(),
                'posto' => $this->apiClient->getPost(),
            ]
        ]);
        return $response;
    }

    public function print(string $numericRepresentation)
    {
        $response = $this->get('/cobranca/boleto/v1/boletos/pdf', [
            'query' => [
                'linhaDigitavel' => $numericRepresentation
            ]
        ], true);
        return $response;
    }
}
