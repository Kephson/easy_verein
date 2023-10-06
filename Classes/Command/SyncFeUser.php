<?php

declare(strict_types=1);

namespace EHAERER\EasyVerein\Command;

use Doctrine\DBAL\DBALException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "Manage the members of the society" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022-2023 Ephraim Härer <mail@ephra.im>, EPHRA.IM
 *
 * -- Example with ddev local --
 * Initial:
 * ddev exec typo3cms easyverein:syncfeuser -p 1 -i 1 -m 0 -l 50 -t {mytoken}
 * Sync steps
 * ddev exec typo3cms easyverein:syncfeuser -p 1 -i 0 -m 0 -l 50 -t {mytoken}
 */

/**
 * SyncFeuser
 */
class SyncFeUser extends Command
{

    /**
     * the extension key
     */
    const EXTKEY = 'easy_verein';

    /**
     * Extension settings
     * @var array
     */
    private array $extSettings = [];

    /**
     * Command line options
     * @var array
     */
    private array $options = [];

    /**
     * API token to use
     * @var string
     */
    private string $token = '';

    /**
     * Printout information to commandline
     * @var false
     */
    private bool $printout = false;

    /**
     * @var array results of members from easyVerein
     */
    private array $members = [];

    /**
     * @var array result of member groups from easyVerein compared with TYPO3
     */
    private array $memberGroups = [];

    /**
     * @var OutputInterface Output interface
     */
    private OutputInterface $output;

