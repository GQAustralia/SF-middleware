<?php

namespace App\Repositories\Eloquent;

use App\OutboundMessage;
use App\Repositories\Contracts\OutboundMessageInterface;

class OutboundMessageRepositoryEloquent extends RepositoryEloquent implements OutboundMessageInterface
{
    /**
     * @var OutboundMessage
     */
    protected $outboundMessageLog;

    /**
     * OutboundMessageRepositoryEloquent constructor.
     *
     * @param OutboundMessage $outboundMessageLog
     */
    public function __construct(OutboundMessage $outboundMessageLog)
    {
        $this->outboundMessageLog = $outboundMessageLog;
    }

    /**
     * @return OutboundMessage
     */
    public function model()
    {
        return $this->outboundMessageLog;
    }

    /**
     * @param array $input
     * @param mixed $field
     * @return null
     */
    public function update(array $input, $field)
    {
        $message = $this->outboundMessageLog->where('id', $field)->first();

        if (!$message) {
            return null;
        }

        $message->fill($input);
        $message->save();

        return $message;
    }
}
