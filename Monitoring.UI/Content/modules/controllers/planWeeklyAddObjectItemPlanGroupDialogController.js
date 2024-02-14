'use strict'

webApp.controller('planWeeklyAddObjectItemPlanGroupDialogController', ['$scope', 'identityService', 'planService', 'ngDialog', '$ngBootbox',
    function ($scope, identityService, planService, ngDialog, $ngBootbox) {

        //console.log($scope.ngDialogData.objectIdsList);
        var identity = identityService.getIdentity();
        $scope.transferData.selectedObjectId = null;
        $scope.transferData.notes = null;

        //ovdje poslati u servis -> grupu idova objekata
        //u backendu napraviti novi command handler, da za svaki id dodaje u plan

        $scope.saveObjectToPlan = function () {
            $scope.dialogAddGroupToPlan = ngDialog.close();
            planService.saveObjectsGroupToPlan(identity, $scope.ngDialogData.objectIdsList, $scope.transferData).then(function (success) {


                $scope.getPlanWeeklyInfo(false);
                $scope.reloadData();
                $scope.reloadPlanOptions();
                $ngBootbox.customDialog(optionsSuccess);
            },
            function (error) {
                $ngBootbox.customDialog(optionsError);
            })
        }

        //TODO: export u neki template vanjski
        var optionsSuccess = {
            message: 'Uspješno spremljeno.',
            buttons: {
                success: {
                    label: "Ok",
                    className: "btn waves-effect waves-button waves-float waves-info",
                }
            }
        }

        //TODO: export u neki template vanjski
        var optionsError = {
            message: 'Došlo je do greške!',
            buttons: {
                warning: {
                    label: "Ok",
                    className: "btn waves-effect waves-button waves-float waves-danger",
                }
            }
        }
    }
]);