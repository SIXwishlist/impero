<?php

use Impero\Apache\Provider\Config as ApacheProvider;
use Impero\Dns\Provider\Config as DnsProvider;
use Impero\Ftp\Provider\Config as FtpProvider;
use Impero\Mysql\Provider\Config as MysqlProvider;
use Pckg\Framework\Application;
use Pckg\Framework\Provider;

class Impero extends Provider
{

    public function providers()
    {
        return [
            ApacheProvider::class,
            FtpProvider::class,
            MysqlProvider::class,
            DnsProvider::class,
        ];
    }

}