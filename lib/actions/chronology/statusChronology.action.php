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
     * @var statusProject
     */
    protected $project;

    /**
     * @var bool
     */
    protected $isMe = false;

    /**
     * @var bool
     */
    protected $isProject = false;

    /**
     * @throws kmwaNotFoundException
     * @throws waException
     */
    protected function preExecute()
    {
        $contactId = waRequest::get('contact_id', 0, waRequest::TYPE_INT);
        $projectId = waRequest::get('project_id', 0, waRequest::TYPE_INT);

        $this->user = !$contactId
            ? stts()->getUser()
            : stts()->getEntityRepository(statusUser::class)->findByContactId($contactId);

        if (!$this->user instanceof statusUser) {
            $this->user = stts()->getEntityFactory(statusUser::class)->createNewWithContact(new waContact($contactId));
        }

        if (!$this->user->isExists()) {
            throw new kmwaNotFoundException('User not found');
        }

        //todo: can view user
        if ($this->user->getContactId() != wa()->getUser()->getId()) {

        }

        stts()->setContextUser($this->user);

        if ($projectId) {
            $this->project = stts()->getEntityRepository(statusProject::class)->findById($projectId);

            if (!$this->project instanceof statusProject) {
                throw new kmwaNotFoundException('Project not found');
            }
        }
        $this->isMe = $this->user->getContactId() == stts()->getUser()->getContactId();
        $this->isProject = $this->project instanceof statusProject;
    }

    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function runAction($params = null)
    {
        $weeks = statusWeekFactory::createLastNWeeks(5, true, 0);
        $weeksDto = statusWeekFactory::getWeeksDto($weeks, $this->user, $this->project);
        $currentWeek = array_shift($weeksDto);

        $tomorrow = new statusDay(new DateTime('tomorrow'));
        $tomorrowDto = new statusDayDto($tomorrow);
        $userDto = new statusUserDto($this->user);
        $tomorrowDto->users[$userDto->contactId] = $userDto;

        // + инфа о дне пользователя
        $userDayInfo = new statusDayUserInfoDto($tomorrowDto->date, $userDto->contactId);
        $tomorrowDto->userDayInfos[$userDto->contactId] = $userDayInfo;

        $userDayInfo->todayStatus = statusTodayStatusFactory::getForContactId(
            $userDto->contactId,
            new DateTime($tomorrowDto->date)
        );

        $dayDtoAssembler = new statusDayDotAssembler();
        $dayDtoAssembler->fillWithCheckins($userDayInfo, [], $userDto);

        $viewData = [
            'currentWeek' => $currentWeek,
            'weeks' => $weeksDto,
            'sidebar_html' => (new statusBackendSidebarAction())->display(),
            'current_contact_id' => $this->user->getContactId(),
            'isMe' => (int)$this->isMe,
            'isProject' => (int)$this->isProject,
            'tomorrow' => (new DateTime())->modify('+1 day')->format('Y-m-d'),
            'statuses' => statusTodayStatusFactory::getAllForUser($this->user),
            'nextStatus' => statusTodayStatusFactory::getForContactId(
                $this->user->getContactId(),
                (new DateTime())->modify('+1 day')
            ),
            'project' => $this->project,
            'dayEditable' => (int)($this->user->getContactId() == stts()->getUser()->getContactId()
                && !$this->project instanceof statusProject),
            'contextUser' => $this->user,
            'tomorrowDto' => $tomorrowDto,
        ];
        $this->view->assign($viewData);
    }
}
