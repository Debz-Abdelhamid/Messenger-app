<?php

    if(!function_exists('TimeAgo'))
    {

        function TimeAgo($timestemp)
        {
            $timeDifference = time() - strtotime($timestemp);
            $seconds = $timeDifference;
            $minutes = round($timeDifference /60);
            $hours = round($timeDifference /3600);
            $days = round($timeDifference /86400);


            if($seconds <= 60)
            {
                if($seconds <= 1)
                {
                    return "now";  
                }
                return $seconds."s ago";

            }elseif($minutes <=60)
            {
                return $minutes."m ago";
            }elseif($hours <=24)
            {
                return $hours."h ago";
            }else
            {
                return date('j M y', strtotime($timestemp));
            }

        }
    }
