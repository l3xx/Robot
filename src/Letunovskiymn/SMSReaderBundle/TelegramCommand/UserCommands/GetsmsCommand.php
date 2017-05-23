<?php
/**
 * Created by PhpStorm.
 * User: letunovskiymn
 * Date: 22.05.17
 * Time: 19:33
 */

namespace Longman\TelegramBot\Commands\UserCommands;


use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

class GetsmsCommand extends UserCommand
{

    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'getSms sms from phone';
    protected $description = 'Return SMS by secret code';
    protected $usage = '/getSms';
    protected $version = '0.1.0';
    protected $enabled = true;
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

        $switch_element = mt_rand(0, 9) < 5 ? 'true' : 'false';

        $inline_keyboard = new InlineKeyboard([
            ['text' => 'inline', 'switch_inline_query' => $switch_element],
            ['text' => 'inline current chat', 'switch_inline_query_current_chat' => $switch_element],
        ], [
            ['text' => 'callback', 'callback_data' => 'identifier'],
            ['text' => 'open url', 'url' => 'https://github.com/akalongman/php-telegram-bot'],
        ]);

        $data = [
            'chat_id'      => $chat_id,
            'text'         => 'inline keyboard',
            'reply_markup' => $inline_keyboard,
        ];

        return Request::sendMessage($data);
    }




}