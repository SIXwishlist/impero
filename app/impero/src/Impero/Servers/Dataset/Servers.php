<?php namespace Impero\Servers\Dataset;

use Impero\Servers\Entity\Servers as ServersEntity;
use Impero\Servers\Record\Server;
use Impero\Services\Service\Apache;
use Impero\Services\Service\Cron;
use Impero\Services\Service\Mysql;
use Impero\Services\Service\Nginx;
use Impero\Services\Service\Openvpn;
use Impero\Services\Service\Php;
use Impero\Services\Service\PhpFpm;
use Impero\Services\Service\Pureftpd;
use Impero\Services\Service\Sendmail;
use Impero\Services\Service\Ssh;
use Impero\Services\Service\SshConnection;
use Impero\Services\Service\Ufw;
use Pckg\Database\Relation\HasAndBelongsTo;

class Servers
{

    public function getServerForUser($serverId)
    {
        return (new ServersEntity())->withTags()
                                    ->withSystem()
                                    ->withServices(function(HasAndBelongsTo $services) {
                                        $services->getMiddleEntity()->withStatus();
                                    })
                                    ->withDependencies(function(HasAndBelongsTo $dependencies) {
                                        $dependencies->getMiddleEntity()->withStatus();
                                    })
                                    ->withJobs()
                                    ->where('id', $serverId)
                                    ->oneOrFail();
    }

    public function getServersForUser()
    {
        return (new ServersEntity())->withTags()
                                    ->withSystem()
                                    ->withServices(function(HasAndBelongsTo $services) {
                                        $services->getMiddleEntity()->withStatus();
                                    })
                                    ->withDependencies(function(HasAndBelongsTo $dependencies) {
                                        $dependencies->getMiddleEntity()->withStatus();
                                    })
                                    ->withJobs()
                                    ->all()
                                    ->map(function(Server $server) {
                                        $data = $server->toArray();
                                        try {
                                            $connection = $server->getConnection();
                                        } catch (\Throwable $e) {
                                            $data['status'] = $e->getMessage();
                                        }

                                        $data['tags'] = $server->tags->toArray();
                                        $data['os'] = $server->system->toArray();

                                        $data['url'] = url('impero.servers.server', ['server' => $server->id]);
                                        $data['applications'] = $this->getServerApplications();
                                        $data['websites'] = $this->getServerWebsites();
                                        $data['deployments'] = $this->getServerDeployments();
                                        $data['logs'] = $this->getServerLogs();

                                        return $data;
                                    });
    }

    public function getServerServices()
    {
        $connection = new SshConnection();
        $apache = new Apache($connection);
        $mysql = new Mysql($connection);
        $ufw = new Ufw($connection);
        $php = new Php($connection);
        $phpfpm = new PhpFpm($connection);
        $nginx = new Nginx($connection);
        $ssh = new Ssh($connection);
        $cron = new Cron($connection);
        $openvpn = new Openvpn($connection);
        $sendmail = new Sendmail($connection);
        $pureftpd = new Pureftpd($connection);

        return [
            [
                'name'      => 'Apache',
                'version'   => $apache->getVersion(),
                'status'    => $apache->getStatus(),
                'installed' => $apache->isInstalled() ? 'yes' : 'no',
            ],
            [
                'name'      => $php->getName(),
                'version'   => $php->getVersion(),
                'status'    => $php->getStatus(),
                'installed' => $php->isInstalled() ? 'yes' : 'no',
            ],
            [
                'name'      => $phpfpm->getName(),
                'version'   => $phpfpm->getVersion(),
                'status'    => $phpfpm->getStatus(),
                'installed' => $phpfpm->isInstalled() ? 'yes' : 'no',
            ],
            [
                'name'      => $ufw->getName(),
                'version'   => $ufw->getVersion(),
                'status'    => $ufw->getStatus(),
                'installed' => $ufw->isInstalled() ? 'yes' : 'no',
            ],
            [
                'name'      => $mysql->getName(),
                'version'   => $mysql->getVersion(),
                'status'    => $mysql->getStatus(),
                'installed' => $mysql->isInstalled() ? 'yes' : 'no',
            ],
            [
                'name'      => $nginx->getName(),
                'version'   => $nginx->getVersion(),
                'status'    => $nginx->getStatus(),
                'installed' => $nginx->isInstalled() ? 'yes' : 'no',
            ],
            [
                'name'      => $ssh->getName(),
                'version'   => $ssh->getVersion(),
                'status'    => $ssh->getStatus(),
                'installed' => $ssh->isInstalled() ? 'yes' : 'no',
            ],
            [
                'name'      => $cron->getName(),
                'version'   => $cron->getVersion(),
                'status'    => $cron->getStatus(),
                'installed' => $cron->isInstalled() ? 'yes' : 'no',
            ],
            [
                'name'      => $openvpn->getName(),
                'version'   => $openvpn->getVersion(),
                'status'    => $openvpn->getStatus(),
                'installed' => $openvpn->isInstalled() ? 'yes' : 'no',
            ],
            [
                'name'      => $sendmail->getName(),
                'version'   => $sendmail->getVersion(),
                'status'    => $sendmail->getStatus(),
                'installed' => $sendmail->isInstalled() ? 'yes' : 'no',
            ],
            [
                'name'      => $pureftpd->getName(),
                'version'   => $pureftpd->getVersion(),
                'status'    => $pureftpd->getStatus(),
                'installed' => $pureftpd->isInstalled() ? 'yes' : 'no',
            ],
        ];
    }

