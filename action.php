<?php

use dokuwiki\plugin\struct\meta\AccessTable;
use dokuwiki\plugin\struct\meta\Assignments;
use dokuwiki\plugin\struct\meta\StructException;

/**
 * DokuWiki Plugin structacl (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Anna Dabrowska <dokuwiki@cosmocode.de>
 */
class action_plugin_structacl extends \dokuwiki\Extension\ActionPlugin
{
    /** @inheritDoc */
    public function register(Doku_Event_Handler $controller)
    {
        $mode = $this->getConf('run');
        $controller->register_hook('AUTH_ACL_CHECK', $mode, $this, 'handleAclCheck', $mode);
    }

    /**
     * Event handler for AUTH_ACL_CHECK.
     *
     * If current user is found in a configured struct field of the current page,
     * upload permissions are granted.
     *
     * @param Doku_Event $event event object by reference
     * @param string $mode BEFORE|AFTER
     * @return void
     */
    public function handleAclCheck(Doku_Event $event, $mode)
    {
        global $ID;
        global $REV;

        $helper = plugin_load('helper', 'structacl');
        $config = $helper->getConfiguration($this->getConf('fields'));

        if (empty($config)) return;

        // check if current page is assigned a schema from configuration
        $assignments = Assignments::getInstance();
        $schemas = $assignments->getPageAssignments($ID);

        if (empty($schemas)) return;

        // get users from schema data
        $users = [];
        foreach ($config as $schemaName => $fields) {
            if (!in_array($schemaName, $schemas)) {
                continue;
            }
            try {
                $schema = new \dokuwiki\plugin\struct\meta\Schema($schemaName);
                $schemaData = AccessTable::getPageAccess($schemaName, $ID, (int)$REV);
                $data = $schemaData->getData();
                foreach ($fields as $field) {
                    $col = $schema->findColumn($field);
                    if ($col && is_a($col->getType(), \dokuwiki\plugin\struct\types\User::class)) {
                        $value = $data[$field]->getValue();
                        if (empty($value)) continue;
                        // multivalue field?
                        if (is_array($value)) {
                            $users = array_merge($users, $value);
                        } else {
                            $users[] = $value;
                        }
                    }
                }
            } catch (StructException $ignored) {
                continue; // no such schema at this revision
            }
        }

        // grant upload permissions if current user is found in struct field
        if (!empty($users) && in_array($event->data['user'], $users)) {
            $event->result = AUTH_UPLOAD;
        }

        // disable standard ACL check for non-admins
        if ($mode === 'BEFORE' && !auth_isadmin()) {
            $event->preventDefault();
        }
    }
}
