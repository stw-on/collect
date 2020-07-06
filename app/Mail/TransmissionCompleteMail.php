<?php

namespace App\Mail;

use App\Models\Transfer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransmissionCompleteMail extends Mailable
{
    use Queueable, SerializesModels;

    private $transfer;
    private $key;

    /**
     * Create a new message instance.
     *
     * @param Transfer $transfer
     */
    public function __construct(Transfer $transfer, string $key)
    {
        $this->transfer = $transfer;
        $this->key = $key;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->text('transfer-complete-mail', [
            'transfer' => $this->transfer,
            'key' => $this->key,
        ]);
    }
}
