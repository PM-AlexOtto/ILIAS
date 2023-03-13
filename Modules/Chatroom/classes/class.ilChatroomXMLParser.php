<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * Class ilChatroomXMLParser
 */
class ilChatroomXMLParser extends ilSaxParser
{
    protected ilChatroom $room;
    protected string $cdata = '';
    protected bool $in_sub_rooms = false;
    protected bool $in_messages = false;
    protected ?string $import_install_id = null;
    protected ?int $exportRoomId = 0;
    protected ?int $owner = 0;
    protected ?int $closed = 0;
    protected ?int $public = 0;
    protected ?int $timestamp = 0;
    protected ?string $message = '';
    protected ?string $title = '';
    /** @var int[]  */
    protected array $userIds = [];

    public function __construct(protected ilObjChatroom $chat, string $a_xml_data)
    {
        parent::__construct();

        $room = ilChatroom::byObjectId($this->chat->getId());
        if ($room !== null) {
            $this->room = $room;
        } else {
            $this->room = new ilChatroom();
            $this->room->setSetting('object_id', $this->chat->getId());
            $this->room->save();
        }

        $this->setXMLContent('<?xml version="1.0" encoding="utf-8"?>' . $a_xml_data);
    }

    public function setImportInstallId(?string $id): void
    {
        $this->import_install_id = $id;
    }

    public function getImportInstallId(): ?string
    {
        return $this->import_install_id;
    }

    private function isSameInstallation(): bool
    {
        return defined('IL_INST_ID') && IL_INST_ID > 0 && $this->getImportInstallId() == IL_INST_ID;
    }

    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, [$this, 'handlerBeginTag'], [$this, 'handlerEndTag']);
        xml_set_character_data_handler($a_xml_parser, [$this, 'handlerCharacterData']);
    }

    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
    {
        switch ($a_name) {
            case 'Messages':
                $this->in_messages = true;
                break;
        }
    }

    public function handlerEndTag($a_xml_parser, string $a_name): void
    {
        $this->cdata = trim($this->cdata);

        switch ($a_name) {
            case 'Title':
                if ($this->in_sub_rooms) {
                    $this->title = ilUtil::stripSlashes($this->cdata);
                } else {
                    $this->chat->setTitle(ilUtil::stripSlashes($this->cdata));
                }
                break;

            case 'Description':
                $this->chat->setDescription(ilUtil::stripSlashes($this->cdata));
                break;

            case 'OnlineStatus':
                $this->room->setSetting('online_status', (int) $this->cdata);
                break;

            case 'AllowAnonymousAccess':
                $this->room->setSetting('allow_anonymous', (int) $this->cdata);
                break;

            case 'AllowCustomUsernames':
                $this->room->setSetting('allow_custom_usernames', (int) $this->cdata);
                break;

            case 'EnableHistory':
                $this->room->setSetting('enable_history', (int) $this->cdata);
                break;

            case 'RestrictHistory':
                $this->room->setSetting('restrict_history', (int) $this->cdata);
                break;

            case 'DisplayPastMessages':
                $this->room->setSetting('display_past_msgs', (int) $this->cdata);
                break;

            case 'AutoGeneratedUsernameSchema':
                $this->room->setSetting('autogen_usernames', ilUtil::stripSlashes($this->cdata));
                break;

            case 'RoomId':
                $this->exportRoomId = (int) $this->cdata;
                break;

            case 'Owner':
                $this->owner = (int) $this->cdata;
                break;

            case 'Closed':
                $this->closed = (int) $this->cdata;
                break;

            case 'Public':
                $this->public = (int) $this->cdata;
                break;

            case 'CreatedTimestamp':
                $this->timestamp = (int) $this->cdata;
                break;

            case 'PrivilegedUserId':
                $this->userIds[] = (int) $this->cdata;
                break;
            case 'SubRooms':
                $this->in_sub_rooms = false;
                break;

            case 'Body':
                $this->message = $this->cdata;
                break;

            case 'Message':
                break;

            case 'Messages':
                $this->in_messages = false;
                break;

            case 'Chatroom':
                $this->chat->update();
                // Set imported chats to offline
                $this->room->setSetting('online_status', 0);
                $this->room->save();
                break;
        }

        $this->cdata = '';
    }

    public function handlerCharacterData($a_xml_parser, string $a_data): void
    {
        if ($a_data !== "\n") {
            $this->cdata .= preg_replace("/\t+/", ' ', $a_data);
        }
    }
}
