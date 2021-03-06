<?php namespace Impero\Mysql\Record;

use Impero\Mysql\Entity\Users;
use Pckg\Database\Record;
use Pckg\Maestro\Service\Contract\Record as MaestroRecord;

class User extends Record implements MaestroRecord
{

    protected $entity = Users::class;

    /**
     * Build edit url.
     *
     * @return string
     */
    public function getEditUrl()
    {
        return url('user.edit', ['user' => $this]);
    }

    /**
     * Build delete url.
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return url('user.delete', ['user' => $this]);
    }

    public function setUserIdByAuthIfNotSet()
    {
        if (!$this->user_id) {
            $this->user_id = auth()->user('id');
        }

        return $this;
    }

}
