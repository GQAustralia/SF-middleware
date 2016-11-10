<?php

namespace App\Repositories\Contracts;

use App\InboundMessage;

interface InboundMessageRepositoryInterface extends RepositoryInterface
{
    /**
     * @param InboundMessage $message
     * @param array $input
     * @return mixed
     */
    public function attachSubscriber(InboundMessage $message, array $input);

    /**
     * @param array $insertUpdateBulk
     * @return mixed
     */
    public function insertIgnoreBulk(array $insertUpdateBulk);

    /**
     * @param string $attribute
     * @param array $value
     * @param array $with
     * @return mixed
     */
    public function findAllWhereIn($attribute, $value, $with = []);

    /**
     * @param integer $messageId
     * @return integer
     */
    public function getTotalFailSentMessage($messageId);

    /**
     * @param array $input
     * @param string $messageId
     * @return mixed
     */
    public function update(array $input, $messageId);
}