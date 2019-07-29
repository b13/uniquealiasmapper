<?php

defined('TYPO3_MODE') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['UniqueAlias'] = \B13\UniqueAliasMapper\Routing\PersistedUniqueAliasMapper::class;
