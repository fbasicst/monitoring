<?php
    namespace API\CommandHandlers\Factories;

    use API\CommandHandlers\Commands\Object\SaveObjectCommand;
    use API\CommandHandlers\Commands\Object\SaveObjectItemMonitoringAndPlanCommand;
    use API\Models\Objects\Object;
    use API\Models\Objects\ObjectItem;
    use API\Models\Objects\ObjectItemMonitoringAndPlan;
    use API\Models\Objects\ObjectItemMonitoringAnalysisRel;
    use API\Models\Objects\ObjectItemPlanSchedule;
    use PDOException;

    class ObjectFactory
    {
        /**
         * @param $customerId
         * @param $contractId
         * @param SaveObjectCommand $command
         * @return Object
         * @throws PDOException
         */
        public static function CreateObject($command, $customerId, $contractId = null)
        {
            $object = new Object();
            $object->Name = $command->Name;
            $object->StreetName = $command->StreetName;
            $object->StreetNumber = $command->StreetNumber;
            $object->ContactPerson = $command->ContactPerson;
            $object->ContactPhone = $command->ContactPhone;
            $object->ContactMail = $command->ContactMail;
            $object->Notes = $command->Notes;
            $object->InsertedBy = $command->UserId;
            $object->CustomerId = $customerId;
            $object->ContractId = $contractId;
            $object->CityId = $command->CityId;
            $object->AreaId = $command->AreaId;
            $object->ObjectTypeId = $command->ObjectTypeId;

            foreach ($command->Departments as $department)
            {
                $department = (object)$department;

                $objectItem = new ObjectItem();
                $objectItem->Name = $department->name;
                $objectItem->IsSeasonal = (bool)$department->seasonal;
                $objectItem->LocationDescription = isset($department->sublocation) ? $department->sublocation : null;
                $objectItem->InsertedBy = $command->UserId;
                $object->Items[] = $objectItem;

                if(isset($department->monitoring))
                    foreach ($department->monitoring as $monitoring)
                    {
                        $monitoring = (object)$monitoring;
                        $monitoring->contractServiceType = (object)$monitoring->contractServiceType;
                        $monitoring->serviceItem = (object)$monitoring->serviceItem;
                        $monitoring->level = (object)$monitoring->level;

                        $objectItemMonitoringAndPlan = new ObjectItemMonitoringAndPlan();
                        $objectItemMonitoringAndPlan->ContractServiceTypeId = $monitoring->contractServiceType->Id;
                        $objectItemMonitoringAndPlan->ServiceItemId = $monitoring->serviceItem->ServiceItemId;
                        $objectItemMonitoringAndPlan->Quantity = $monitoring->quantity;
                        $objectItemMonitoringAndPlan->Description = isset($monitoring->description) ? $monitoring->description : null;
                        $objectItemMonitoringAndPlan->InsertedBy = $command->UserId;
                        $objectItemMonitoringAndPlan->ScheduleLevelId = $monitoring->level->value;
                        $objectItemMonitoringAndPlan->MonthlyRepeatsCount = $monitoring->monthlyRepeats;
                        $objectItemMonitoringAndPlan->IsValidFurther = (bool)$monitoring->validFurther;
                        $objectItemMonitoringAndPlan->EndDate = !$objectItemMonitoringAndPlan->IsValidFurther ? $monitoring->endDate : null;
                        $objectItem->MonitoringsAndPlans[] = $objectItemMonitoringAndPlan;

                        if(isset($monitoring->analysis))
                            foreach ((array)$monitoring->analysis as $analysis)
                            {
                                $analysis = (object)$analysis;

                                $objectItemMonitoringAnalysisRel = new ObjectItemMonitoringAnalysisRel();
                                $objectItemMonitoringAnalysisRel->AnalysisId = $analysis->Id;
                                $objectItemMonitoringAndPlan->Analysis[] = $objectItemMonitoringAnalysisRel;
                            }

                        if(!isset($monitoring->months) || empty($monitoring->months))
                            throw new PDOException("Plan ne sadrži raspored.");
                        foreach ($monitoring->months as $month)
                        {
                            $month = (object)$month;

                            $objectItemPlanSchedule = new ObjectItemPlanSchedule();
                            $objectItemPlanSchedule->Month = $month->Id;
                            $objectItemMonitoringAndPlan->ScheduleMonths[] = $objectItemPlanSchedule;
                        }
                    }
            }
            return $object;
        }

        //TODO ovu metodu iskoristi za ovu iznad - CreateObject
        /**
         * @param SaveObjectItemMonitoringAndPlanCommand $command
         * @return ObjectItemMonitoringAndPlan
         */
        public static function CreateObjectItemMonitoringAndPlan($command)
        {
            $objectItemMonitoringAndPlan = new ObjectItemMonitoringAndPlan();
            $objectItemMonitoringAndPlan->ContractServiceTypeId = $command->ContractServiceTypeId;
            $objectItemMonitoringAndPlan->Quantity = $command->Quantity;
            $objectItemMonitoringAndPlan->Description = $command->Description;
            $objectItemMonitoringAndPlan->ServiceItemId = $command->ServiceItemId;
            $objectItemMonitoringAndPlan->InsertedBy = $command->UserId;

            $objectItemMonitoringAndPlan->ScheduleLevelId = $command->ScheduleLevelId;
            $objectItemMonitoringAndPlan->MonthlyRepeatsCount = $command->MonthlyRepeatsCount;
            $objectItemMonitoringAndPlan->IsValidFurther = $command->IsValidFurther;
            $objectItemMonitoringAndPlan->EndDate = !$objectItemMonitoringAndPlan->IsValidFurther ? $command->EndDate : null;

            if(isset($command->Analysis))
                foreach ((array)$command->Analysis as $analysis)
                {
                    $analysis = (object)$analysis;
                    $objectItemMonitoringAnalysisRel = new ObjectItemMonitoringAnalysisRel();
                    $objectItemMonitoringAnalysisRel->AnalysisId = $analysis->Id;

                    $objectItemMonitoringAndPlan->Analysis[] = $objectItemMonitoringAnalysisRel;
                }

            if($command->ScheduleMonths == null)
                throw new PDOException("Plan ne sadrži raspored.");
            foreach ($command->ScheduleMonths as $month)
            {
                $month = (object)$month;
                $objectItemPlanSchedule = new ObjectItemPlanSchedule();
                $objectItemPlanSchedule->Month = $month->Id;

                $objectItemMonitoringAndPlan->ScheduleMonths[] = $objectItemPlanSchedule;
            }
            return $objectItemMonitoringAndPlan;
        }
    }