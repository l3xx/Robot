<?php
/**
 * Created by PhpStorm.
 * User: letunovskiymn
 * Date: 22.05.17
 * Time: 19:12
 */

namespace Letunovskiymn\SMSReaderBundle\Telegram;


use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class Telegram extends \Longman\TelegramBot\Telegram
{
    /**
     * Process bot Update request
     *
     * @param \Longman\TelegramBot\Entities\Update $update
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function processUpdate(Update $update)
    {

        $this->update = $update;

        //If all else fails, it's a generic message.
        $command = 'genericmessage';

        $update_type = $this->update->getUpdateType();
        if (in_array($update_type, ['edited_message', 'channel_post', 'edited_channel_post', 'inline_query', 'chosen_inline_result', 'callback_query'], true)) {
            $command = $this->getCommandFromType($update_type);
        } elseif ($update_type === 'message') {
            $message = $this->update->getMessage();

//            $this->addCommandsPath(BASE_COMMANDS_PATH . '/UserCommands', false);

            $type = $message->getType();
            if ($type === 'command') {
                $command = $message->getCommand();
            } elseif (in_array($type, [
                'channel_chat_created',
                'delete_chat_photo',
                'group_chat_created',
                'left_chat_member',
                'migrate_from_chat_id',
                'migrate_to_chat_id',
                'new_chat_member',
                'new_chat_photo',
                'new_chat_title',
                'pinned_message',
                'supergroup_chat_created',
            ], true)
            ) {
                $command = $this->getCommandFromType($type);
            }
        }

        //Make sure we have an up-to-date command list
        //This is necessary to "require" all the necessary command files!
        $this->getCommandsList();

        return $this->executeCommand($command);
    }
}