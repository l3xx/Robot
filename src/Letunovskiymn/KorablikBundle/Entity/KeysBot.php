<?php

namespace Letunovskiymn\KorablikBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * KeysBot
 *
 * @ORM\Table(name="keys_bot")
 * @ORM\Entity(repositoryClass="Letunovskiymn\KorablikBundle\Repository\KeysBotRepository")
 */
class KeysBot
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="key_client", type="string", length=255, unique=true)
     */
    private $keyClient;

    /**
     * @var string
     *
     * @ORM\Column(name="chat_id", type="string", length=255)
     */
    private $chatId;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set keyClient
     *
     * @param string $keyClient
     *
     * @return KeysBot
     */
    public function setKeyClient($keyClient)
    {
        $this->keyClient = $keyClient;

        return $this;
    }

    /**
     * Get keyClient
     *
     * @return string
     */
    public function getKeyClient()
    {
        return $this->keyClient;
    }

    /**
     * Set chatId
     *
     * @param string $chatId
     *
     * @return KeysBot
     */
    public function setChatId($chatId)
    {
        $this->chatId = $chatId;

        return $this;
    }

    /**
     * Get chatId
     *
     * @return string
     */
    public function getChatId()
    {
        return $this->chatId;
    }
}

