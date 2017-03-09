<?php
/**
 * FW 消息分发客户端
 * @category   FW
 * @package  framework
 * @subpackage  core/PACKAGE/SAM
 */
class _SAM {

    public $SAM;

    public function __construct($conf) {
//        require_cache(dirname(__FILE__) . '/php_sam.php');
//        $conn = new SAMConnection();
//        if ($conf['debug']) {
//            $conn->debug = true;
//        }
//        $conn->connect(SAM_MQTT, array(
//            SAM_HOST => $conf['host'],
//            SAM_PORT => $conf['port'],
//        ));
//        $this->SAM = $conn;

        $conn = new Mosquitto\Client('publish client', false);
        if ($conf['username']) {
            $conn->setCredentials($conf['username'], $conf['password']);
        }
        $conn->onConnect('sam_connect');
        $conn->onMessage('sam_message');
        $conn->onSubscribe('sam_subscribe');
        $conn->onDisconnect('sam_disconnect');

        $conn->connect($conf['host'], $conf['port'], 5);
        $conn->subscribe('/#', 1);

        $this->conn = $conn;
        $this->SAM = $this;
    }

    public function send($topic, $msg) {
        $this->conn->loop();
        $mid = $this->conn->publish($topic, $msg, 1, 0);
        echo "Sent message ID: {$mid}\n";
        $this->conn->loop();
    }

    public function __destruct() {
        //$this->SAM->disconnect();
        $this->conn->disconnect();
    }

}

function sam_connect($r) {
    echo "I got code {$r}\n";
}

function sam_message($m) {
    printf("Got a message ID %d on topic %s with payload:\n%s\n\n", $m->mid, $m->topic, $m->payload);
}

function sam_subscribe() {
    echo "Subscribed to a topic\n";
}

function sam_disconnect() {
    echo "Disconnected cleanly\n";
}
