<?php


/**
* 
*/


function is_running($pid){
    try{
        $result = shell_exec(sprintf("ps %d", $pid));
        if( count(preg_split("/\n/", $result)) > 2){
            return true;
        }
    }catch(Exception $e){}

    return false;
}


class AsyncTask
{
    public $cmd = '';
    public $pidfile =  '';
    public $outputfile = '';
    public $tail_recent_number = 100;

    public $error_msg = [];
    


    public function __construct($cmd, $pidfile, $outputfile, $tail_recent_number=100)
    {
        $this->cmd = $cmd;
        $this->pidfile = $pidfile;
        $this->outputfile = $outputfile;
        $this->tail_recent_number =  $tail_recent_number;
    }

    function start()
    {
        $exec_cmd = sprintf("%s > %s 2>&1 & echo $! > %s", $this->cmd, $this->outputfile, $this->pidfile);
        debug("start: ${exec_cmd} \n");
        if (!$this->is_running()) {
            exec($exec_cmd);
            debug("start():ok.");
            return true;
        } else {
            $this->error_msg[] = "Task is already running!";
            debug("start():Task is already running!");
            return false;
        }
    }

    public function stop()
    {
        $pid = trim(file_get_contents($this->pidfile));
        $cmd = "/bin/kill -9 $pid";
        debug("kill : $cmd \n");
        exec($cmd);
    }

    public function is_running()
    {
        return is_running($this->get_pid());
    }

    public function get_pid()
    {
        return trim(file_get_contents($this->pidfile));
    }

    public function stat()
    {
        $shell_exec = sprintf("/usr/bin/tail -n %s %s", $this->tail_recent_number, $this->outputfile);
        debug("start: ${shell_exec} \n");
        $output = shell_exec($shell_exec);
        return $output;
    }


    public function download_file_url()
    {
        $shell_exec = sprintf("/bin/grep '^@file' %s | sed -e 's/@file//g' -e 's/ //g'", $this->outputfile);
        debug("start: ${shell_exec} \n");
        $output = shell_exec($shell_exec);
        $file_list = explode("\n", $output);
        return $file_list;
    }



}




