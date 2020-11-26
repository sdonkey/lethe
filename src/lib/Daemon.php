<?php
namespace Lethe\Lib;

/**
 * Daemon
 *
 * @author wuqiying@ruijie.com.cn
 */

class Daemon
{
    /**
     * @var int
     */
    private $masterPid;
    /**
     * @var string
     */
    private $masterPidFile;
    /**
     * @var array
     */
    private $workerPids;
    /**
     * @var int
     */
    private $processNum;
    /**
     * @var string
     */
    private $logFile;
    /**
     * @var string
     */
    private $title;
    /**
     * @var callable
     */
    private $handler;

    public function __construct()
    {
        if (!extension_loaded('pcntl')) {
            throw new Exception('daemon needs support of pcntl extension');
        }
        if ('cli' != PHP_SAPI) {
            throw new Exception('daemon only works in CLI mode');
        }
    }
    /**
     * @param int $num
     */
    public function setProcessNum($num)
    {
        $this->processNum = (int) $num;
    }
    /**
     * @param string $filename
     */
    public function setPidFile($filename)
    {
        $this->masterPidFile = $filename;
    }
    /**
     * @param string $filename
     */
    public function setLogFile($filename)
    {
        $this->logFile = $filename;
    }
    public function setTitle($title)
    {
        $this->title = $title;
    }
    /**
     * @param mixed $handler
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }
    /**
     * @param string $command
     */
    public function run($command)
    {
        try {
            $this->init($command);
            switch ($command) {
                case 'start':
                    $this->start();
                    break;
                case 'stop':
                    $this->stop();
                    break;
                case 'restart':
                    $this->restart();
                    break;
                default:
                    $this->usage();
                    break;
            }
        } catch (Exception $e) {
            $this->log($e->getMessage());
            throw $e;
        }
    }
    /**
     * @param string $op
     * @param mixed $command
     */
    private function init($command)
    {
        if (!$this->title) {
            $backtrace = debug_backtrace();
            $this->setTitle($backtrace[count($backtrace) - 1]['file']);
        }
        $this->setTitle('phpdaemon(' . $this->title . '):');
        if (!$this->processNum) {
            $this->setProcessNum(1);
        }
        if (!$this->masterPidFile) {
            $this->setPidFile(__DIR__ . '/daemon.pid');
        }
        if (!$this->logFile) {
            $this->setLogFile(__DIR__ . '/daemon.log');
        }
        $this->masterPid = $this->checkRunning() ? file_get_contents($this->masterPidFile) : 0;
        $this->log($command);
    }
    /**
     * @throws Exception
     */
    private function start()
    {
        if (true === $this->checkRunning()) {
            throw new Exception('daemon is running');
        }
        if (empty($this->handler)) {
            throw new Exception('daemon process handler unregistered');
        }
        $this->master();
        $this->workers();
    }
    /**
     * @param bool $check
     * @throws Exception
     */
    private function stop($check = true)
    {
        if (true === $this->checkRunning()) {
            unlink($this->masterPidFile);
            posix_kill($this->masterPid, SIGINT);
        } elseif (true === $check) {
            throw new Exception('daemon is not running');
        }
    }
    private function restart()
    {
        $this->stop(false);
        $this->start();
    }
    private function usage()
    {
        exit("Usage: php yourfile.php {start|stop|restart}\n");
    }
    /**
     * @return bool
     */
    private function checkRunning()
    {
        return is_file($this->masterPidFile);
    }
    private function master()
    {
        $this->setProcessTitle($this->title . ' master process');

        umask(0);
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new Exception('daemon master fork fail');
        } elseif ($pid) {
            exit(SIG_DFL);
        }
        if (-1 === posix_setsid()) {
            throw new Exception("daemon master setsid fail");
        }
        // Fork again avoid SVR4 system regain the control of terminal.
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new Exception("daemon master fork fail");
        } elseif ($pid) {
            exit(SIG_DFL);
        }
        $this->masterPid = posix_getpid();
        file_put_contents($this->masterPidFile, $this->masterPid);

        pcntl_signal(SIGINT, [$this, 'masterSignal'], false); // master stop
    }
    /**
     * @param int $signal
     */
    private function masterSignal($signal)
    {
        switch ($signal) {
            case SIGINT:
                //master stop, kill workers & exit
                $this->stopWorkers();
                break;
        }
    }
    private function workers()
    {
        $this->forkWorkers();
        $this->monitorWorkers();
    }
    private function forkWorkers()
    {
        for ($i=0; $i < $this->processNum; $i++) {
            if (!$this->workerPids[$i]) {
                $this->forkWorker($i);
            }
        }
    }
    /**
     * @param int $num
     * @throws
     */
    private function forkWorker($num)
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new Exception('daemon worker fork fail. num:' . $num);
        } elseif ($pid) {
            $this->workerPids[$num] = $pid;
            $this->log('worker num:' . $num . ' pid:' . $pid . ' fork');
        } else {
            $this->setProcessTitle($this->title . ' worker process ' . $num);
            pcntl_signal(SIGINT, SIG_IGN, false);

            ob_start();
            try {
                call_user_func($this->handler, $num);
            } catch (\Exception $e) {
                $this->log((string) $e);
            } catch (\Error $e) {
                $this->log((string) $e);
            }
            if ($content = ob_get_clean()) {
                $this->log('handler response:' . "\n" . $content);
            }
            exit(250);
        }
    }
    private function monitorWorkers()
    {
        while (true) {
            pcntl_signal_dispatch();
            $status = 0;
            $pid = pcntl_wait($status, WUNTRACED);
            pcntl_signal_dispatch();

            if ($pid > 0) {
                foreach ($this->workerPids as $workerNum => $workerPid) {
                    if ($pid === $workerPid) {
                        unset($this->workerPids[$workerNum]);
                        $this->log('worker num:' . $workerNum . ' pid:' . $workerPid . ' finish');
                    }
                }
                $this->forkWorkers();
            }
        }
    }
    private function stopWorkers()
    {
        foreach ($this->workerPids as $workerNum => $workerPid) {
            posix_kill($workerPid, SIGKILL);
            $this->log('worker num:' . $workerNum . ' pid:' . $workerPid . ' finish');
        }
        exit(SIG_DFL);
    }
    /**
     * @param string $title
     */
    private function setProcessTitle($title)
    {
        if (function_exists('cli_set_process_title')) {
            // >=php 5.5
            cli_set_process_title($title);
        } elseif (extension_loaded('proctitle') && function_exists('setproctitle')) {
            // Need proctitle when php<=5.5 .
            setproctitle($title);
        }
    }
    /**
     * @param string $message
     */
    private function log($message)
    {
        $data = [
            date('Y-m-d H:i:s'),
            'pid:' . posix_getpid(),
            'title:' . $this->title,
            (string) $message
        ];
        $data = implode("\t", $data) . "\n\n";
        $filename = $this->logFile;

        if (!file_exists($filename)) {
            $path = dirname($filename);
            !is_dir($path) && mkdir($path, 0775, true);
        }

        file_put_contents($filename, $data, FILE_APPEND | LOCK_EX);
    }
}
