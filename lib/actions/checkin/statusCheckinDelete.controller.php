<?php

/**
 * Class statusCheckinSaveController
 */
class statusCheckinDeleteController extends statusJsonController
{
    /**
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function execute()
    {
        $id = waRequest::post('id', 0, waRequest::TYPE_INT);

        /** @var statusCheckin $checkin */
        if (empty($id)) {
            throw new kmwaNotFoundException();
        }

        /** @var statusCheckinRepository $repository */
        $repository = stts()->getEntityRepository(statusCheckin::class);
        $checkin = $repository->findById($id);
        if (!$checkin instanceof statusCheckin) {
            throw new kmwaNotFoundException('No checkin with id '.$id);
        }

        stts()->getEntityPersister()->delete($checkin);
    }
}
