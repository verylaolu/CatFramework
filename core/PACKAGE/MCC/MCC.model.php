<?php
/**
 * FW MCC 连接程序
 * @category   FW
 * @package  framework
 * @subpackage  core/PACKAGE/MCC
 */
class _MCC {

    public $MCC;

    function __construct($conf) {
        require_cache(dirname(__FILE__) . '/phpmcc.inc');
        $this->conf = $conf;
        $this->MCC = $this;
    }

    function send($msg, $queue) {
        list($queue, $level, $exp) = $this->conf['queues'][$queue];
        file_put_contents('./upload/msg_test.log', "$msg $queue $level $exp");
        $mcc = new MccProxy($this->conf['host'], $this->conf['port'], $queue);
        return $mcc->send_message($msg, $queue, $level, $exp);
    }

}
