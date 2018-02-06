<?php
/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/8/12
 * Time: 19:09
 */

require '/path/to/simple-fork-php/autoload.php';


class TestRunnable implements \Jenner\SimpleFork\Runnable
{

    /**
     * 进程执行入口
     * @return mixed
     */
    public function run()
    {
        echo "I am a sub process" . PHP_EOL;
    }
}

$process = new \Jenner\SimpleFork\Process(new TestRunnable());
$process->start();