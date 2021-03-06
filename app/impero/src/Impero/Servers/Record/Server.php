<?php namespace Impero\Servers\Record;

use Impero\Jobs\Record\Job;
use Impero\Servers\Entity\Servers;
use Impero\Services\Service\SshConnection;
use Pckg\Database\Record;

class Server extends Record
{

    protected $entity = Servers::class;

    protected $toArray = ['services', 'dependencies', 'jobs'];

    protected $connection;

    public function getConnection()
    {
        if (!$this->connection) {
            $this->connection = new SshConnection($this->ip, $this->user, $this->port, path('storage') . 'private/keys/id_rsa_' . $this->id);
        }

        return $this->connection;
    }

    public function fetchJobs()
    {
        $connection = $this->getConnection();
        $users = [
            'root',
            'impero',
            'www-data',
            'schtr4jh',
        ];
        $jobs = [];
        foreach ($users as $user) {
            $result = $connection->exec('sudo crontab -l -u ' . $user, $error);
            if (!$result) {
                continue;
            }
            $lines = explode("\n", $result);
            foreach ($lines as $line) {
                $line = trim($line);

                if (!$line) {
                    continue;
                }

                $inactive = false;
                if (strpos($line, '#impero') === false) {
                    $inactive = true;
                } elseif (strpos($line, '#') === 0) {
                    continue;
                }

                if (strpos($line, 'MAILTO') === 0) {
                    continue;
                }

                $command = implode(' ', array_slice(explode(' ', $line), 5));
                $frequency = substr($line, 0, strlen($line) - strlen($command));

                Job::create([
                                'server_id' => $this->id,
                                'name'      => '',
                                'status'    => $inactive
                                    ? 'inactive'
                                    : 'active',
                                'command'   => $command,
                                'frequency' => $frequency,
                            ]);
            }
        }

        return $jobs;
    }

}