    /**
     * Synchronize all members
     * @var false
     */
    private bool $syncAll = true;

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Synchronize fe_users via easyVerein API.')
            ->addOption(
                'token',
                't',
                InputArgument::OPTIONAL,
                'easyVerein API token'
            )->addOption(
                'limit',
                'l',
                InputArgument::OPTIONAL,
                'The limit to use with the requests'
            )
            ->addOption(
                'print',
                'p',
                InputArgument::OPTIONAL,
                'Print output on command line',
                0
            )
            ->addOption(
                'initial',
                'i',
                InputArgument::OPTIONAL,
                'Initial syncronisation with easyVerein writing user id to TYPO3',
                0
            )->addOption(
                'printMembers',
                'm',
                InputArgument::OPTIONAL,
                'Print members on command line',
                0
            )->addOption(
                'syncAll',
                'a',
                InputArgument::OPTIONAL,
                'Synchronize all members',
                1
            );
    }

    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int error code
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws GuzzleException|DBALException|InvalidPasswordHashException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->extSettings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(self::EXTKEY);
        $this->options = $input->getOptions();
        $this->output = $output;

        if (isset($this->options['token']) && !empty($this->options['token'])) {
            $this->token = $this->options['token'];
        } else {
            $this->token = $this->extSettings['easy_verein_api_token'];
        }
        if (isset($this->options['print']) && (int)$this->options['print'] === 1) {
            $this->printout = true;
        }
        if (isset($this->options['limit']) && !empty($this->options['limit'])) {
            $limit = (int)$this->options['limit'];
        } else {
            $limit = (int)$this->extSettings['easy_verein_request_limit'];
        }
        if (isset($this->options['syncAll']) && (int)$this->options['syncAll'] === 1) {
            $this->syncAll = true;
        } else {
            $this->syncAll = false;
        }

        $this->loadUserGroups();
        $this->getMembers($limit);

        if ($this->printout) {
            $output->writeln('Number of members loaded: ' . count($this->members));
            $this->printDifferentEmail();
        }
        if (isset($this->options['initial']) && (int)$this->options['initial'] === 1) {
            $initialComparedMembers = $this->initiallyCompareMembers();
            if ($this->printout) {
                $output->writeln('Initially compared members: ' . $initialComparedMembers);
            }
        } else {
            $return = $this->syncronizeMembers();
            if ($this->printout) {
                $output->writeln('------ Synchronization done ------');
                $output->writeln('Updated members: ' . $return['syncronizedMembers']);
                $output->writeln('Added members: ' . $return['addedMembers']);
                $output->writeln('Deleted members: ' . $return['deletedMembers']);
            }
        }
        if (isset($this->options['printMembers']) && (int)$this->options['printMembers'] === 1) {
            $this->printMembers();
        }

        return Command::SUCCESS;
    }

    /**
     * @param int $limit
     * @param string $next
     * @return void
     * @throws GuzzleException
     */
    private function getMembers(int $limit = 100, string $next = '')
    {
        $query = '{id,contactDetails{id,firstName,familyName,name,privateEmail},joinDate,resignationDate,memberGroups{memberGroup{id,short,name}},membershipNumber}';
        $ordering = '-membershipNumber';
        $uri = 'https://easyverein.com/api/stable/member?query=' . $query . '&limit=' . $limit . '&ordering=' . $ordering;
        if (!empty($next)) {
            $uri = $next;
        }

        $results = $this->getApiResults($uri);

        if (isset($results['results'])) {
            foreach ($results['results'] as $m) {
                $this->members[$m['id']] = $m;
            }
            if ($this->printout) {
                $this->output->writeln('Members: ' . count($this->members));
            }
            if (isset($results['next']) && !empty($results['next']) && $this->syncAll) {
                $this->getMembers($limit, $results['next']);
            }
        }
    }

    /**
     * Print member entries e.g. on command line
     *
     * @return void
     */
    private function printMembers()
    {
        $d = " | ";
        if ($this->members) {
            foreach ($this->members as $r) {
                $email = $r['contactDetails']['privateEmail'] ?: " - ";
                $joinDate = '';
                if ($r['joinDate']) {
                    $joinDate = date('d.m.Y', strtotime($r['joinDate']));
                }
                $text = $r['id'] . $d . $r['membershipNumber'] . $d . $joinDate . $d . $r['contactDetails']['name'] . $d . $email;
                $this->output->writeln($text);
            }
        }
    }

    /**
     * Print member entries with different email addresses
     *
     * @return void
     */
    private function printDifferentEmail()
    {
        $d = " | ";
        $i = 0;
        if ($this->members) {
            foreach ($this->members as $r) {
                if (isset($r['typo3Email'])) {
                    if ($i === 0) {
                        $this->output->writeln('--------------------------------------------------');
                        $this->output->writeln('Found follwing different E-Mail Adresses:');
                    }
                    $typo3Email = $r['typo3Email'];
                    $email = $r['contactDetails']['privateEmail'];
                    $joinDate = '';
                    if ($r['joinDate']) {
                        $joinDate = date('d.m.Y', strtotime($r['joinDate']));
                    }
                    $text = $r['id'] . $d . $r['membershipNumber'] . $d . $joinDate . $d . $email . $d . 'TYPO3-E-Mail: ' . $typo3Email;
                    $this->output->writeln($text);
                    $i++;
                }
            }
        }
    }

    /**
     * Initially compare members of easyVerein with the database
     * if email address is not the same, log it to the profile
     *
     * @return int
     * @throws DBALException
     */
    private function initiallyCompareMembers(): int
    {
        $initialComparedMembers = 0;

        $tableName = 'fe_users';
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        $queryBuilder->getRestrictions()->removeAll();

        if ($this->members) {
            foreach ($this->members as $k => $r) {
                $memberNo = trim((string)$r['membershipNumber']);
                $easyVereinPk = $r['id'];
                $evEmail = $r['contactDetails']['privateEmail'];
                $user = $queryBuilder->resetQueryParts()->select('uid', 'username', 'email')
                    ->from($tableName)
                    ->where(
                        $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($memberNo))
                    )->execute()->fetch();
                if ($user) {
                    $update = $queryBuilder->resetQueryParts()
                        ->update($tableName)
                        ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($user['uid'], PDO::PARAM_INT)))
                        ->set('easyverein_pk', $easyVereinPk)
                        ->executeStatement();
                    if (!empty($evEmail) && $evEmail !== $user['email']) {
                        $this->members[$k]['typo3Email'] = $user['email'];
                    }
                    if ($update === 1) {
                        $initialComparedMembers++;
                    }
                }
            }
        }

        return $initialComparedMembers;
    }

    /**
     * Sync members of easyVerein with the fe_user database
     *
     * @return array
     * @throws DBALException
     * @throws InvalidPasswordHashException
     * @throws GuzzleException
     */
    private function syncronizeMembers(): array
    {
        $return = [
            'syncronizedMembers' => 0,
            'addedMembers' => 0,
            'deletedMembers' => 0,
        ];

        $tableName = 'fe_users';
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        $queryBuilder->getRestrictions()->removeAll();

        if ($this->members) {
            foreach ($this->members as $r) {
                $memberNo = trim((string)$r['membershipNumber']);
                $easyVereinPk = $r['id'];
                $evEmail = $r['contactDetails']['privateEmail'];
                $name = $r['contactDetails']['name'];
                $firstName = $r['contactDetails']['firstName'];
                $familyName = $r['contactDetails']['familyName'];
                $deleted = 0;
                $joinDate = 0;
                $resignationDate = 0;
                $tstamp = time();
                if ($r['joinDate']) {
                    $joinDate = strtotime($r['joinDate']);
                }
                if ($r['resignationDate']) {
                    $resignationDate = strtotime($r['resignationDate']);
                    if ($resignationDate < time()) {
                        $deleted = 1;
                    }
                }
                $evUserGroup = '';
                if ($deleted === 0) {
                    $evUserGroup = $this->getEvUserGroups($r);
                }
                $user = $queryBuilder->resetQueryParts()->select('uid', 'username', 'email', 'easyverein_pk', 'usergroup')
                    ->from($tableName)
                    ->where(
                        $queryBuilder->expr()->eq('easyverein_pk', $queryBuilder->createNamedParameter($easyVereinPk, PDO::PARAM_INT))
                    )->execute()->fetch();

                if ($user && isset($user['uid'])) {
                    $userGroup = $this->mergeUsergroups($evUserGroup, $user['usergroup']);
                    $update = $queryBuilder->resetQueryParts()
                        ->update($tableName)
                        ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($user['uid'], PDO::PARAM_INT)))
                        ->set('easyverein_pk', $easyVereinPk)
                        ->set('email', $evEmail)
                        ->set('name', $name)
                        ->set('first_name', $firstName)
                        ->set('last_name', $familyName)
                        ->set('usergroup', $userGroup)
                        ->set('starttime', $joinDate)
                        ->set('endtime', $resignationDate)
                        ->set('deleted', $deleted)
                        ->set('tstamp', $tstamp)
                        ->executeStatement();
                    if ($update === 1) {
                        $return['syncronizedMembers']++;
                        if ($deleted === 1) {
                            $return['deletedMembers']++;
                        }
                    }
                    if ($this->printout && $deleted === 0 && $user['usergroup'] !== $userGroup) {
                        $d = ' | ';
                        $text = $memberNo . $d . 'TYPO3 ID: ' . $user['uid'] . $d . $user['email'];
                        $this->output->writeln($text);
                    }
                } elseif (!empty($evEmail) && !empty($memberNo)) {
                    $password = hash('sha256', uniqid((string)$easyVereinPk, true));
                    $hashInstance = GeneralUtility::makeInstance(PasswordHashFactory::class)->getDefaultHashInstance('FE');
                    $hashedPassword = $hashInstance->getHashedPassword($password);
                    $newUser = [
                        'username' => $memberNo,
                        'email' => $evEmail,
                        'easyverein_pk' => $easyVereinPk,
                        'name' => $name,
                        'first_name' => $firstName,
                        'last_name' => $familyName,
                        'password' => $hashedPassword,
                        'usergroup' => $evUserGroup,
                        'starttime' => $joinDate,
                        'endtime' => $resignationDate,
                        'deleted' => $deleted,
                        'tstamp' => $tstamp,
                        'crdate' => $tstamp,
                        'pid' => $this->extSettings['typo3_default_user_pid'],
                        'cruser_id' => 1,
                    ];
                    $insert = $queryBuilder->resetQueryParts()->insert($tableName)->values($newUser)->executeStatement();
                    if ($insert === 1) {
                        $return['addedMembers']++;
                        if ($deleted === 1) {
                            $return['deletedMembers']++;
                        }
                    }
                }

            }
        }

        return $return;
    }

    /**
     * Get easyVerein user groups to set in TYPO3
     *
     * @param array $member
     *
     * @return string
     * @throws GuzzleException
     */
    private function getEvUserGroups(array $member): string
    {
        $userGroup = $this->extSettings['typo3_default_group_id'];

        if (isset($member['memberGroups']) && is_array($member['memberGroups'])) {
            foreach ($member['memberGroups'] as $g) {
                if (isset($g['id'], $this->memberGroups[$g['id']])) {
                    $userGroup .= ',' . $this->memberGroups[$g['id']];
                }
            }
        }
        return $userGroup;
    }

    /**
     * Get easyVerein user groups to set in TYPO3
     *
     * @param string $newUsergroup
     * @param string $oldUsergroup
     *
     * @return string
     */
    private function mergeUsergroups(string $newUsergroup, string $oldUsergroup): string
    {
        $userGroups = [];
        $newUsergroups = explode(',', $newUsergroup);
        $oldUsergroups = explode(',', $oldUsergroup);

        foreach ($newUsergroups as $n) {
            $id = (int)$n;
            if (!isset($userGroups[$id])) {
                $userGroups[$id] = $id;
            }
        }
        foreach ($oldUsergroups as $o) {
            $id = (int)$o;
            if (!isset($userGroups[$id])) {
                $userGroups[$id] = $id;
            }
        }

        return implode(',', $userGroups);
    }

    /**
     * Get user groups to set in TYPO3
     *
     * @param int $limit
     *
     * @return void
     * @throws GuzzleException|DBALException
     */
    private function loadUserGroups(int $limit = 100): void
    {
        if (empty($this->memberGroups)) {
            $uri = 'https://easyverein.com/api/stable/member-group/?limit=' . $limit;
            $groups = $this->getApiResults($uri);
            $tableName = 'fe_groups';
            if (isset($groups['results'])) {
                /** @var QueryBuilder $queryBuilder */
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
                $queryBuilder->getRestrictions()->removeAll();

                if ($this->printout) {
                    $this->output->writeln('------ Following groups found ------');
                }

                foreach ($groups['results'] as $g) {
                    if (isset($g['short'])) {
                        $evGroupId = $g['id'];
                        $evGroupShort = $g['short'];
                        $group = $queryBuilder->resetQueryParts()->select('uid', 'title', 'easyverein_g_short')
                            ->from($tableName)
                            ->where(
                                $queryBuilder->expr()->eq('easyverein_g_short', $queryBuilder->createNamedParameter($evGroupShort))
                            )->execute()->fetch();
                        if ($group && isset($group['uid'])) {
                            $this->memberGroups[$evGroupId] = $group['uid'];
                            if ($this->printout) {
                                $this->output->writeln('Group: ' . $group['title'] . ' | easyVerein group ID: ' . $evGroupId . ' | TYPO3 ID: ' . $group['uid']);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $uri
     * @return array
     * @throws GuzzleException
     */
    private function getApiResults(string $uri): array
    {
        $client = new Client();
        $response = $client->request('GET', $uri, [
            'headers' => [
                'Authorization' => 'Token ' . $this->token,
            ]
        ]);

        $results = [];
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaderLine('content-type');
        $rawBody = $response->getBody();
        if ($contentType === 'application/json' && $statusCode === 200) {
            $results = json_decode((string)$rawBody, true);
        } else {
            $this->output->writeln('There was an error while getting data via easyVerein API; Statuscode: ' . $statusCode);
        }

        return $results;
    }
}
