<?php

namespace Topxia\Service\File\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\UploadFileShareHistoryService;

class UploadFileShareHistoryServiceImpl extends BaseService implements UploadFileShareHistoryService
{
	public function getShareHistory($id)
	{
		return $this->getUploadFileShareHistoryDao()->getShareHistory($id);
	}

	public function addShareHistory($sourceUserId, $targetUserId, $isActive)
	{
		 $fileShareHistoryFields = array(
            'sourceUserId' => $sourceUserId,
            'targetUserId' => $targetUserId,
            'isActive'     => $isActive,
            'createdTime'  => time()
        );

        return $this->getUploadFileShareHistoryDao()->addShareHistory($fileShareHistoryFields);
	}

	public function findShareHistory($sourceUserId)
    {
        $shareHistories = $this->getUploadFileShareHistoryDao()->findShareHistoryByUserId($sourceUserId);

        return $shareHistories;
    }

    public function searchShareHistoryCount($conditions)
    {
        return $this->getUploadFileShareHistoryDao()->searchShareHistoryCount($conditions);
    }

    public function searchShareHistories($conditions, $orderBy, $start, $limit)
    {
        return $this->getUploadFileShareHistoryDao()->searchShareHistories($conditions,$orderBy, $start, $limit);
    }

	protected function getUploadFileShareHistoryDao()
    {
        return $this->createDao('File.UploadFileShareHistoryDao');
    }
}