<?php
class TaskManager{
    /**
     * Check whether scheduled task exists or not.
     *
     * @access protected
     * @param string $name Task name
     * @return boolean True is task exists.
     */
    public static function check($name)
    {
        $output = null;

        if (It::isLinux())
        {
            exec('crontab -l | grep -i '. $name, $output);

//            return (is_array($output) && (count($output) > 0));
            if (is_array($output) && (count($output) > 0)) {

                return true;
            }
        }
        else if (It::isWindows())
        {
            exec('schtasks /query /nh /fo:CSV', $output);

            // strip header
            $output = array_slice($output, 1);

            if (count($output) > 0)
            {
                foreach ($output as $csvLine)
                {
                    $lines = explode(',', $csvLine);

                    if ((count($lines) > 1) && ($lines[0] == '"'. $name .'"'))
                    {
                        return true;
                    }
                }
            }

            return false;
        }

        return false;
    }

    /**
     * Creates scheduled task.
     *
     * @access protected
     * @param string $name Task name.
     * @param string $command Coomand line string to run in task.
     * @param string $periodicity Possible values: minutely, hourly, daily, monthly, weekly ...
     * @param string $interval Task interval (once an minute, once in 5 hours, etc).
     * @param string $startTime Exact time of start for smae periodicities.
     */
    public  static function create($name, $command, $periodicity, $interval = 1, $startTime = null)
    {
        if (It::isLinux())
        {
            // Build start-up time-line fron crontab
            $cronTimes = '';

            $startHour = 0;
            $startMinute = 0;
            $matches = array();

            if (!is_null($startTime) && (preg_match('/^([0-9]+):([0-9]+)$/', $startTime, $matches) > 0))
            {
                $startHour = (int)$matches[1];
                $startMinute = (int)$matches[2];
            }

            switch ($periodicity)
            {
                case 'minutely':

                    if ($startMinute === 0)
                    {
                        $cronTimes = ($interval > 1 ? '*/'. $interval : '*') .' * * * *';
                    }
                    else
                    {
                        $cronTimes = $startMinute .'-59'. ($interval > 1 ? '/'. $interval : '') .' * * * *';
                    }

                    break;

                case 'hourly':

                    $cronTimes = $startMinute .' ' . ($interval > 1 ? '*/'. $interval : '*') .' * * *';
                    break;

                case 'daily':

                    $cronTimes = $startMinute .' '. $startHour .' '. ($interval > 1 ? '*/'. $interval : '*') .' * *';
                    break;

                case 'monthly':

                    $cronTimes = $startMinute .' '. $startHour .' 1 ' . ($interval > 1 ? '*/'. $interval : '*') .' *';
                    break;

                case 'weekly':

                    $cronTimes = $startMinute .' '. $startHour .' * * ' . ($interval > 1 ? '*/'. $interval : '*');
                    break;
            }

            exec('crontab -l | { cat; echo "'. $cronTimes .' '. $command .' #'. $name .'"; } | crontab -');
        }
        else if (It::isWindows())
        {
            exec('schtasks /create /sc '. $periodicity .' /mo '. $interval . (is_null($startTime) ? '' : ' /st '. $startTime) .' /ru "SYSTEM" /tn '. $name .' /tr "'. $command .'"');
        }
    }

    /**
     * Deletes task by name.
     *
     * @param string $name
     */
    public  static function delete($name)
    {
        if (It::isLinux())
        {
            exec('crontab -l | grep -v '. $name .' | crontab -');
        }
        else if (It::isWindows())
        {
            exec('schtasks /delete /tn '. $name .' /f');
        }
    }
}