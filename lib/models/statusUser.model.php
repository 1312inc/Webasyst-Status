<?php

/**
 * Class statusUserModel
 */
class statusUserModel extends statusModel
{
    protected $table = 'status_user';

    /**
     * @return array
     */
    public function findAllOrderByLastCheckin()
    {
        return $this->select('*')->order('last_checkin_datetime DESC')->fetchAll();
    }
}
