<?php
/**
 * Created by PhpStorm.
 * User: LetunovskiyMN
 * Date: 04.04.2017
 * Time: 16:12
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Bitbucket\API\Authentication\Basic;
use Bitbucket\API\Http\Listener\BasicAuthListener;
use Bitbucket\API\Repositories;
use Bitbucket\API\Teams;
use Bitbucket\API\User;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GetBranchCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'getBranch';
    protected $description = 'Response branches';
    protected $usage = '/getBranch';
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
        /** @var ContainerInterface $container */
        $container=$this->telegram->container;


//        $keyboards[] = new Keyboard(
//            ['7', '8', '9'],
//            ['4', '5', '6'],
//            ['1', '2', '3'],
//            [' ', '0', ' ']
//        );

        $chat_id = $this->getMessage()->getChat()->getId();

        $data = [
            'chat_id'      => $chat_id,
            'text'         =>'sdf',
//            'reply_markup' => Keyboard::forceReply(),
        ];

        return Request::sendMessage($data);
    }




}