    public function getServerDependencies()
    {
        return [
            [
                'name'    => 'Composer',
                'version' => $this->getVersion(),
            ],
            [
                'name'    => 'Npm',
                'version' => $this->getVersion(),
            ],
            [
                'name'    => 'Bower',
                'version' => $this->getVersion(),
            ],
            [
                'name'    => 'Git',
                'version' => $this->getVersion(),
            ],
            [
                'name'    => 'Svn',
                'version' => $this->getVersion(),
            ],
        ];
    }

    public function getServerDeployments()
    {
        return [
            [
                'application' => $this->getApplication(),
                'started_at'  => $this->getDatetime(),
                'ended_at'    => $this->getDatetime(),
                'status'      => 'ok',
                'log'         => '',
                'version'     => $this->getGitVersion(),
            ],
            [
                'application' => $this->getApplication(),
                'started_at'  => $this->getDatetime(),
                'ended_at'    => $this->getDatetime(),
                'status'      => 'ok',
                'log'         => '',
                'version'     => $this->getGitVersion(),
            ],
            [
                'application' => $this->getApplication(),
                'started_at'  => $this->getDatetime(),
                'ended_at'    => $this->getDatetime(),
                'status'      => 'ok',
                'log'         => '',
                'version'     => $this->getGitVersion(),
            ],
        ];
    }

    public function getApplication()
    {
        $applications = $this->getServerApplications();

        return $applications[array_rand($applications)];
    }

    public function getServerApplications()
    {
        return [
            [
                'name'    => 'GoNParty Shop',
                'url'     => 'http://gonparty.eu',
                'status'  => 'success',
                'version' => $this->getGitVersion(),
                'type'    => 'www',
                'source'  => 'git',
            ],
            [
                'name'    => 'HardIsland Shop',
                'url'     => 'http://shop.hardisland.com',
                'status'  => 'warning',
                'version' => $this->getGitVersion(),
                'type'    => 'www',
                'source'  => 'svn',
            ],
            [
                'name'    => 'GoNParty Market Place',
                'url'     => 'http://gonparty.xyz',
                'status'  => 'success',
                'version' => $this->getGitVersion(),
                'type'    => 'www',
                'source'  => 'zip',
            ],
            [
                'name'    => 'Start Maestro',
                'url'     => 'http://startmaestro.com',
                'status'  => 'danger',
                'version' => $this->getGitVersion(),
                'type'    => 'www',
                'source'  => 'git',
            ],
        ];
    }

    public function getServerWebsites()
    {
        return [
            [
                'name'        => 'GoNParty',
                'url'         => 'https://gonparty.eu',
                'application' => $this->getApplication(),
                'https'       => 'on',
                'version'     => $this->getGitVersion(),
                'status'      => 'offline',
                'urls'        => [
                    'si.gonparty.eu',
                    'www.gonparty.eu',
                    'hr.gonparty.eu',
                ],
            ],
            [
                'name'        => 'HardIsland',
                'url'         => 'https://shop.hardisland.com',
                'application' => $this->getApplication(),
                'https'       => 'on',
                'version'     => $this->getGitVersion(),
                'status'      => 'online',
                'urls'        => [],
            ],
            [
                'name'        => 'Server status',
                'url'         => 'http://status.foobar.si',
                'application' => $this->getApplication(),
                'https'       => 'on',
                'version'     => $this->getGitVersion(),
                'status'      => 'online',
                'urls'        => [],
            ],
            [
                'name'        => 'GNP.si',
                'url'         => 'http://gnp.si',
                'application' => $this->getApplication(),
                'https'       => 'on',
                'version'     => $this->getGitVersion(),
                'status'      => 'online',
            ],
        ];
    }

    private function getCommand()
    {
        $dirs = [
            '/backup/cli/',
            '/www/user/domain.tld/htdocs/',
            '/home/user/',
        ];
        $prefixes = ['sh', 'php'];
        $execs = ['console', 'database.sh', 'foo'];
        $args = [
            '--option --argument=1',
            '--bla --blabla --foo=bar',
        ];

        return $prefixes[array_rand($prefixes)] . ' ' . $dirs[array_rand($dirs)] . ' ' .
               $execs[array_rand($execs)] . ' ' . $args[array_rand($args)] . ' ';
    }

    private function getGitVersion()
    {
        return '#' . substr(sha1(microtime()), 0, 12);
    }

    private function getVersion()
    {
        $length = rand(1, 3);
        $versions = [];
        foreach (range(1, $length) as $length) {
            $versions[] = rand(1, 5);
        }

        return 'v' . implode('.', $versions);
    }

    private function getDatetime()
    {
        return date('Y-m-d H:i:s', rand(time() - (2 * 365 * 24 * 60 * 60), time()));
    }

    private function getServerLogs()
    {
        return [
            [
                'name'       => 'SSH key created',
                'created_at' => $this->getDatetime(),
            ],
        ];
    }

    private function getFrequency()
    {
        $freqs = [
            'every ' . rand(1, 50) . ' minutes',
            'every ' . rand(1, 50) . ' hours',
            'every day at ' . rand(0, 23) . ':00',
            'every monday and thursday at ' . rand(0, 23) . ':30',
            'every first friday in month',
            'every day',
        ];

        return $freqs[array_rand($freqs)];
    }

    private function getServerTags()
    {
        return ['loadbalancer', 'master', 'slave', 'mail', 'web', 'database', 'storage'];
    }

}