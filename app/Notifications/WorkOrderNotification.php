<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Notifications\Notification;

class WorkOrderNotification extends Notification
{
    public function __construct(
        private readonly WorkOrder $workOrder,
        private readonly string $message,
        private readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'work_order_id' => $this->workOrder->id,
            'work_order_code' => $this->workOrder->code,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}
