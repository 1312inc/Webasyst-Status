<?php

/**
 * Class statusBackendAction
 */
class statusChronologyAction extends statusViewAction
{
    /**
     * @var statusUser
     */
    protected $user;

    /**
     * @throws kmwaNotFoundException
     * @throws waException
     */
    protected function preExecute()
    {
        $contactId = waRequest::get('contact_id', 0, waRequest::TYPE_INT);

        $this->user = !$contactId
            ? stts()->getUser()
            : stts()->getEntityRepository(statusUser::class)->findByContactId($contactId);

        if (!$this->user instanceof statusUser) {
            throw new kmwaNotFoundException('User not found');
        }

        if (!$this->user->isExists()) {
            throw new kmwaNotFoundException('User not found');
        }

        //todo: can view user
        if ($this->user->getContactId() != wa()->getUser()->getId()) {

        }

        stts()->setContextUser($this->user);
    }

    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function runAction($params = null)
    {
        $weeksDto = statusWeekFactory::getWeeksDto($this->user, 5, true);
        $currentWeek = array_shift($weeksDto);

        $this->view->assign(
            [
                'currentWeek'        => $currentWeek,
                'weeks'              => $weeksDto,
                'sidebar_html'       => (new statusBackendSidebarAction())->display(),
                'current_contact_id' => $this->user->getContactId(),
            ]
        );
    }
}
