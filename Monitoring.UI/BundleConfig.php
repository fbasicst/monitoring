<?php

    class BundleConfig
    {
        public static function GenerateScriptBundles($version)
        {
            $scriptBundles = array(
                //Libraries
                'Content/libraries/adminPro/scripts/ui.js',
                'Content/libraries/angularJS-v1.5.8/angular-local-storage.min.js',
                'Content/libraries/angular-ui-router-v0.2.13/angular-ui-router.min.js',
                'Content/libraries/bootstrap-v3.0.3/js/bootstrap.min.js',
                'Content/libraries/ngDialog/js/ngDialog.min.js',
                'Content/libraries/underscoreJS-v1.8.3/underscore-min.js',
                'Content/libraries/moment-v2.15.2/moment.min.js',
                'Content/libraries/moment-v2.15.2/moment-with-locales.min.js',
                'Content/libraries/datatables-v1.10.4/js/dataTables.responsive.js',
                'Content/libraries/datatables-v1.10.4/js/dataTables.tableTools.min.js',
                'Content/libraries/datatables-v1.10.4/js/jquery.dataTables.min.js',
                'Content/libraries/angular-datatables-v0.5.4/js/angular-datatables.min.js',
                'Content/libraries/bootstrap-datetimepicker-v4.7.14/js/bootstrap-datetimepicker.min.js',
                'Content/libraries/jquery.inputmask.min/jquery.inputmask.min.js',
                'Content/libraries/bootbox-v4.4.0/js/bootbox.min.js',
                'Content/libraries/ngBootbox.min/js/ngBootbox.min.js',
                'Content/libraries/chartJS-v2.4.0/js/chart.min.js',
                'Content/libraries/angular-chart-v1.0.3/js/angular-chart.min.js',

                'Content/modules/app.js',
                //AdminPro theme js
                'Content/libraries/adminPro/scripts/directives/layout.js',

                //Controllers
                'Content/modules/controllers/appController.js',
                'Content/modules/controllers/logInController.js',
                'Content/modules/controllers/headerController.js',
                'Content/modules/controllers/dashboardController.js',
                'Content/modules/controllers/objectEntryController.js',
                'Content/modules/controllers/objectsController.js',
                'Content/modules/controllers/objectsArchivedController.js',
                'Content/modules/controllers/objectEditController.js',
                'Content/modules/controllers/plansWeeklyController.js',
                'Content/modules/controllers/planWeeklyEditController.js',
                'Content/modules/controllers/plansMonthlyController.js',
                'Content/modules/controllers/plansAnnuallyController.js',
                'Content/modules/controllers/objectEditGeneralInfoDialogController.js',
                'Content/modules/controllers/planWeeklyAddObjectItemPlanGroupDialogController.js',
                'Content/modules/controllers/reportServiceItemsController.js',

                //Services
                'Content/modules/services/authorizationService.js',
                'Content/modules/services/authInterceptorService.js',
                'Content/modules/services/identityService.js',
                'Content/modules/services/masterDataService.js',
                'Content/modules/services/objectService.js',
                'Content/modules/services/planService.js',
                'Content/modules/services/monitoringService.js',
                'Content/modules/services/reportService.js',
                //Directives
                'Content/modules/directives/masksDirective.js',
                'Content/modules/directives/datatablesLazy.js',
                //Filters
                'Content/modules/filters/filters.js'
            );

            foreach ($scriptBundles as $bundle)
            {
                echo '<script src="../../'.$bundle.'?ver='.$version.'"></script>';
            }
        }

        public static function GenerateStyleBundles($version)
        {
            $styleBundles = array(
                'Content/libraries/bootstrap-v3.0.3/css/bootstrap.min.css',
                'Content/libraries/adminPro/styles/font-awesome.css',
                'Content/libraries/adminPro/styles/weather-icons.css',
                'Content/libraries/adminPro/styles/main.css',
                'Content/libraries/adminPro/styles/waves.css',
                'Content/libraries/ngDialog/css/ngDialog-theme-default.css',
                'Content/libraries/ngDialog/css/ngDialog.css',
                'Content/libraries/bootstrap-datetimepicker-v4.7.14/css/bootstrap-datetimepicker.min.css',
                'Content/libraries/datatables-v1.10.4/css/dataTables.responsive.css',
                'Content/libraries/datatables-v1.10.4/css/dataTables.tableTools.css',
                'Content/libraries/datatables-v1.10.4/css/jquery.dataTables.min.css',
                'Content/libraries/angular-datatables-v0.5.4/css/datatables.bootstrap.min.css',
                'Content/libraries/angular-chart-v1.0.3/css/angular-chart.min.css',
                //MyCss
                'Content/css/monitoring.css'
            );

            foreach ($styleBundles as $bundle)
            {
                echo '<link rel="stylesheet" href="../../'.$bundle.'?ver='.$version.'">';
            }
        }
    }