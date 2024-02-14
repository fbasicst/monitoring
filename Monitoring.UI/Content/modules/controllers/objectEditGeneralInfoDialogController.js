'use strict'

webApp.controller('objectEditGeneralInfoDialogController', ['$scope', '$window', 'identityService', 'masterDataService', 'objectService', 'ngDialog', '$ngBootbox',
    function ($scope, $window, identityService, masterDataService, objectService, ngDialog, $ngBootbox) {

        var identity = identityService.getIdentity();
        $scope.yesNoSelectOptions = [{ name: 'Da', value: true }, { name: 'Ne', value: false }];
        $scope.objectEditData = angular.copy($scope.ngDialogData.objectFull.GeneralInfo);

        objectService.getContracts(identity, $scope.objectEditData.CustomerRemoteId).then(function (data){
            $scope.contracts = data;
        });
        masterDataService.getCities(identity).then(function (data){
            $scope.cities = data;
        });
        masterDataService.getObjectAreas(identity).then(function (data){
            $scope.areas = data;
        });
        objectService.getObjectTypes(identity).then(function (types) {
            $scope.objectTypes = types;
        });


        $scope.updateObjectGeneralInfo = function () {
            $scope.dialogEditObject = ngDialog.close();

            objectService.updateObjectGeneralInfo(identity, $scope.objectEditData).then(function (success) {
                $ngBootbox.customDialog(optionsSuccess);
                $scope.getObjectFull();
            },
            function (error) {
                $ngBootbox.customDialog(optionsError);
            });
        }

        $scope.showPDF = function(){
            var fileURL = '';
            objectService.getContractPDF($scope.objectEditData.ContractBarcode)
                .then(function (result) {

                    var file = new Blob([result], { type: 'application/pdf' });
                    var fileURL = $window.URL.createObjectURL(file);
                    $window.open(fileURL);
                });
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