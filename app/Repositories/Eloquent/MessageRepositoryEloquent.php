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
        $insertFields = ['message_id', 'queue_id', 'message_content', 'completed'];
        $query = $this->resolve('message', $insertFields, $messages);

        return DB::affectingStatement($query);
    }

    /**
     * @param string $attribute
     * @param array $value
     * @param array $with
     * @return Message
     */
    public function findAllWhereIn($attribute, $value, $with = [])
    {
        return $this->message->with($with)->whereIn($attribute, $value)->get();
    }
}