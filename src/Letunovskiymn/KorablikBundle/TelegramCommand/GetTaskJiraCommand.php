<?php
/**
 * Created by PhpStorm.
 * User: LetunovskiyMN
 * Date: 04.04.2017
 * Time: 16:12
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class GetTaskJiraCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'GetTaskJira';
    protected $description = 'Force reply with reply markup';
    protected $usage = '/getTaskJira';
    protected $version = '0.1.0';
    /**#@-*/

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $chat_id = $this->getMessage()->getChat()->getId();

        $data = [
            'chat_id'      => $chat_id,
            'text'         => 'Write something:sdfsdfsdfsdffs',
//            'reply_markup' => Keyboard::forceReply(),
        ];

        return Request::sendMessage($data);
    }




}