<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ReviewReminder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ReviewReminder\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;

/**
 * Class Logs
 * @package Mageplaza\ReviewReminder\Model\ResourceModel
 */
class Logs extends AbstractDb
{
    /**
     * Date model
     *
     * @var DateTime
     */
    private $date;

    /**
     * @var Timezone
     */
    private $timeZone;

    /**
     * constructor
     *
     * @param DateTime $date
     * @param Context $context
     * @param Timezone $timeZone
     */
    public function __construct(
        Context $context,
        DateTime $date,
        Timezone $timeZone
    ) {
        $this->date     = $date;
        $this->timeZone = $timeZone;

        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mageplaza_reviewreminder_logs', 'id');
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @param null $dimension
     *
     * @return array
     * @throws LocalizedException
     */
    public function loadReportData($fromDate, $toDate, $dimension = null)
    {
        $result       = [];
        $timeZoneFrom = $this->timeZone->date($fromDate);
        $timeZoneTo   = $this->timeZone->date($toDate);
        $timeDiff     = $timeZoneFrom->diff($timeZoneTo);
        if ($dimension === 'month') {
            $numbers = $timeDiff->m;
            $level   = ' month';
            if ($numbers === 0 && $this->date->date('m', $fromDate) !== $this->date->date('m', $toDate)) {
                $numbers = 1;
            }
        } else {
            $level   = ' days';
            $numbers = $timeDiff->days;
        }
        for ($number = 0; $number <= $numbers; $number++) {
            if ($dimension === 'month') {
                $fromDate = $this->date->date('m/01/Y', $fromDate);
            }
            $date       = $this->date->date('m/d/Y', $fromDate . '+' . $number . $level);
            $nextDate   = $this->date->date('m/d/Y', $date . '+1' . $level);
            $dateFormat = $date;
            if ($dimension === 'month') {
                $date       = $this->date->date('m/01/Y', $date);
                $dateFormat = $this->date->date('m/Y', $date);
            }
            $result[] = [
                $dateFormat,
                $this->getLogData($date, $nextDate, 'sent'),
                $this->getLogData($date, $nextDate, 'error')
            ];
        }

        return $result;
    }

    /**
     * @param $date
     * @param $nextDate
     * @param $column
     *
     * @return int
     * @throws LocalizedException
     */
    private function getLogData($date, $nextDate, $column)
    {
        $adapter = $this->_resources->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where('updated_at >= ?', $this->convertDate($date))
            ->where('updated_at < ?', $this->convertDate($nextDate));
        if ($column === 'error') {
            $select->where('status = ?', false);
        } else {
            $select->where('status = ?', 1);
        }
        $collection = $adapter->fetchCol($select);

        return count($collection);
    }

    /**
     *
     * @param string $date
     *
     * @return string
     */
    private function convertDate($date)
    {
        return $this->date->date('Y-m-d H:i:s', strtotime($date));
    }

    /**
     * @throws LocalizedException
     */
    public function clear()
    {
        $bind = ['display' => false];
        $this->getConnection()->update($this->getMainTable(), $bind);
    }

    /**
     * @inheritdoc
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }
        $object->setUpdatedAt($this->date->date());

        return parent::_beforeSave($object);
    }
}
