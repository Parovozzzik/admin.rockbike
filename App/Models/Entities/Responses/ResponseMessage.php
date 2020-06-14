<?php

namespace App\Models\Entities\Responses;

class ResponseMessage
{
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_INFO = 'info';
    const STATUS_WARNING = 'warning';

    const ICON_SUCCESS = 'hs-admin-check';
    const ICON_ERROR = 'hs-admin-close';
    const ICON_INFO = 'hs-admin-info';
    const ICON_WARNING = 'hs-admin-alert';

    /** @var string */
    protected $status;

    /** @var string */
    protected $message;

    /** @var string */
    protected $icon;

    /**
     * ResponseMessage constructor.
     * @param string $message
     * @param string $status
     * @param string $icon
     */
    public function __construct(string $message, string $status = '', string $icon = '')
    {
        $this->message = $message;
        $this->status = trim($status) === '' ? self::STATUS_INFO : $status;
        $this->icon = trim($icon) === '' ? self::ICON_WARNING : $icon;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'status' => $this->getStatus(),
            'icon' => $this->getIcon(),
        ];
    }
}