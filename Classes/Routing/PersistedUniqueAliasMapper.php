<?php
declare(strict_types=1);

namespace B13\UniqueAliasMapper\Routing;

/*
 * This file is part of TYPO3 CMS-based extension "uniquealiasmapper" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */


use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Routing\Aspect\PersistedMappableAspectInterface;
use TYPO3\CMS\Core\Routing\Aspect\PersistenceDelegate;
use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;
use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Works like uniqAlias in RealURL
 */
class PersistedUniqueAliasMapper implements PersistedMappableAspectInterface, StaticMappableAspectInterface
{
    use SiteLanguageAwareTrait;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $aliasField;

    /**
     * @var PersistenceDelegate
     */
    protected $persistenceDelegate;

    /**
     * @var string[]
     */
    protected $persistenceFieldNames;

    /**
     * @var string|null
     */
    protected $languageParentFieldName;

    /**
     * @param array $settings
     * @throws \InvalidArgumentException
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
        $this->tableName = $settings['tableName'] ?? null;
        $this->aliasField = $settings['aliasField'] ?? null;
        $this->persistenceFieldNames = $this->buildPersistenceFieldNames();
        $this->languageParentFieldName = $GLOBALS['TCA'][$this->tableName]['ctrl']['transOrigPointerField'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $value): ?string
    {
        $aliasFromCache = $this->fetchProcessedAliasFromCache($value);
        if (!empty($aliasFromCache)) {
            return $aliasFromCache;
        }
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->tableName);
        $result = $queryBuilder
            ->select(...$this->persistenceFieldNames)
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($value, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetch();
        $result = $this->resolveOverlay($result);

        if (!isset($result[$this->aliasField])) {
            return null;
        }

        $originalAlias = $result[$this->aliasField];
        $helper = new SlugHelper($this->tableName, $this->aliasField, $this->settings['uniqueConfiguration'] ?? []);
        $alias = $helper->sanitize($originalAlias);
        $alias = trim($alias);

        // store in uniquealias table, but never duplicates
        if ($this->fetchIdFromProcessedAliasFromCache($alias) > 0) {
            $alias = $alias . '-' . $value;
        }
        $this->storeInCache($alias, $value);

        return (string)$alias;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $value): ?string
    {
        return (string)$this->fetchIdFromProcessedAliasFromCache($value);
    }

    /**
     * @return string[]
     */
    protected function buildPersistenceFieldNames(): array
    {
        return array_filter([
            'uid',
            'pid',
            $this->aliasField,
            $GLOBALS['TCA'][$this->tableName]['ctrl']['languageField'] ?? null,
            $GLOBALS['TCA'][$this->tableName]['ctrl']['transOrigPointerField'] ?? null,
        ]);
    }

    /**
     * @param array|null $record
     * @return array|null
     */
    protected function resolveOverlay(?array $record): ?array
    {
        $languageId = $this->siteLanguage->getLanguageId();
        if ($record === null || $languageId === 0) {
            return $record;
        }

        $pageRepository = $this->createPageRepository();
        return $pageRepository->getLanguageOverlay($this->tableName, $record);
    }

    /**
     * @return PageRepository
     */
    protected function createPageRepository(): PageRepository
    {
        $context = clone GeneralUtility::makeInstance(Context::class);
        $context->setAspect(
            'language',
            LanguageAspectFactory::createFromSiteLanguage($this->siteLanguage)
        );
        return GeneralUtility::makeInstance(PageRepository::class, $context);
    }

    protected function fetchProcessedAliasFromCache($uidValue)
    {
        $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_uniquealiasmapper');
        $result = $conn->select(
            ['aliasvalue'],
            'tx_uniquealiasmapper',
            [
                'recorduid' => $uidValue,
                'aliasfieldname' => $this->aliasField,
                'tablename' => $this->tableName,
                'sys_language_uid' => $this->siteLanguage->getLanguageId()
            ]
        )->fetch();
        if (is_array($result)) {
            return $result['aliasvalue'];
        }
        return null;
    }

    protected function fetchIdFromProcessedAliasFromCache($aliasValue)
    {
        $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_uniquealiasmapper');
        $result = $conn->select(
            ['recorduid'],
            'tx_uniquealiasmapper',
            [
                'aliasvalue' => $aliasValue,
                'aliasfieldname' => $this->aliasField,
                'tablename' => $this->tableName,
                'sys_language_uid' => $this->siteLanguage->getLanguageId()
            ]
        )->fetch();
        if (is_array($result)) {
            return (int)$result['recorduid'];
        }
        return null;
    }

    protected function storeInCache($aliasValue, $uidValue)
    {
        $conn = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_uniquealiasmapper');
        $conn->insert(
            'tx_uniquealiasmapper',
            [
                'recorduid' => $uidValue,
                'aliasvalue' => $aliasValue,
                'aliasfieldname' => $this->aliasField,
                'tablename' => $this->tableName,
                'expireson' => 0,
                'updatedon' => $GLOBALS['EXEC_TIME'],
                'sys_language_uid' => $this->siteLanguage->getLanguageId()
            ]
        );
    }
}
