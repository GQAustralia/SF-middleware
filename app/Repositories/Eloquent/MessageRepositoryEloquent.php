<?php

namespace App\Repositories\Eloquent;

use App\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Exceptions\DuplicateRecordsException;
use App\Repositories\Exceptions\FailedSyncManyToMany;
use App\Resolvers\InsertIgnoreBulkMySqlResolver;
use Illuminate\Support\Facades\DB;

class MessageRepositoryEloquent extends RepositoryEloquent implements MessageRepositoryInterface
{
    use InsertIgnoreBulkMySqlResolver;

    /**
     * @var Message
     */
    private $message;

    /**
     * MessageRepositoryEloquent constructor.
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return $this->message;
    }

    /**
     * @param array $dataPayload
     * @return static
     * @throws DuplicateRecordsException
     */
    final public function create($dataPayload = [])
    {
        $message = $this->findBy('message_id', $dataPayload['message_id']);

        if ($message) {
            throw new DuplicateRecordsException();
        }

        return parent::create($dataPayload);
    }

    /**
     * @param Message $message
     * @param array $input
     * @return Message
     * @throws FailedSyncManyToMany
     */
    public function attachSubscriber(Message $message, array $input)
    {
        if (empty($message->id)) {
            throw new FailedSyncManyToMany('Message does not exist.');
        }

        if (empty($input)) {
            throw new FailedSyncManyToMany('Subscribers does not exist.');
        }

        $message->subscriber()->syncWithoutDetaching($input);

        return $message;
    }

    /**
     * @param array $messages
     * @return int
     */
    public function insertIgnoreBulk(array $messages)
    {
        $insertFields = ['message_id', 'action_id', 'message_content', 'completed','created_at', 'updated_at'];
        $query = $this->resolve('message', $insertFields, $messages);

        return DB::affectingStatement($query);
    }

    /**
     * @param string $attribute
     * @param array $value
     * @param array $with
     * @param array $optionalWhere
     * @return Message
     */
    public function findAllWhereIn($attribute, $value, $with = [], $optionalWhere = [])
    {
        $result = $this->message->with($with)->whereIn($attribute, $value);

        if (!empty($optionalWhere)) {
            $key = key($optionalWhere);
            $result->where($key, $optionalWhere[$key]);
        }

        return $result->get();
    }

    /**
     * @param integer $messageId
     * @return bool
     */
    public function getTotalFailSentMessage($messageId)
    {
        return $this->message->whereHas('subscriber', function ($subscriber) use ($messageId) {
            $subscriber->where('status', 'failed');
        })->where('message_id', $messageId)->count();
    }

    /**
     * @param array $input
     * @param string $messageId
     * @return null
     */
    public function update(array $input, $messageId)
    {
        $message = $this->message->where('message_id', $messageId)->first();

        if (!$message) {
            return null;
        }

        $message->fill($input);
        $message->save();

        return $message;
    }
}