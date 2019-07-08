<?php

namespace ICSearch\libs;

class Log
{

    const TYPE_MSG = 1; //消息
    const TYPE_PROCESS = 2; //流程

    public $logs = array();
    public $curProcessKey = null;//当前记录流程key

    public function record($title, $data = array())
    {
        if (is_null($this->curProcessKey)) {
            $this->curProcessKey = count($this->logs);
            $this->logs[] = [
                'type' => self::TYPE_PROCESS,
                'curProcessKey' => $this->curProcessKey,
                'title' => $title,
                'record' => [
                    $this->_record(time(), $title, $data)
                ]
            ];
        } else {
            $this->logs[$this->curProcessKey]['record'][] = $this->_record(time(), $title, $data);
        }

    }

    public function _record($time, $title, $data)
    {
        return [
            'time' => time(),
            'title' => $title,
            'data' => $data
        ];
    }


    public function msg($title, $data = null, $curProcessKey = null)
    {
        $this->logs[] = [
            'type' => self::TYPE_MSG,
            'record' => $this->_record(time(), $title, $data)
        ];
    }

    /**
     * 
     */
    public function startRecord($title, $data = array())
    {
        $this->curProcessKey = null;
        $this->record($title, $data);
    }

    /**
     * 
     */
    public function endRecord($data = array())
    {
        $curLog = $this->getLogByKey($this->curProcessKey);
        $title = $curLog['title'];
        $this->record($title, $data);
        $this->curProcessKey = null;
    }

    /**
     * 
     */
    public function formatLogs()
    {
        $logs = [];
        if ($this->logs) {
            foreach ($this->logs as $key => $item) {
                switch ($item['type']) {
                    case self::TYPE_MSG:
                        $logs[] = $this->_formatLog($item['record']['title'], $item['record']['time'], $item['record']['data']);
                        break;
                    case self::TYPE_PROCESS:
                        $tCount = count($item['record']);
                        foreach ($item['record'] as $key => $v) {
                            $title = $v['title'];

                            if ($key == 0) {
                                 //开始
                                $t = "Start====" . $title . "====Start";
                                $logs[] = $this->_formatLog($t, $v['time'], null);
                                if (!empty($v['data'])) {
                                    foreach ($v['data'] as $tt => $vv) {
                                        $logs[] = $this->_formatLog('                    处理前-' . $tt, null, $vv);
                                    }
                                }

                            } else if ($key == $tCount - 1) {
                                 //结束
                                if (!empty($v['data'])) {
                                    foreach ($v['data'] as $tt => $vv) {
                                        $logs[] = $this->_formatLog('                    处理后-' . $tt, null, $vv);
                                    }
                                }
                                $t = "End====" . $title . "====End";
                                $logs[] = $this->_formatLog($t, $v['time'], null);

                            } else {
                                //过程
                                $logs[] = $this->_formatLog('                      ' . $title, null, $v['data']);
                            }
                        }
                        break;
                }

            }
        }
        return $logs;
    }

    public function _formatLog($title, $time = null, $data = array())
    {
        $dataStr = is_array($data) ? json_encode($data) : $data;
        // $t = $time?date('Y-m-d H:i:s', $time).' ':null;
        $t = '';
        $t .= $title;
        if ($dataStr) {
            $t .= ':' . $dataStr;
        }
        return $t;
    }

    public function getLogsKey()
    {
        $array = $this->logs;
        end($array);
        return key($array);
    }


    public function updateRecord($key, $data)
    {
        if (!is_null($key)) {
            $this->logs[$key] = $data;
        }

    }

    public function getLogByKey($key)
    {
        if (!is_null($key)) {
            return isset($this->logs[$key]) ? $this->logs[$key] : null;
        }
    }



}
