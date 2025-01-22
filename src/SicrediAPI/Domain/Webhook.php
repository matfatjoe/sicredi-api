<?php

namespace SicrediAPI\Domain;

class Webhook
{
    private $events;
    private $url;
    private $urlStatus;
    private $contractStatus;
    private $responsibleName;
    private $email;
    private $phone;

    public function __construct(
        array $events,
        string $url,
        string $urlStatus,
        string $contractStatus,
        string $responsibleName,
        string $email,
        string $phone
    ) {
        $this->events = $events;
        $this->url = $url;
        $this->urlStatus = $urlStatus;
        $this->contractStatus = $contractStatus;
        $this->responsibleName = $responsibleName;
        $this->email = $email;
        $this->phone = $phone;
    }


    public function getEvents(): array
    {
        return $this->events;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getUrlStatus(): string
    {
        return $this->urlStatus;
    }

    public function getContractStatus(): string
    {
        return $this->contractStatus;
    }

    public function getResponsibleName(): string
    {
        return $this->responsibleName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }
}
