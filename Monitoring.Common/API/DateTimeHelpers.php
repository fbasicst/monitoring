<?php
    namespace Common\API;

    class DateTimeHelpers
    {
        public static function getLastNDays($days, $format = 'Y-m-d'){
            $m = date("m"); $de= date("d"); $y= date("Y");
            $dateArray = array();
            for($i=0; $i<=$days-1; $i++){
                $dateArray[] = '' . date($format, mktime(0,0,0,$m,($de-$i),$y)) . '';
            }
            return array_reverse($dateArray);
        }

        public static function GetDayNameFromDate($date)
        {
            $dayOfWeekInteger = date('N', strtotime($date));

            switch ($dayOfWeekInteger)
            {
                case 1:
                    return 'Ponedjeljak';
                case 2:
                    return 'Utorak';
                case 3:
                    return 'Srijeda';
                case 4:
                    return 'Četvrtak';
                case 5:
                    return 'Petak';
                case 6:
                    return 'Subota';
                case 7:
                    return 'Nedjelja';
                default:
                    return '';
            }
        }

        public static function GetMonthNameFromInt($monthInt)
        {
            switch ($monthInt)
            {
                case 1:
                    return 'Siječanj';
                case 2:
                    return 'Veljača';
                case 3:
                    return 'Ožujak';
                case 4:
                    return 'Travanj';
                case 5:
                    return 'Svibanj';
                case 6:
                    return 'Lipanj';
                case 7:
                    return 'Srpanj';
                case 8:
                    return 'Kolovoz';
                case 9:
                    return 'Rujan';
                case 10:
                    return 'Listopad';
                case 11:
                    return 'Studeni';
                case 12:
                    return 'Prosinac';
                default:
                    return '';
            }
        }
    }
