<?php
namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * task 应用（对应 TP5 的 task 模块，定时任务/命令行，生产中约 50 个 PHP 文件）。
 * 多应用下：CLI 应用，无 HTTP 路由，命令类放在 app/task/command/，在根 app/command.php 注册。
 */
class Hello extends Command
{
    protected function configure()
    {
        $this->setName('hello')
             ->setDescription('task 应用示例命令（对应 TP5 的 task 模块定时任务）');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('task app command ok');
    }
}
