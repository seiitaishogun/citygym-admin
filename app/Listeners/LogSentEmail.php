<?php
/**
 * @author tmtuan
 * created Date: 02-Mar-21
 */
namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Models\EmailLog;

class LogSentEmail {
    /**
     * Handle the event.
     *
     * @param MessageSending $event
     */
    public function handle(MessageSent $event)
    {
        $message = $event->message;

        EmailLog::insert([
            'from' => $this->formatAddressField($message, 'From'),
            'to' => $this->formatAddressField($message, 'To'),
            'cc' => $this->formatAddressField($message, 'Cc'),
            'bcc' => $this->formatAddressField($message, 'Bcc'),
            'subject' => $message->getSubject(),
            'body' => $message->getBody(),
            'headers' => (string)$message->getHeaders(),
            'attachments' => $message->getChildren() ? implode("\n\n", $message->getChildren()) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Format address strings for sender, to, cc, bcc.
     *
     * @param $message
     * @param $field
     * @return null|string
     */
    function formatAddressField($message, $field)
    {
        $headers = $message->getHeaders();

        if (!$headers->has($field)) {
            return null;
        }

        $mailboxes = $headers->get($field)->getFieldBodyModel();

        $strings = [];
        foreach ($mailboxes as $email => $name) {
            $mailboxStr = $email;
            if (null !== $name) {
                $mailboxStr = $name . ' <' . $mailboxStr . '>';
            }
            $strings[] = $mailboxStr;
        }
        return implode(', ', $strings);
    }
}
