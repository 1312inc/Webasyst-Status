<?php

/**
 * Class statusBackendAction
 */
class statusChronologyAction extends statusViewAction
{
    /**
     * @var bool
     */
    protected $isMe = false;

    /**
     * @var bool
     */
    protected $isProject = false;

    /**
     * @var statusGetWeekDataFilterRequestDto
     */
    protected $getWeekDataFilterRequestDto;

    /**
     * @var statusUser|null
     */
    protected $user;

    /**
     * @var int|null
     */
    protected $contactId;

    /**
     * @var int|null
     */
    protected $projectId;

    /**
     * @var int|null
     */
    protected $groupId;

    /**
     * @throws kmwaForbiddenException
     * @throws kmwaLogicException
     * @throws kmwaNotFoundException
     * @throws waException
     */
    protected function preExecute()
    {
        parent::preExecute();

        $this->contactId = waRequest::get('contact_id', null, waRequest::TYPE_INT);
        $this->projectId = waRequest::get('project_id', null, waRequest::TYPE_INT);
        $this->groupId = waRequest::get('group_id', null, waRequest::TYPE_INT);

        $this->getWeekDataFilterRequestDto = new statusGetWeekDataFilterRequestDto($this->contactId, $this->projectId, $this->groupId);

        $this->isProject = $this->getWeekDataFilterRequestDto->getProject() instanceof statusProject;

        if ($this->getWeekDataFilterRequestDto->getUsers()) {
            $this->user = $this->getWeekDataFilterRequestDto->getUsers()[0];
            $this->isMe = $this->user->getContactId() == stts()->getUser()->getContactId()
                && !$this->isProject
                && !$this->groupId
                && $this->contactId !== statusGetWeekDataFilterRequestDto::ALL_USERS_ID;
        }
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
        $weeksDto = statusWeekFactory::getWeeksDto($weeks, $this->getWeekDataFilterRequestDto);

        $weekFilter = new statusWeekFilter();
        foreach ($weeksDto as $weekDto) {
            $weekFilter->filterNonExistingUserWithNoActivity($weekDto);
        }

        $currentWeek = array_shift($weeksDto);

        if (!$this->user) {
            $this->user = stts()->getUser();
        }

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

        stts()->setContextUser($this->user);

        $group = false;
        if ($this->groupId) {
            $group = (new waGroupModel())->getName($this->groupId);
        }

        $viewData = [
            'currentWeek' => $currentWeek,
            'weeks' => $weeksDto,
            'sidebar_html' => (new statusBackendSidebarAction())->display(),
            'current_contact_id' => (int) $this->contactId,
            'isMe' => (int) $this->isMe,
            'isProject' => (int) $this->isProject,
            'tomorrow' => (new DateTime())->modify('+1 day')->format('Y-m-d'),
            'statuses' => statusTodayStatusFactory::getAllForUser($this->user),
            'nextStatus' => statusTodayStatusFactory::getForContactId(
                $this->user->getContactId(),
                (new DateTime())->modify('+1 day')
            ),
            'project' => $this->getWeekDataFilterRequestDto->getProject(),
            'dayEditable' => (int) $this->isMe,
            'contextUser' => $this->user,
            'tomorrowDto' => $tomorrowDto,
            'showTrace' => stts()->canShowTrace(),
            'group' => $group,
            'groupId' => $this->groupId,
            'allUsers' => $this->contactId === statusGetWeekDataFilterRequestDto::ALL_USERS_ID,
        ];
        $this->view->assign($viewData);
    }
}
